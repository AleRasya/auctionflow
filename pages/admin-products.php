<?php
require_once __DIR__ . '/../config/database.php';
requireAdmin($connection);

$user = getCurrentUser($connection);

// Get all products
$products = getAllProducts($connection, []);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Admin - Sistem Lelang Online</title>
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
                <li><a href="/pages/admin-products.php" class="active">📋 Kelola Produk</a></li>
                <li><a href="/pages/admin-users.php">👥 Kelola User</a></li>
                <li><a href="/pages/admin-bids.php">💵 Kelola Bid</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h1>Kelola Produk Lelang</h1>
            <p class="text-muted">Mengelola semua produk dalam sistem</p>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Gambar</th>
                            <th>Judul</th>
                            <th>Penjual</th>
                            <th>Harga Awal</th>
                            <th>Harga Tertinggi</th>
                            <th>Status</th>
                            <th>Bid</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo !empty($product['image_path']) ? htmlspecialchars($product['image_path']) : '/assets/images/placeholder.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($product['title']); ?>"
                                             style="width: 40px; height: 40px; object-fit: cover; border-radius: 5px;">
                                    </td>
                                    <td><?php echo htmlspecialchars(substr($product['title'], 0, 30)); ?></td>
                                    <td><?php echo htmlspecialchars($product['full_name']); ?></td>
                                    <td><?php echo formatCurrency($product['starting_price']); ?></td>
                                    <td><?php echo formatCurrency($product['highest_bid'] ?? $product['starting_price']); ?></td>
                                    <td>
                                        <span class="product-status <?php echo $product['status']; ?>">
                                            <?php echo ucfirst($product['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $product['bid_count']; ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="/pages/product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-small">Lihat</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted" style="padding: 40px;">
                                    Tidak ada produk
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <p class="text-muted mt-30">
                Total Produk: <strong><?php echo count($products); ?></strong>
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