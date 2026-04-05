<?php
/**
 * Home / Index Page
 * Redirect ke dashboard jika sudah login, ke login jika belum
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/functions/helpers.php';

// Start session
startSecureSession();

if (isLoggedIn()) {
    // Redirect ke dashboard
    header('Location: ' . APP_URL . '/pages/dashboard/index.php');
    exit;
} else {
    // Redirect ke login
    header('Location: ' . APP_URL . '/pages/auth/login.php');
    exit;
}
?>