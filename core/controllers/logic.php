<?php


// switch ($full_key) {
//     case 'GET /':
//         echo 'Home Page';
//         $handler_func = 'handleHomePage';
//         $handler_args = [];
//         break;
//     case 'GET /index.php':
//         echo 'Home Page';
//         $handler_func = 'handleHomePage';
//         $handler_args = [];
//         break;
//     case 'GET /about':
//         echo 'About Page';
//         $handler_func = 'handleAboutPage';
//         $handler_args = [];
//         break;  
//     case 'GET /projects':
//         echo 'Projects Page';
//         $handler_func = 'handleProjectsPage';
//         $handler_args = [];
//         break;
//     case 'GET /contact':
//         echo 'Contact Page';
//         $handler_func = 'handleContactPage';
//         $handler_args = [];
//         break;  
//     case 'GET /blog':
//         echo 'Blog Page';
//         $handler_func = 'handleBlogPage';
//         $handler_args = [];
//         break;
//     default:
//         http_response_code(404);
//         echo json_encode(["message" => "Endpoint not found"]);
//         break;
// }


function view(string $view_name, array $data = []):void{
    extract($data, EXTR_SKIP);
    require_once __DIR__ . "/../templates/regions/header.php";
    require_once __DIR__ . "/../templates/regions/header-nav.php";
    require_once __DIR__ . "/../templates/{$view_name}.php";
    require_once __DIR__ . "/../templates/regions/footer.php";
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