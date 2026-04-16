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
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
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
    requireAuth();
    view('dashboard');
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
    Database::createUsersTable($db);
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
        echo json_encode([
            "status" => "success",
            "message" => "Login successful.",
            "redirect" => "/dashboard"
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            "status" => "error",
            "message" => "Invalid credentials."
        ]);
    }
}

function resume(): void {
    $conn = Database::getLiteConnection();
    $stmt = $conn->query("SELECT id, title, company, summary, start_year, end_year FROM resume ORDER BY created_at DESC");
    $resumes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    view('resume', ['resumes' => $resumes]);
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
