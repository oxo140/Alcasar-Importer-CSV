<?php
// Force la redirection vers HTTPS si la connexion est en HTTP
if ($_SERVER['HTTPS'] != "on") {
    $url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $url");
    exit;
}

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
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
            animation: fadeIn 1s ease-out;
        }
        input[type="password"], input[type="submit"] {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
            transition: box-shadow 0.3s ease, transform 0.2s ease;
        }
        input[type="password"]:focus {
            box-shadow: 0 0 8px rgba(76, 175, 80, 0.5);
            transform: scale(1.03);
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
            transform: scale(1.05);
        }
        p {
            color: red;
            font-weight: bold;
            animation: shake 0.3s ease;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes shake {
            0%, 100% {
                transform: translateX(0);
            }
            25% {
                transform: translateX(-5px);
            }
            50% {
                transform: translateX(5px);
            }
            75% {
                transform: translateX(-5px);
            }
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
