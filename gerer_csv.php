<?php
session_start();

// Vérifie si l'utilisateur est authentifié
if (!isset($_SESSION['authenticated'])) {
    header('Location: login.php');
    exit;
}

<!DOCTYPE html>
<html>
<head>
    <title>Gérer CSV</title>
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
            width: 100%;
            max-width: 600px;
        }
        h2 {
            margin-top: 0;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        input[type="file"] {
            margin-bottom: 10px;
        }
        input[type="submit"], button {
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 10px;
            width: 100%;
        }
        input[type="submit"].merge {
            background-color: #4CAF50;
        }
        input[type="submit"].merge:hover {
            background-color: #45a049;
        }
        input[type="submit"].delete {
            background-color: #f44336;
        }
        input[type="submit"].delete:hover {
            background-color: #e53935;
        }
        input[type="submit"].overwrite {
            background-color: #FFC107; /* Jaune */
        }
        input[type="submit"].overwrite:hover {
            background-color: #ffca28; /* Jaune clair */
        }
        button.download {
            background-color: #2196F3;
        }
        button.download:hover {
            background-color: #1e88e5;
        }
        .confirmation-section {
            margin-top: 20px;
        }
        .confirmation-section p {
            margin: 0 0 10px;
            text-align: center;
        }
        .confirmation-section input[type="text"] {
            text-align: center; /* Centre le texte à l'intérieur du champ */
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Gérer vos fichiers CSV</h2>
        <form action="upload.php" method="post" enctype="multipart/form-data">
            Choisir votre fichier CSV à importer:
            <input type="file" name="fileToUpload" id="fileToUpload">
            <input type="submit" value="Fusionner le CSV avec la base" name="submit" class="merge">
            <input type="submit" value="Reverse CSV" name="delete" class="delete">
            
            <!-- Champ de confirmation -->
            <div class="confirmation-section">
                <p>Pour écraser la base et importer le CSV, entrez "confirmer" :</p>
                <input type="text" id="confirmationInput" placeholder="Entrez 'confirmer'">
                <input type="submit" value="Écraser la base et importer le CSV" name="overwrite" class="overwrite" id="overwriteButton" disabled>
            </div>
        </form>

        <!-- Formulaire pour télécharger la base en CSV -->
        <form action="exportbasecsv.php" method="post">
            <button type="submit" class="download">Télécharger la base en CSV</button>
        </form>
    </div>

    <script>
        const confirmationInput = document.getElementById('confirmationInput');
        const overwriteButton = document.getElementById('overwriteButton');

        confirmationInput.addEventListener('input', function() {
            // Active le bouton seulement si le champ contient "confirmer"
            if (confirmationInput.value.trim().toLowerCase() === 'confirmer') {
                overwriteButton.disabled = false;
            } else {
                overwriteButton.disabled = true;
            }
        });
    </script>
</body>
</html>
