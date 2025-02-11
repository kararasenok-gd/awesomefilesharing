<?php

header('Content-Type: application/json');
session_start();

$config = require '../config.php';
require "../usefulFuncs.php";

if (!isset($_SESSION['user'])) {
    errorResponse(401, "User is not logged in");
}

$user = $_SESSION['user'];

if (!isset($_POST['password'])) {
    errorResponse(400, "Bad Request");
}

$mysqli = new mysqli($config['database']['host'], $config['database']['user'], $config['database']['password'], $config['database']['database']);
if ($mysqli->connect_error) {
    errorResponse(500, "Internal Server Error");
}

$stmt = $mysqli->prepare("SELECT password FROM users WHERE id = ?");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$result = $stmt->get_result();
$passwd = $result->fetch_assoc();

if (md5($_POST['password']) !== $passwd['password']) {
    errorResponse(403, "Wrong password");
}

$stmt = $mysqli->prepare("SELECT * FROM files WHERE user_id = ?");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($file = $result->fetch_assoc()) {
        unlink($config['files']['src'] . $file['filename']);
    }
}

$stmt = $mysqli->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $user['id']);
$stmt->execute();

$stmt = $mysqli->prepare("DELETE FROM files WHERE user_id = ?");
$stmt->bind_param("i", $user['id']);
$stmt->execute();

session_destroy();
echo json_encode(["success" => true]);
exit;