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

if ($file['user_id'] != $_SESSION['user']['id']) {
    errorResponse(403, "You can only create short links for your own files");
}

$stmt = $mysqli->prepare("SELECT code FROM short WHERE filename = ?");
$stmt->bind_param("s", $file['filename']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $code = $config['files']['shortBaseUrl'] . $result->fetch_assoc()['code'];
    echo json_encode(["success" => true, "link" => $code, "note" => "Short link already exists"]);
    exit;
}

function generateCode() {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';
    for ($i = 0; $i < 6; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}
$code = generateCode();

$stmt = $mysqli->prepare("SELECT code FROM short");
$stmt->execute();
$result = $stmt->get_result();
$existingCodes = [];
while ($row = $result->fetch_assoc()) {
    $existingCodes[] = $row['code'];
}

while (in_array($code, $existingCodes)) {
    $code = generateCode();
}

$oneWeek = time() + $config['short']['expireTime'];

$stmt = $mysqli->prepare("INSERT INTO short (filename, code, expire) VALUES (?, ?, ?)");
$stmt->bind_param("ssi", $file['filename'], $code, $oneWeek);
$stmt->execute();

$code = $config['files']['shortBaseUrl'] . $code;

echo json_encode(["success" => true, "link" => $code]);

