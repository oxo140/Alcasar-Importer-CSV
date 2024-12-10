<?php
session_start();

// Vérifie si l'utilisateur est authentifié
if (!isset($_SESSION['authenticated'])) {
    header('Location: login.php');
    exit;
}

// Configuration de la base de données
$servername = "localhost";
$username = "radius";
$password = "w7LpEGxvk6nGmCOI";
$dbname = "radius";

// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Vérifier le mot de passe -> Connection failed: " . $conn->connect_error);
}

// Initialisation des variables
$months = isset($_POST['months']) ? intval($_POST['months']) : 1;
$usernames_to_delete = [];

// Requête pour trouver les utilisateurs non actifs avec jointure sur userinfo
$sql = "SELECT radcheck.username, COALESCE(userinfo.name, 'Non spécifié') AS name
        FROM radcheck
        LEFT JOIN userinfo ON radcheck.username = userinfo.username
        WHERE radcheck.username NOT IN (
            SELECT DISTINCT username
            FROM radacct
            WHERE acctstarttime BETWEEN FROM_UNIXTIME(UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ? MONTH)))
            AND FROM_UNIXTIME(UNIX_TIMESTAMP(LAST_DAY(DATE_SUB(NOW(), INTERVAL ? MONTH))))
        )";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $months, $months);
$stmt->execute();
$result = $stmt->get_result();

// Si le bouton "Exporter CSV" est cliqué
if (isset($_POST['export_csv'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="utilisateurs_inactifs.csv"');

    $output = fopen('php://output', 'w');

    // Écrire les en-têtes dans le CSV
    fputcsv($output, ['Username', 'Name']);

    // Ajouter les données dans le CSV
    while ($row = $result->fetch_assoc()) {
        $username = $row['username'];
        $name = $row['name'];
        fputcsv($output, [$username, $name]);
    }

    fclose($output);
    exit; // Stopper le script après l'export
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utilisateurs Inactifs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: auto;
        }
        .warning {
            color: red;
            font-weight: bold;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            margin-bottom: 5px;
        }
        a {
            color: #2196F3;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        button {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #e53935;
        }
        .yellow-button {
            background-color: #FFC107;
            color: black;
        }
        .yellow-button:hover {
            background-color: #ffca28;
        }
        .form-control {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Utilisateurs Inactifs</h1>

        <form action="" method="post">
            <label for="months">Choisir le nombre de mois d'inactivité :</label>
            <input type="number" id="months" name="months" value="<?php echo htmlspecialchars($months); ?>" min="1">
            <button type="submit" class="yellow-button">Afficher les utilisateurs</button>
            <button type="submit" name="export_csv" class="yellow-button">Exporter en CSV</button>
        </form>

        <?php
        if ($result->num_rows > 0) {
            echo '<p class="warning">Attention, les utilisateurs jamais connectés apparaissent comme non actifs (comprend ceux qui viennent d\'être créés)</p>';
            echo '<p class="warning">Les utilisateurs suivants n\'ont pas été actifs depuis ' . htmlspecialchars($months) . ' mois :</p>';
            echo '<ul>';
            while ($row = $result->fetch_assoc()) {
                $username = htmlspecialchars($row['username']);
                $name = htmlspecialchars($row['name']);
                echo '<li>' . $username . ' - ' . $name . '</li>';
                $usernames_to_delete[] = $username;
            }
            echo '</ul>';

            // Convertir les usernames en format JSON pour la suppression
            $usernames_json = json_encode($usernames_to_delete);
            echo '<form action="" method="post">';
            echo '<input type="hidden" name="usernames" value=\'' . htmlspecialchars($usernames_json) . '\'>';
            echo '<button type="submit" name="delete">Supprimer ces utilisateurs</button>';
            echo '</form>';
        } else {
            echo '<p>Aucun utilisateur inactif trouvé pour ' . htmlspecialchars($months) . ' mois.</p>';
        }

        // Traitement de la demande de suppression
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
            $usernames_to_delete = json_decode($_POST['usernames'], true);

            if (!empty($usernames_to_delete)) {
                foreach ($usernames_to_delete as $username) {
                    // Supprimer les utilisateurs de la table radcheck
                    $stmt_delete = $conn->prepare("DELETE FROM radcheck WHERE username = ?");
                    $stmt_delete->bind_param("s", $username);
                    $stmt_delete->execute();
                    $stmt_delete->close();

                    // Supprimer les utilisateurs de la table userinfo (si applicable)
                    $stmt_delete_userinfo = $conn->prepare("DELETE FROM userinfo WHERE username = ?");
                    $stmt_delete_userinfo->bind_param("s", $username);
                    $stmt_delete_userinfo->execute();
                    $stmt_delete_userinfo->close();
                }

                echo '<p>Les utilisateurs ont été supprimés avec succès.</p>';
            }
        }

        $conn->close();
        ?>
        <p><a href="index.php">Retour à la page d'importation</a></p>
    </div>
</body>
</html>
