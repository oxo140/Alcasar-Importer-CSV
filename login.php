<?php
session_start();

// Vérifie si le formulaire est soumis
if (isset($_POST['submit'])) {
    // Le mot de passe correct
    $password = 'votremotdepasse';

    // Vérifie si le mot de passe est correct
    if ($_POST['password'] === $password) {
        // Si correct, démarre la session et redirige vers index.php
        $_SESSION['authenticated'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Mot de passe incorrect';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Connexion</title>
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
        input[type="password"], input[type="submit"] {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        p {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Connexion</h2>
        <form method="post">
            <input type="password" name="password" placeholder="Mot de passe" required>
            <input type="submit" name="submit" value="Se connecter">
        </form>
        <?php if (isset($error)) { echo '<p>'.$error.'</p>'; } ?>
    </div>
</body>
</html>
