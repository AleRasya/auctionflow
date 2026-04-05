<?php
require_once __DIR__ . '/../config/database.php';
requireLogin();

$user = getCurrentUser($connection);

// Get user's wins
$stmt = $connection->prepare(
    "SELECT p.*, u.username, u.full_name
     FROM products p
     JOIN users u ON p.user_id = u.id
     WHERE p.winner_id = ?
     ORDER BY p.updated_at DESC"
);

$stmt->bind_param("i", $user['id']);
$stmt->execute();
$wins = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kemenangan Saya - Sistem Lelang Online</title>
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
                <li><a href="/pages/my-bids.php">💰 Bid Saya</a></li>
                <li><a href="/pages/my-wins.php" class="active">🏆 Kemenangan Saya</a></li>
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
            <h1>🏆 Kemenangan Saya</h1>
            <p class="text-muted">Lelang yang Anda menangkan</p>

            <?php if (!empty($wins)): ?>
                <div class="products-grid">
                    <?php foreach ($wins as $product): ?>
                        <div class="product-card">
                            <img src="<?php echo !empty($product['image_path']) ? htmlspecialchars($product['image_path']) : '/assets/images/placeholder.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($product['title']); ?>" 
                                 class="product-image">
                            <div class="product-info">
                                <h5 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h5>
                                <div class="product-price"><?php echo formatCurrency($product['current_price']); ?></div>
                                
                                <span class="product-status <?php echo $product['status']; ?>">
                                    🏆 Pemenang
                                </span>

                                <div class="product-timer" style="margin-bottom: 10px;">
                                    <small>
                                        📝 Penjual: <?php echo htmlspecialchars($product['full_name']); ?>
                                    </small>
                                </div>

                                <small class="text-muted">
                                    ✅ Selesai pada <?php echo formatDate($product['updated_at']); ?>
                                </small>

                                <div class="product-footer">
                                    <a href="/pages/product-detail.php?id=<?php echo $product['id']; ?>" 
                                       class="btn btn-primary btn-small">
                                        Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body">
                        <div class="empty-state">
                            <div class="empty-state-icon">🏆</div>
                            <h3>Belum Ada Kemenangan</h3>
                            <p>Anda belum memenangkan lelang apapun. Mulai bid pada produk sekarang!</p>
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