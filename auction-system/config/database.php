<?php
/**
 * Konfigurasi Database
 * 
 * File ini berisi pengaturan koneksi ke database MySQL
 * Gunakan prepared statement untuk keamanan
 */

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'auction_system');
define('DB_PORT', 3306);

// Membuat koneksi mysqli
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset UTF-8
$conn->set_charset("utf8mb4");

// Konfigurasi aplikasi
define('APP_NAME', 'Auction System');
define('APP_URL', 'http://localhost/auction-system');
define('UPLOAD_DIR', __DIR__ . '/../uploads/products/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Konfigurasi Session
define('SESSION_TIMEOUT', 3600); // 1 jam

// Konfigurasi Email (opsional)
define('MAIL_FROM', 'noreply@auction.com');

?>