<?php
header('Content-Type: application/json');

if (isset($_GET['err'])) {
    $errorMessages = [
        '403' => 'Forbidden',
        '404' => 'Not found'
    ];
    
    $errorCode = $_GET['err'];
    $message = $errorMessages[$errorCode] ?? 'Unknown error';
    
    http_response_code((int)$errorCode);
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

$config = require 'config.php';

$mysqli = new mysqli(
    $config['database']['host'],
    $config['database']['user'],
    $config['database']['password'],
    $config['database']['database']
);

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    $stmt = $mysqli->prepare("SELECT * FROM short WHERE code = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Code not found']);
        exit;
    }

    $row = $result->fetch_assoc();

    if (time() > $row['expire']) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Code expired']);

        $stmt = $mysqli->prepare("DELETE FROM short WHERE code = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();

        exit;
    }

    $path = $config['files']['baseUrl'] . $row['filename'];
    header("Location: $path");
}