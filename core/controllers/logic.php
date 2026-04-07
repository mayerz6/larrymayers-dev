<?php

function view(string $view_name, array $data = []):void{
    extract($data, EXTR_SKIP);

    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    if(!$isAjax){
        // header('Content-Type: application/json');
        require_once __DIR__ . "/../templates/regions/header.php";
        require_once __DIR__ . "/../templates/regions/header-nav.php";
        // echo json_encode($data);
        // return;
    }   
  
    require_once __DIR__ . "/../templates/{$view_name}.php";

    if($isAjax){
        // header('Content-Type: application/json');
        require_once __DIR__ . "/../templates/regions/footer.php";
        // echo json_encode($data);
        // return;
    }


    // require_once __DIR__ . "/../templates/regions/header.php";
    // require_once __DIR__ . "/../templates/regions/header-nav.php";
    // require_once __DIR__ . "/../templates/regions/footer.php";
    
}


function home():void {
    view('home');
}
function about():void {
    view('about');
}
function contact():void {
    view('contact');
}

function contactPost():void {
    // Handle form submission logic here
    // For example, you could validate the input and send an email
    if(($_SERVER['CONTENT_TYPE'] ?? '') === 'application/x-www-form-urlencoded'){
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $message = $_POST['message'] ?? '';

        // Basic FORM validation
        if ($name && $email && $message) {
            // Here you would typically send the email or save the message to a database
            // For demonstration, we'll just return a success message
            echo json_encode(["message" => "Thank you for your message, {$name}!"]);
        } else {
            http_response_code(400);
            echo json_encode(["message" => "All fields are required."]);
        }
    } else {
        http_response_code(415);
        echo json_encode(["message" => "Unsupported Media Type."]);
    }
}
function projects():void {
    view('projects');
}

function expertise():void {
    view('expertise');
}

function resume():void {
    view('resume');
}