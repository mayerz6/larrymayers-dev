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
function projects():void {
    view('projects');
}