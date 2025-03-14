<?php
require '../../api/config.php';
require '../inc/config.php';
require '../../api/usefulFuncs.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$userId = (int)$_POST['user_id'];

// Логируем действие
logAction($mysqli, $_SESSION['user']['id'], "Удаление пользователя ID: $userId");

// Получаем файлы пользователя
$stmt = $mysqli->prepare("SELECT filename FROM files WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$files = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Удаляем файлы
foreach ($files as $file) {
    $filePath = $config['files']['src'] . $file['filename'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

// Удаляем из БД
$mysqli->begin_transaction();
try {
    $stmt = $mysqli->prepare("DELETE FROM files WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    $stmt = $mysqli->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    $mysqli->commit();
} catch (Exception $e) {
    $mysqli->rollback();
    http_response_code(500);
    exit;
}

header("Location: users.php");