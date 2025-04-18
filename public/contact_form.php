<?php

require_once './assets/classes/Database.php';

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
// header('Content-Type: application/json'); // important!


if($_SERVER["REQUEST_METHOD"] !== "POST"){
    echo json_encode(["message" => "Invalid request!!!"]);
    exit;
}

if($_SERVER["REQUEST_METHOD"] === "POST"){
    $firstname = trim($_POST["firstname"]);
    $lastname = trim($_POST["lastname"]);
    $email = trim($_POST["email"]);
    $topic = trim($_POST["topic"]);
    $message = trim($_POST["message"]);

    /* INPUT VALIDATION */
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        http_response_code(400);
        echo json_encode(["message" => "Invalid email address."]);
        exit;
    }

    /* LOGIC tree to execute processes based on the TOPIC provided. */

    if(empty($firstname) || empty($lastname) || empty($email) || empty($topic) || empty($message)){
        echo json_encode(["message" => "All fields are required."]);
        exit;
    }
    /* Add form data to the contents of a simple TXT file. */
    // file_put_contents("messages.txt", "$name -> $email | <$topic>: $message\n", FILE_APPEND);
    
    
    
    try {
        
        /* PASS form fields to SQL insert query for INSERTION to DB table */
        $db = new Database();
        $db_conn = $db->getConnection();

            $stmt = $db_conn->prepare("INSERT INTO messages (firstname, lastname, email, topic, message) VALUES (:firstname, :lastname, :email, :topic, :message)");
            $stmt->execute([
                ':firstname' => $firstname,
                ':lastname' => $lastname,
                ':email' => $email,
                ':topic' => $topic,
                ':message' => $message
            ]);

            echo json_encode(["message" => "Message sent successfully!"]);
            exit;
        } catch (PDOException $e) {
            echo json_encode(["message" => "Database error: " . $e->getMessage()]);
            exit;
        }

    // echo json_encode(["message" => "Message sent successfully!"]);
} else {
    echo json_encode(["message" => "Invalid request."]);
    exit;
}