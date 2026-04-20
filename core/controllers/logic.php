<?php

function view(string $view_name, array $data = []):void{
    extract($data, EXTR_SKIP);

    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

     if($isAjax){
            require __DIR__ . "/../templates/{$view_name}.php";
                return;
            }
        // header('Content-Type: application/json');
        require __DIR__ . "/../templates/regions/header.php";
        require __DIR__ . "/../templates/regions/header-nav.php";
        
            require __DIR__ . "/../templates/{$view_name}.php";
        
        require __DIR__ . "/../templates/regions/footer.php";
        
}
    

function requireAuth(): void {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit();
    }
}


function home():void {
    view('home');
}
function about():void {
    view('about');
}
function contact():void {
    view('contact');
}

function dashboard(): void {
    session_start();
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    
    requireAuth();
    view('dashboard', ['flash' => $flash]);
}
function contactPost():void {
    // Handle form submission logic here
    // For example, you could validate the input and send an email
    // if(($_SERVER['CONTENT_TYPE'] ?? '') === 'application/x-www-form-urlencoded'){
        header('Content-Type: application/json');

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $message = trim($_POST['message'] ?? '');

        // Basic FORM validation
        if ($name && $email && $message) {

            try {
                    $conn = Database::getLiteConnection();
                    Database::createMsgTable($conn);

                    $stmt = $conn->prepare("INSERT INTO messages (name, email, message) VALUES (:name, :email, :message)");
                    
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':message', $message);
                    $stmt->execute();

                    echo json_encode([
                        "status" => "success",
                        "message" => "Thank you for your message, {$name}!"]);
            
                }catch(Exception $e) {
                        http_response_code(500);
                        echo json_encode([
                            "status" => "error",
                            "message" => "An error occurred while processing your message. Please try again later."
                        ]);
                        exit;
                    }

       } else {
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "message" => "All fields are required."
            ]);
        }
}

function messages(): void {
    $conn = Database::getLiteConnection();
    $stmt = $conn->query("SELECT id, name, email, message, created_at FROM messages ORDER BY created_at DESC");
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    view('messages', ['messages' => $messages]);
}

function login(): void {
    $db = Database::getLiteConnection();
    // Database::createUsersTable($db);
    // Database::insertDefaultUser($db);
    view('login');
}   

function loginPost(): void {
    session_start();
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
    session_start();
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

function resume(): void {
    $conn = Database::getLiteConnection();

    $stmt = $conn->query("SELECT id, title, company, summary, start_year, end_year FROM resume ORDER BY created_at ASC");
    $resumes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $conn->query("SELECT id, resume_id, duty, order_index FROM duties ORDER BY created_at ASC");
    $duties = $stmt->fetchAll(PDO::FETCH_ASSOC);    

    $dutiesMap = [];
    foreach ($duties as $duty) {
        $dutiesMap[$duty['resume_id']][] = $duty;
    }

    view('resume', ['resumes' => $resumes, 'duties' => $dutiesMap]);
}

function resumeManage(): void {
    session_start();
    requireAuth();
    $conn = Database::getLiteConnection();
    // Database::createResumeTable($conn);
    // Database::createDutiesTable($conn);
    $stmt = $conn->query("SELECT id, title, company, summary, start_year, end_year FROM resume ORDER BY created_at ASC");
    $resumes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $conn->query("SELECT id, resume_id, duty, order_index FROM duties ORDER BY created_at ASC");
    $duties = $stmt->fetchAll(PDO::FETCH_ASSOC);    

    $dutiesMap = [];
    foreach ($duties as $duty) {
        $dutiesMap[$duty['resume_id']][] = $duty;
    }

    view('resume-manage', ['resumes' => $resumes, 'duties' => $dutiesMap]);
}


function resumeCreate(): void {
    session_start();
    requireAuth();
    header('Content-Type: application/json');

    $title = trim($_POST['title'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $start = trim($_POST['start_year'] ?? '');
    $end = trim($_POST['end_year'] ?? '');
    $dutiesRaw = trim($_POST['duties'] ?? '');

    if (!$title || !$company) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Title and company are required"
        ]);
        return;
    }

    $db = Database::getLiteConnection();

    try {
        $db->beginTransaction();

        $stmt = $db->query("SELECT COALESCE(MAX(order_index), 0) + 1 AS next_order FROM resume");
        // $nextOrder = $stmt->fetch(PDO::FETCH_ASSOC)['next_order'] ?? 1;
        $nextOrder = $stmt->fetchColumn();

        

        $stmt = $db->prepare("
            INSERT INTO resume (title, company, summary, start_year, end_year, order_index)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([$title, $company, $summary, $start, $end, $nextOrder]);

        $resumeId = $db->lastInsertId();

        $duties = array_filter(array_map('trim', explode("\n", $dutiesRaw)));
        $order = 1;
        foreach ($duties as $duty) {
            $stmt = $db->prepare("
                INSERT INTO duties (resume_id, duty, order_index)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$resumeId, $duty, $order++]);
        }

        $db->commit();

        echo json_encode([
            "status" => "success",
            "message" => "Resume entry created successfully."
            ]);
    } catch (Exception $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "{$e->getMessage()} An error occurred while creating the resume entry. Please try again later."]);
    }
}

function resumeDelete(): void {
    session_start();
    requireAuth();
    header('Content-Type: application/json');

    $id = $_POST['id'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Missing ID"]);
        return;
    }

    $db = Database::getLiteConnection();

    try {
        $stmt = $db->prepare("DELETE FROM resume WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(["status" => "success"]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "error"]);
    }
}

function resumeUpdate(): void {
    session_start();
    requireAuth();
    header('Content-Type: application/json');

    $id = $_POST['id'] ?? null;
    $title = trim($_POST['title'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $start = trim($_POST['start_year'] ?? '');
    $end = trim($_POST['end_year'] ?? '');
    $dutiesRaw = trim($_POST['duties'] ?? '');

    if (!$id || !$title) {
        http_response_code(400);
        echo json_encode(["status" => "error"]);
        return;
    }

    $db = Database::getLiteConnection();

    $stmt = $db->prepare("
        UPDATE resume
        SET title = ?
        WHERE id = ?
    ");

    $stmt->execute([$title, $id]);

    echo json_encode(["status" => "success"]);
}

function blog(): void {
    $conn = Database::getLiteConnection();
    $stmt = $conn->query("SELECT id, title, content, created_at FROM blog_posts ORDER BY created_at DESC");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    view('blog', ['posts' => $posts]);
}

function deleteMessage(): void {
    header('Content-Type: application/json');

    $id = $_POST['id'] ?? null;

    if ($id) {
        try {
            $conn = Database::getLiteConnection();
            $stmt = $conn->prepare("DELETE FROM messages WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode([
                "status" => "success",
                "message" => "Message deleted successfully."
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "An error occurred while deleting the message. Please try again later."
            ]);
        }
    } else {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Message ID is required."
        ]);
    }
}

function projects():void {
    view('projects');
}

function expertise():void {
    view('expertise');
}
