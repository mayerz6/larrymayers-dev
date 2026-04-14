<?php


$routes = [
    'GET /' => ['handler' => 'home', 'args' => []],
    'GET /index.php' => ['handler' => 'home', 'args' => []],
    'GET /profile' => ['handler' => 'home', 'args' => []],
    'GET /about' => ['handler' => 'about', 'args' => []],
    'GET /messages' => ['handler' => 'messages', 'args' => []],
    'POST /messages/delete' => ['handler' => 'deleteMessage', 'args' => []],
    'GET /projects' => ['handler' => 'projects', 'args' => []],
    'GET /expertise' => ['handler' => 'expertise', 'args' => []],
    'GET /resume' => ['handler' => 'resume', 'args' => []],
    'GET /contact' => ['handler' => 'contact', 'args' => []],
    'POST /contact' => ['handler' => 'contactPost', 'args' => []],
    'GET /login' => ['handler' => 'login', 'args' => []],
    'POST /login' => ['handler' => 'loginPost', 'args' => []],
    'GET /blog' => ['handler' => 'blog', 'args' => []],
    // Add more routes here as needed
];