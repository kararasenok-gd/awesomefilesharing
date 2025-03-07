<?php

$config = require '../config.php';
require "../usefulFuncs.php";

header('Content-Type: application/json');

if (!isset($_GET['code'])) {
    errorResponse(400, "Bad Request: Missing code");
}

$verificationCode = $_GET['code'];

$mysqli = new mysqli(
    $config['database']['host'],
    $config['database']['user'],
    $config['database']['password'],
    $config['database']['database']
);

if ($mysqli->connect_error) {
    errorResponse(500, "Database connection failed");
}

$stmt = $mysqli->prepare("SELECT user_id, new_passwd FROM restore WHERE code = ?");
$stmt->bind_param("s", $verificationCode);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    errorResponse(404, "Verification code not found");
}

$row = $result->fetch_assoc();
$userId = $row['user_id'];
$newPassword = md5($row['new_passwd']);

$stmt = $mysqli->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->bind_param("si", $newPassword, $userId);
$stmt->execute();

$stmt = $mysqli->prepare("DELETE FROM restore WHERE code = ?");
$stmt->bind_param("s", $verificationCode);
$stmt->execute();

$stmt->close();
$mysqli->close();

header("Location: /login?msg=Пароль восстановлен. Он был отправлен в письме с ссылкой.");