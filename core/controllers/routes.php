<?php


$routes = [
    'GET /' => ['handler' => 'home', 'args' => []],
    'GET /index.php' => ['handler' => 'home', 'args' => []],
    'GET /profile' => ['handler' => 'home', 'args' => []],
    'GET /about' => ['handler' => 'about', 'args' => []],
    'GET /projects' => ['handler' => 'projects', 'args' => []],
    'GET /expertise' => ['handler' => 'expertise', 'args' => []],
    'GET /resume' => ['handler' => 'resume', 'args' => []],
    'GET /contact' => ['handler' => 'contact', 'args' => []],
    'POST /contact' => ['handler' => 'contactPost', 'args' => []],
    'GET /blog' => ['handler' => 'blog', 'args' => []],
    // Add more routes here as needed
];