<?php
session_start();

if (!isset($_SESSION['authenticated'])) {
    header('Location: login.php');
    exit;
}

$correspondanceFile = '/var/www/html/csv/correspondancedhcp.txt';

if (file_exists($correspondanceFile)) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="correspondancedhcp.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Adresse MAC', 'Adresse IP', 'Commentaire']); // En-tête

    $lines = file($correspondanceFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $parts = preg_split('/\s+/', $line, 3);
        $mac = $parts[0] ?? '';
        $ip = $parts[1] ?? '';
        $comment = $parts[2] ?? '';
        fputcsv($output, [$mac, $ip, $comment]);
    }

    fclose($output);
    exit;
} else {
    echo "Fichier de correspondance introuvable.";
}
