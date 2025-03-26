<?php
session_start();

if (!isset($_SESSION['authenticated'])) {
    header('Location: login.php');
    exit;
}

function removeAccents($string) {
    return strtr($string, [
        'à' => 'a', 'â' => 'a', 'ä' => 'a', 'á' => 'a', 'ã' => 'a', 'å' => 'a',
        'ç' => 'c',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'î' => 'i', 'ï' => 'i', 'í' => 'i', 'ì' => 'i',
        'ô' => 'o', 'ö' => 'o', 'ò' => 'o', 'ó' => 'o', 'õ' => 'o',
        'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ú' => 'u',
        'ÿ' => 'y', 'ñ' => 'n',
        'À' => 'A', 'Â' => 'A', 'Ä' => 'A', 'Á' => 'A', 'Ã' => 'A', 'Å' => 'A',
        'Ç' => 'C',
        'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
        'Î' => 'I', 'Ï' => 'I', 'Í' => 'I', 'Ì' => 'I',
        'Ô' => 'O', 'Ö' => 'O', 'Ò' => 'O', 'Ó' => 'O', 'Õ' => 'O',
        'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ú' => 'U',
        'Ÿ' => 'Y', 'Ñ' => 'N'
    ]);
}

$message = '';
$debugLog = [];
$ethersFile = '/usr/local/etc/alcasar-ethers';
$correspondanceFile = '/var/www/html/csv/correspondancedhcp.txt';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mac_address'], $_POST['ip_address'])) {
    $mac = strtoupper(trim($_POST['mac_address']));
    $ip = trim($_POST['ip_address']);
    $mac = str_replace(':', '-', $mac);

    if (!preg_match('/^([0-9A-F]{2}-){5}[0-9A-F]{2}$/', $mac)) {
        $message = "Erreur : adresse MAC invalide.";
    } elseif (!filter_var($ip, FILTER_VALIDATE_IP)) {
        $message = "Erreur : adresse IP invalide.";
    } else {
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
            $message = "Erreur : cette adresse MAC ou IP existe deja.";
        } else {
            $entry = $mac . ' ' . $ip . PHP_EOL;
            $entryWithComment = $mac . ' ' . $ip . ' ' . 'Ajout manuel' . PHP_EOL;

            if (file_put_contents($ethersFile, $entry, FILE_APPEND | LOCK_EX)) {
                file_put_contents($correspondanceFile, $entryWithComment, FILE_APPEND | LOCK_EX);
                $message = "Enregistrement effectue : $mac associe a $ip.";
            } else {
                $message = "Erreur : impossible d'ecrire dans le fichier.";
            }
        }
    }
}

