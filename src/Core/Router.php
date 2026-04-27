<?php


$routes = [
    'GET /' => ['handler' => 'home', 'args' => []],
    'GET /index.php' => ['handler' => 'home', 'args' => []],
    'GET /profile' => ['handler' => 'home', 'args' => []],
    'GET /contact' => ['handler' => 'contact', 'args' => []],
    'GET /about' => ['handler' => 'about', 'args' => []],
    'GET /resume' => ['handler' => 'resume', 'args' => []],
    'GET /resume/manage' => ['handler' => 'resumeManage', 'args' => []],
    'GET /projects' => ['handler' => 'projects', 'args' => []],
    'GET /messages' => ['handler' => 'messages', 'args' => []],
    
    // 'GET /expertise' => ['handler' => 'expertise', 'args' => []],
    // 'POST /resume/create' => ['handler' => 'resumeCreate', 'args' => []],
    // 'POST /resume/update' => ['handler' => 'resumeUpdate', 'args' => []],
    'POST /contact' => ['handler' => 'contactPost', 'args' => []],
    // 'POST /resume/delete' => ['handler' => 'resumeDelete', 'args' => []],
    'GET /login' => ['handler' => 'login', 'args' => []],
    'GET /dashboard' => ['handler' => 'dashboard', 'args' => []],
    'POST /login' => ['handler' => 'loginPost', 'args' => []],
    'POST /logout' => ['handler' => 'logoutPost', 'args' => []],
    'POST /messages/delete' => ['handler' => 'deleteMessage', 'args' => []],
    // 'GET /blog' => ['handler' => 'blog', 'args' => []],
    // Add more routes here as needed
];