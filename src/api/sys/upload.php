<?php

session_start();
$config = require '../config.php';

header('Content-Type: application/json');

require "../usefulFuncs.php";

if (!isset($_SESSION['user'])) {
    errorResponse(401, "Unauthorized");
}

if (!isset($_FILES['file'])) {
    errorResponse(400, "Bad Request: file not found");
}
if (!isset($_POST['isNSFW'])) {
    errorResponse(400, "Bad Request: isNSFW not found");
}
if (!isset($_POST['hcaptcha'])) {
    errorResponse(400, "Bad Request: hcaptcha not found");
}

$file = $_FILES['file'];
$isNSFW = $_POST['isNSFW'];
$hcaptchaResponse = $_POST['hcaptcha'];

// $hcaptchaSecret = $config['hcaptcha']['secret'];
// $hcaptchaVerify = file_get_contents("https://hcaptcha.com/siteverify?secret=$hcaptchaSecret&response=$hcaptchaResponse");
// $hcaptchaData = json_decode($hcaptchaVerify, true);
// if (!$hcaptchaData['success']) {
//     errorResponse(403, "Failed to check captcha. Try to complete it again");
// }

$allowedPrefixes = ['image/', 'audio/', 'video/', 'text/'];
$allowedMimeTypes = ['application/zip', 'application/x-zip-compressed'];

$fileMimeType = mime_content_type($file['tmp_name']);

$allowed = in_array($fileMimeType, $allowedMimeTypes) || array_filter($allowedPrefixes, fn($prefix) => str_starts_with($fileMimeType, $prefix));

if (!$allowed) {
    errorResponse(415, "File type '$fileMimeType' is not allowed");
}

$maxSize = $config['files']['maxSize'];
if ($file['size'] > $maxSize) {
    errorResponse(413, "File too large. Limit: " . ($maxSize / 1024 / 1024) . " MB");
}

$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = bin2hex(random_bytes(16)) . ($extension ? ".$extension" : "");
$filePath = $config['files']['src'] . $filename;

if (!move_uploaded_file($file['tmp_name'], $filePath)) {
    errorResponse(500, "Failed to save file");
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

$stmt = $mysqli->prepare("INSERT INTO files (filename, is_nsfw, user_id, size, file_type, upload_date, views, views_raw) VALUES (?, ?, ?, ?, ?, ?, 0, 0)");
$userId = $_SESSION['user']['id'];
$uploadUnixTime = time();
$stmt->bind_param("siiisi", $filename, $isNSFW, $userId, $file['size'], $fileMimeType, $uploadUnixTime);
if (!$stmt->execute()) {
    errorResponse(500, "Failed to save file info to database");
}

$stmt->close();
$mysqli->close();

echo json_encode(["success" => true, "filename" => $filename]);
exit;

