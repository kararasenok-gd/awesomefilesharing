<?php

function errorResponse($code, $message) {
    http_response_code($code);
    echo json_encode(["success" => false, "error" => $message]);
    exit;
}