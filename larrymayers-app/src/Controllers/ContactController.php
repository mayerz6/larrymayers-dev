<?php

function contact():void {
    view('contact');
}


function contactPost():void {
        header('Content-Type: application/json');

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $message = trim($_POST['message'] ?? '');

        // Basic FORM validation
        if ($name && $email && $message) {

            try {
                    $conn = Database::getLiteConnection();
                    $stmt = $conn->prepare("INSERT INTO messages (name, email, message) VALUES (:name, :email, :message)");
                    
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':message', $message);
                    $stmt->execute();

                    echo json_encode([
                        "status" => "success",
                        "message" => "Thank you for your message, {$name}!"]);
            
                }catch(Exception $e) {
                        http_response_code(500);
                        echo json_encode([
                            "status" => "error",
                            "message" => "An error occurred while processing your message. Please try again later."
                        ]);
                        exit;
                    }

       } else {
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "message" => "All fields are required."
            ]);
        }
}
