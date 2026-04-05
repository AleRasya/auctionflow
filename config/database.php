<?php
// Konfigurasi database
// Database Connection Configuration

// Environment configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'auction_system');
define('DB_PORT', 3306);

// Create connection menggunakan MySQLi
$connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Check connection
if ($connection->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Connection failed: ' . $connection->connect_error
    ]));
}

// Set charset to UTF-8
$connection->set_charset("utf8mb4");

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Error handling
if ($_SERVER['REQUEST_METHOD'] === 'GET' && basename($_SERVER['PHP_SELF']) === 'database.php') {
    http_response_code(403);
    die('Direct access not allowed');
}

// Autoload functions
require_once __DIR__ . '/functions.php';
?>