<?php
/**
 * Halaman Riwayat Lelang
 */

$page_title = 'Riwayat Lelang';

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../functions/helpers.php';
require_once __DIR__ . '/../../functions/product.php';
require_once __DIR__ . '/../../functions/auction.php';

requireLogin();

$user = getUserSession();
$page = intval($_GET['page'] ?? 1);
$limit = 12;
$offset = ($page - 1) * $limit;

// Get finished products (baik yang user menang atau kalah)
$query = "SELECT p.*, u.full_name as seller_fullname, w.full_name as winner_fullname, COUNT(b.id) as bid_count 
          FROM products p 
          LEFT JOIN users u ON p.user_id = u.id 
          LEFT JOIN users w ON p.highest_bidder_id = w.id 
          LEFT JOIN bids b ON p.id = b.product_id 
          WHERE p.status = 'finished' AND (p.user_id = ? OR p.highest_bidder_id = ?)
          GROUP BY p.id 
          ORDER BY p.updated_at DESC 
          LIMIT ? OFFSET ?";
$finished_products = getRows($conn, $query, [$user['id'], $user['id'], $limit, $offset]);

// Count
$count_query = "SELECT COUNT(DISTINCT p.id) as total FROM products p 
               WHERE p.status = 'finished' AND (p.user_id = ? OR p.highest_bidder_id = ?)";
$count_result = getRow($conn, $count_query, [$user['id'], $user['id']]);
$total = $count_result['total'] ?? 0;
$total_pages = ceil($total / $limit);
?>

<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">
        📜 Riwayat Lelang
    </h1>
    <p style="color: #999;">Daftar lelang yang sudah selesai</p>
</div>

<!-- Statistics -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
    <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-value"><?php echo $total; ?></div>
        <div class="stat-label">Total Lelang Selesai</div>
    </div>
</div>

<!-- Auctions List -->
<?php if (!empty($finished_products)): ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <?php foreach ($finished_products as $product): ?>
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
                        <span class="badge badge-danger">Selesai</span>
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
                        <span><?php echo formatDate($product['updated_at']); ?></span>
                    </div>

                    <div style="margin-bottom: 1rem; padding: 0.75rem; background-color: rgba(30, 58, 138, 0.2); border-radius: 0.375rem;">
                        <?php 
                        if ($product['user_id'] == $user['id']) {
                            echo '<div style="font-size: 0.85rem; color: #999;">Anda adalah Penjual</div>';
                        } else if ($product['highest_bidder_id'] == $user['id']) {
                            echo '<div style="font-size: 0.85rem; color: #10b981;">🏆 Anda Menang Lelang Ini</div>';
                        } else {
                            echo '<div style="font-size: 0.85rem; color: #999;">Anda Tidak Menang</div>';
                        }
                        ?>
                        
                        <?php if ($product['highest_bidder_id']): ?>
                            <div style="font-size: 0.85rem; color: #ccc; margin-top: 0.25rem;">
                                Pemenang: <strong><?php echo htmlspecialchars($product['winner_fullname']); ?></strong>
                            </div>
                        <?php endif; ?>
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
            Belum ada riwayat lelang
        </p>
        <p style="color: #666; margin-top: 1rem;">
            Riwayat lelang yang sudah selesai akan muncul di sini
        </p>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>