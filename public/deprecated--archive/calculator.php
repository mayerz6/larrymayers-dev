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

    $val1 = isset($data['val_1']) ? (float)$data['val_1'] : null;
    $val2 = isset($data['val_2']) ? (float)$data['val_2'] : null;
    $operation = isset($data['operation']) ? $data['operation'] : null;

    if ($val1 === null || $val2 === null || $operation === null) {
        echo json_encode(['error' => 'Missing input values or operation.']);
        exit;
    }

    $result = null;
    switch ($operation) {
        case 'add':
            $result = $val1 + $val2;
            break;
        case 'subtract':
            $result = $val1 - $val2;
            break;
        default:
            echo json_encode(['error' => 'Invalid operation.']);
            exit;
    }

    echo json_encode(['result' => $result]);
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}
?>
