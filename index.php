<?php
session_start();

// Vérifie si l'utilisateur est authentifié
if (!isset($_SESSION['authenticated'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Accueil</title>
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
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 10px;
        }
        button:hover {
            background-color: #45a049;
        }
        .yellow-button {
            background-color: #FFC107;
            color: black;
        }
        .yellow-button:hover {
            background-color: #ffca28;
        }
        .inactive-user {
            background-color: #FFC107;
            color: black;
        }
        .inactive-user:hover {
            background-color: #ffca28;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Page d'accueil</h2>
        <form action="gerer_csv.php" method="get">
            <button type="submit">Gérer CSV</button>
        </form>
        <form action="ajoutmac.php" method="get">
            <button type="submit" class="green-button">Ajouter une adresse MAC</button>
        </form>
        <form action="backup.php" method="post">
            <button type="submit" name="backup">Extraire la base de données</button>
        </form>
        <form action="doublon.php" method="get">
            <button type="submit" class="yellow-button">Vérifier les doublons</button>
        </form>
        <form action="utilisateursinactifs.php" method="get">
            <button type="submit" class="yellow-button">Utilisateurs Inactifs</button>
        </form>
        <form action="genererlog.php" method="get">
            <button type="submit" class="inactive-user">Générer Logs</button>
        </form>
    </div>
</body>
</html>
