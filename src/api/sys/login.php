<?php

session_start();
$config = require '../config.php';

header('Content-Type: application/json');

require "../usefulFuncs.php";

if (!isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['hcaptcha'])) {
    errorResponse(400, "Bad Request: Missing username, password, or hcaptcha");
}

$username = $_POST['username'];
$password = md5($_POST['password']);
$hcaptchaResponse = $_POST['hcaptcha'];

$hcaptchaSecret = $config['hcaptcha']['secret'];
$hcaptchaVerify = file_get_contents("https://hcaptcha.com/siteverify?secret=$hcaptchaSecret&response=$hcaptchaResponse");
$hcaptchaData = json_decode($hcaptchaVerify, true);
if (!$hcaptchaData['success']) {
    errorResponse(403, "Failed to check captcha. Try to complete it again");
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

$stmt = $mysqli->prepare("SELECT id, username, email, active FROM users WHERE username = ? AND password = ?");
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    errorResponse(401, "Invalid username or password");
}

$user = $result->fetch_assoc();

if ($user['active'] === 0) {
    errorResponse(403, "Account is not activated");
}

$sha256Email = hash('sha256', $user['email']);

$_SESSION['user'] = [
    "id" => $user['id'],
    "username" => $user['username'],
    "avatar" => "https://gravatar.com/avatar/$sha256Email",
];

$stmt->close();
$mysqli->close();

echo json_encode(["success" => true, "user" => $_SESSION['user']]);
exit;
