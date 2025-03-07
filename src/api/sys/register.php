<?php

session_start();
$config = require '../config.php';
require __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

require "../usefulFuncs.php";

if (!$config['accounts']['registration']) {
    errorResponse(403, "Registration is disabled in the server configuration.\n\nIf you a visitor, please contact the server administrator.\nIf you are a server administrator, please enable registration in the server configuration ('\"registration\" => true' in \"accounts\" section).");
}

if (!isset($_POST['username'], $_POST['password'], $_POST['email'], $_POST['hcaptcha'])) {
    errorResponse(400, "Bad Request: Missing username, password, email, or hcaptcha");
}

$username = $_POST['username'];
$password = $_POST['password'];
$email = $_POST['email'];
$hcaptchaResponse = $_POST['hcaptcha'];

if (strlen($username) < $config['accounts']['minUsernameLength'] || strlen($username) > $config['accounts']['maxUsernameLength']) {
    errorResponse(400, "Username length must be between {$config['accounts']['minUsernameLength']} and {$config['accounts']['maxUsernameLength']} characters");
}

if (
    strlen($password) < $config['accounts']['minPasswordLength'] ||
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

$hcaptchaSecret = $config['hcaptcha']['secret'];
$hcaptchaVerify = file_get_contents("https://hcaptcha.com/siteverify?secret=$hcaptchaSecret&response=$hcaptchaResponse");
$hcaptchaData = json_decode($hcaptchaVerify, true);
if (!$hcaptchaData['success']) {
    errorResponse(403, "Failed to check captcha. Try to complete it again");
}

$hashedPassword = md5($password);

$mysqli = new mysqli(
    $config['database']['host'],
    $config['database']['user'],
    $config['database']['password'],
    $config['database']['database']
);
if ($mysqli->connect_error) {
    errorResponse(500, "Database connection failed");
}

$stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    errorResponse(409, "Username or email already exists");
}
$stmt->close();

$active = $config['accounts']['emailVerification'] ? 0 : 1;

$stmt = $mysqli->prepare("INSERT INTO users (username, password, email, active) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sssi", $username, $hashedPassword, $email, $active);
$stmt->execute();
$userId = $stmt->insert_id;
$stmt->close();

if ($config['accounts']['emailVerification']) {
    $verificationCode = generateRandomString(128);
    $stmt = $mysqli->prepare("INSERT INTO codes (user_id, code) VALUES (?, ?)");
    $stmt->bind_param("is", $userId, $verificationCode);
    $stmt->execute();
    $stmt->close();

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
        $mail->Subject = mb_encode_mimeheader("Awesome File Sharing - Подтверждение регистрации", "UTF-8", "B");
        $mail->Body = "
    <html>
    <body style='font-family: Arial, sans-serif; line-height: 1.6;'>
        <p>Привет, <strong>$username</strong>!</p>
        
        <p>Спасибо за регистрацию на Awesome File Sharing!</p>
        
        <p>Для завершения регистрации, пожалуйста, перейдите по ссылке:<br>
        <a href='https://awesomefilesharing.rf.gd/api/sys/verify.php?code=$verificationCode'>
            https://awesomefilesharing.rf.gd/api/sys/verify.php?code=$verificationCode
        </a></p>

        <p>Если вы не регистрировались на Awesome File Sharing, просто проигнорируйте это письмо.</p>
        
        <p style='margin-top: 20px; color: #666;'>
            С уважением,<br>
            Команда Awesome File Sharing
        </p>
    </body>
    </html>
";
        $mail->AltBody = "Привет, $username!\n\n
Спасибо за регистрацию на Awesome File Sharing!\n\n
Для завершения регистрации перейдите по ссылке:\n
https://awesomefilesharing.rf.gd/api/sys/verify.php?code=$verificationCode\n\n
Если вы не регистрировались, проигнорируйте это письмо.\n\n
С уважением,\n
Команда Awesome File Sharing";
        $mail->isHTML(true);
        $mail->send();
    } catch (Exception $e) {
        errorResponse(500, "Failed to send verification email");
    }
}

$mysqli->close();

echo json_encode(["success" => true, "message" => $config['accounts']['emailVerification'] ? "Verification email sent" : "Registration successful"]);
exit;

