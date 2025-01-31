<?php
// includes/db.php - Connexion à la base de données
$host = 'localhost'; // Serveur de la base de données
$dbname = 'revapp'; // Nom de votre base de données
$username = 'root'; // Utilisateur de la base de données
$password = ''; // Mot de passe de la base de données

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connexion échouée : ' . $e->getMessage();
}
?>