<?php

function messages(): void {
    $conn = Database::getLiteConnection();
    $stmt = $conn->query("SELECT id, name, email, message, created_at FROM messages ORDER BY created_at DESC");
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    view('messages', ['messages' => $messages]);
}



function deleteMessage(): void {
    header('Content-Type: application/json');

    $id = $_POST['id'] ?? null;

    if ($id) {
        try {
            $conn = Database::getLiteConnection();
            $stmt = $conn->prepare("DELETE FROM messages WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode([
                "status" => "success",
                "message" => "Message deleted successfully."
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "An error occurred while deleting the message. Please try again later."
            ]);
        }
    } else {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Message ID is required."
        ]);
    }
}
