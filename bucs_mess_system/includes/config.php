<?php
// includes/config.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'bucs_messaging');
define('APP_NAME', 'BUCS Messaging System');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('BASE_URL',   '/bucs/');          // change if your folder name differs

function getDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die('DB Error: ' . $conn->connect_error);
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

function sanitize($conn, $val) {
    return $conn->real_escape_string(htmlspecialchars(trim($val)));
}

function redirect($path) {
    header('Location: ' . BASE_URL . $path);
    exit;
}

function auth_guard() {
    if (!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
    return $_SESSION['user'];
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
