<?php
/**
 * Halaman Browse/Jelajahi Lelang
 */

$page_title = 'Jelajahi Lelang';

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../functions/helpers.php';
require_once __DIR__ . '/../../functions/product.php';

requireLogin();

// Get filter parameters
$status = sanitize($_GET['status'] ?? 'all');
$search = sanitize($_GET['search'] ?? '');
$page = intval($_GET['page'] ?? 1);
$limit = 12;
$offset = ($page - 1) * $limit;

// Validasi status
if (!in_array($status, ['all', 'active', 'finished'])) {
    $status = 'all';
}

// Get products
$products = getProducts($conn, $status, $search, $limit, $offset);
$total = countProducts($conn, $status, $search);
$total_pages = ceil($total / $limit);

// Auto-close expired auctions
autoCloseExpiredAuctions($conn);
?>

<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<!-- Search and Filter -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-body">
        <form method="GET" action="">
            <div style="display: grid; grid-template-columns: 1fr 200px auto; gap: 1rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <input
                        type="text"
                        name="search"
                        id="search-input"
                        class="form-control"
                        placeholder="Cari produk lelang..."
                        value="<?php echo htmlspecialchars($search); ?>"
                    >
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <select name="status" id="status-filter" class="form-control">
                        <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                        <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Lelang Aktif</option>
                        <option value="finished" <?php echo $status === 'finished' ? 'selected' : ''; ?>>Lelang Selesai</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="align-self: flex-end;">
                    🔍 Cari
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Results Info -->
<div style="margin-bottom: 2rem;">
    <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem;">
        📦 Hasil Pencarian
    </h2>
    <p style="color: #999;">
        Ditemukan <strong><?php echo $total; ?></strong> lelang
        <?php if (!empty($search)): ?>
            dengan keyword "<strong><?php echo htmlspecialchars($search); ?></strong>"
        <?php endif; ?>
    </p>
</div>

<!-- Products Grid -->
<?php if (!empty($products)): ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <?php foreach ($products as $product): ?>
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
                        <?php if ($product['status'] === 'active'): ?>
                            <span class="badge badge-success">Aktif</span>
                        <?php elseif ($product['status'] === 'finished'): ?>
                            <span class="badge badge-danger">Selesai</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Batal</span>
                        <?php endif; ?>
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

                    <div class="product-seller">
                        <strong>Penjual:</strong> <?php echo htmlspecialchars($product['seller_name']); ?>
                    </div>

                    <?php if ($product['status'] === 'active'): ?>
                        <div class="product-timer" data-timer data-product-id="<?php echo $product['id']; ?>" data-end-time="<?php echo $product['end_time']; ?>" id="timer-<?php echo $product['id']; ?>">
                            Menghitung...
                        </div>
                    <?php else: ?>
                        <div class="product-timer" style="background-color: rgba(239, 68, 68, 0.2); color: #ef4444;">
                            <?php echo $product['status'] === 'finished' ? 'Lelang Selesai' : 'Lelang Dibatalkan'; ?>
                        </div>
                    <?php endif; ?>

                    <div class="product-actions">
                        <a href="<?php echo APP_URL; ?>/pages/auction/detail.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>&page=1">« Pertama</a>
                <a href="?status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page - 1; ?>">‹ Sebelumnya</a>
            <?php endif; ?>

            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="active"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page + 1; ?>">Selanjutnya ›</a>
                <a href="?status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $total_pages; ?>">Terakhir »</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="card" style="text-align: center; padding: 3rem;">
        <p style="color: #999; font-size: 1.1rem;">
            Tidak ada lelang yang ditemukan
        </p>
        <p style="color: #666; margin-top: 1rem;">
            <?php if (!empty($search)): ?>
                Coba gunakan kata kunci yang berbeda
            <?php else: ?>
                Belum ada lelang yang tersedia saat ini
            <?php endif; ?>
        </p>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>