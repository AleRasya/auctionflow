<?php
// Redirect to login or dashboard based on session
require_once __DIR__ . '/config/database.php';

if (isLoggedIn()) {
    header('Location: /pages/dashboard.php');
} else {
    header('Location: /pages/login.php');
}
exit;
?>