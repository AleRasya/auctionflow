<?php
/**
 * Halaman Detail Lelang dan Sistem Bid
 */

$page_title = 'Detail Lelang';

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../functions/helpers.php';
require_once __DIR__ . '/../../functions/product.php';
require_once __DIR__ . '/../../functions/auction.php';

requireLogin();

$user = getUserSession();

// Get product ID
$product_id = intval($_GET['id'] ?? 0);
if (!$product_id) {
    header('Location: ' . APP_URL . '/pages/products/browse.php');
    exit;
}

// Get product detail
$product = getProduct($conn, $product_id);
if (!$product) {
    setFlashMessage('error', 'Produk tidak ditemukan');
    header('Location: ' . APP_URL . '/pages/products/browse.php');
    exit;
}

// Check if auction expired and auto-close
if ($product['status'] === 'active' && !isAuctionActive($product['end_time'])) {
    updateProductStatus($conn, $product_id, 'finished');
    $product['status'] = 'finished';
}

// Get latest bid
$latest_bid = getLatestBid($conn, $product_id);

// Get all bids for history
$all_bids = getProductBids($conn, $product_id, 20);

// Get auction history
$auction_history = getAuctionHistory($conn, $product_id);

// Process bid submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'place_bid') {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Anda harus login untuk mengajukan penawaran');
        header('Location: ' . APP_URL . '/pages/auth/login.php');
        exit;
    }

    $bid_amount = floatval($_POST['bid_amount'] ?? 0);

    $result = addBid($conn, $product_id, $user['id'], $bid_amount);

    if ($result['success']) {
        setFlashMessage('success', 'Penawaran Anda berhasil diajukan!');
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    } else {
        $errors = $result['errors'];
    }
}

// Calculate remaining time
$time_remaining = getTimeRemaining($product['end_time']);
$is_active = isAuctionActive($product['end_time']) && $product['status'] === 'active';
?>

<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div style="margin-bottom: 2rem;">
    <a href="<?php echo APP_URL; ?>/pages/products/browse.php" style="color: #999; text-decoration: underline;">← Kembali ke Lelang</a>
</div>

