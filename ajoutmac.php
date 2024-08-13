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
$password = "mdpbasededonnées";
$dbname = "radius";

// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Vérifier le mot de passe -> Connection failed: " . $conn->connect_error);
}

// Fonction pour générer un hash SHA-256 au format crypt
function generate_sha256_crypt($value, $salt = 'rtkdwayv') {
    return crypt($value, '$5$' . $salt);
}

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mac_address = strtoupper(join('-', str_split(str_replace('-', '', $_POST['mac_address']), 2))); // Format xx-xx-xx-xx-xx-xx
    $password = generate_sha256_crypt("password"); // Mot de passe "password" chiffré en SHA-256
    $attribute = "Crypt-Password";
    $op = ":=";
    $name = $_POST['name'];
    $mail = isset($_POST['mail']) ? $_POST['mail'] : null;
    $department = isset($_POST['department']) ? $_POST['department'] : null;
    $workphone = isset($_POST['workphone']) ? $_POST['workphone'] : null;
    $homephone = isset($_POST['homephone']) ? $_POST['homephone'] : null;
    $mobile = isset($_POST['mobile']) ? $_POST['mobile'] : null;

    // Commencer une transaction pour assurer l'intégrité des données
    $conn->begin_transaction();

    try {
        // Insérer dans la table radcheck
        $stmt_radcheck = $conn->prepare("INSERT INTO radcheck (username, attribute, op, value) VALUES (?, ?, ?, ?)");
        $stmt_radcheck->bind_param("ssss", $mac_address, $attribute, $op, $password);
        $stmt_radcheck->execute();

        // Obtenir l'ID auto-incrémenté
        $id = $conn->insert_id;

        // Insérer dans la table userinfo
        $stmt_userinfo = $conn->prepare("INSERT INTO userinfo (id, username, name, mail, department, workphone, homephone, mobile) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_userinfo->bind_param("isssssss", $id, $mac_address, $name, $mail, $department, $workphone, $homephone, $mobile);
        $stmt_userinfo->execute();

        // Commit de la transaction
        $conn->commit();

        echo "Adresse MAC et informations utilisateur ajoutées avec succès.";

    } catch (Exception $e) {
        // Annuler la transaction en cas d'erreur
        $conn->rollback();
        echo "Erreur lors de l'ajout : " . $e->getMessage();
    }

    // Fermer les statements
    $stmt_radcheck->close();
    $stmt_userinfo->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ajouter une adresse MAC</title>
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
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        input[type="text"], input[type="email"], input[type="tel"] {
            margin-bottom: 10px;
            padding: 10px;
            width: 100%;
            max-width: 300px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        input[type="submit"], .back-button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        input[type="submit"]:hover, .back-button:hover {
            background-color: #45a049;
        }
        label {
            align-self: flex-start;
            margin-bottom: 5px;
        }
        .required {
            color: red;
        }
        .back-button {
            background-color: #2196F3;
            margin-top: 20px;
            text-decoration: none;
            padding: 10px 20px;
            display: inline-block;
            color: white;
        }
        .back-button:hover {
            background-color: #1e88e5;
        }
    </style>
    <script>
        // Fonction pour formater l'adresse MAC
        function formatMACAddress(input) {
            input = input.replace(/[^a-fA-F0-9]/g, '').toUpperCase(); // Supprimer tous les caractères non-hexadécimaux
            let formatted = '';
            for (let i = 0; i < input.length; i += 2) {
                if (i > 0) {
                    formatted += '-';
                }
                formatted += input.substr(i, 2);
            }
            return formatted.substring(0, 17); // Limiter à 17 caractères (xx-xx-xx-xx-xx-xx)
        }

        // Fonction pour gérer la saisie de l'utilisateur
        function handleMACInput(event) {
            event.target.value = formatMACAddress(event.target.value);
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Ajouter une adresse MAC</h2>
        <form action="ajoutmac.php" method="post">
            <label for="mac_address">Adresse MAC <span class="required">*</span> :</label>
            <input type="text" id="mac_address" name="mac_address" required oninput="handleMACInput(event)" maxlength="17" placeholder="XX-XX-XX-XX-XX-XX">

            <label for="name">Nom complet <span class="required">*</span> :</label>
            <input type="text" id="name" name="name" required>

            <label for="mail">Email :</label>
            <input type="email" id="mail" name="mail">

            <label for="department">Département :</label>
            <input type="text" id="department" name="department">

            <label for="workphone">Téléphone professionnel :</label>
            <input type="tel" id="workphone" name="workphone">

            <label for="homephone">Téléphone personnel :</label>
            <input type="tel" id="homephone" name="homephone">

            <label for="mobile">Mobile :</label>
            <input type="tel" id="mobile" name="mobile">

            <input type="submit" value="Ajouter">
        </form>
        <!-- Bouton pour retourner à la page d'accueil -->
        <a href="index.php" class="back-button">Retour à l'accueil</a>
    </div>
</body>
</html>
