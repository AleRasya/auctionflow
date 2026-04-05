<?php
require_once __DIR__ . '/../config/database.php';
requireLogin();

$user = getCurrentUser($connection);

// Get filters
$filters = [
    'search' => sanitizeInput($_GET['search'] ?? ''),
    'status' => sanitizeInput($_GET['status'] ?? 'active'),
    'category' => sanitizeInput($_GET['category'] ?? '')
];

// Get products
$products = getAllProducts($connection, $filters);

// Get categories
$categoriesResult = $connection->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL ORDER BY category");
$categories = $categoriesResult->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk - Sistem Lelang Online</title>
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
            <h1>Semua Produk Lelang</h1>

            <!-- Filters -->
            <div class="filters-section">
                <div class="filter-group" style="flex: 2;">
                    <label for="searchInput">Cari Produk</label>
                    <input 
                        type="text" 
                        id="searchInput" 
                        placeholder="Cari berdasarkan nama atau deskripsi..."
                        value="<?php echo htmlspecialchars($filters['search']); ?>"
                        onkeyup="filterProducts()"
                    >
                </div>

                <div class="filter-group">
                    <label for="statusFilter">Status</label>
                    <select id="statusFilter" onchange="filterProducts()">
                        <option value="">Semua Status</option>
                        <option value="active" <?php echo $filters['status'] === 'active' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="ended" <?php echo $filters['status'] === 'ended' ? 'selected' : ''; ?>>Selesai</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="categoryFilter">Kategori</label>
                    <select id="categoryFilter" onchange="filterProducts()">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['category']); ?>" 
                                <?php echo $filters['category'] === $cat['category'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['category']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <a href="/pages/products.php" class="btn btn-secondary">Reset</a>
            </div>

            <!-- Products Grid -->
            <?php if (!empty($products)): ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <?php 
                            $remainingTime = getRemainingTime($product['end_time']);
                            $isEnded = $remainingTime['status'] === 'ended';
                        ?>
                        <div class="product-card">
                            <img src="<?php echo !empty($product['image_path']) ? htmlspecialchars($product['image_path']) : '/assets/images/placeholder.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($product['title']); ?>" 
                                 class="product-image">
                            <div class="product-info">
                                <h5 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h5>
                                
                                <div class="product-price">
                                    <?php echo formatCurrency($product['highest_bid'] ?? $product['starting_price']); ?>
                                </div>

                                <span class="product-status <?php echo $product['status']; ?>">
                                    <?php 
                                        $statusText = [
                                            'active' => 'Aktif',
                                            'ended' => 'Selesai',
                                            'cancelled' => 'Dibatalkan'
                                        ];
                                        echo $statusText[$product['status']] ?? ucfirst($product['status']);
                                    ?>
                                </span>

                                <div class="product-timer <?php echo ($remainingTime['days'] == 0 && $remainingTime['hours'] < 1) ? 'warning' : ''; ?>">
                                    <small>
                                        <?php 
                                            if ($isEnded) {
                                                echo '❌ Lelang Berakhir';
                                            } else {
                                                echo sprintf('⏱️ %d hari %d jam', $remainingTime['days'], $remainingTime['hours']);
                                            }
                                        ?>
                                    </small>
                                </div>

                                <small class="text-muted">
                                    📝 <?php echo $product['bid_count']; ?> bid
                                </small>

                                <div class="product-footer">
                                    <a href="/pages/product-detail.php?id=<?php echo $product['id']; ?>" 
                                       class="btn btn-primary btn-small">
                                        Detail & Bid
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
                            <div class="empty-state-icon">📭</div>
                            <h3>Tidak Ada Produk</h3>
                            <p>Tidak ada produk yang sesuai dengan pencarian Anda</p>
                            <a href="/pages/products.php" class="btn btn-primary">Lihat Semua Produk</a>
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