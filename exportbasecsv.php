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
$password = "vdY7v31vk07I0K09";
$dbname = "radius";

// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Nom du fichier CSV à générer
$csvFileName = 'export_radius_data.csv';

// Créer le fichier CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="' . $csvFileName . '"');

// Ouvrir un fichier en écriture (l'envoie au navigateur)
$output = fopen('php://output', 'w');

// Écrire l'en-tête du CSV
fputcsv($output, array('username', 'attribute', 'value', 'name', 'mail', 'department', 'workphone', 'homephone', 'mobile'));

// Requête pour récupérer les données de radcheck
$sqlRadcheck = "SELECT username, attribute, value FROM radcheck";
$resultRadcheck = $conn->query($sqlRadcheck);

if ($resultRadcheck->num_rows > 0) {
    while ($rowRadcheck = $resultRadcheck->fetch_assoc()) {
        $username = $rowRadcheck['username'];
        
        // Requête pour récupérer les données correspondantes dans userinfo
        $sqlUserinfo = "SELECT name, mail, department, workphone, homephone, mobile FROM userinfo WHERE username = ?";
        $stmtUserinfo = $conn->prepare($sqlUserinfo);
        $stmtUserinfo->bind_param("s", $username);
        $stmtUserinfo->execute();
        $resultUserinfo = $stmtUserinfo->get_result();

        if ($resultUserinfo->num_rows > 0) {
            while ($rowUserinfo = $resultUserinfo->fetch_assoc()) {
                // Combiner les données des deux tables et écrire la ligne dans le CSV
                $csvRow = array_merge($rowRadcheck, $rowUserinfo);
                fputcsv($output, $csvRow);
            }
        } else {
            // Si pas de correspondance dans userinfo, écrire une ligne avec les données de radcheck seulement
            fputcsv($output, $rowRadcheck);
        }

        $stmtUserinfo->close();
    }
} else {
    echo "No data found in radcheck.";
}

// Fermer les connexions et le fichier CSV
$conn->close();
fclose($output);
exit;
?>
