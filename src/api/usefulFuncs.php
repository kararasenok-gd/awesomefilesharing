<?php

function errorResponse($code, $message) {
    http_response_code($code);
    echo json_encode(["success" => false, "error" => $message]);
    exit;
}

function generateRandomString($length) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function logAction($mysqli, $userId, $action) {
    $timestamp = time();
    $stmt = $mysqli->prepare("INSERT INTO logs (user_id, action, timestamp) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $userId, $action, $timestamp);
    $stmt->execute();
    $stmt->close();
}