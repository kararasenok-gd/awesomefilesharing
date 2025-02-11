<?php

header('Content-Type: application/json');

$config = require '../config.php';
$mysqli = new mysqli(
    $config['database']['host'],
    $config['database']['user'],
    $config['database']['password'],
    $config['database']['database']
);

require "../usefulFuncs.php";

if (!isset($_GET['code'])) {
    errorResponse(400, "Bad Request: Missing code");
}

$verificationCode = $_GET['code'];
$stmt = $mysqli->prepare("SELECT user_id FROM codes WHERE code = ?");
$stmt->bind_param("s", $verificationCode);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    errorResponse(404, "Verification code not found");
}

$userId = $result->fetch_assoc()['user_id'];
$stmt = $mysqli->prepare("UPDATE users SET active = 1 WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();

$stmt = $mysqli->prepare("DELETE FROM codes WHERE user_id = ? AND code = ?");
$stmt->bind_param("is", $userId, $verificationCode);
$stmt->execute();

$stmt = $mysqli->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$username = $result->fetch_assoc()['username'];

header("Location: /login?msg=Аккаунт {$username} активирован! Теперь вы можете в него зайти! Спасибо что выбрали AwesomeFileSharing :3");
exit;