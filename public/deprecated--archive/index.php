<?php

require_once '../app/Controllers/ContactController.php';
require_once '../app/Controllers/BlogController.php';

$routes = [
    'home' => '../app/Views/home.php',
    'contact' => '../app/Views/contact_form.php',
    'about' => '../app/Views/about.php',
];

$page = $_GET['page'] ?? 'home';


if (array_key_exists($page, $routes)) {
    // include $routes[$page];
    switch($page){
        case 'contact':
            $controller = new ContactController();
            $controller->submit($_POST);
            break;
    
        case 'about':
            include '../app/Views/about.php';
            break;

        case 'blog':
            $controller = new BlogController();
            echo $controller->fetchPost(5);
            break;
    
        case 'home':
        default:
            include '../app/Views/home.php';
            break;
    }
} else {
    include '../app/Views/404.php';
}

