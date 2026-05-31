<?php

function dashboard(): void {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    
    requireAuth();

    $stats = [
        'messages' => 0,
        'projects' => 0,
        'resume' => 0,
    ];

    try {
        $db = Database::getLiteConnection();
        // Database::createMsgTable($db);
        // Database::createProjectsTable($db);
        // Database::createResumeTable($db);

        $stats['messages'] = (int) $db->query("SELECT COUNT(*) FROM messages")->fetchColumn();
        $stats['projects'] = (int) $db->query("SELECT COUNT(*) FROM projects")->fetchColumn();
        $stats['resume'] = (int) $db->query("SELECT COUNT(*) FROM resume")->fetchColumn();
    } catch (Throwable $e) {
        $flash = $flash ?: 'Dashboard metrics are temporarily unavailable.';
    }

    view('dashboard', [
        'flash' => $flash,
        'stats' => $stats,
    ]);
}
