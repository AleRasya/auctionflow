<?php
require_once __DIR__ . '/../config/database.php';
requireLogin();

$user = getCurrentUser($connection);
$userStats = getUserDashboardStats($connection, $user['id']);

// Get recent auctions
$recentProducts = getProductsByUser($connection, $user['id']);

// Get dashboard stats for admin
if ($user['role'] === 'admin') {
    $allStats = getDashboardStats($connection);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Lelang Online</title>
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
                <li><a href="/pages/dashboard.php" class="active">📊 Dashboard</a></li>
                <li><a href="/pages/products.php">📦 Produk</a></li>
                <li><a href="/pages/my-products.php">🎯 Produk Saya</a></li>
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
            <h1>Dashboard</h1>
            <p class="text-muted">Selamat datang, <?php echo htmlspecialchars($user['full_name']); ?>!</p>

            <?php if ($user['role'] === 'admin'): ?>
                <!-- Admin Dashboard -->
                <h2 class="mt-30">Statistik Sistem</h2>
                <div class="stats-grid">
                    <div class="stat-card primary">
                        <div class="stat-label">Produk Aktif</div>
                        <div class="stat-value"><?php echo $allStats['active_products']; ?></div>
                    </div>
                    <div class="stat-card success">
                        <div class="stat-label">Total Bid</div>
                        <div class="stat-value"><?php echo $allStats['total_bids']; ?></div>
                    </div>
                    <div class="stat-card warning">
                        <div class="stat-label">Total User</div>
                        <div class="stat-value"><?php echo $allStats['total_users']; ?></div>
                    </div>
                    <div class="stat-card danger">
                        <div class="stat-label">Lelang Selesai</div>
                        <div class="stat-value"><?php echo $allStats['ended_auctions']; ?></div>
                    </div>
                </div>
            <?php else: ?>
                <!-- User Dashboard -->
                <h2 class="mt-30">Statistik Saya</h2>
                <div class="stats-grid">
                    <div class="stat-card primary">
                        <div class="stat-label">Produk Saya</div>
                        <div class="stat-value"><?php echo $userStats['my_products']; ?></div>
                    </div>
                    <div class="stat-card success">
                        <div class="stat-label">Bid Saya</div>
                        <div class="stat-value"><?php echo $userStats['my_bids']; ?></div>
                    </div>
                    <div class="stat-card warning">
                        <div class="stat-label">Kemenangan</div>
                        <div class="stat-value"><?php echo $userStats['my_wins']; ?></div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <h2 class="mt-30">Aksi Cepat</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <a href="/pages/create-product.php" class="card" style="padding: 25px; text-align: center; text-decoration: none; transition: all 0.3s ease;">
                        <div style="font-size: 40px; margin-bottom: 10px;">➕</div>
                        <h4 style="margin-bottom: 5px;">Buat Lelang</h4>
                        <p class="text-muted" style="margin: 0;">Mulai lelang produk baru</p>
                    </a>
                    <a href="/pages/products.php" class="card" style="padding: 25px; text-align: center; text-decoration: none; transition: all 0.3s ease;">
                        <div style="font-size: 40px; margin-bottom: 10px;">🔍</div>
                        <h4 style="margin-bottom: 5px;">Cari Produk</h4>
                        <p class="text-muted" style="margin: 0;">Lihat semua produk lelang</p>
                    </a>
                    <a href="/pages/my-wins.php" class="card" style="padding: 25px; text-align: center; text-decoration: none; transition: all 0.3s ease;">
                        <div style="font-size: 40px; margin-bottom: 10px;">🏆</div>
                        <h4 style="margin-bottom: 5px;">Kemenangan</h4>
                        <p class="text-muted" style="margin: 0;">Lihat lelang yang Anda menangkan</p>
                    </a>
                </div>

                <!-- Recent Products -->
                <?php if (!empty($recentProducts)): ?>
                    <h2 class="mt-30">Produk Terbaru Saya</h2>
                    <div class="products-grid">
                        <?php foreach (array_slice($recentProducts, 0, 3) as $product): ?>
                            <div class="product-card">
                                <img src="<?php echo !empty($product['image_path']) ? htmlspecialchars($product['image_path']) : '/assets/images/placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($product['title']); ?>" class="product-image">
                                <div class="product-info">
                                    <h5 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h5>
                                    <div class="product-price"><?php echo formatCurrency($product['current_price']); ?></div>
                                    <span class="product-status <?php echo $product['status']; ?>"><?php echo ucfirst($product['status']); ?></span>
                                    <div class="product-footer">
                                        <a href="/pages/product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-small" style="margin-bottom: 5px;">Detail</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Information Section -->
            <div class="card mt-30">
                <div class="card-header">
                    ℹ️ Informasi Penting
                </div>
                <div class="card-body">
                    <ul style="margin: 0; padding-left: 20px;">
                        <li>Pastikan data profil Anda selalu terbaru</li>
                        <li>Bid Anda harus lebih tinggi dari bid sebelumnya minimal Rp 1.000</li>
                        <li>Pemenang lelang adalah user dengan bid tertinggi saat waktu berakhir</li>
                        <li>Hubungi admin jika ada kendala atau pertanyaan</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <script src="/assets/js/main.js"></script>
</body>
</html>