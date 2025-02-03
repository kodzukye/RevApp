<?php
session_start();
header('Content-Type: application/json');

$rasa_server_url = "http://localhost:5005/webhooks/rest/webhook";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Store user message
    $_SESSION['chat_history'][] = [
        'sender' => 'user',
        'message' => $data['message'],
        'time' => date('H:i')
    ];

    // Forward to Rasa
    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode([
                'sender' => session_id(),
                'message' => $data['message']
            ])
        ]
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($rasa_server_url, false, $context);
    $botResponses = json_decode($result, true);

    // Store and return bot responses
    $output = [];
    foreach ($botResponses as $message) {
        $_SESSION['chat_history'][] = [
            "sender" => "bot", 
            "message" => $message['text']
        ];
        $output[] = [
            "sender" => "bot",
            "message" => $message['text']
        ];
    }
    header_remove();
    header('Content-Type: application/json');
    http_response_code(200);
    echo json_encode($output);
    exit;
}
?>