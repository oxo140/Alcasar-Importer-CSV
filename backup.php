<?php
session_start();

// Vérifie si l'utilisateur est authentifié
if (!isset($_SESSION['authenticated'])) {
    header('Location: login.php');
    exit;
}
?>

<?php

// Chemin vers le répertoire à vider et où Sauvegarde.sh sera exécuté
$directory = '/var/www/html/csv/apache';

// Supprimer tous les fichiers du répertoire
array_map('unlink', glob("$directory/*"));


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['backup'])) {
    // Message de début
    echo "Début de l'extraction de la base de données...<br>";

    // Exécuter le script bash avec l'option --dump pour extraire la base de données
    $output = shell_exec('bash /var/www/html/csv/Sauvegarde.sh --dump 2>&1');
    echo "<pre>$output</pre>"; // Afficher la sortie du script bash pour le débogage

    // Chemin vers le répertoire où le fichier de sauvegarde est créé
    $backupDir = '/var/www/html/csv/apache';

    // Trouver le fichier de sauvegarde le plus récent
    $backupFilePattern = "$backupDir/alcasar-users-database-*.tar.gz";
    $backupFiles = glob($backupFilePattern);
    
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Exporter et Télécharger la Sauvegarde</title>
    <style>

        .button-container a.button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
            margin-bottom: 10px; /* Espacement entre les boutons */
            width: 100%; /* Pleine largeur des boutons */
            text-align: center; /* Centrer le texte des boutons */
            transition: background-color 0.3s ease;
        }
        .button-container a.button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="button-container">
                <a href="download.php" class="button">Télécharger l'archive complète</a>
                <!-- Ajoutez ici d'autres boutons si nécessaire -->
            </div>
        </form>
    </div>
</body>
</html>
