<?php
require_once __DIR__ . '/../config/database.php';
requireLogin();

$user = getCurrentUser($connection);

// Get user's bids
$bids = getUserBids($connection, $user['id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bid Saya - Sistem Lelang Online</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="navbar-brand">
            <span>⚡</span> Lelang Online
        </div>
        <div class="navbar-user">
            <span><?php echo htmlspecialchars($user['full_name']); ?></span>
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
                <li><a href="/pages/my-products.php">🎯 Produk Saya</a></li>
                <li><a href="/pages/my-bids.php" class="active">💰 Bid Saya</a></li>
                <li><a href="/pages/my-wins.php">🏆 Kemenangan Saya</a></li>
                <?php if ($user['role'] === 'admin'): ?>
                    <div class="sidebar-section-title">Admin</div>
                    <li><a href="/pages/admin-products.php">📋 Kelola Produk</a></li>
                    <li><a href="/pages/admin-users.php">👥 Kelola User</a></li>
                    <li><a href="/pages/admin-bids.php">💵 Kelola Bid</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h1>Riwayat Bid Saya</h1>
            <p class="text-muted">Lihat semua bid yang Anda lakukan</p>

            <?php if (!empty($bids)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Gambar</th>
                                <th>Produk</th>
                                <th>Bid Amount</th>
                                <th>Waktu Bid</th>
                                <th>Status Lelang</th>
                                <th>Berakhir</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bids as $bid): ?>
                                <?php $remainingTime = getRemainingTime($bid['end_time']); ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo !empty($bid['image_path']) ? htmlspecialchars($bid['image_path']) : '/assets/images/placeholder.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($bid['title']); ?>"
                                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                    </td>
                                    <td><?php echo htmlspecialchars(substr($bid['title'], 0, 30)); ?></td>
                                    <td class="text-primary" style="font-weight: 700;">
                                        <?php echo formatCurrency($bid['bid_amount']); ?>
                                    </td>
                                    <td>
                                        <small><?php echo formatDateTime($bid['bid_time']); ?></small>
                                    </td>
                                    <td>
                                        <span class="product-status <?php echo $bid['status']; ?>">
                                            <?php echo ucfirst($bid['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small>
                                            <?php 
                                                if ($remainingTime['status'] === 'ended') {
                                                    echo 'Selesai';
                                                } else {
                                                    echo sprintf('%d hari', $remainingTime['days']);
                                                }
                                            ?>
                                        </small>
                                    </td>
                                    <td>
                                        <a href="/pages/product-detail.php?id=<?php echo $bid['product_id']; ?>" class="btn btn-primary btn-small">Detail</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body">
                        <div class="empty-state">
                            <div class="empty-state-icon">💰</div>
                            <h3>Belum Ada Bid</h3>
                            <p>Anda belum melakukan bid pada produk manapun</p>
                            <a href="/pages/products.php" class="btn btn-primary">Lihat Produk</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <script src="/assets/js/main.js"></script>
</body>
</html>