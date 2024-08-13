<?php
session_start();

// Vérifie si l'utilisateur est authentifié
if (!isset($_SESSION['authenticated'])) {
    header('Location: login.php');
    exit;
}

// Fonction pour exécuter une commande shell et retourner le résultat
function executeShellCommand($command) {
    $output = [];
    $returnVar = null;
    exec($command, $output, $returnVar);
    return ['output' => $output, 'return_var' => $returnVar];
}

// Variables et configurations
$dir = '/var/www/html/csv/apache/';
$tmpSql = $dir . 'connexion.log';

// Connexion à la base de données
$servername = "localhost";
$username = "root"; // Utilisez 'root' pour le test
$password = "mdp root dans /root/ALCASAR-password.txt";
$dbname = "radius";

$mysqli = new mysqli($servername, $username, $password, $dbname);

if ($mysqli->connect_error) {
    die("Erreur de connexion: " . $mysqli->connect_error);
}

if (isset($_GET['start_date'])) {
    // Sélectionner la date depuis le formulaire
    $startDate = $_GET['start_date'];
    $endDate = date('Y-m-d H:i:s'); // Date actuelle

    // Préparer la requête SQL en fonction de la date
    $query = "SELECT username, callingstationid, framedipaddress, acctstarttime, acctstoptime, acctinputoctets, acctoutputoctets, acctterminatecause 
              FROM radacct 
              WHERE acctstarttime >= '$startDate' AND acctstarttime <= '$endDate' 
              ORDER BY acctstarttime INTO OUTFILE '$tmpSql' 
              FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n';";

    // Exécuter la requête SQL
    if (!$mysqli->query($query)) {
        die("Erreur lors de l'exécution de la requête SQL: " . $mysqli->error);
    }

    // Téléchargement du fichier CSV
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="historique-de-connexion.csv"');
    readfile($tmpSql);

    // Supprimer le fichier après le téléchargement
    unlink($tmpSql);

    // Fermer la connexion à la base de données
    $mysqli->close();
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Générer Logs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        input[type="date"] {
            margin-bottom: 10px;
            padding: 10px;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #FFC107;
            color: black;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #ffca28;
        }
        .warning {
            color: #e53935;
            margin-bottom: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Générer les logs d'imputabilité</h2>
        <p class="warning">Attention, une date lointaine peut être longue à générer.</p>
        <form action="genererlog.php" method="get">
            <label for="start_date">Date de début:</label>
            <input type="date" id="start_date" name="start_date" min="2022-01-01" value="<?= date('Y-m-d', strtotime('-24 hours')) ?>" required />
            <button type="submit">Télécharger CSV</button>
        </form>
    </div>
</body>
</html>
