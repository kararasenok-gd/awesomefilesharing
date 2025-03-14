<?php
require '../../api/config.php';
require '../inc/config.php';
require '../../api/usefulFuncs.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$fileId = (int)$_POST['file_id'];
$filename = $mysqli->real_escape_string($_POST['filename']);

// Логируем действие
logAction($mysqli, $_SESSION['user']['id'], "Удаление файла ID: $fileId ($filename)");

// Удаляем файл
$filePath = $config['files']['src'] . $filename;
if (file_exists($filePath)) {
    unlink($filePath);
}

// Удаляем из БД
$stmt = $mysqli->prepare("DELETE FROM files WHERE id = ?");
$stmt->bind_param("i", $fileId);
$stmt->execute();
$stmt->close();

header("Location: files.php");