<?php
require_once __DIR__ . '/../config/database.php';
requireAdmin($connection);

$user = getCurrentUser($connection);

// Get all bids
$result = $connection->query(
    "SELECT b.*, u.username, u.full_name, p.title
     FROM bids b
     JOIN users u ON b.user_id = u.id
     JOIN products p ON b.product_id = p.id
     ORDER BY b.bid_time DESC
     LIMIT 100"
);
$bids = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Bid - Admin - Sistem Lelang Online</title>
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
                <li><a href="/pages/admin-users.php">👥 Kelola User</a></li>
                <li><a href="/pages/admin-bids.php" class="active">💵 Kelola Bid</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h1>Kelola Bid</h1>
            <p class="text-muted">Monitoring semua bid dalam sistem (100 terbaru)</p>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Pembid</th>
                            <th>Jumlah Bid</th>
                            <th>Waktu Bid</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($bids)): ?>
                            <?php foreach ($bids as $bid): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(substr($bid['title'], 0, 30)); ?></td>
                                    <td><?php echo htmlspecialchars($bid['full_name']); ?></td>
                                    <td class="text-primary" style="font-weight: 700;">
                                        <?php echo formatCurrency($bid['bid_amount']); ?>
                                    </td>
                                    <td>
                                        <small><?php echo formatDateTime($bid['bid_time']); ?></small>
                                    </td>
                                    <td>
                                        <a href="/pages/product-detail.php?id=<?php echo $bid['product_id']; ?>" class="btn btn-primary btn-small">Detail</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted" style="padding: 40px;">
                                    Tidak ada bid
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <p class="text-muted mt-30">
                Total Bid (100 terbaru): <strong><?php echo count($bids); ?></strong>
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