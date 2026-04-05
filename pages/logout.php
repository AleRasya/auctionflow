<?php
require_once __DIR__ . '/../config/database.php';

$user = getCurrentUser($connection);
if ($user) {
    logAudit($connection, $user['id'], 'LOGOUT', 'User logged out');
}

logoutUser();
?>