<?php


$routes = [
    'GET /' => ['handler' => 'home', 'args' => []],
    'GET /index.php' => ['handler' => 'home', 'args' => []],
    'GET /profile' => ['handler' => 'home', 'args' => []],
    'GET /contact' => ['handler' => 'contact', 'args' => []],
    'GET /about' => ['handler' => 'about', 'args' => []],
    'GET /resume' => ['handler' => 'resume', 'args' => []],
    'GET /resume/manage' => ['handler' => 'resumeManage', 'args' => []],
    'GET /resume/create' => ['handler' => 'resumeCreateForm', 'args' => []],
    'GET /resume/edit' => ['handler' => 'resumeEditForm', 'args' => []],
    'GET /projects' => ['handler' => 'projects', 'args' => []],
    'GET /projects/manage' => ['handler' => 'projectManage', 'args' => []],
    'GET /projects/create' => ['handler' => 'projectCreateForm', 'args' => []],
    'GET /projects/edit' => ['handler' => 'projectEditForm', 'args' => []],
    'GET /messages' => ['handler' => 'messages', 'args' => []],
    'GET /blog' => ['handler' => 'blog', 'args' => []],
    'GET /blog/manage' => ['handler' => 'blogManage', 'args' => []],
    'GET /blog/create' => ['handler' => 'blogCreateForm', 'args' => []],
    'GET /blog/edit' => ['handler' => 'blogEditForm', 'args' => []],

    // 'GET /expertise' => ['handler' => 'expertise', 'args' => []],
    'POST /projects/create' => ['handler' => 'projectCreate', 'args' => []],
    'POST /projects/update' => ['handler' => 'projectUpdate', 'args' => []],
    'POST /resume/create' => ['handler' => 'resumeCreate', 'args' => []],
    'POST /resume/update' => ['handler' => 'resumeUpdate', 'args' => []],
    'POST /contact' => ['handler' => 'contactPost', 'args' => []],
    'POST /resume/delete' => ['handler' => 'resumeDelete', 'args' => []],
    'GET /login' => ['handler' => 'login', 'args' => []],
    'GET /dashboard' => ['handler' => 'dashboard', 'args' => []],
    'POST /login' => ['handler' => 'loginPost', 'args' => []],
    'POST /logout' => ['handler' => 'logoutPost', 'args' => []],
    'POST /messages/delete' => ['handler' => 'deleteMessage', 'args' => []],
    'POST /blog/create' => ['handler' => 'blogCreate', 'args' => []],
    'POST /blog/update' => ['handler' => 'blogUpdate', 'args' => []],
    // Add more routes here as needed
];
