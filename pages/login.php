<?php
session_start();
require_once 'config/config.php';

$errors = [];
$success = false;


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $mot_de_passe = trim($_POST["mot_de_passe"]);

    // Validation des champs
    if (empty($email)) {
        $errors[] = "L'email est requis";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'email invalide";
    }
    if (empty($mot_de_passe)) {
        $errors[] = "Le mot de passe est requis";
    }

    if (empty($errors)) {
        try {
            $checkEmail = $pdo->prepare("SELECT email, mot_de_passe FROM utilisateurs WHERE email = ?");
            $checkEmail->execute([$email]);
            
            if ($checkEmail->rowCount() <= 0) {
                $errors[] = "Cet email n'existe pas.";
            } else { 
                $user = $checkEmail->fetch();
                if (password_verify($mot_de_passe, $user['mot_de_passe'])) {
                    $success = true;
                    $_SESSION['inscription_success'] = true;
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['user_statut'] = 'connected';
                    header('Refresh: 3; url=index.php?');
                } else {
                    $errors[] = "Mot de passe incorrect.";
                }
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur technique : " . $e->getMessage();
        }
    }
}

?>

<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
</head>
<body>
<?php if ($success): ?>
    <div class="success">
        <p>Connexion réussie !</p>
    </div>
<?php else: ?>
    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div>

        <h1>Bienvenue</h1>
        
        <form action="" method="post">
            <label for="email"> Email </label>
            <input type="email" name="email" id="email" required>

            <label for="mot_de_passe"> Mot de passe </label>
            <input type="password" name="mot_de_passe" id="email" required minlength="4">

            <div class="button">
                <button type="submit" id="login" value="login">Se connecter</button>
            </div>
        </form>

        <p>Vous n’avez pas de compte ? <a href="index.php?page=registration">S’inscire !</a></p>

        <a href="index.php?page=forgotten_password">Mot de passe oublié ?</a>
    </div>
    <?php endif; ?>
</body>
</html>
