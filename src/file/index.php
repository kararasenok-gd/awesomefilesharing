<?php

header('Content-Type: application/json');

$config = require '../api/config.php';

if (!isset($_GET['name'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing parameters."]);
    exit;
}

$name = basename($_GET['name']);
$filePath = "../uploads/" . $name;

if (!file_exists($filePath)) {
    http_response_code(404);
    echo json_encode(["error" => "File not found."]);
    exit;
}

if (!isset($_GET['raw'])) {
    $fileSize = filesize($filePath);
    $fileType = mime_content_type($filePath);
    $fileName = basename($filePath);
    $filePreview = "";

    $mysqli = new mysqli(
        $config['database']['host'],
        $config['database']['user'],
        $config['database']['password'],
        $config['database']['database']
    );

    if ($mysqli->connect_error) {
        http_response_code(500);
        echo json_encode(["error" => "Database connection failed."]);
        exit;
    }

    $stmt = $mysqli->prepare("SELECT * FROM files WHERE filename = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(["error" => "File not found in the database."]);
        exit;
    }

    $row = $result->fetch_assoc();
    $isNSFW = $row['is_nsfw'] === 1;
    $user_id = $row['user_id'];

    $stmt = $mysqli->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $userResult = $stmt->get_result();

    if ($userResult->num_rows === 0) {
        http_response_code(404);
        echo json_encode(["error" => "User not found in the database."]);
        exit;
    }

    $userRow = $userResult->fetch_assoc();
    $owner = $userRow['username'];

    $fileClasses = "file-preview";
    if ($isNSFW) {
        $fileClasses .= " nsfw";
    }

    if (strpos($fileType, 'image') !== false) {
        $filePreview = "<img src='../file/?name={$name}&raw=1' class='{$fileClasses}' style='max-width: 100%; height: auto; border-radius: 8px;' />";
    } elseif (strpos($fileType, 'video') !== false) {
        $filePreview = "<video controls class='{$fileClasses}' style='max-width: 100%; border-radius: 8px;'>
                            <source src='../file/?name={$name}&raw=1' type='{$fileType}'>
                        </video>";
    } elseif (strpos($fileType, 'audio') !== false) {
        $filePreview = "<audio controls class='file-preview' style='width: 100%;'>
                            <source src='../file/?name={$name}&raw=1' type='{$fileType}'>
                        </audio>";
    } elseif (strpos($fileType, 'application/zip') !== false) {
        $filePreview = "<iframe src='../file/zip.php?file={$name}' class='file-preview' style='width: 100%; height: 500px; border-radius: 8px; border: none;'></iframe>";
    } else {
        $fileContent = htmlspecialchars(file_get_contents($filePath));
        $filePreview = "<pre class='{$fileClasses}' style='background-color: #312d2b; color: #b8b8b8; padding: 10px; border-radius: 8px; text-align: left; white-space: pre-wrap; overflow-x: auto; max-height: 300px;'>{$fileContent}</pre>";
    }

    header("Content-Type: text/html");
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Просмотр файла: {$fileName}</title>
        <style>
            body { background-color: #282828; color: #846f65; font-family: Arial, sans-serif; text-align: center; padding: 50px; }
            .container { max-width: 800px; margin: 0 auto; }
            .file-view { background-color: #312d2b; padding: 20px; border-radius: 8px; }
            .file-name { font-size: 1.5em; margin-bottom: 10px; color: #f4c542; }
            .file-info { font-size: 1.2em; margin-bottom: 20px; }
            .download-btn, .view-btn { background-color: #846f65; color: #282828; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; }
            .download-btn:hover, .view-btn:hover { background-color: #312d2b; color: #846f65; }
            .file-preview { border-radius: 8px; margin-top: 20px; }
            .nsfw { filter: blur(25px); cursor: pointer; transition: filter 0.3s ease; }
            .unblurred { filter: none !important; transition: filter 0.3s ease; }

            @media (max-width: 600px) { 
                .file-name { font-size: 1.2em; }
                .file-info { font-size: 1em; }
            }

            @media (max-width: 500px) {
                .file-name { font-size: 1em; }
                .file-info { font-size: 0.8em; }
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='file-view'>
                <div class='file-name'>{$fileName}</div>
                <div class='file-info'>
                    Размер: " . formatBytes($fileSize) . "<br>
                    Тип файла: {$fileType}<br>
                    Владелец: {$owner}<br>
                </div>
                <div class='file-preview'>
                    {$filePreview}
                </div>
                <br>
                <a href='../file/?name={$name}&raw=1' class='view-btn'>Прямая ссылка</a>
                <a href='../file/?name={$name}&raw=1' download='{$fileName}' class='download-btn'>Скачать файл</a>
            </div>
        </div>
        <script src='../scripts/blur.js'></script>
    </body>
    </html>";

    exit();
}

header("Content-Type: " . mime_content_type($filePath));
header("Accept-Ranges: bytes");
readfile($filePath);
exit();

function formatBytes($bytes) {
    $units = ['Б', 'КБ', 'МБ', 'ГБ', 'ТБ'];
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.2f", $bytes / pow(1024, $factor)) . " " . $units[$factor];
}

