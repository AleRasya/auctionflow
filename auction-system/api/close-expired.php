<?php
/**
 * API untuk Menutup Lelang yang Expired
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/helpers.php';
require_once __DIR__ . '/../functions/auction.php';

// Proteksi - hanya admin
requireLogin();
$user = getUserSession();

if ($user['role'] !== 'admin') {
    setFlashMessage('error', 'Anda tidak memiliki akses');
    header('Location: ' . APP_URL . '/pages/dashboard/index.php');
    exit;
}

try {
    autoCloseExpiredAuctions($conn);
    setFlashMessage('success', 'Lelang yang sudah expired berhasil ditutup!');
} catch (Exception $e) {
    setFlashMessage('error', 'Gagal menutup lelang: ' . $e->getMessage());
}

header('Location: ' . APP_URL . '/pages/admin/index.php');
exit;
?>