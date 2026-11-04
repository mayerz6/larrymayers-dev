<?php

function resumeDb(): PDO
{
    $db = Database::getLiteConnection();
    // Database::createResumeTable($db);
    // Database::createDutiesTable($db);
    return $db;
}

function redirectTo(string $path): void
{
    header("Location: {$path}");
    exit();
}

function normalizeDuties(string $dutiesRaw): array
{
    return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $dutiesRaw))));
}

function fetchResumeEntries(PDO $db): array
{
    $stmt = $db->query("
        SELECT id, title, company, summary, start_year, end_year, order_index
        FROM resume
        ORDER BY order_index ASC, created_at DESC
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchDutiesMap(PDO $db): array
{
    $stmt = $db->query("
        SELECT id, resume_id, duty, order_index
        FROM duties
        ORDER BY resume_id ASC, order_index ASC
    ");

    $dutiesMap = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $duty) {
        $dutiesMap[$duty['resume_id']][] = $duty;
    }

    return $dutiesMap;
}

function resume(): void
{
    $db = resumeDb();
    view('resume', [
        'resumes' => fetchResumeEntries($db),
        'duties' => fetchDutiesMap($db),
    ]);
}

function resumeManage(): void
{
    requireAuth();

    $db = resumeDb();
    view('resume-manage', [
        'resumes' => fetchResumeEntries($db),
        'duties' => fetchDutiesMap($db),
        'flash' => $_SESSION['flash'] ?? null,
    ]);

    unset($_SESSION['flash']);
}

function resumeCreateForm(): void
{
    requireAuth();
    view('resume-create');
}

function resumeCreate(): void
{
    requireAuth();

    $title = trim($_POST['title'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $start = trim($_POST['start_year'] ?? '');
    $end = trim($_POST['end_year'] ?? '');
    $duties = normalizeDuties(trim($_POST['duties'] ?? ''));

    if ($title === '' || $company === '') {
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => 'Title and company are required.',
            ], 400);
            return;
        }

        $_SESSION['flash'] = 'Title and company are required.';
        redirectTo('/resume/create');
    }

    $db = resumeDb();

    try {
        $db->beginTransaction();

        $nextOrder = (int) $db
            ->query("SELECT COALESCE(MAX(order_index), 0) + 1 FROM resume")
            ->fetchColumn();

        $stmt = $db->prepare("
            INSERT INTO resume (title, company, summary, start_year, end_year, order_index)
            VALUES (:title, :company, :summary, :start_year, :end_year, :order_index)
        ");
        $stmt->execute([
            ':title' => $title,
            ':company' => $company,
            ':summary' => $summary,
            ':start_year' => $start,
            ':end_year' => $end,
            ':order_index' => $nextOrder,
        ]);

        $resumeId = (int) $db->lastInsertId();
        saveResumeDuties($db, $resumeId, $duties);

        $db->commit();

        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'success',
                'message' => 'Resume entry created successfully.',
                'redirect' => '/resume/manage',
            ]);
            return;
        }

        $_SESSION['flash'] = 'Resume entry created successfully.';
        redirectTo('/resume/manage');
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }

        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => 'Unable to create resume entry.',
            ], 500);
            return;
        }

        $_SESSION['flash'] = 'Unable to create resume entry.';
        redirectTo('/resume/create');
    }
}

function resumeEditForm(): void
{
    requireAuth();

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        $_SESSION['flash'] = 'Missing resume entry ID.';
        redirectTo('/resume/manage');
    }

    $db = resumeDb();

    $stmt = $db->prepare("SELECT * FROM resume WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $resume = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$resume) {
        $_SESSION['flash'] = 'Resume entry not found.';
        redirectTo('/resume/manage');
    }

    $stmt = $db->prepare("SELECT * FROM duties WHERE resume_id = :id ORDER BY order_index ASC");
    $stmt->execute([':id' => $id]);

    view('resume-edit', [
        'resume' => $resume,
        'duties' => $stmt->fetchAll(PDO::FETCH_ASSOC),
    ]);
}

function resumeUpdate(): void
{
    requireAuth();

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $title = trim($_POST['title'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $start = trim($_POST['start_year'] ?? '');
    $end = trim($_POST['end_year'] ?? '');
    $duties = normalizeDuties(trim($_POST['duties'] ?? ''));

    if (!$id || $title === '' || $company === '') {
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => 'ID, title, and company are required.',
            ], 400);
            return;
        }

        $_SESSION['flash'] = 'ID, title, and company are required.';
        redirectTo('/resume/manage');
    }

    $db = resumeDb();

    try {
        $db->beginTransaction();

        $stmt = $db->prepare("
            UPDATE resume
            SET title = :title,
                company = :company,
                summary = :summary,
                start_year = :start_year,
                end_year = :end_year
            WHERE id = :id
        ");
        $stmt->execute([
            ':title' => $title,
            ':company' => $company,
            ':summary' => $summary,
            ':start_year' => $start,
            ':end_year' => $end,
            ':id' => $id,
        ]);

        $stmt = $db->prepare("DELETE FROM duties WHERE resume_id = :id");
        $stmt->execute([':id' => $id]);
        saveResumeDuties($db, $id, $duties);

        $db->commit();

        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'success',
                'message' => 'Resume entry updated successfully.',
                'redirect' => '/resume/manage',
            ]);
            return;
        }

        $_SESSION['flash'] = 'Resume entry updated successfully.';
        redirectTo('/resume/manage');
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }

        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => 'Unable to update resume entry.',
            ], 500);
            return;
        }

        $_SESSION['flash'] = 'Unable to update resume entry.';
        redirectTo("/resume/edit?id={$id}");
    }
}

function resumeDelete(): void
{
    requireAuth();

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => 'Missing resume entry ID.',
            ], 400);
            return;
        }

        $_SESSION['flash'] = 'Missing resume entry ID.';
        redirectTo('/resume/manage');
    }

    $db = resumeDb();

    try {
        $stmt = $db->prepare("DELETE FROM resume WHERE id = :id");
        $stmt->execute([':id' => $id]);

        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'success',
                'message' => 'Resume entry deleted successfully.',
            ]);
            return;
        }

        $_SESSION['flash'] = 'Resume entry deleted successfully.';
        redirectTo('/resume/manage');
    } catch (Throwable $e) {
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => 'Unable to delete resume entry.',
            ], 500);
            return;
        }

        $_SESSION['flash'] = 'Unable to delete resume entry.';
        redirectTo('/resume/manage');
    }
}

function saveResumeDuties(PDO $db, int $resumeId, array $duties): void
{
    $stmt = $db->prepare("
        INSERT INTO duties (resume_id, duty, order_index)
        VALUES (:resume_id, :duty, :order_index)
    ");

    foreach ($duties as $index => $duty) {
        $stmt->execute([
            ':resume_id' => $resumeId,
            ':duty' => $duty,
            ':order_index' => $index + 1,
        ]);
    }
}
