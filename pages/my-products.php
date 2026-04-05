<?php
require_once __DIR__ . '/../config/database.php';
requireLogin();

$user = getCurrentUser($connection);

// Get user's products
$products = getProductsByUser($connection, $user['id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk Saya - Sistem Lelang Online</title>
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
                <li><a href="/pages/my-products.php" class="active">🎯 Produk Saya</a></li>
                <li><a href="/pages/my-bids.php">💰 Bid Saya</a></li>
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
            <h1>Produk Lelang Saya</h1>
            <p class="text-muted">Kelola semua produk lelang Anda</p>

            <a href="/pages/create-product.php" class="btn btn-primary" style="margin-bottom: 20px;">➕ Buat Produk Baru</a>

            <?php if (!empty($products)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Gambar</th>
                                <th>Judul Produk</th>
                                <th>Harga Awal</th>
                                <th>Harga Tertinggi</th>
                                <th>Bid</th>
                                <th>Status</th>
                                <th>Berakhir</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <?php $remainingTime = getRemainingTime($product['end_time']); ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo !empty($product['image_path']) ? htmlspecialchars($product['image_path']) : '/assets/images/placeholder.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($product['title']); ?>"
                                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                    </td>
                                    <td><?php echo htmlspecialchars(substr($product['title'], 0, 30)); ?></td>
                                    <td><?php echo formatCurrency($product['starting_price']); ?></td>
                                    <td><?php echo formatCurrency($product['highest_bid'] ?? $product['starting_price']); ?></td>
                                    <td><?php echo $product['bid_count']; ?></td>
                                    <td>
                                        <span class="product-status <?php echo $product['status']; ?>">
                                            <?php echo ucfirst($product['status']); ?>
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
                                        <div class="btn-group">
                                            <a href="/pages/product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-small">Lihat</a>
                                            <?php if ($product['status'] === 'active' && $product['bid_count'] === 0): ?>
                                                <button type="button" class="btn btn-danger btn-small" onclick="deleteProduct(<?php echo $product['id']; ?>)">Hapus</button>
                                            <?php endif; ?>
                                        </div>
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
                            <div class="empty-state-icon">📭</div>
                            <h3>Belum Ada Produk</h3>
                            <p>Anda belum membuat produk lelang. Mulai sekarang!</p>
                            <a href="/pages/create-product.php" class="btn btn-primary">Buat Produk Pertama</a>
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