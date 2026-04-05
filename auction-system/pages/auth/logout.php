<?php
/**
 * Halaman Logout
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../functions/helpers.php';

// Logout user
logout();

// Redirect ke login
setFlashMessage('success', 'Anda telah logout');
header('Location: ' . APP_URL . '/pages/auth/login.php');
exit;
?>