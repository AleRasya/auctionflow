<?php
/**
 * Halaman Daftar Penawaran Saya
 */

$page_title = 'Penawaran Saya';

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../functions/helpers.php';
require_once __DIR__ . '/../../functions/auction.php';

requireLogin();

$user = getUserSession();
$page = intval($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

// Get user bids
$bids = getUserBids($conn, $user['id'], $limit, $offset);

// Count
$query = "SELECT COUNT(*) as total FROM bids WHERE user_id = ?";
$result = getRow($conn, $query, [$user['id']]);
$total = $result['total'] ?? 0;
$total_pages = ceil($total / $limit);
?>

<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">
        💰 Penawaran Saya
    </h1>
    <p style="color: #999;">Daftar semua penawaran yang telah Anda ajukan</p>
</div>

<!-- Statistics -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
    <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-value"><?php echo $total; ?></div>
        <div class="stat-label">Total Penawaran</div>
    </div>
</div>

<!-- Bids Table -->
<?php if (!empty($bids)): ?>
    <div class="card">
        <div class="card-header">
            <h2 style="margin: 0; color: white;">📋 Daftar Penawaran</h2>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Penawaran</th>
                        <th>Status</th>
                        <th>Waktu Penawaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bids as $bid): ?>
                        <tr>
                            <td>
                                <div style="display: flex; gap: 1rem; align-items: center;">
                                    <?php if (!empty($bid['image'])): ?>
                                        <img src="<?php echo APP_URL; ?>/uploads/products/<?php echo $bid['image']; ?>" alt="<?php echo htmlspecialchars($bid['product_name']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 0.25rem;">
                                    <?php else: ?>
                                        <div style="width: 50px; height: 50px; background-color: #1a1f2e; border-radius: 0.25rem;"></div>
                                    <?php endif; ?>
                                    <div>
                                        <div style="font-weight: 600;">
                                            <?php echo htmlspecialchars(substr($bid['product_name'], 0, 30)); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong><?php echo formatCurrency($bid['bid_amount']); ?></strong>
                            </td>
                            <td>
                                <?php 
                                if ($bid['product_status'] === 'active') {
                                    if ($bid['highest_bidder_id'] == $user['id']) {
                                        echo '<span class="badge badge-success">Penawaran Tertinggi</span>';
                                    } else {
                                        echo '<span class="badge badge-warning">Sedang Berjalan</span>';
                                    }
                                } elseif ($bid['product_status'] === 'finished') {
                                    if ($bid['highest_bidder_id'] == $user['id']) {
                                        echo '<span class="badge badge-success">🏆 Pemenang</span>';
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
        </div>
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
            Anda belum melakukan penawaran apapun
        </p>
        <p style="color: #666; margin-top: 1rem;">
            <a href="<?php echo APP_URL; ?>/pages/products/browse.php" class="btn btn-accent">
                🔍 Mulai Mengajukan Penawaran
            </a>
        </p>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>