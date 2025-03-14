<?php
session_start();
$config = require '../../api/config.php';

if(!$config['admin']['enabled'] || !isset($_SESSION['user'])) {
    http_response_code(403);
    exit;
}

$mysqli = new mysqli(
    $config['database']['host'],
    $config['database']['user'],
    $config['database']['password'],
    $config['database']['database']
);

if($mysqli->connect_error) {
    http_response_code(500);
    exit;
}

if(!in_array($_SESSION['user']['id'], $config['admin']['allowed_ids'])) {
    http_response_code(403);
    exit;
}
?>