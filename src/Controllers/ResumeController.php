<?php


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
