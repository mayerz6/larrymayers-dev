<?php

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $rawData = file_get_contents("php://input");
    // Decode the JSON data
    $data = json_decode($rawData, true);
    
    if (is_null($data)) {
        echo json_encode(['error' => 'Invalid JSON input.']);
        exit;
    }

    $title = $data["title"] ?? null;
    $author = $data["author"] ?? null;
    $contents = $data["contents"] ?? null;

    if ($title === null || $author === null || $contents === null) {
        echo json_encode(['error' => 'Missing input values or operation.']);
        exit;
    }
    
    $fp = fopen('assets/posts/blog_entries.csv', 'a');
    if ($fp === false) {
        echo json_encode(['error' => 'Failed to open file.']);
        exit;
    }
    
    $result = fputcsv($fp, [time(), $title, $author, $contents, date('m/d/Y')]);
    fclose($fp);
    
    if ($result === false) {
        echo json_encode(['error' => 'Failed to write to file.']);
        exit;
    }

    echo json_encode(['success' => true]);
    exit;
}
?>
