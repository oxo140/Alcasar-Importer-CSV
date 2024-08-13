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
        .confirm-section, .final-confirm-section, .final-input-section {
            display: none;
            margin-top: 20px;
        }
        .confirm-button {
            background-color: #FFC107; /* Jaune */
            color: black;
        }
        .confirm-button:hover {
            background-color: #ffca28; /* Jaune clair */
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Gérer vos fichiers CSV</h2>
        <form action="upload.php" method="post" enctype="multipart/form-data" id="uploadForm">
            Choisir votre fichier CSV à importer:
            <input type="file" name="fileToUpload" id="fileToUpload">
            <input type="submit" value="Fusionner le CSV avec la base" name="submit" class="merge">
            <input type="submit" value="Reverse CSV" name="delete" class="delete">
            <input type="submit" value="Écraser la base et importer le CSV" name="overwrite" class="overwrite">
        </form>

        <!-- Confirmation Section -->
        <form action="upload.php" method="post" id="confirmForm">
            <div class="confirm-section" id="confirmSection">
                <p>Vous êtes sûr de vouloir écraser la base et importer le CSV ?</p>
                <button type="submit" name="confirmYes" class="confirm-button">Oui</button>
                <button type="submit" name="confirmNo" class="confirm-button">Non</button>
            </div>
        </form>

        <!-- Final Confirmation Section -->
        <form action="upload.php" method="post" id="finalConfirmForm">
            <div class="final-confirm-section" id="finalConfirmSection">
                <p>Vous êtes vraiment sûr ?</p>
                <button type="submit" name="finalConfirmYes" class="confirm-button">Oui</button>
                <button type="submit" name="finalConfirmNo" class="confirm-button">Non</button>
            </div>
        </form>

        <!-- Final Input Section -->
        <form action="upload.php" method="post">
            <div class="final-input-section" id="finalInputSection">
                <p>Veuillez entrer "confirmer" pour valider :</p>
                <input type="text" name="confirmationInput" id="confirmationInput">
                <button type="submit" name="confirmFinal" class="confirm-button">Confirmer</button>
            </div>
        </form>

        <form action="download.php" method="post">
            <button type="submit" class="download">Télécharger la base en CSV</button>
        </form>
    </div>
</body>
</html>
