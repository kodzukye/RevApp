<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot avec cadre image</title>
    <link rel="stylesheet" href="../styles/chatbot.css">
</head>
<body>

<div class="chat-container">
    <!-- Zone de texte -->
    <textarea id="user-input" placeholder="Envoyer un message au ChatBot"></textarea>

    <!-- Boutons en dessous -->
    <div class="button-container">
        <!-- Bouton upload -->
        <div class="file-upload-container">
            <label for="file-upload" class="upload-btn">
                <img src="../assets/images/icons/fichier.png" alt="Ajouter un fichier" class="upload-icon">
            </label>
            <input type="file" id="file-upload" style="display: none;">
        </div>

        <!-- Bouton audio -->
        <button id="audio-btn" class="audio-btn">
            <img src="../assets/images/icons/audio.png" alt="Audio">
        </button>

        <!-- Bouton envoyer-->
        <button id="send-btn" class="send-btn">
            <img src="../assets/images/icons/envoyer.png" alt="Envoyer" class="send-icon">
        </button>
    </div>
</div>

<script src="../javascript/chatbot.js"></script>
</body>
</html>
