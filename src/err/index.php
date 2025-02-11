<?php

header('Content-Type: application/json');

if (!isset($_GET['code'])) {
    http_response_code(400);
    echo json_encode(["code" => 400, "error" => "Missing parameters."]);
    exit;
}

$code = $_GET['code'];

$codeExplanations = [
    400 => "Bad Request",
    401 => "Unauthorized",
    403 => "Forbidden",
    404 => "Not Found",
    405 => "Method Not Allowed",
    413 => "Payload Too Large",
    500 => "Internal Server Error",
    503 => "Service Unavailable"
];

if (isset($codeExplanations[$code])) {
    http_response_code($code);
    echo json_encode(["code" => $code, "error" => $codeExplanations[$code]]);
    exit;
}

echo json_encode(["code" => $code, "error" => "Explanation of this error is not found."]);