<?php
require_once "./assets/classes/DB_Lite.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST'){

    // $email = $_POST['email'] ?? "";
    // $topic = $_POST['topic'] ?? "";
    // $message = $_POST['message'] ?? "";

    $msgRec = [ 
       "email" => $_POST['email'] ?? "",
       "topic" => $_POST['topic'] ?? "",
       "message" => $_POST['message'] ?? ""
    ];

    //Instantiate PDO connection to DB
    $pdoInstance = DB_Lite::connect();
    $schemaFile = "./assets/databases/schema.sql";
    DB_Lite::loadSchema($pdoInstance, $schemaFile);

    // Execute DB Method to INSERT message
    if(DB_Lite::insertMessage($pdoInstance, $msgRec)){
        echo "Thank you for our feedback...We'll be in touch soon!!!";
    } else {
        echo "DB Table was not updated!!!";
    }
    

}