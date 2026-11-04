<?php


function login(): void { view('login'); }   

function loginPost(): void {
    header('Content-Type: application/json');

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $db = Database::getLiteConnection();
    $stmt = $db->prepare("SELECT id, email, password_hash FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);

    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['flash'] = "Welcome back, " . htmlspecialchars($user['email']) . "!";
        echo json_encode([
            "status" => "success",
            "message" => "Login successful.",
            "redirect" => "/dashboard"
        ]);
        exit();
    } else {
        http_response_code(401);
        echo json_encode([
            "status" => "error",
            "message" => "Invalid credentials."
        ]);
    }
}

function logoutPost(): void {
    session_unset();
    $_SESSION = [];
    session_destroy();

    // If AJAX request → return JSON
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode([
            "status" => "success",
            "message" => "Logged out successfully.",
            "redirect" => "/"
        ]);
        return;
    }

    header('Location: /');
    exit();
}
