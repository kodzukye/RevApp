<?php
session_start();
require_once '../config/config.php';

$errors = [];
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST["nom"]);
    $prenom = trim($_POST["prenom"]);
    $email = trim($_POST["email"]);
    $mot_de_passe = trim($_POST["mot_de_passe"]);

    // Validation des champs
    if (empty($nom)) $errors[] = "Le nom est requis";
    if (empty($prenom)) $errors[] = "Le prénom est requis";
    if (empty($email)) {
        $errors[] = "L'email est requis";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'email invalide";
    }
    if (empty($mot_de_passe)) {
        $errors[] = "Le mot de passe est requis";
    } elseif (strlen($mot_de_passe) < 4) {
        $errors[] = "Le mot de passe doit contenir au moins 4 caractères";
    }

    if (empty($errors)) {
        try {
            // Vérification email unique
            $checkEmail = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
            $checkEmail->execute([$email]);
            
            if ($checkEmail->rowCount() > 0) {
                $errors[] = "Cet email est déjà utilisé";
            } else {
                // Insertion utilisateur
                $hashedPassword = password_hash($mot_de_passe, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe) 
                                     VALUES (:nom, :prenom, :email, :mot_de_passe)");
                
                $stmt->execute([
                    ':nom' => $nom,
                    ':prenom' => $prenom,
                    ':email' => $email,
                    ':mot_de_passe' => $hashedPassword
                ]);

                if ($stmt->rowCount() > 0) {
                    $success = true;
                    $_SESSION['inscription_success'] = true;
                    header("Refresh: 3; url=index.php?page=login");
                }
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur technique : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <link rel="stylesheet" href="../styles/registration.css">
    <style>
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="title">Formulaire d'inscription</h1>

        <?php if ($success): ?>
        <div class="success">
            <p>Inscription réussie ! Redirection vers la page de connexion dans 3 secondes...</p>
        </div>
        <?php else: ?>
        <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

            <form action="" method="post">

            <label>Nom</label>
            <input type="text" name="nom" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" placeholder="Nom" required>

            <label>Prénom</label>
            <input type="text" name="prenom" value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" placeholder="Prénom" required>

            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="E-mail" required>

            <label>Mot de passe</label>
            <input type="password" name="mot_de_passe" placeholder="Mot de passe" required minlength="4">

            <button type="submit">S'inscrire</button>
        </form>
        <?php endif; ?>

        <p>Déjà inscrit ? <a href="index.php?page=login">Connectez-vous ici</a></p>
    </div>
</body>
</html>
