<?php
session_start();

// Vérifie si l'utilisateur est authentifié
if (!isset($_SESSION['authenticated'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$ethersFile = '/usr/local/etc/alcasar-ethers';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mac = strtoupper(trim($_POST['mac_address']));
    $ip = trim($_POST['ip_address']);
    $mac = str_replace(':', '-', $mac); // On conserve le format avec tirets

    // Validation MAC avec tirets
    if (!preg_match('/^([0-9A-F]{2}-){5}[0-9A-F]{2}$/', $mac)) {
        $message = "Erreur : adresse MAC invalide.";
    }
    // Validation IP
    elseif (!filter_var($ip, FILTER_VALIDATE_IP)) {
        $message = "Erreur : adresse IP invalide.";
    } else {
        // Vérifie que la MAC ou l'IP n'existent pas déjà
        $exists = false;
        if (file_exists($ethersFile)) {
            $lines = file($ethersFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (stripos($line, $mac) !== false || stripos($line, $ip) !== false) {
                    $exists = true;
                    break;
                }
            }
        }

        if ($exists) {
            $message = "Erreur : cette adresse MAC ou IP existe déjà.";
        } else {
            $entry = $mac . ' ' . $ip . PHP_EOL;
            if (file_put_contents($ethersFile, $entry, FILE_APPEND | LOCK_EX)) {
                // Redémarrer Chilli si besoin :
                // exec("sudo systemctl restart chilli");
                $message = "Enregistrement effectué : $mac associé à $ip.";
            } else {
                $message = "Erreur : impossible d'écrire dans le fichier.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Réservation DHCP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef2f3;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #fff;
            padding: 25px 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            text-align: center;
            width: 400px;
        }
        h2 {
            margin-top: 0;
        }
        input[type="text"], input[type="submit"], .back-button {
            margin-bottom: 15px;
            padding: 10px;
            width: 100%;
            max-width: 300px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .back-button {
            background-color: #2196F3;
            color: white;
            text-decoration: none;
            display: inline-block;
        }
        .back-button:hover {
            background-color: #1e88e5;
        }
        .message {
            font-size: 16px;
            margin-top: 15px;
            color: #333;
        }
        .success { color: green; }
        .error { color: red; }
    </style>
    <script>
        function formatMACAddress(input) {
            input = input.replace(/[^a-fA-F0-9]/g, '').toUpperCase();
            let formatted = '';
            for (let i = 0; i < input.length; i += 2) {
                if (i > 0) formatted += '-';
                formatted += input.substr(i, 2);
            }
            return formatted.substring(0, 17);
        }

        function handleMACInput(event) {
            event.target.value = formatMACAddress(event.target.value);
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Nouvelle réservation DHCP</h2>
        <form method="POST" action="dhcp.php">
            <input type="text" name="mac_address" required placeholder="Adresse MAC (AA-BB-CC-DD-EE-FF)" maxlength="17" oninput="handleMACInput(event)">
            <input type="text" name="ip_address" required placeholder="Adresse IP (ex: 172.16.0.101)">
            <input type="submit" value="Ajouter à la réservation">
        </form>

        <?php if (!empty($message)): ?>
            <div class="message <?= strpos($message, 'Erreur') === false ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <a href="index.php" class="back-button">Retour à l'accueil</a>
    </div>
</body>
</html>
