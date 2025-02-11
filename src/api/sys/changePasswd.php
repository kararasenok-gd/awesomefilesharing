<?php

// Скрипт на смену пароля

header('Content-Type: application/json');
session_start();

$config = require '../config.php';
require "../usefulFuncs.php";

if (!isset($_SESSION['user'])) {
    errorResponse(401, "User is not logged in");
}

if (!isset($_POST['oldPasswd']) || !isset($_POST['newPasswd'])) {
    errorResponse(400, "Bad Request");
}

$oldPasswd = md5($_POST['oldPasswd']);
$newPasswd = md5($_POST['newPasswd']);

if (
    strlen($_POST['newPasswd']) < $config['accounts']['minPasswordLength'] ||
    ($config['accounts']['passwordRequired']['uppercase'] && !preg_match('/[A-Z]/', $password)) ||
    ($config['accounts']['passwordRequired']['numbers'] && !preg_match('/[0-9]/', $password)) ||
    ($config['accounts']['passwordRequired']['symbols'] && !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password))
) {
    $requiredUpper = $config['accounts']['passwordRequired']['uppercase'] ? "yes" : "no";
    $requiredNumbers = $config['accounts']['passwordRequired']['numbers'] ? "yes" : "no";
    $requiredSymbols = $config['accounts']['passwordRequired']['symbols'] ? "yes" : "no";
    errorResponse(400, "Password does not meet security requirements.\n\nServer Security Requirements:\n" .
        "- Minimum length: {$config['accounts']['minPasswordLength']} characters\n" .
        "- Uppercase: {$requiredUpper}\n" .
        "- Numbers: {$requiredNumbers}\n" .
        "- Symbols: {$requiredSymbols}");
}

$mysqli = new mysqli($config['database']['host'], $config['database']['user'], $config['database']['password'], $config['database']['database']);
if ($mysqli->connect_error) {
    errorResponse(500, "Internal Server Error");
}

$user = $_SESSION['user'];

$stmt = $mysqli->prepare("SELECT id FROM users WHERE id = ? AND password = ?");
$stmt->bind_param("is", $user, $oldPasswd);
$stmt->execute();

if ($stmt->get_result()->num_rows === 0) {
    errorResponse(401, "Username or password is incorrect");
}

$stmt = $mysqli->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->bind_param("si", $newPasswd, $user);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    errorResponse(500, "Internal Server Error");
}

echo json_encode(["success" => true]);
exit;