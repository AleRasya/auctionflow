<?php
require_once __DIR__ . '/../config/database.php';
requireLogin();

$user = getCurrentUser($connection);

// Get product ID
$productId = sanitizeInput($_GET['id'] ?? '');
if (!$productId || !is_numeric($productId)) {
    header('Location: /pages/products.php');
    exit;
}

// Get product details
$product = getProductById($connection, $productId);
if (!$product) {
    header('Location: /pages/products.php');
    exit;
}

// Get bid history
$bidHistory = getBidHistory($connection, $productId);

// Get remaining time
$remainingTime = getRemainingTime($product['end_time']);
$isEnded = $remainingTime['status'] === 'ended';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['title']); ?> - Sistem Lelang Online</title>
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
                <li><a href="/pages/products.php" class="active">📦 Produk</a></li>
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
            <a href="/pages/products.php" class="btn btn-secondary btn-small" style="margin-bottom: 20px;">← Kembali</a>

            <div class="auction-detail">
                <!-- Image Section -->
                <div class="auction-image-container">
                    <img src="<?php echo !empty($product['image_path']) ? htmlspecialchars($product['image_path']) : '/assets/images/placeholder.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($product['title']); ?>" 
                         class="auction-image">
                </div>

                <!-- Info Section -->
                <div class="auction-info">
                    <h2><?php echo htmlspecialchars($product['title']); ?></h2>
                    <p class="text-muted">📝 <?php echo htmlspecialchars($product['description']); ?></p>

                    <!-- Price Box -->
                    <div class="auction-price-box">
                        <div class="auction-price-label">Harga Tertinggi Saat Ini</div>
                        <div class="auction-price"><?php echo formatCurrency($product['highest_bid'] ?? $product['starting_price']); ?></div>
                        <small><?php echo $product['bid_count']; ?> bid</small>
                    </div>

                    <!-- Timer Box -->
                    <div id="timer-<?php echo $productId; ?>" 
                         class="auction-timer-box" 
                         data-end-time="<?php echo $product['end_time']; ?>">
                        <div class="timer-label">Waktu Tersisa</div>
                    </div>

                    <!-- Bidder Info -->
                    <?php if ($product['highest_bid']): ?>
                        <div class="card" style="margin-bottom: 20px;">
                            <div class="card-body">
                                <strong>👤 Bidder Tertinggi:</strong><br>
                                <span><?php echo htmlspecialchars($bidHistory[0]['full_name'] ?? 'Anonim'); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Bid Section -->
                    <?php if ($user['id'] != $product['user_id'] && !$isEnded && $product['status'] === 'active'): ?>
                        <div class="bid-section">
                            <h5>📌 Tempatkan Bid</h5>
                            <div id="bidForm-<?php echo $productId; ?>" class="bid-form">
                                <input 
                                    type="number" 
                                    id="bidAmount-<?php echo $productId; ?>" 
                                    placeholder="Jumlah bid (minimal <?php echo formatCurrency($product['current_price'] + 1000); ?>)" 
                                    min="<?php echo $product['current_price'] + 1000; ?>"
                                    step="1000"
                                >
                                <button type="button" 
                                        id="bidBtn-<?php echo $productId; ?>" 
                                        class="btn btn-warning"
                                        onclick="placeBid(<?php echo $productId; ?>)">
                                    Bid Sekarang
                                </button>
                            </div>
                        </div>
                    <?php elseif ($isEnded): ?>
                        <div class="alert alert-warning" style="margin-bottom: 20px;">
                            ❌ Lelang ini telah berakhir
                            <?php if ($product['winner_id']): ?>
                                <br>🏆 Pemenang: <strong><?php echo htmlspecialchars($product['winner_name']); ?></strong>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($user['id'] == $product['user_id']): ?>
                        <div class="alert alert-info" style="margin-bottom: 20px;">
                            ℹ️ Ini adalah produk lelang Anda. Anda tidak bisa melakukan bid pada produk sendiri.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger" style="margin-bottom: 20px;">
                            ❌ Lelang ini tidak aktif
                        </div>
                    <?php endif; ?>

                    <!-- Product Info -->
                    <div class="card">
                        <div class="card-body">
                            <strong>📋 Informasi Produk</strong><br><br>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; font-size: 14px;">
                                <div>
                                    <strong>Harga Awal:</strong><br>
                                    <span class="text-primary"><?php echo formatCurrency($product['starting_price']); ?></span>
                                </div>
                                <div>
                                    <strong>Kategori:</strong><br>
                                    <span><?php echo htmlspecialchars($product['category'] ?? '-'); ?></span>
                                </div>
                                <div>
                                    <strong>Penjual:</strong><br>
                                    <span><?php echo htmlspecialchars($product['full_name']); ?></span>
                                </div>
                                <div>
                                    <strong>Status:</strong><br>
                                    <span class="product-status <?php echo $product['status']; ?>" style="display: inline-block;">
                                        <?php echo ucfirst($product['status']); ?>
                                    </span>
                                </div>
                                <div>
                                    <strong>Dibuat:</strong><br>
                                    <span><?php echo formatDateTime($product['created_at']); ?></span>
                                </div>
                                <div>
                                    <strong>Berakhir:</strong><br>
                                    <span><?php echo formatDateTime($product['end_time']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bid History -->
            <div class="card" style="margin-top: 30px;">
                <div class="card-header">
                    💰 Riwayat Bid (<?php echo count($bidHistory); ?>)
                </div>
                <div class="card-body">
                    <?php if (!empty($bidHistory)): ?>
                        <div class="bid-history">
                            <?php foreach ($bidHistory as $bid): ?>
                                <div class="bid-item">
                                    <div>
                                        <div class="bid-item-name">👤 <?php echo htmlspecialchars($bid['full_name']); ?></div>
                                        <div class="bid-item-time"><?php echo formatDateTime($bid['bid_time']); ?></div>
                                    </div>
                                    <div class="bid-item-amount"><?php echo formatCurrency($bid['bid_amount']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state" style="padding: 30px 20px;">
                            <p class="text-muted">Belum ada bid untuk produk ini</p>
                        </div>
                    <?php endif; ?>
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