<!-- Main Content -->
<div class="auction-container">
    <!-- Left: Product Image & Info -->
    <div>
        <div class="auction-image">
            <?php if (!empty($product['image'])): ?>
                <img src="<?php echo APP_URL; ?>/uploads/products/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            <?php else: ?>
                <div style="background-color: #1a1f2e; height: 400px; display: flex; align-items: center; justify-content: center; color: #666;">
                    No Image
                </div>
            <?php endif; ?>
        </div>

        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h2 style="margin: 0; color: white;">📋 Deskripsi Produk</h2>
            </div>
            <div class="card-body">
                <h1 style="font-size: 1.75rem; font-weight: 700; margin-bottom: 1rem;">
                    <?php echo htmlspecialchars($product['name']); ?>
                </h1>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid #333;">
                    <div>
                        <div style="color: #999; font-size: 0.85rem; text-transform: uppercase;">Kategori</div>
                        <div style="font-size: 1rem; color: #fff; font-weight: 500;">
                            <?php echo htmlspecialchars($product['category']); ?>
                        </div>
                    </div>
                    <div>
                        <div style="color: #999; font-size: 0.85rem; text-transform: uppercase;">Penjual</div>
                        <div style="font-size: 1rem; color: #fff; font-weight: 500;">
                            <?php echo htmlspecialchars($product['seller_fullname']); ?>
                        </div>
                    </div>
                </div>

                <div style="line-height: 1.8; color: #ccc; margin-bottom: 1rem;">
                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </div>

                <div style="background-color: rgba(30, 58, 138, 0.2); padding: 1rem; border-radius: 0.375rem; color: #999;">
                    <p style="margin: 0; font-size: 0.85rem;">
                        Dibuat pada: <?php echo formatDateTime($product['created_at']); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Bid History -->
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h2 style="margin: 0; color: white;">💰 Riwayat Penawaran</h2>
            </div>
            <div class="card-body">
                <?php if (!empty($all_bids)): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Pembid</th>
                                <th>Penawaran</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_bids as $bid): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($bid['full_name']); ?></td>
                                    <td><strong><?php echo formatCurrency($bid['bid_amount']); ?></strong></td>
                                    <td><?php echo formatDateTime($bid['bid_time']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="text-align: center; padding: 2rem; color: #999;">
                        <p>Belum ada penawaran untuk produk ini</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right: Bid Form & Status -->
    <div>
        <!-- Countdown Timer -->
        <div class="countdown-timer">
            <div class="info-label">⏱️ Waktu Tersisa</div>
            <div class="countdown-value" data-timer data-product-id="<?php echo $product['id']; ?>" data-end-time="<?php echo $product['end_time']; ?>" id="timer-<?php echo $product['id']; ?>">
                Menghitung...
            </div>
            <div class="countdown-label">
                Berakhir: <?php echo formatDateTime($product['end_time']); ?>
            </div>
        </div>

        <!-- Price Info -->
        <div class="info-section">
            <div class="info-label">Harga Awal</div>
            <div class="info-value"><?php echo formatCurrency($product['initial_price']); ?></div>
        </div>

        <div class="info-section">
            <div class="info-label">Harga Tertinggi Saat Ini</div>
            <div class="info-value"><?php echo formatCurrency($product['current_price']); ?></div>

            <?php if ($latest_bid): ?>
                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #333;">
                    <div style="font-size: 0.85rem; color: #999;">Pembid Tertinggi</div>
                    <div style="font-size: 1rem; color: var(--accent-yellow); font-weight: 600;">
                        <?php echo htmlspecialchars($latest_bid['full_name']); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Bid Info -->
        <div class="info-section">
            <div class="info-label">Total Penawaran</div>
            <div class="info-value"><?php echo countProductBids($conn, $product_id); ?></div>
        </div>

        <!-- Status -->
        <div class="info-section">
            <div class="info-label">Status Lelang</div>
            <div style="margin-top: 0.5rem;">
                <?php if ($is_active): ?>
                    <span class="badge badge-success">Lelang Aktif</span>
                <?php elseif ($product['status'] === 'finished'): ?>
                    <span class="badge badge-danger">Lelang Selesai</span>
                    <?php if ($product['highest_bidder_id']): ?>
                        <?php 
                        $winner = getUserById($conn, $product['highest_bidder_id']);
                        if ($winner):
                        ?>
                            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #333;">
                                <div style="font-size: 0.85rem; color: #999;">🏆 Pemenang</div>
                                <div style="font-size: 1rem; color: var(--accent-yellow); font-weight: 600;">
                                    <?php echo htmlspecialchars($winner['full_name']); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="badge badge-warning">Lelang Dibatalkan</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Bid Form -->
        <?php if ($is_active): ?>
            <form method="POST" action="" id="bid-form-<?php echo $product['id']; ?>" data-bid-form data-product-id="<?php echo $product['id']; ?>" class="card" style="margin-top: 1.5rem;">
                <div class="card-header">
                    <h2 style="margin: 0; color: white;">💵 Ajukan Penawaran</h2>
                </div>

                <div class="card-body">
                    <?php if (isset($errors)): ?>
                        <div class="alert alert-error">
                            <ul style="margin: 0; padding-left: 1.5rem;">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <input type="hidden" name="action" value="place_bid">
                    <input type="hidden" name="current_price" id="current-price-<?php echo $product['id']; ?>" value="<?php echo $product['current_price']; ?>">

                    <div class="form-group">
                        <label for="bid-amount-<?php echo $product['id']; ?>">Jumlah Penawaran (Rp)</label>
                        <input
                            type="number"
                            id="bid-amount-<?php echo $product['id']; ?>"
                            name="bid_amount"
                            class="form-control"
                            placeholder="Contoh: 1500000"
                            min="<?php echo $product['current_price'] + ($product['current_price'] * 0.05); ?>"
                            step="50000"
                            required
                        >
                        <small style="color: #999;">Minimal: <?php echo formatCurrency($product['current_price'] + ($product['current_price'] * 0.05)); ?></small>
                    </div>

                    <button type="submit" class="btn btn-accent btn-block">
                        ✓ Ajukan Penawaran
                    </button>
                </div>
            </form>

            <div style="background-color: rgba(59, 130, 246, 0.1); border-left: 4px solid #3b82f6; padding: 1rem; border-radius: 0.375rem; margin-top: 1rem;">
                <div style="font-size: 0.85rem; color: #999;">ℹ️ Info Penawaran</div>
                <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem; font-size: 0.85rem; color: #ccc;">
                    <li>Penawaran harus lebih tinggi dari harga saat ini</li>
                    <li>Kenaikan minimal: <?php echo formatCurrency($product['current_price'] * 0.05); ?></li>
                    <li>Penawaran bersifat mengikat dan tidak dapat dibatalkan</li>
                </ul>
            </div>
        <?php else: ?>
            <div class="card" style="margin-top: 1.5rem;">
                <div class="card-header">
                    <h2 style="margin: 0; color: white;">⏹️ Lelang Telah Berakhir</h2>
                </div>
                <div class="card-body" style="text-align: center;">
                    <p style="color: #999; margin-bottom: 1rem;">
                        Maaf, lelang untuk produk ini telah berakhir dan tidak bisa menerima penawaran baru.
                    </p>
                    <a href="<?php echo APP_URL; ?>/pages/products/browse.php" class="btn btn-primary">
                        🔍 Lihat Lelang Lainnya
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>