<?php

require_once '../app/Models/Message.php';

class ContactController {
    public function submit($data){
        header('Content-Type: application/json'); // important!

        
        if(empty($data['firstname']) || empty($data['lastname']) || empty($data['email']) || empty($data['topic']) || empty($data['message'])){
            echo json_encode(["message" => "All fields are required"]);
            http_response_code(400);
            return;
        }
        /* INPUT VALIDATION | Email Address */
        if(!filter_var($data["email"], FILTER_VALIDATE_EMAIL)){
            echo json_encode(["message" => "Invalid email address."]);
            http_response_code(400);
            exit;
        }

        try{
            $message = new Message();
            $message->save($data);

            echo json_encode(["message" => "Message sent successfully!!!"]);
            http_response_code(200);
        } catch (Exception $e){
            echo json_encode(["message" => "Error: " . $e->getMessage()]);
            http_response_code(500);
        }

    }

}