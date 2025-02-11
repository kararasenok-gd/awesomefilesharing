<?php

session_start();

if (isset($_SESSION['user'])) {
    unset($_SESSION['user']);
    session_destroy();
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
    http_response_code(401);
}