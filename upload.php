<?php
// Vérifier si un fichier a été téléchargé
if (isset($_FILES['fileToUpload'])) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);

    // Déplacer le fichier téléchargé vers le répertoire de destination
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        echo "The file " . htmlspecialchars(basename($_FILES["fileToUpload"]["name"])) . " has been uploaded.<br>";

        // Lire et fragmenter le fichier CSV
        fragmentCSV($target_file);

        // Créer une archive tar.gz des fichiers fragmentés
        $archive_file_name = 'csv_fragments_' . time() . '.tar.gz';
        $archive_path = 'archives/' . $archive_file_name;
        if (!is_dir('archives')) {
            mkdir('archives', 0777, true);
        }
        exec("tar -czvf $archive_path uploads/*");

        echo "Archive created at $archive_path<br>";
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}

// Fonction pour fragmenter le CSV en fichiers séparés par colonne
function fragmentCSV($csvFile) {
    $handle = fopen($csvFile, "r");
    if ($handle === FALSE) {
        die("Cannot open the file " . $csvFile);
    }

    // Lire les en-têtes
    $columns = fgetcsv($handle, 1000, ",");
    if ($columns === FALSE) {
        die("Cannot read the columns from the file " . $csvFile);
    }

    // Créer un fichier pour chaque colonne
    $files = [];
    foreach ($columns as $column) {
        $files[$column] = fopen("uploads/$column.txt", "w");
        if ($files[$column] === FALSE) {
            die("Cannot create the file for column " . $column);
        }
    }

    // Lire les données et les écrire dans les fichiers correspondants
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        foreach ($columns as $index => $column) {
            fwrite($files[$column], $data[$index] . PHP_EOL);
        }
    }

    // Fermer tous les fichiers
    foreach ($files as $file) {
        fclose($file);
    }

    fclose($handle);
}
?>
