<?php
// Définir l'URL de votre serveur Rasa
$rasa_server_url = "http://localhost:5005/webhooks/rest/webhook";

// Initialiser la session pour stocker l'historique de la conversation
session_start();

if (!isset($_SESSION['user_statut'])) {
    header('Location: ../index.php?page=login');
} else if ($_SESSION['user_statut'] !== 'connected') {
    header('Location: ../index.php?page=login');
}

if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [];
}

// Envoyer un message au bot Rasa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = $_POST['message'];

    // Envoyer le message au bot Rasa
    $data = json_encode(["sender" => "user", "message" => $message]);
    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => $data,
        ],
    ];
    $context  = stream_context_create($options);
    $result = file_get_contents($rasa_server_url, false, $context);

    // Ajouter la réponse du bot à l'historique de la conversation
    $response = json_decode($result, true);
    foreach ($response as $message) {
        $_SESSION['chat_history'][] = ["sender" => "bot", "message" => $message['text']];
    }
}

// Envoyer un message initial "Bonjour" au bot Rasa lors du chargement de la page
if (empty($_SESSION['chat_history'])) {
    $data = json_encode(["sender" => "user", "message" => "Bonjour"]);
    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => $data,
        ],
    ];
    $context  = stream_context_create($options);
    $result = file_get_contents($rasa_server_url, false, $context);

    // Ajouter la réponse du bot à l'historique de la conversation
    $response = json_decode($result, true);
    foreach ($response as $message) {
        $_SESSION['chat_history'][] = ["sender" => "bot", "message" => $message['text']];
    }
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
            <img src="../assets/images/icons/profil.png" alt="Profile" class="profile-icon">
        </div>
        <div class="chat-box" id="chat-box">
            <?php foreach ($_SESSION['chat_history'] as $chat): ?>
                <div class="chat-message <?php echo $chat['sender']; ?>">
                    <p><?php echo htmlspecialchars($chat['message']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="chat-user-input">
        <form method="POST" action="">>
            <!-- Zone de texte -->
            <textarea id="user-input" name="message" placeholder="Envoyer un message au ChatBot" required></textarea>

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
                <button type="submit" id="send-btn" class="send-btn">
                    <img src="../assets/images/icons/envoyer.png" alt="Envoyer" class="send-icon">
                </button>
            </div>
        </form>
    </div>

<script src="../javascript/chatbot.js"></script>
</body>
</html>