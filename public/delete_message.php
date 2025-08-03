<?php
require_once "./assets/classes/DB_Lite.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    if (!$id) {
        http_response_code(400);
        echo "Missing message ID";
        exit;
    }

    $pdo = DB_Lite::connect();
    $deleted = DB_Lite::deleteMessage($pdo, (int)$id);

    if ($deleted) {
        echo "Message deleted.";
    } else {
        http_response_code(500);
        echo "Failed to delete message.";
    }
}
