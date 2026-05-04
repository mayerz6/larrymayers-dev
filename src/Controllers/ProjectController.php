<?php

function projectDb(): PDO
{
    $db = Database::getLiteConnection();
    Database::createProjectsTable($db);
    Database::createProjectDutiesTable($db);
    return $db;
}

function fetchProjectEntries(PDO $db): array {
    $stmt = $db->query("SELECT id, title, description, link, created_at FROM projects ORDER BY created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function projectManage(): void {

    requireAuth();
    $db = projectDb();
    $projects = fetchProjectEntries($db);
    view('project-manage', ['projects' => $projects]);
    }
    
    
    function projects(): void {
        // $db = projectDb();
        // $projects = fetchProjectEntries($db);
        // view('projects', ['projects' => $projects]);
    requireAuth();
    view('projects');
}