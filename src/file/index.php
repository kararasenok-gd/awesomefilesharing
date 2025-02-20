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
    $nsfwTag = "Отсутствует";
    if ($isNSFW) {
        // $fileClasses .= " nsfw";
        $nsfwTag = "Присутствует";
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
        <title>AFSView - {$fileName}</title>
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
                    NSFW Тег: {$nsfwTag}<br>
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
} else {
    $filePath = "../uploads/" . $name;

    if (!file_exists($filePath)) {
        http_response_code(404);
        echo json_encode(["error" => "File not found."]);
        exit;
    }

    $fileSize = filesize($filePath);
    $mimeType = mime_content_type($filePath);

    header("Content-Type: $mimeType");
    header("Accept-Ranges: bytes");
    header("Content-Length: $fileSize");

    if (isset($_SERVER['HTTP_RANGE'])) {
        $range = $_SERVER['HTTP_RANGE'];
        if (strpos($range, 'bytes=') === 0) {
            $range = substr($range, 6);
            list($start, $end) = explode('-', $range);

            $start = intval($start);
            $end = $end === '' ? $fileSize - 1 : intval($end);

            if ($start >= $fileSize || $end >= $fileSize || $start > $end) {
                http_response_code(416);
                header("Content-Range: bytes */$fileSize");
                exit;
            }

            $length = $end - $start + 1;
            http_response_code(206);
            header("Content-Range: bytes $start-$end/$fileSize");
            header("Content-Length: $length");

            $file = fopen($filePath, 'rb');
            fseek($file, $start);
            echo fread($file, $length);
            fclose($file);
        } else {
            http_response_code(416);
            header("Content-Range: bytes */$fileSize");
            exit;
        }
    } else {
        readfile($filePath);
    }
    exit();
}

function formatBytes($bytes) {
    $units = ['Б', 'КБ', 'МБ', 'ГБ', 'ТБ'];
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.2f", $bytes / pow(1024, $factor)) . " " . $units[$factor];
}
