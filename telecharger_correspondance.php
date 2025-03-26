<?php
session_start();

if (!isset($_SESSION['authenticated'])) {
    header('Location: login.php');
    exit;
}

$correspondanceFile = '/var/www/html/csv/correspondancedhcp.txt';

if (file_exists($correspondanceFile)) {
    header('Content-Description: File Transfer');
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="correspondancedhcp.txt"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($correspondanceFile));
    readfile($correspondanceFile);
    exit;
} else {
    echo "Fichier de correspondance introuvable.";
}
