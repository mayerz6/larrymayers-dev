<?php

declare(strict_types=1);
$rootDir = __DIR__ . "/../"; // Adjust as needed to point to the root directory

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once "{$rootDir}/src/Core/Auth.php";
include_once "{$rootDir}/src/Core/Database.php";
// include_once "{$rootDir}/core/databases/Database.php";
include_once "{$rootDir}/src/Core/View.php";
include_once "{$rootDir}/src/Core/Response.php";
include_once "{$rootDir}/src/Core/Router.php";
include_once "{$rootDir}/src/Controllers/HomeController.php";
include_once "{$rootDir}/src/Controllers/AboutController.php";
include_once "{$rootDir}/src/Controllers/AdminController.php";
include_once "{$rootDir}/src/Controllers/ContactController.php";
include_once "{$rootDir}/src/Controllers/ResumeController.php";
include_once "{$rootDir}/src/Controllers/ProjectController.php";
include_once "{$rootDir}/src/Controllers/MessageController.php";
include_once "{$rootDir}/src/Controllers/DashboardController.php";
include_once "{$rootDir}/src/Controllers/BlogController.php";

$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$request_method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$request_path = normalizeRequestPath($request_uri);


$full_key = "{$request_method} {$request_path}";
// echo $full_key; // Debugging output

if ($request_method === 'POST') {
    $override = $_POST['_method'] ?? $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? null;
    if (is_string($override) && $override !== '') {
        $request_method = strtoupper($override);
    }
}

function normalizeRequestPath(string $requestUri): string
{
    $path = parse_url($requestUri, PHP_URL_PATH);

    if (!is_string($path) || $path === '') {
        return '/';
    }

    $path = preg_replace('#/+#', '/', $path) ?? $path;

    if ($path !== '/') {
        $path = rtrim($path, '/');
    }

    return $path[0] === '/' ? $path : "/{$path}";
}


function run(array $routes, string $request, string $method): void
{
    $full_key = "{$method} {$request}";
    if (isset($routes[$full_key])) {
        $handler_info = $routes[$full_key];
        $handler_func = $handler_info['handler'];
        $handler_args = $handler_info['args'] ?? [];
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Endpoint not found"]);
        exit;
    }

    loadContent($handler_info, $handler_args);
}

function loadContent(array $routeDef, array $params): void
{
    $handler_func = $routeDef['handler'] ?? null;
    $handler_args = $routeDef['args'] ?? [];

    if (is_callable($handler_func)) {
        // $handler_func(...$handler_args);
        $handler_func(...$handler_args);
    } else {
        http_response_code(500);
        echo "Handler function {$handler_func} not found";
    }
}

run($routes, $request_path, $request_method);

?>