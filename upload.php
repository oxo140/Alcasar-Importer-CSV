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

// Fonction pour générer un hash SHA-256 au format crypt
function generate_sha256_crypt($value, $salt = 'rtkdwayv') {
    return crypt($value, '$5$' . $salt);
}

// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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

        if (isset($_POST['overwrite'])) {
            // Étape de confirmation terminée, maintenant vider la base de données
            $conn->query("TRUNCATE TABLE radcheck");
            $conn->query("TRUNCATE TABLE userinfo");
        }

        // Lire le fichier CSV
        $handle = fopen($target_file, "r");
        if ($handle !== FALSE) {
            // Ignorer la ligne d'en-tête
            fgetcsv($handle, 1000, ",");

            if (isset($_POST['submit']) || isset($_POST['overwrite'])) {
                // Lire les données du CSV pour ajout
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $username = $data[1];
                    $attribute = $data[2];
                    $value = generate_sha256_crypt($data[4]); // Chiffrement SHA-256 de la valeur
                    $name = $data[5];
                    $mail = $data[6];
                    $department = $data[7];
                    $workphone = $data[8];
                    $homephone = $data[9];
                    $mobile = $data[10];

                    // Insérer dans radcheck et obtenir l'id auto-incrémenté
                    $radcheck_sql = $conn->prepare("INSERT INTO radcheck (username, attribute, value) VALUES (?, ?, ?)");
                    $radcheck_sql->bind_param("sss", $username, $attribute, $value);
                    if ($radcheck_sql->execute()) {
                        $id = $conn->insert_id;

                        // Insérer dans userinfo avec l'id obtenu
                        $userinfo_sql = $conn->prepare("INSERT INTO userinfo (id, username, name, mail, department, workphone, homephone, mobile) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $userinfo_sql->bind_param("isssssss", $id, $username, $name, $mail, $department, $workphone, $homephone, $mobile);
                        if (!$userinfo_sql->execute()) {
                            echo "Error inserting into userinfo: " . $userinfo_sql->error . "<br>";
                        }
                        $userinfo_sql->close();
                    } else {
                        echo "Error inserting into radcheck: " . $radcheck_sql->error . "<br>";
                    }
                    $radcheck_sql->close();
                }
                echo "Data has been imported successfully.";
            } elseif (isset($_POST['delete'])) {
                // Lire les données du CSV pour suppression
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $username = $data[1];

                    // Supprimer de radcheck
                    $delete_radcheck_sql = $conn->prepare("DELETE FROM radcheck WHERE username = ?");
                    $delete_radcheck_sql->bind_param("s", $username);
                    if (!$delete_radcheck_sql->execute()) {
                        echo "Error deleting from radcheck: " . $delete_radcheck_sql->error . "<br>";
                    }
                    $delete_radcheck_sql->close();

                    // Supprimer de userinfo
                    $delete_userinfo_sql = $conn->prepare("DELETE FROM userinfo WHERE username = ?");
                    $delete_userinfo_sql->bind_param("s", $username);
                    if (!$delete_userinfo_sql->execute()) {
                        echo "Error deleting from userinfo: " . $delete_userinfo_sql->error . "<br>";
                    }
                    $delete_userinfo_sql->close();
                }
                echo "Users have been deleted successfully.";
            }

            fclose($handle);
        } else {
            echo "Cannot open the file.";
        }
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}

// Fermer la connexion à la base de données
$conn->close();
?>
