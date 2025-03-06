<?php

$config = require '../config.php';
require "../usefulFuncs.php";

session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    errorResponse(401, "Unauthorized");
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    errorResponse(400, "Bad Request: Missing or invalid id");
}

$mysqli = new mysqli(
    $config['database']['host'],
    $config['database']['user'],
    $config['database']['password'],
    $config['database']['database']
);

if ($mysqli->connect_error) {
    errorResponse(500, "Database connection failed");
}

$id = $_GET['id'];
$stmt = $mysqli->prepare("SELECT * FROM files WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    errorResponse(404, "File not found");
}

$row = $result->fetch_assoc();
$stmt->close();
$mysqli->close();

if ($row['user_id'] !== $_SESSION['user']['id']) {
    errorResponse(403, "Forbidden");
}

echo json_encode([
    "success" => true,
    "data" => $row
]);