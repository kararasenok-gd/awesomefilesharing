<?php

header('Content-Type: application/json');
session_start();

$config = require '../config.php';
require "../usefulFuncs.php";

if (!isset($_POST['id'])) {
    errorResponse(400, "Bad Request: Missing id");
}

if (!isset($_SESSION['user'])) {
    errorResponse(401, "Unauthorized");
}

$id = $_POST['id'];

$mysqli = new mysqli(
    $config['database']['host'],
    $config['database']['user'],
    $config['database']['password'],
    $config['database']['database']
);
if ($mysqli->connect_error) {
    errorResponse(500, "Database connection failed");
}

$stmt = $mysqli->prepare("SELECT user_id, filename FROM files WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    errorResponse(404, "File not found");
}
$file = $result->fetch_assoc();

if ($file['user_id'] !== $_SESSION['user']['id']) {
    errorResponse(403, "This file is not owned by you");
}

if (!unlink($config['files']['src'] . $file['filename'])) {
    errorResponse(500, "Failed to delete file");
}

$stmt = $mysqli->prepare("DELETE FROM files WHERE id = ?");
$stmt->bind_param("i", $id);
if (!$stmt->execute()) {
    errorResponse(500, "Failed to delete file");
}

echo json_encode(["success" => true]);