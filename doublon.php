<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utilisateurs Dupliqués</title>
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Utilisateurs Dupliqués</h1>

        <?php
        // Configuration de la base de données
        $servername = "localhost";
        $username = "radius";
        $password = "vdY7v31vk07I0K09";
        $dbname = "radius";

        // Connexion à la base de données
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Requête pour trouver les usernames dupliqués
        $sql = "SELECT username
                FROM radcheck
                GROUP BY username
                HAVING COUNT(*) > 1";
        $result = $conn->query($sql);

        $usernames_to_keep = [];

        if ($result->num_rows > 0) {
            echo '<p class="warning">Les utilisateurs suivants apparaissent plus d\'une fois :</p>';
            echo '<ul>';
            while ($row = $result->fetch_assoc()) {
                $username = htmlspecialchars($row['username']);
                echo '<li>' . $username . '</li>';
                $usernames_to_keep[] = $username;
            }
            echo '</ul>';

            // Convertir les usernames en format JSON pour la suppression
            $usernames_json = json_encode($usernames_to_keep);
            echo '<form action="doublon.php" method="post">';
            echo '<input type="hidden" name="usernames" value=\'' . htmlspecialchars($usernames_json) . '\'>';
            echo '<button type="submit" name="delete">Supprimer les doublons</button>';
            echo '</form>';
        } else {
            echo '<p>Aucun utilisateur dupliqué trouvé.</p>';
        }

        // Traitement de la demande de suppression
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
            $usernames_to_keep = json_decode($_POST['usernames'], true);

            if (!empty($usernames_to_keep)) {
                foreach ($usernames_to_keep as $username) {
                    // Conserver une occurrence et supprimer les autres
                    $stmt_delete_extra = $conn->prepare("
                        DELETE FROM radcheck
                        WHERE username = ? AND id NOT IN (
                            SELECT id FROM (
                                SELECT MIN(id) as id
                                FROM radcheck
                                WHERE username = ?
                                GROUP BY username
                            ) as subquery
                        )
                    ");
                    $stmt_delete_extra->bind_param("ss", $username, $username);
                    $stmt_delete_extra->execute();
                    $stmt_delete_extra->close();

                    $stmt_delete_userinfo_extra = $conn->prepare("
                        DELETE FROM userinfo
                        WHERE username = ? AND id NOT IN (
                            SELECT id FROM (
                                SELECT MIN(id) as id
                                FROM userinfo
                                WHERE username = ?
                                GROUP BY username
                            ) as subquery
                        )
                    ");
                    $stmt_delete_userinfo_extra->bind_param("ss", $username, $username);
                    $stmt_delete_userinfo_extra->execute();
                    $stmt_delete_userinfo_extra->close();
                }

                echo '<p>Les doublons ont été supprimés avec succès.</p>';
            }
        }

        $conn->close();
        ?>
        <p><a href="index.php">Retour à la page d\'importation</a></p>
    </div>
</body>
</html>
