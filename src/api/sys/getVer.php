<?php

header('Content-Type: application/json');

$config = require '../config.php';
echo json_encode(['success' => true, 'version' => $config['meta']['version']]);