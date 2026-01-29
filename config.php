<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');       
define('DB_PORT', 3306);    
define('DB_NAME', 'trioclosetdb');
function db_connect() {
    static $conn = null;
    if ($conn !== null) return $conn;

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

    if ($conn->connect_errno) {
        die('Database connection failed: ' . $conn->connect_error);
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

