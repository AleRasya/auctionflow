<?php
/**
 * Halaman Daftar Produk Saya
 */

$page_title = 'Produk Saya';

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../functions/helpers.php';
require_once __DIR__ . '/../../functions/product.php';

requireLogin();

$user = getUserSession();
$page = intval($_GET['page'] ?? 1);
$limit = 12;
$offset = ($page - 1) * $limit;

// Get user products
$products = getUserProducts($conn, $user['id'], $limit, $offset);

// Count
$query = "SELECT COUNT(*) as total FROM products WHERE user_id = ?";
$result = getRow($conn, $query, [$user['id']]);
$total = $result['total'] ?? 0;
$total_pages = ceil($total / $limit);

// Get status counts
$status_counts = countProductsByStatus($conn, $user['id']);

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $result = deleteProduct($conn, $product_id, $user['id']);

    if ($result['success']) {
        setFlashMessage('success', 'Produk berhasil dihapus!');
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    } else {
        setFlashMessage('error', implode(', ', $result['errors']));
    }
}
?>

<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">
            📦 Produk Saya
        </h1>
        <p style="color: #999;">Kelola barang lelang Anda</p>
    </div>
    <a href="<?php echo APP_URL; ?>/pages/products/create.php" class="btn btn-accent">
        ➕ Buat Lelang Baru
    </a>
</div>

<!-- Status Filter -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
    <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-value"><?php echo $total; ?></div>
        <div class="stat-label">Total Produk</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🔴</div>
        <div class="stat-value"><?php echo $status_counts['active']; ?></div>
        <div class="stat-label">Lelang Aktif</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">✓</div>
        <div class="stat-value"><?php echo $status_counts['finished']; ?></div>
        <div class="stat-label">Lelang Selesai</div>
    </div>
</div>

<!-- Products List -->
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
                            Lihat
                        </a>
                        <?php if ($product['status'] === 'active' && $product['bid_count'] == 0): ?>
                            <a href="<?php echo APP_URL; ?>/pages/products/edit.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">
                                Edit
                            </a>
                        <?php endif; ?>
                        <?php if ($product['bid_count'] == 0): ?>
                            <form method="POST" action="" style="flex: 1;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" style="width: 100%; justify-content: center;" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">
                                    Hapus
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=1">« Pertama</a>
                <a href="?page=<?php echo $page - 1; ?>">‹ Sebelumnya</a>
            <?php endif; ?>

            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="active"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>">Selanjutnya ›</a>
                <a href="?page=<?php echo $total_pages; ?>">Terakhir »</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="card" style="text-align: center; padding: 3rem;">
        <p style="color: #999; font-size: 1.1rem;">
            Anda belum membuat lelang apapun
        </p>
        <p style="color: #666; margin-top: 1rem;">
            <a href="<?php echo APP_URL; ?>/pages/products/create.php" class="btn btn-accent">
                ➕ Buat Lelang Pertama Anda
            </a>
        </p>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>