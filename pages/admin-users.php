<?php
require_once __DIR__ . '/../config/database.php';
requireAdmin($connection);

$user = getCurrentUser($connection);

// Get all users
$result = $connection->query("SELECT id, username, email, full_name, role, created_at, is_active FROM users ORDER BY created_at DESC");
$users = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Admin - Sistem Lelang Online</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="navbar-brand">
            <span>⚡</span> Lelang Online - Admin
        </div>
        <div class="navbar-user">
            <span><?php echo htmlspecialchars($user['full_name']); ?> (Admin)</span>
            <div class="navbar-user-menu">
                <div class="user-avatar"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></div>
                <a href="/pages/profile.php" class="btn btn-small">Profil</a>
                <a href="/pages/logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </div>

    <div class="sidebar-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li><a href="/pages/dashboard.php">📊 Dashboard</a></li>
                <li><a href="/pages/products.php">📦 Produk</a></li>
                <div class="sidebar-section-title">Admin</div>
                <li><a href="/pages/admin-products.php">📋 Kelola Produk</a></li>
                <li><a href="/pages/admin-users.php" class="active">👥 Kelola User</a></li>
                <li><a href="/pages/admin-bids.php">💵 Kelola Bid</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h1>Kelola User</h1>
            <p class="text-muted">Mengelola semua user dalam sistem</p>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Nama Lengkap</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Terdaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($u['username']); ?></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                                    <td>
                                        <strong><?php echo ucfirst($u['role']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="product-status <?php echo $u['is_active'] ? 'active' : 'cancelled'; ?>">
                                            <?php echo $u['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small><?php echo formatDate($u['created_at']); ?></small>
                                    </td>
                                    <td>
                                        <small class="text-muted">Lihat profil</small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted" style="padding: 40px;">
                                    Tidak ada user
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <p class="text-muted mt-30">
                Total User: <strong><?php echo count($users); ?></strong>
            </p>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <script src="/assets/js/main.js"></script>
</body>
</html>