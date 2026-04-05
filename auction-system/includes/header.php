<?php
/**
 * Header dan Navbar - Layout Umum
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/helpers.php';

// Proteksi halaman - harus login
requireLogin();

$user = getUserSession();
$messages = getAllFlashMessages();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Auction System</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
    <?php if (isset($extra_css)): ?>
        <?php echo $extra_css; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 0 2rem;">
            <div style="display: flex; align-items: center;">
                <a href="<?php echo APP_URL; ?>/pages/dashboard/index.php" class="navbar-brand">
                    🎯 AUCTION SYSTEM
                </a>
            </div>

            <div style="display: flex; align-items: center; gap: 2rem;">
                <div style="display: flex; gap: 1rem;">
                    <a href="<?php echo APP_URL; ?>/pages/products/browse.php" class="nav-link">Jelajahi Lelang</a>
                    <a href="<?php echo APP_URL; ?>/pages/dashboard/index.php" class="nav-link">Dashboard</a>
                    <?php if ($user['role'] === 'admin'): ?>
                        <a href="<?php echo APP_URL; ?>/pages/admin/index.php" class="nav-link">Admin Panel</a>
                    <?php endif; ?>
                </div>

                <div style="display: flex; align-items: center; gap: 1rem; border-left: 1px solid #333; padding-left: 1rem;">
                    <span style="color: #999; font-size: 0.9rem;">
                        <?php echo htmlspecialchars($user['full_name']); ?>
                    </span>
                    <a href="<?php echo APP_URL; ?>/pages/auth/logout.php" class="btn btn-sm btn-primary">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h5>Menu Utama</h5>
            </div>

            <div class="sidebar-item">
                <a href="<?php echo APP_URL; ?>/pages/dashboard/index.php" class="<?php echo (basename($_SERVER['PHP_SELF']) === 'index.php' && dirname($_SERVER['PHP_SELF']) === '/auction-system/pages/dashboard') ? 'active' : ''; ?>">
                    📊 Dashboard
                </a>
            </div>

            <div class="sidebar-item">
                <a href="<?php echo APP_URL; ?>/pages/products/browse.php" class="<?php echo (basename($_SERVER['PHP_SELF']) === 'browse.php') ? 'active' : ''; ?>">
                    🔍 Jelajahi Lelang
                </a>
            </div>

            <div class="sidebar-item">
                <a href="<?php echo APP_URL; ?>/pages/products/my-products.php" class="<?php echo (basename($_SERVER['PHP_SELF']) === 'my-products.php') ? 'active' : ''; ?>">
                    📦 Produk Saya
                </a>
            </div>

            <div class="sidebar-item">
                <a href="<?php echo APP_URL; ?>/pages/products/create.php" class="<?php echo (basename($_SERVER['PHP_SELF']) === 'create.php') ? 'active' : ''; ?>">
                    ➕ Buat Lelang
                </a>
            </div>

            <div class="sidebar-item">
                <a href="<?php echo APP_URL; ?>/pages/auction/my-bids.php" class="<?php echo (basename($_SERVER['PHP_SELF']) === 'my-bids.php') ? 'active' : ''; ?>">
                    💰 Penawaran Saya
                </a>
            </div>

            <div class="sidebar-item">
                <a href="<?php echo APP_URL; ?>/pages/auction/history.php" class="<?php echo (basename($_SERVER['PHP_SELF']) === 'history.php') ? 'active' : ''; ?>">
                    📜 Riwayat Lelang
                </a>
            </div>

            <div class="sidebar-item">
                <a href="<?php echo APP_URL; ?>/pages/profile/index.php" class="<?php echo (basename($_SERVER['PHP_SELF']) === 'index.php' && dirname($_SERVER['PHP_SELF']) === '/auction-system/pages/profile') ? 'active' : ''; ?>">
                    👤 Profil Saya
                </a>
            </div>

            <?php if ($user['role'] === 'admin'): ?>
                <hr style="border: none; border-top: 1px solid #333; margin: 1rem 0;">

                <div class="sidebar-item">
                    <a href="<?php echo APP_URL; ?>/pages/admin/index.php" class="<?php echo (basename($_SERVER['PHP_SELF']) === 'index.php' && dirname($_SERVER['PHP_SELF']) === '/auction-system/pages/admin') ? 'active' : ''; ?>">
                        ⚙️ Admin Panel
                    </a>
                </div>

                <div class="sidebar-item">
                    <a href="<?php echo APP_URL; ?>/pages/admin/users.php" class="<?php echo (basename($_SERVER['PHP_SELF']) === 'users.php') ? 'active' : ''; ?>">
                        👥 Kelola User
                    </a>
                </div>
            <?php endif; ?>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Alerts Container -->
            <div id="alerts-container">
                <?php if (!empty($messages['success'])): ?>
                    <div class="alert alert-success">
                        <span class="alert-close" onclick="this.parentElement.remove();">&times;</span>
                        <?php echo $messages['success']; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($messages['error'])): ?>
                    <div class="alert alert-error">
                        <span class="alert-close" onclick="this.parentElement.remove();">&times;</span>
                        <?php echo $messages['error']; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($messages['warning'])): ?>
                    <div class="alert alert-warning">
                        <span class="alert-close" onclick="this.parentElement.remove();">&times;</span>
                        <?php echo $messages['warning']; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($messages['info'])): ?>
                    <div class="alert alert-info">
                        <span class="alert-close" onclick="this.parentElement.remove();">&times;</span>
                        <?php echo $messages['info']; ?>
                    </div>
                <?php endif; ?>
            </div>