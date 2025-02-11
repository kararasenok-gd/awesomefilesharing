<?php

session_start();
header("Content-Type: application/json");

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode([
        "success" => false
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "user" => $_SESSION['user']
]);