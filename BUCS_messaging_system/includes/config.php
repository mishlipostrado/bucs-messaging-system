<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'bucs_messaging');
define('APP_NAME', 'BUCS Messaging System');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');

function getDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

function sanitize($conn, $val) {
    return $conn->real_escape_string(htmlspecialchars(trim($val)));
}

session_start();