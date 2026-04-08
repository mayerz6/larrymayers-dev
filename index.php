<?php  

declare(strict_types=1);

include_once "./core/databases/Database.php";
include_once "./core/security/Secrets.php";    
include_once "./core/databases/messages.sqlite";    
include_once "./core/controllers/routes.php";
include_once "./core/controllers/logic.php";

$request_uri = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$request_path = strtok($request_uri, '?'); // Remove query string


$full_key = "{$request_method} {$request_path}";
// echo $full_key; // Debugging output

if($request_method === 'POST'){
    $override = $_POST['_method'] ?? $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? null;
    if(is_string($override) && $override !== ''){
        $request_method = strtoupper($override);
    }
}


function run(array $routes, string $request, string $method):void{
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

function loadContent(array $routeDef, array $params):void {
    $handler_func = $routeDef['handler'] ?? null;
    $handler_args = $routeDef['args'] ?? [];

    if(is_callable($handler_func)){
        // $handler_func(...$handler_args);
        $handler_func(...$handler_args);
    } else {
        http_response_code(500);
        echo "Handler function {$handler_func} not found";
    }
}

run($routes, $request_path, $request_method);

?>

