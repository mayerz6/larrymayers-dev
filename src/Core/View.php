<?php


function view(string $view_name, array $data = []):void{
    extract($data, EXTR_SKIP);

    $viewsPath = __DIR__ . "/../../views";
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

     if($isAjax){
            require "{$viewsPath}/pages/{$view_name}.php";
                return;
            }
        // header('Content-Type: application/json');
        require "{$viewsPath}/partials/header.php";
        require "{$viewsPath}/partials/header-nav.php";
        
        require "{$viewsPath}/pages/{$view_name}.php";
        
        require "{$viewsPath}/partials/footer.php";
        
}
    