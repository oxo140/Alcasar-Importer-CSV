<?php
session_start();

// ───────────────────────────────────────────────────────────────────────────────
// 1. Affichage des erreurs (à commenter ou supprimer en prod)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Vérification de session
if (!isset($_SESSION['authenticated'])) {
    header('Location: login.php');
    exit;
}

// 3. Définition des chemins de fichiers
$correspondanceFile = '/var/www/html/csv/correspondancedhcp.txt';
$ethersFile         = '/usr/local/etc/alcasar-ethers';

// 4. Vérifier l'existence et les droits de lecture/écriture
$errors = [];

// Vérif correspondancedhcp.txt
if (!file_exists($correspondanceFile)) {
    $errors[] = "Le fichier $correspondanceFile n'existe pas.";
} else {
    if (!is_readable($correspondanceFile)) {
        $errors[] = "Le fichier $correspondanceFile n'est pas lisible.";
    }
    if (!is_writable($correspondanceFile)) {
        $errors[] = "Le fichier $correspondanceFile n'est pas accessible en écriture.";
    }
}

// Vérif alcasar-ethers
if (!file_exists($ethersFile)) {
    $errors[] = "Le fichier $ethersFile n'existe pas.";
} else {
    if (!is_readable($ethersFile)) {
        $errors[] = "Le fichier $ethersFile n'est pas lisible.";
    }
    if (!is_writable($ethersFile)) {
        $errors[] = "Le fichier $ethersFile n'est pas accessible en écriture.";
    }
}

// 5. Si une erreur est détectée, on l’affiche puis on stoppe
if (!empty($errors)) {
    echo "<h2>Erreurs d'accès aux fichiers :</h2><ul>";
    foreach ($errors as $err) {
        echo "<li>$err</li>";
    }
    echo "</ul>";
    exit; // stop
}

// 6. Aucune erreur => on génère le ZIP
$tmpFile = tempnam(sys_get_temp_dir(), 'dhcp_');
$zip = new ZipArchive();

if ($zip->open($tmpFile, ZipArchive::CREATE) !== true) {
    // Impossible de créer le ZIP => on le signale
    echo "Impossible de créer l'archive ZIP";
    exit;
}

// -- Ajout de correspondancedhcp.txt converti en CSV
if (file_exists($correspondanceFile)) {
    $lines = file($correspondanceFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $csvContent = "Adresse MAC,Adresse IP,Commentaire\n";
    foreach ($lines as $line) {
        // On suppose que "adresse MAC adresse IP commentaire"
        $parts = preg_split('/\\s+/', $line, 3);
        $mac     = $parts[0] ?? '';
        $ip      = $parts[1] ?? '';
        $comment = $parts[2] ?? '';
        $csvContent .= "$mac,$ip,$comment\n";
    }
    $zip->addFromString("correspondancedhcp.csv", $csvContent);
}

// -- Ajout du fichier alcasar-ethers brut
if (file_exists($ethersFile)) {
    // 2ème paramètre = nom à l'intérieur de l’archive
    $zip->addFile($ethersFile, "alcasar-ethers.txt");
}

$zip->close();

// 7. Envoi de l’archive (on nettoie le buffer avant)
ob_clean();
flush();

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="sauvegarde_dhcp.zip"');
header('Content-Length: ' . filesize($tmpFile));

readfile($tmpFile);
unlink($tmpFile);

// 8. Vider les fichiers : attention, si on arrive ici, c’est que l’archive a été envoyée
file_put_contents($correspondanceFile, '');
file_put_contents($ethersFile, '');

exit;
