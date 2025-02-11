<?php

session_start();
$config = require '../config.php';

header('Content-Type: application/json');

require "../usefulFuncs.php";

if (!isset($_SESSION['user'])) {
    errorResponse(401, "Unauthorized");
}

$userId = $_SESSION['user']['id'];

$mysqli = new mysqli(
    $config['database']['host'],
    $config['database']['user'],
    $config['database']['password'],
    $config['database']['database']
);
if ($mysqli->connect_error) {
    errorResponse(500, "Database connection failed");
}

$stmt = $mysqli->prepare("SELECT * FROM files WHERE user_id = ? ORDER BY upload_date DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$files = [];
while ($row = $result->fetch_assoc()) {
    $files[] = $row;
}

$stmt->close();
$mysqli->close();

echo json_encode(["success" => true, "files" => $files]);
exit;
