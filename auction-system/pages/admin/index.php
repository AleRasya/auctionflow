<?php
/**
 * Halaman Admin Dashboard
 */

$page_title = 'Admin Panel';

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../functions/helpers.php';
require_once __DIR__ . '/../../functions/auction.php';

requireLogin();

$user = getUserSession();

// Check if admin
if ($user['role'] !== 'admin') {
    setFlashMessage('error', 'Anda tidak memiliki akses ke halaman ini');
    header('Location: ' . APP_URL . '/pages/dashboard/index.php');
    exit;
}

// Get statistics
$stats = getDashboardStats($conn);
?>

<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">
        ⚙️ Admin Panel
    </h1>
    <p style="color: #999;">Kelola sistem lelang</p>
</div>

<!-- Statistics -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-value"><?php echo $stats['total_users'] ?? 0; ?></div>
        <div class="stat-label">Total User</div>
    </div>

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
</div>

<!-- Admin Actions -->
<div class="card" style="margin-top: 2rem; margin-bottom: 2rem;">
    <div class="card-header">
        <h2 style="margin: 0; color: white;">🛠️ Kelola Sistem</h2>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
            <a href="<?php echo APP_URL; ?>/pages/admin/users.php" class="btn btn-primary" style="justify-content: center;">
                👥 Kelola User
            </a>
            <a href="<?php echo APP_URL; ?>/pages/admin/products.php" class="btn btn-primary" style="justify-content: center;">
                📦 Kelola Produk
            </a>
            <button onclick="if(confirm('Tutup semua lelang yang sudah expired?')) {location.href='<?php echo APP_URL; ?>/api/close-expired.php';}" class="btn btn-warning" style="justify-content: center;">
                ⏹️ Tutup Lelang Expired
            </button>
        </div>
    </div>
</div>

<!-- Recent Products -->
<div class="card">
    <div class="card-header">
        <h2 style="margin: 0; color: white;">📦 Produk Terbaru</h2>
    </div>
    <div class="card-body">
        <?php 
        $recent_products = getProducts($conn, 'all', '', 10, 0);
        ?>
        <?php if (!empty($recent_products)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Penjual</th>
                        <th>Harga</th>
                        <th>Status</th>
                        <th>Bid</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_products as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(substr($product['name'], 0, 30)); ?></td>
                            <td><?php echo htmlspecialchars($product['seller_name']); ?></td>
                            <td><?php echo formatCurrency($product['current_price']); ?></td>
                            <td>
                                <?php if ($product['status'] === 'active'): ?>
                                    <span class="badge badge-success">Aktif</span>
                                <?php elseif ($product['status'] === 'finished'): ?>
                                    <span class="badge badge-danger">Selesai</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Batal</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $product['bid_count']; ?></td>
                            <td>
                                <a href="<?php echo APP_URL; ?>/pages/auction/detail.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">
                                    Lihat
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color: #999; text-align: center; padding: 2rem;">Tidak ada produk</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>