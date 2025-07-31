<?php
header('Content-Type: application/json');

$csvFilePath = 'blog_entries.csv';
$data = [];

if (($handle = fopen($csvFilePath, 'r')) !== FALSE) {
    // Get the headers
    $headers = fgetcsv($handle, 1000, ',');

    while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
        $data[] = array_combine($headers, $row);
    }
    fclose($handle);

    // Reverse the array to get the records in reverse order
    $data = array_reverse($data);
} else {
    echo json_encode(['error' => 'Unable to open the CSV file.']);
    exit;
}

echo json_encode($data);
?>
