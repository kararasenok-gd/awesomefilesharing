<?php

$config = require '../config.php';

header('Content-Type: application/json');

$filesFolder = $config['files']['src'];
$size = 0;

if (is_dir($filesFolder)) {
    $files = scandir($filesFolder);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $filePath = $filesFolder . $file;
            $size += filesize($filePath);
        }
    }    
}

echo json_encode([
    "success" => true,
    "size" => $size,
    "files" => count($files) - 2,
    "max" => $config['files']['storageLimit']
]);