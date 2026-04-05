<?php
require_once __DIR__ . '/../config/database.php';
requireLogin();

$user = getCurrentUser($connection);

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and validate input
    $title = sanitizeInput($_POST['title'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $category = sanitizeInput($_POST['category'] ?? '');
    $starting_price = (float) ($_POST['starting_price'] ?? 0);
    $start_time = sanitizeInput($_POST['start_time'] ?? '');
    $end_time = sanitizeInput($_POST['end_time'] ?? '');
    $image_path = '';

    // Validation
    if (empty($title)) {
        $errors[] = 'Judul produk tidak boleh kosong';
    } elseif (strlen($title) < 5 || strlen($title) > 200) {
        $errors[] = 'Judul harus 5-200 karakter';
    }

    if (empty($description)) {
        $errors[] = 'Deskripsi tidak boleh kosong';
    } elseif (strlen($description) < 10) {
        $errors[] = 'Deskripsi minimal 10 karakter';
    }

    if (empty($category)) {
        $errors[] = 'Kategori tidak boleh kosong';
    }

    if ($starting_price <= 0) {
        $errors[] = 'Harga awal harus lebih dari 0';
    }

    if (empty($start_time)) {
        $errors[] = 'Waktu mulai tidak boleh kosong';
    } elseif (strtotime($start_time) < time()) {
        $errors[] = 'Waktu mulai harus di masa depan';
    }

    if (empty($end_time)) {
        $errors[] = 'Waktu berakhir tidak boleh kosong';
    } elseif (strtotime($end_time) <= strtotime($start_time)) {
        $errors[] = 'Waktu berakhir harus setelah waktu mulai';
    }

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Error saat upload gambar';
        } else {
            $uploadResult = uploadProductImage($_FILES['image']);
            if ($uploadResult['success']) {
                $image_path = $uploadResult['path'];
            } else {
                $errors[] = $uploadResult['message'];
            }
        }
    }

    // If no errors, create product
    if (empty($errors)) {
        $data = [
            'user_id' => $user['id'],
            'title' => $title,
            'description' => $description,
            'category' => $category,
            'image_path' => $image_path,
            'starting_price' => $starting_price,
            'start_time' => $start_time,
            'end_time' => $end_time
        ];

        $productId = createProduct($connection, $data);

        if ($productId) {
            $success = true;
            logAudit($connection, $user['id'], 'PRODUCT_CREATED', 'Product ID: ' . $productId);
        } else {
            $errors[] = 'Gagal membuat produk. Silakan coba lagi.';
        }
    }
}

// Get categories
$categoriesResult = $connection->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL ORDER BY category");
$categories = $categoriesResult->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Produk - Sistem Lelang Online</title>
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
                <li><a href="/pages/products.php">📦 Produk</a></li>
                <li><a href="/pages/my-products.php" class="active">🎯 Produk Saya</a></li>
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
            <h1>Buat Produk Lelang Baru</h1>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <strong>Sukses!</strong> Produk berhasil dibuat. Silakan <a href="/pages/my-products.php">lihat produk Anda</a>.
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <strong>Gagal:</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">Informasi Produk</div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" id="createProductForm">
                        <div class="form-group">
                            <label for="title">Judul Produk *</label>
                            <input 
                                type="text" 
                                id="title" 
                                name="title" 
                                placeholder="Masukkan judul produk"
                                value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="description">Deskripsi Produk *</label>
                            <textarea 
                                id="description" 
                                name="description" 
                                placeholder="Jelaskan produk Anda secara detail"
                                required
                            ><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="category">Kategori *</label>
                                <input 
                                    type="text" 
                                    id="category" 
                                    name="category" 
                                    placeholder="Contoh: Elektronik, Fashion, dll"
                                    value="<?php echo htmlspecialchars($_POST['category'] ?? ''); ?>"
                                    required
                                    list="categoryList"
                                >
                                <datalist id="categoryList">
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat['category']); ?>"></option>
                                    <?php endforeach; ?>
                                </datalist>
                            </div>

                            <div class="form-group">
                                <label for="starting_price">Harga Awal (Rp) *</label>
                                <input 
                                    type="number" 
                                    id="starting_price" 
                                    name="starting_price" 
                                    placeholder="Contoh: 100000"
                                    min="1"
                                    step="1000"
                                    value="<?php echo htmlspecialchars($_POST['starting_price'] ?? ''); ?>"
                                    required
                                >
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="image">Gambar Produk (JPG, PNG, GIF, Max 5MB)</label>
                            <input 
                                type="file" 
                                id="image" 
                                name="image" 
                                accept="image/jpeg,image/png,image/gif"
                                onchange="previewImage('image', 'imagePreview'); validateImageFile(this);"
                            >
                            <img id="imagePreview" src="" alt="Preview" style="max-width: 200px; margin-top: 10px; display: none; border-radius: 5px;">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="start_time">Waktu Mulai Lelang (UTC+7) *</label>
                                <input 
                                    type="datetime-local" 
                                    id="start_time" 
                                    name="start_time" 
                                    value="<?php echo htmlspecialchars($_POST['start_time'] ?? ''); ?>"
                                    required
                                >
                                <div class="form-help">Waktu saat lelang dimulai</div>
                            </div>

                            <div class="form-group">
                                <label for="end_time">Waktu Berakhir Lelang (UTC+7) *</label>
                                <input 
                                    type="datetime-local" 
                                    id="end_time" 
                                    name="end_time" 
                                    value="<?php echo htmlspecialchars($_POST['end_time'] ?? ''); ?>"
                                    required
                                >
                                <div class="form-help">Waktu saat lelang berakhir</div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Buat Produk</button>
                            <a href="/pages/my-products.php" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Info Box -->
            <div class="card mt-30">
                <div class="card-header">ℹ️ Panduan Membuat Lelang</div>
                <div class="card-body" style="font-size: 14px;">
                    <ul>
                        <li><strong>Judul:</strong> Buat judul yang menarik dan deskriptif</li>
                        <li><strong>Deskripsi:</strong> Jelaskan kondisi dan fitur produk secara detail</li>
                        <li><strong>Kategori:</strong> Pilih kategori yang sesuai untuk memudahkan pencarian</li>
                        <li><strong>Harga Awal:</strong> Tentukan harga awal yang kompetitif</li>
                        <li><strong>Gambar:</strong> Gunakan gambar berkualitas tinggi untuk menarik bidder</li>
                        <li><strong>Waktu Lelang:</strong> Tentukan durasi lelang yang wajar (minimal 1 hari)</li>
                        <li><strong>Minimum Bid:</strong> Setiap bid harus lebih tinggi minimal Rp 1.000 dari bid sebelumnya</li>
                    </ul>
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