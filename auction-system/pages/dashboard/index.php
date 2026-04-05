<?php
/**
 * Dashboard Halaman Utama
 */

$page_title = 'Dashboard';

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../functions/helpers.php';
require_once __DIR__ . '/../../functions/product.php';
require_once __DIR__ . '/../../functions/auction.php';

requireLogin();

$user = getUserSession();

// Get statistics
$stats = getDashboardStats($conn, $user['id']);

// Get recent products
$recent_products = getProducts($conn, 'active', '', 6, 0);

// Get user's recent bids
$recent_bids = getUserBids($conn, $user['id'], 5, 0);

$page_title = 'Dashboard';
?>

<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<!-- Dashboard Content -->
<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">
        Selamat datang, <?php echo htmlspecialchars($user['full_name']); ?>! 👋
    </h1>
    <p style="color: #999;">Berikut adalah ringkasan aktivitas lelang Anda</p>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-value"><?php echo $stats['total_products']; ?></div>
        <div class="stat-label">Total Produk</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">🔴</div>
        <div class="stat-value"><?php echo $stats['active_products']; ?></div>
        <div class="stat-label">Lelang Aktif</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-value"><?php echo $stats['total_bids']; ?></div>
        <div class="stat-label">Total Penawaran</div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">🏆</div>
        <div class="stat-value"><?php echo isset($stats['winning_auctions']) ? $stats['winning_auctions'] : '0'; ?></div>
        <div class="stat-label">Lelang Dimenangkan</div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h2 style="margin: 0; color: white;">⚡ Aksi Cepat</h2>
    </div>
    <div class="card-body" style="display: flex; gap: 1rem; flex-wrap: wrap;">
        <a href="<?php echo APP_URL; ?>/pages/products/create.php" class="btn btn-accent">
            ➕ Buat Lelang Baru
        </a>
        <a href="<?php echo APP_URL; ?>/pages/products/browse.php" class="btn btn-primary">
            🔍 Jelajahi Lelang
        </a>
        <a href="<?php echo APP_URL; ?>/pages/auction/my-bids.php" class="btn btn-outline">
            💰 Lihat Penawaran Saya
        </a>
    </div>
</div>

<!-- Recent Active Auctions -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h2 style="margin: 0; color: white;">🔥 Lelang Aktif Terbaru</h2>
    </div>
    <div class="card-body">
        <?php if (!empty($recent_products)): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
                <?php foreach ($recent_products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?php echo APP_URL; ?>/uploads/products/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                                <div style="background-color: #1a1f2e; height: 100%; display: flex; align-items: center; justify-content: center; color: #666;">
                                    No Image
                                </div>
                            <?php endif; ?>
                            <div class="product-status">
                                <span class="badge badge-success">Aktif</span>
                            </div>
                        </div>

                        <div class="product-info">
                            <div class="product-name"><?php echo htmlspecialchars(substr($product['name'], 0, 30)); ?></div>
                            <div class="product-category"><?php echo htmlspecialchars($product['category']); ?></div>

                            <div class="product-price">
                                <?php echo formatCurrency($product['current_price']); ?>
                            </div>

                            <div class="product-meta">
                                <span>Bid: <?php echo $product['bid_count']; ?></span>
                                <span><?php echo formatDate($product['created_at']); ?></span>
                            </div>

                            <div class="product-timer" data-timer data-product-id="<?php echo $product['id']; ?>" data-end-time="<?php echo $product['end_time']; ?>" id="timer-<?php echo $product['id']; ?>">
                                Menghitung...
                            </div>

                            <div class="product-actions">
                                <a href="<?php echo APP_URL; ?>/pages/auction/detail.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 2rem; color: #999;">
                <p>Belum ada lelang aktif. <a href="<?php echo APP_URL; ?>/pages/products/browse.php" style="color: #FACC15;">Jelajahi lelang</a></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Recent Bids -->
<div class="card">
    <div class="card-header">
        <h2 style="margin: 0; color: white;">💰 Penawaran Terbaru Saya</h2>
    </div>
    <div class="card-body">
        <?php if (!empty($recent_bids)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Penawaran</th>
                        <th>Status</th>
                        <th>Waktu</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_bids as $bid): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(substr($bid['product_name'], 0, 25)); ?></td>
                            <td><strong><?php echo formatCurrency($bid['bid_amount']); ?></strong></td>
                            <td>
                                <?php 
                                if ($bid['product_status'] === 'active') {
                                    if ($bid['product_id'] == $_SESSION['user_id']) {
                                        echo '<span class="badge badge-success">Pemenang Sementara</span>';
                                    } else {
                                        echo '<span class="badge badge-warning">Sedang Berjalan</span>';
                                    }
                                } elseif ($bid['product_status'] === 'finished') {
                                    if ($bid['highest_bidder_id'] == $_SESSION['user_id']) {
                                        echo '<span class="badge badge-success">Pemenang</span>';
                                    } else {
                                        echo '<span class="badge badge-danger">Kalah</span>';
                                    }
                                }
                                ?>
                            </td>
                            <td><?php echo formatDateTime($bid['bid_time']); ?></td>
                            <td>
                                <a href="<?php echo APP_URL; ?>/pages/auction/detail.php?id=<?php echo $bid['product_id']; ?>" class="btn btn-sm btn-primary">
                                    Lihat
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="text-align: center; padding: 2rem; color: #999;">
                <p>Anda belum melakukan penawaran. <a href="<?php echo APP_URL; ?>/pages/products/browse.php" style="color: #FACC15;">Mulai mengajukan penawaran</a></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>