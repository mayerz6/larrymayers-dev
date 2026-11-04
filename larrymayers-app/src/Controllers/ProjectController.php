<?php

function projectDb(): PDO
{
    $db = Database::getLiteConnection();
    Database::createProjectsTable($db);
    Database::createProjectDutiesTable($db);
    Database::createProjectTechnologiesTable($db);
    return $db;
}

function normalizeTechnologies(string $technologiesRaw): array
{
    return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $technologiesRaw))));
}

function fetchProjectEntries(PDO $db): array
{
    $stmt = $db->query("
        SELECT id, title, description, link, order_index, created_at
        FROM projects
        ORDER BY order_index ASC, created_at DESC
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchProjectTechnologiesMap(PDO $db): array
{
    $stmt = $db->query("
        SELECT id, project_id, technology, order_index
        FROM project_technologies
        ORDER BY project_id ASC, order_index ASC
    ");

    $technologiesMap = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $technology) {
        $technologiesMap[$technology['project_id']][] = $technology;
    }

    return $technologiesMap;
}

function projects(): void
{
    $db = projectDb();

    view('projects', [
        'projects' => fetchProjectEntries($db),
        'technologies' => fetchProjectTechnologiesMap($db),
    ]);
}

function projectManage(): void
{
    requireAuth();

    $db = projectDb();

    view('project-manage', [
        'projects' => fetchProjectEntries($db),
        'technologies' => fetchProjectTechnologiesMap($db),
        'flash' => $_SESSION['flash'] ?? null,
    ]);

    unset($_SESSION['flash']);
}

function projectCreateForm(): void
{
    requireAuth();
    view('project-create');
}

function projectCreate(): void
{
    requireAuth();

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $link = trim($_POST['link'] ?? '');
    $technologies = normalizeTechnologies(trim($_POST['technologies'] ?? ''));

    if ($title === '' || $description === '') {
        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => 'Title and description are required.',
            ], 400);
            return;
        }

        $_SESSION['flash'] = 'Title and description are required.';
        redirectTo('/projects/create');
    }

    $db = projectDb();

    try {
        $db->beginTransaction();

        $nextOrder = (int) $db
            ->query("SELECT COALESCE(MAX(order_index), 0) + 1 FROM projects")
            ->fetchColumn();

        $stmt = $db->prepare("
            INSERT INTO projects (title, description, link, order_index)
            VALUES (:title, :description, :link, :order_index)
        ");
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':link' => $link,
            ':order_index' => $nextOrder,
        ]);

        $projectId = (int) $db->lastInsertId();
        saveProjectTechnologies($db, $projectId, $technologies);

        $db->commit();

        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'success',
                'message' => 'Project created successfully.',
                'redirect' => '/projects/manage',
            ]);
            return;
        }

        $_SESSION['flash'] = 'Project created successfully.';
        redirectTo('/projects/manage');
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }

        if (isAjaxRequest()) {
            jsonResponse([
                'status' => 'error',
                'message' => 'Unable to create project entry.',
            ], 500);
            return;
        }

        $_SESSION['flash'] = 'Unable to create project entry.';
        redirectTo('/projects/create');
    }
}

function projectEditForm(): void
{
    requireAuth();

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        $_SESSION['flash'] = 'Missing project ID.';
        redirectTo('/projects/manage');
    }

    $db = projectDb();

    $stmt = $db->prepare("SELECT * FROM projects WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        $_SESSION['flash'] = 'Project not found.';
        redirectTo('/projects/manage');
    }

    $stmt = $db->prepare("
        SELECT * FROM project_technologies
        WHERE project_id = :id
        ORDER BY order_index ASC
    ");
    $stmt->execute([':id' => $id]);

    view('project-edit', [
        'project' => $project,
        'technologies' => $stmt->fetchAll(PDO::FETCH_ASSOC),
    ]);
}

function projectUpdate(): void
{
    requireAuth();

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $link = trim($_POST['link'] ?? '');
    $technologies = normalizeTechnologies(trim($_POST['technologies'] ?? ''));

    if (!$id || $title === '' || $description === '') {
        $_SESSION['flash'] = 'Project ID, title, and description are required.';
        redirectTo('/projects/manage');
    }

    $db = projectDb();

    try {
        $db->beginTransaction();

        $stmt = $db->prepare("
            UPDATE projects
            SET title = :title,
                description = :description,
                link = :link
            WHERE id = :id
        ");
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':link' => $link,
            ':id' => $id,
        ]);

        $stmt = $db->prepare("DELETE FROM project_technologies WHERE project_id = :id");
        $stmt->execute([':id' => $id]);
        saveProjectTechnologies($db, $id, $technologies);

        $db->commit();

        $_SESSION['flash'] = 'Project updated successfully.';
        redirectTo('/projects/manage');
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }

        $_SESSION['flash'] = 'Unable to update project entry.';
        redirectTo("/projects/edit?id={$id}");
    }
}

function saveProjectTechnologies(PDO $db, int $projectId, array $technologies): void
{
    $stmt = $db->prepare("
        INSERT INTO project_technologies (project_id, technology, order_index)
        VALUES (:project_id, :technology, :order_index)
    ");

    foreach ($technologies as $index => $technology) {
        $stmt->execute([
            ':project_id' => $projectId,
            ':technology' => $technology,
            ':order_index' => $index + 1,
        ]);
    }
}
