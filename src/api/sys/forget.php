<?php

$config = require '../config.php';
require "../usefulFuncs.php";
require "../vendor/autoload.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if (!isset($_POST['email'])) {
    errorResponse(400, "Bad Request: Missing email");
}

$email = $_POST['email'];

$mysqli = new mysqli(
    $config['database']['host'],
    $config['database']['user'],
    $config['database']['password'],
    $config['database']['database']
);

// checking if user exists
$stmt = $mysqli->prepare("SELECT id, username FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    errorResponse(404, "User not found");
}
$user = $result->fetch_assoc();

// generating verification code
$verificationCode = generateRandomString(128);
$newPasswd = generateRandomString(16);

// inserting verification code into database
$stmt = $mysqli->prepare("INSERT INTO restore (user_id, code, new_passwd) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $user['id'], $verificationCode, $newPasswd);
$stmt->execute();
$stmt->close();

// sending email
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = $config['mail']['smtp_host'];
    $mail->SMTPAuth = $config['mail']['smtp_auth'];
    $mail->Username = $config['mail']['smtp_username'];
    $mail->Password = $config['mail']['smtp_password'];
    $mail->SMTPSecure = $config['mail']['smtp_secure'];
    $mail->Port = $config['mail']['smtp_port'];
    $mail->setFrom($config['mail']['from_email'], $config['mail']['from_name']);
    $mail->addAddress($email);
    $mail->Subject = mb_encode_mimeheader("Awesome File Sharing - Восстановление пароля", "UTF-8", "B");
    $mail->Body = "
    <html>
    <body>
        <p>Привет, <strong>{$user['username']}</strong>!</p>

        <p>Мы получили запрос на восстановление пароля. Он уже готов, осталось подтвердить его сброс.</p>
        <br>
        <p><strong>Ваш новый пароль:</strong> {$newPasswd} | Его можно изменить в настройках</p>
        <p><strong>Код подтверждения:</strong> <a href='https://awesomefilesharing.rf.gd/api/sys/restore.php?code={$verificationCode}'>https://awesomefilesharing.rf.gd/api/sys/restore.php?code={$verificationCode}</a></p>
        <br>

        <p>Если вы не запрашивали сброс пароля, просто проигнорируйте это письмо.</p>
    </body>
    </html>";
    $mail->isHTML();

    $mail->send();
} catch (Exception $e) {
    errorResponse(500, "Failed to send verification email");
}

echo json_encode(["success" => true]);