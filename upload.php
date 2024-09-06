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
$password = "db_password";
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
        $message = "The file " . htmlspecialchars(basename($_FILES["fileToUpload"]["name"])) . " has been uploaded.<br>";

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
                $groupname = isset($_POST['groupname']) ? $_POST['groupname'] : '';

                if (empty($groupname)) {
                    die("Veuillez sélectionner un groupname.");
                }

                // Préparer la requête SQL pour éviter les doublons
                $insert_sql = $conn->prepare("INSERT IGNORE INTO radusergroup (username, groupname, priority) VALUES (?, ?, 1)");

                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $username = $data[0];
                    $attribute = $data[1];
                    $value = generate_sha256_crypt($data[2]); // Chiffrement SHA-256 de la valeur
                    $name = $data[3];
                    $mail = $data[4];
                    $department = $data[5];
                    $workphone = $data[6];
                    $homephone = $data[7];
                    $mobile = $data[8];

                    // Insérer dans radcheck et obtenir l'id auto-incrémenté
                    $radcheck_sql = $conn->prepare("INSERT INTO radcheck (username, attribute, value) VALUES (?, ?, ?)");
                    $radcheck_sql->bind_param("sss", $username, $attribute, $value);
                    if ($radcheck_sql->execute()) {
                        $id = $conn->insert_id;

                        // Insérer dans userinfo avec l'id obtenu
                        $userinfo_sql = $conn->prepare("INSERT INTO userinfo (id, username, name, mail, department, workphone, homephone, mobile) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $userinfo_sql->bind_param("isssssss", $id, $username, $name, $mail, $department, $workphone, $homephone, $mobile);
                        if (!$userinfo_sql->execute()) {
                            $message .= "Error inserting into userinfo: " . $userinfo_sql->error . "<br>";
                        }
                        $userinfo_sql->close();

                        // Insérer les données dans radreply
                        $radreply_sql = $conn->prepare("INSERT INTO radreply (username, attribute, op, value) VALUES (?, 'Alcasar-Status-Page-Must-Stay-Open', '=', '2')");
                        $radreply_sql->bind_param("s", $username);
                        if (!$radreply_sql->execute()) {
                            $message .= "Error inserting into radreply: " . $radreply_sql->error . "<br>";
                        }
                        $radreply_sql->close();

                        // Insérer dans radusergroup avec prévention des doublons
                        $insert_sql->bind_param("ss", $username, $groupname);
                        if (!$insert_sql->execute()) {
                            $message .= "Error inserting into radusergroup: " . $insert_sql->error . "<br>";
                        }
                    } else {
                        $message .= "Error inserting into radcheck: " . $radcheck_sql->error . "<br>";
                    }
                    $radcheck_sql->close();
                }
                $message .= "Data has been imported successfully.";
                $insert_sql->close();
            } elseif (isset($_POST['delete'])) {
                // Lire les données du CSV pour suppression
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $username = $data[0];

                    // Supprimer de radcheck
                    $delete_radcheck_sql = $conn->prepare("DELETE FROM radcheck WHERE username = ?");
                    $delete_radcheck_sql->bind_param("s", $username);
                    if (!$delete_radcheck_sql->execute()) {
                        $message .= "Error deleting from radcheck: " . $delete_radcheck_sql->error . "<br>";
                    }
                    $delete_radcheck_sql->close();

                    // Supprimer de userinfo
                    $delete_userinfo_sql = $conn->prepare("DELETE FROM userinfo WHERE username = ?");
                    $delete_userinfo_sql->bind_param("s", $username);
                    if (!$delete_userinfo_sql->execute()) {
                        $message .= "Error deleting from userinfo: " . $delete_userinfo_sql->error . "<br>";
                    }
                    $delete_userinfo_sql->close();
                }
                $message .= "Users have been deleted successfully.";
            }

            fclose($handle);
        } else {
            $message .= "Cannot open the file.";
        }
    } else {
        $message .= "Sorry, there was an error uploading your file.";
    }
}

// Fermer la connexion à la base de données
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestion des utilisateurs</title>
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
            width: 400px;
        }
        h2 {
            margin-top: 0;
        }
        .message {
            margin-top: 20px;
            font-size: 16px;
            color: #333;
            text-align: center; /* Centre le texte du message */
        }
        .message.success {
            color: green;
        }
        .message.error {
            color: red;
        }
        .back-button {
            background-color: #2196F3;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            text-decoration: none;
            display: inline-block;
        }
        .back-button:hover {
            background-color: #1e88e5;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Gestion des utilisateurs</h2>
        <!-- Affichage du message de succès ou d'erreur -->
        <?php if (isset($message) && $message): ?>
            <div class="message <?php echo strpos($message, 'Error') === false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <!-- Bouton pour retourner à l'accueil -->
        <a href="index.php" class="back-button">Retour à l'accueil</a>
    </div>
</body>
</html>
