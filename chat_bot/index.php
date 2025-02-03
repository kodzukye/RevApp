<?php
session_start();
// Définir l'URL de votre serveur Rasa
$rasa_server_url = "http://localhost:5005/webhooks/rest/webhook";

if (!isset($_SESSION['user_statut'])) {
    header('Location: ../index.php?page=login');
} else if ($_SESSION['user_statut'] !== 'connected') {
    header('Location: ../index.php?page=login');
}

if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChatBot Rasa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="chat-container">
        
        <div class="chat-header">
            <img src="../assets/images/icons/nouveau.png" alt="NewChat" class="new-chat-icon">
            <h2>RevAPP</h2>

            <div class="profile-menu">
                <a href="#" class="profile-link">
                    <img src="../assets/images/icons/profil.png" alt="Profil">
                </a>
                <div class="dropdown">
                    <a href="../pages/profil.php">Mon Profil</a>
                    <a href="../pages/logout.php">Se Déconnecter</a>
                </div>
            </div>

        </div>
    </div>
    
    <form method="POST" action="" id="chat-form">
        <div id="conversation" class="conversation"><!-- Messages will appear here --></div>
        <div class="chat-user-input">
            <!-- Zone de texte -->
            <textarea id="user-input" name="message" placeholder="Envoyer un message au ChatBot" required></textarea>

            <!-- Boutons en dessous -->
            <div class="button-container">
                <!-- Bouton upload -->
                <div class="file-upload-container">
                    <label for="file-upload" class="upload-btn">
                        <button onclick="document.getElementById('file-upload').click()">
                            <img src="../assets/images/icons/fichier.png" alt="Ajouter un fichier" class="upload-icon">
                        </button>
                    </label>
                    <input type="file" id="file-upload" style="display: none;">
                    <span id="filename"></span>
                </div>

                <!-- Bouton audio -->
                <button id="audio-btn" class="audio-btn">
                    <img src="../assets/images/icons/audio.png" alt="Audio">
                </button>

                <!-- Bouton envoyer-->
                <button type="submit" id="send-btn" class="send-btn">
                    <img src="../assets/images/icons/envoyer.png" alt="Envoyer" class="send-icon">
                </button>
            </div>
        </div>
    </form>

<script src="conversation.js"></script>
<script src="../javascript/chatbot.js"></script>
</body>
</html>