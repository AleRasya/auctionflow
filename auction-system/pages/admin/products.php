<?php
/**
 * Halaman Kelola Produk (Admin)
 */

$page_title = 'Kelola Produk';

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../functions/helpers.php';
require_once __DIR__ . '/../../functions/product.php';

requireLogin();

$user = getUserSession();

// Check if admin
if ($user['role'] !== 'admin') {
    setFlashMessage('error', 'Anda tidak memiliki akses ke halaman ini');
    header('Location: ' . APP_URL . '/pages/dashboard/index.php');
    exit;
}

$page = intval($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;
$status = sanitize($_GET['status'] ?? 'all');
$search = sanitize($_GET['search'] ?? '');

// Get products
$products = getProducts($conn, $status, $search, $limit, $offset);
$total = countProducts($conn, $status, $search);
$total_pages = ceil($total / $limit);

// Auto-close expired
autoCloseExpiredAuctions($conn);
?>

<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">
            📦 Kelola Produk
        </h1>
        <p style="color: #999;">Total produk: <strong><?php echo $total; ?></strong></p>
    </div>
</div>

<!-- Search and Filter -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-body">
        <form method="GET" action="">
            <div style="display: grid; grid-template-columns: 1fr 200px auto; gap: 1rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Cari produk..."
                        value="<?php echo htmlspecialchars($search); ?>"
                    >
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <select name="status" class="form-control">
                        <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                        <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="finished" <?php echo $status === 'finished' ? 'selected' : ''; ?>>Selesai</option>
                        <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Batal</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="align-self: flex-end;">
                    🔍 Cari
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Products Table -->
<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Penjual</th>
                    <th>Harga Saat Ini</th>
                    <th>Bid</th>
                    <th>Status</th>
                    <th>Dibuat</th>
                    <th>Berakhir</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars(substr($product['name'], 0, 25)); ?></strong><br>
                            <small style="color: #999;"><?php echo htmlspecialchars($product['category']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($product['seller_name']); ?></td>
                        <td><strong><?php echo formatCurrency($product['current_price']); ?></strong></td>
                        <td><?php echo $product['bid_count']; ?></td>
                        <td>
                            <?php if ($product['status'] === 'active'): ?>
                                <span class="badge badge-success">Aktif</span>
                            <?php elseif ($product['status'] === 'finished'): ?>
                                <span class="badge badge-danger">Selesai</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Batal</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo formatDate($product['created_at']); ?></td>
                        <td><?php echo formatDateTime($product['end_time']); ?></td>
                        <td>
                            <a href="<?php echo APP_URL; ?>/pages/auction/detail.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">
                                Lihat
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=1<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&status=<?php echo $status; ?>">« Pertama</a>
            <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&status=<?php echo $status; ?>">‹ Sebelumnya</a>
        <?php endif; ?>

        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
            <?php if ($i === $page): ?>
                <span class="active"><?php echo $i; ?></span>
            <?php else: ?>
                <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&status=<?php echo $status; ?>"><?php echo $i; ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&status=<?php echo $status; ?>">Selanjutnya ›</a>
            <a href="?page=<?php echo $total_pages; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&status=<?php echo $status; ?>">Terakhir »</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>