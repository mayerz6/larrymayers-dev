<?php
require_once "./assets/classes/DB_Lite.php";
header('Content-Type: application/json');

$pdo = DB_Lite::connect();
$messages = DB_Lite::fetchMessages($pdo);
echo json_encode($messages);
