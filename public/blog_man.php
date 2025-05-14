<?php

require_once '../app/Controllers/BlogController.php';


$routes = [
    'create' => '../app/Views/Partials/cp_form.php',
    'update' => '../app/Views/Partials/up_form.php',
    'delete' => '../app/Views/Partials/up_form.php',
    'view' => '../app/Views/Partials/blog_listings.php',
];

/* Default FUNCTION is to View the most recent Blog post articles */
$func = $_GET['func'] ?? 'view';


if (array_key_exists($func, $routes)) {
    // include $routes[$page];
    switch($func){
        case 'create':
            $controller = new BlogController();
            $controller->addPost($_POST);
            break;
    
        case 'update':
            $controller = new BlogController();
            $controller->updatePost($_POST);
            break;

        case 'delete':
            $controller = new BlogController();
            $controller->deletePost($id);
            break;
    
        case 'view':
        default:
            include '../app/Views/Partials/blog_listings.php';
            break;
    }
} else {
    include '../app/Views/404.php';
}

