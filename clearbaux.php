<?php
session_start();

if (!isset($_SESSION['authenticated'])) {
    header('Location: login.php');
    exit;
}

$correspondanceFile = '/var/www/html/csv/correspondancedhcp.txt';
$ethersFile = '/usr/local/etc/alcasar-ethers';

// 🔽 Téléchargement de la correspondance au format CSV
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="sauvegarde_dhcp.zip"');

$zip = new ZipArchive();
$tmpFile = tempnam(sys_get_temp_dir(), 'dhcp_');

if ($zip->open($tmpFile, ZipArchive::CREATE) !== true) {
    die("Impossible de créer l'archive ZIP");
}

// Ajout du fichier correspondance converti en CSV
if (file_exists($correspondanceFile)) {
    $lines = file($correspondanceFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $csvContent = "Adresse MAC,Adresse IP,Commentaire\n";

    foreach ($lines as $line) {
        $parts = preg_split('/\\s+/', $line, 3);
        $mac = $parts[0] ?? '';
        $ip = $parts[1] ?? '';
        $comment = $parts[2] ?? '';
        $csvContent .= "$mac,$ip,$comment\n";
    }

    $zip->addFromString("correspondancedhcp.csv", $csvContent);
}

// Ajout du fichier alcasar-ethers brut
if (file_exists($ethersFile)) {
    $zip->addFile($ethersFile, "alcasar-ethers.txt");
}

$zip->close();

// Envoi de l’archive zip
readfile($tmpFile);
unlink($tmpFile);

// 🔁 Nettoyage (vider les deux fichiers)
file_put_contents($correspondanceFile, '');
file_put_contents($ethersFile, '');
exit;

