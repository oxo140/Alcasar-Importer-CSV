<!DOCTYPE html>
<html>
<head>
    <title>Importer CSV</title>
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
        }
        h2 {
            margin-top: 0;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        input[type="file"] {
            margin-bottom: 10px;
        }
        input[type="submit"], button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 10px;
        }
        input[type="submit"]:hover, button:hover {
            background-color: #45a049;
        }
        input[type="submit"].delete {
            background-color: #f44336;
        }
        input[type="submit"].delete:hover {
            background-color: #e53935;
        }
        button {
            background-color: #2196F3;
        }
        button:hover {
            background-color: #1e88e5;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Importer votre fichier CSV</h2>
        <form action="upload.php" method="post" enctype="multipart/form-data">
            Choisir votre fichier CSV à importer:
            <input type="file" name="fileToUpload" id="fileToUpload">
            <input type="submit" value="Upload CSV" name="submit">
            <input type="submit" value="Reverse CSV" name="delete" class="delete">
        </form>
        <form action="backup.php" method="post">
            <button type="submit" name="backup">Extraire la base de données</button>
        </form>
    </div>
</body>
</html>
