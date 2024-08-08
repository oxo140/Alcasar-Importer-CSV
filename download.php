<?php
// Chemin vers le répertoire à télécharger
$directory = '/var/www/html/csv/apache';

// Vérifier si le répertoire existe et est accessible
if (!is_dir($directory)) {
    die('Erreur: Répertoire non trouvé.');
}

// Créer une archive temporaire
$tmpFile = tempnam(sys_get_temp_dir(), 'backup_archive');
$archiveName = basename($tmpFile) . '.tar.gz';

// Créer une archive tar.gz de tous les fichiers dans le répertoire
exec("tar -czf $tmpFile -C $directory .");

// Déclencher le téléchargement du fichier d'archive
if (file_exists($tmpFile)) {
    // Télécharger le fichier d'archive
    header('Content-Type: application/gzip');
    header('Content-Disposition: attachment; filename="' . $archiveName . '"');
    header('Content-Length: ' . filesize($tmpFile));
    readfile($tmpFile);

    // Supprimer le fichier temporaire
    unlink($tmpFile);

    // Supprimer tous les fichiers du répertoire original
    array_map('unlink', glob("$directory/*"));
    rmdir($directory);

    exit;
} else {
    die('Erreur: Impossible de créer l\'archive.');
}
?>
