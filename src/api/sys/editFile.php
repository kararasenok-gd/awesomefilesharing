<?php

$config = require '../config.php';
require "../usefulFuncs.php";

session_start();
if (!isset($_SESSION['user'])) {
    errorResponse(401, "Unauthorized");
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    errorResponse(400, "Bad Request: Missing or invalid id");
}

if (!isset($_POST['is_nsfw']) || !isset($_POST['displayname']) || !isset($_POST['tags'])) {
    errorResponse(400, "Bad Request: Missing or invalid data");
}

$id = $_GET['id'];
$is_nsfw = $_POST['is_nsfw'];
$displayname = $_POST['displayname'];
$tags = $_POST['tags'];

$mysqli = new mysqli(
    $config['database']['host'],
    $config['database']['user'],
    $config['database']['password'],
    $config['database']['database']
);

if ($mysqli->connect_error) {
    errorResponse(500, "Internal Server Error: Database connection failed");
}

$stmt = $mysqli->prepare("SELECT user_id, filename FROM files WHERE id = ?");
if (!$stmt) {
    errorResponse(500, "Internal Server Error: Database query failed");
}

$stmt->bind_param("i", $id);
if (!$stmt->execute()) {
    errorResponse(500, "Internal Server Error: Database query failed");
}

$result = $stmt->get_result();
if ($result->num_rows == 0) {
    errorResponse(404, "File not found");
}

$file = $result->fetch_assoc();
if ($file['user_id'] != $_SESSION['user']['id']) {
    errorResponse(403, "Forbidden");
}

$stmt = $mysqli->prepare("UPDATE files SET is_nsfw = ?, displayname = ?, tags = ? WHERE id = ?");
if (!$stmt) {
    errorResponse(500, "Internal Server Error: Database query failed");
}

$stmt->bind_param("issi", $is_nsfw, $displayname, $tags, $id);
if (!$stmt->execute()) {
    errorResponse(500, "Internal Server Error: Database query failed");
}

echo json_encode(["success" => true]);
exit;