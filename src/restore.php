<!DOCTYPE html>
<html>
<head>
    <title>Восстановление неактивированного аккаунта без email</title>
</head>
<body>
    <form method="POST" action="">
        <input type="text" name="username" placeholder="Имя пользователя" required><br>
        <input type="password" name="password" placeholder="Пароль" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <button type="submit">Подтвердить</button>
    </form>
</body>
</html>


<?php
$config = require './api/config.php';
require './api/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Проверка наличия данных
if (!isset($_POST['username'], $_POST['password'], $_POST['email'])) {
    die("Заполните все поля: имя пользователя, пароль и email.");
}

$username = $_POST['username'];
$password = $_POST['password'];
$email = $_POST['email'];

// Подключение к БД
$mysqli = new mysqli(
    $config['database']['host'],
    $config['database']['user'],
    $config['database']['password'],
    $config['database']['database']
);
if ($mysqli->connect_error) {
    die("Ошибка подключения к БД: " . $mysqli->connect_error);
}

// Поиск пользователя
$stmt = $mysqli->prepare("SELECT id, password, email FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Пользователь не найден.");
}
$user = $result->fetch_assoc();

// Проверка пароля
if (md5($password) !== $user['password']) {
    die("Неверный пароль.");
}

// Проверка текущего email
if (!empty($user['email'])) {
    die("Email уже привязан к аккаунту.");
}

// Проверка занятости email
$stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    die("Email уже занят.");
}

// Генерация кода
$verificationCode = bin2hex(random_bytes(64));
$stmt = $mysqli->prepare("INSERT INTO codes (user_id, code) VALUES (?, ?)");
$stmt->bind_param("is", $user['id'], $verificationCode);
if (!$stmt->execute()) {
    die("Ошибка сохранения кода.");
}

// Отправка письма
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
    $mail->Subject = mb_encode_mimeheader("Awesome File Sharing - Восстановление неактивированного аккаунта", "UTF-8", "B");
    $link = "https://awesomefilesharing.rf.gd/api/sys/verify.php?code=$verificationCode";
    $mail->Body = "Привет, $username!<br><br>Мы получили заявку на восстановление аккаунта без почты и активации.<br><br>Для завершения восстановления, пожалуйста, перейдите по ссылке:<br><a href='$link'>$link</a>";
    $mail->isHTML(true);
    $mail->send();
    echo "Письмо с подтверждением отправлено.";
} catch (Exception $e) {
    die("Ошибка отправки: " . $mail->ErrorInfo);
}

$mysqli->close();
?>