if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
    $filename = $_FILES['csv_file']['name'];
    $ext = pathinfo($filename, PATHINFO_EXTENSION);

    if (strtolower($ext) !== 'csv') {
        $message = "Erreur : le fichier doit etre au format CSV.";
    } else {
        $csvRaw = file_get_contents($_FILES['csv_file']['tmp_name']);
        $sep = (substr_count($csvRaw, ';') > substr_count($csvRaw, ',')) ? ';' : ',';
        $csv = fopen($_FILES['csv_file']['tmp_name'], 'r');
        $imported = 0;
        $skipped = 0;

        if ($csv) {
            $firstLine = true;
            while (($data = fgetcsv($csv, 1000, $sep)) !== false) {
                $debugLog[] = "Ligne brute : " . implode(' | ', $data);

                if ($firstLine) {
                    $firstLine = false;
                    continue;
                }
                if (count($data) >= 2) {
                    $mac = strtoupper(trim(str_replace(':', '-', $data[0])));
                    $ip = trim(str_replace(',', '.', $data[1]));
                    $comment = isset($data[2]) ? trim($data[2]) : '';
                    $mac = removeAccents($mac);
                    $ip = removeAccents($ip);
                    $comment = removeAccents($comment);

                    if (preg_match('/^([0-9A-F]{2}-){5}[0-9A-F]{2}$/', $mac) && filter_var($ip, FILTER_VALIDATE_IP)) {
                        $exists = false;
                        $lines = file($ethersFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                        foreach ($lines as $line) {
                            if (stripos($line, $mac) !== false || stripos($line, $ip) !== false) {
                                $exists = true;
                                break;
                            }
                        }
                        if (!$exists) {
                            $entry = $mac . ' ' . $ip . PHP_EOL;
                            $entryWithComment = $mac . ' ' . $ip . ' ' . $comment . PHP_EOL;

                            file_put_contents($ethersFile, $entry, FILE_APPEND | LOCK_EX);

                            $debugLog[] = "Chemin du fichier correspondance: $correspondanceFile";
                            if (!is_writable(dirname($correspondanceFile))) {
                                $debugLog[] = "⚠️ Le dossier n'est pas accessible en ecriture par le serveur web.";
                            }
                            if (file_exists($correspondanceFile) && !is_writable($correspondanceFile)) {
                                $debugLog[] = "⚠️ Le fichier existe mais n'est pas accessible en ecriture.";
                            }

                            if (file_put_contents($correspondanceFile, $entryWithComment, FILE_APPEND | LOCK_EX) !== false) {
                                $debugLog[] = "Ajout dans correspondancedhcp : $entryWithComment";
                            } else {
                                $debugLog[] = "⚠️ echec d'ajout dans correspondancedhcp pour : $entryWithComment";
                            }

                            $imported++;
                        } else {
                            $skipped++;
                        }
                    } else {
                        $skipped++;
                    }
                }
            }
            fclose($csv);

            $message = "Importation CSV terminee. $imported adresse(s) ajoutee(s), $skipped ignoree(s).\n" . implode("\n", $debugLog);
        } else {
            $message = "Erreur : impossible de lire le fichier CSV.";
        }
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Reservation DHCP</title>
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
            width: 600px;
            overflow: auto;
        }
        h2 {
            margin-top: 0;
        }
        input[type="text"], input[type="submit"], .back-button, .download-button, input[type="file"] {
            margin-bottom: 15px;
            padding: 10px;
            width: 100%;
            max-width: 400px;
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
        .back-button, .download-button {
            background-color: #2196F3;
            color: white;
            text-decoration: none;
            display: inline-block;
            padding: 10px;
            border-radius: 5px;
            margin: 10px auto;
        }
        .back-button:hover, .download-button:hover {
            background-color: #1e88e5;
        }
        .message {
            font-size: 14px;
            margin-top: 15px;
            color: #333;
            text-align: left;
            white-space: pre-wrap;
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
        <h2>Nouvelle reservation DHCP</h2>
        <form method="POST" action="dhcp.php">
            <input type="text" name="mac_address" required placeholder="Adresse MAC (AA-BB-CC-DD-EE-FF)" maxlength="17" oninput="handleMACInput(event)">
            <input type="text" name="ip_address" required placeholder="Adresse IP (ex: 172.16.0.101)">
            <input type="submit" value="Ajouter a la reservation">
        </form>

        <form method="POST" action="dhcp.php" enctype="multipart/form-data">
            <input type="file" name="csv_file" accept=".csv" required>
            <input type="submit" value="Importer depuis CSV">
        </form>

        <form method="POST" action="telecharger_correspondance.php">
            <input type="submit" class="download-button" value="Télécharger la correspondance DHCP">
        </form>

        <form method="POST" action="clearbaux.php">
            <input type="submit" class="download-button" value="Vider les baux DHCP">
        </form>

        <?php if (!empty($message)): ?>
            <div class="message <?= strpos($message, 'Erreur') === false ? 'success' : 'error' ?>">
                <?= nl2br(htmlspecialchars($message)) ?>
            </div>
        <?php endif; ?>

        <div class="message" style="color: red; font-weight: bold; text-align: center; margin-top: 20px;">
            ⚠️ Redémarrer le service chilli ou le serveur pour une prise en compte.
        </div>

        <a href="index.php" class="back-button">Retour à l'accueil</a>
    </div>
</body>
</html>

