<?php
/**
 * Halaman Edit Produk
 */

$page_title = 'Edit Lelang';

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../functions/helpers.php';
require_once __DIR__ . '/../../functions/product.php';

requireLogin();

$user = getUserSession();
$product_id = intval($_GET['id'] ?? 0);

if (!$product_id) {
    header('Location: ' . APP_URL . '/pages/products/my-products.php');
    exit;
}

// Get product
$product = getProduct($conn, $product_id);
if (!$product || $product['user_id'] != $user['id']) {
    setFlashMessage('error', 'Produk tidak ditemukan atau Anda tidak memiliki akses');
    header('Location: ' . APP_URL . '/pages/products/my-products.php');
    exit;
}

// Check if product has bids (can only edit if no bids)
$bid_count = countProductBids($conn, $product_id);
if ($bid_count > 0) {
    setFlashMessage('error', 'Produk tidak dapat diedit karena sudah ada penawaran');
    header('Location: ' . APP_URL . '/pages/products/my-products.php');
    exit;
}

$errors = [];
$image_filename = $product['image'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $category = sanitize($_POST['category'] ?? '');
    $initial_price = floatval($_POST['initial_price'] ?? 0);

    // Handle file upload
    try {
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $image_filename = uploadImage($_FILES['image']);
        }
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }

    if (empty($errors)) {
        $result = updateProduct(
            $conn,
            $product_id,
            $user['id'],
            $name,
            $description,
            $category,
            $initial_price,
            $product['start_time'],
            $product['end_time'],
            $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE ? $image_filename : null
        );

        if ($result['success']) {
            setFlashMessage('success', 'Produk berhasil diperbarui!');
            header('Location: ' . APP_URL . '/pages/products/my-products.php');
            exit;
        } else {
            $errors = $result['errors'];
        }
    }
}
?>

<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">
        ✏️ Edit Lelang
    </h1>
    <p style="color: #999;">Perbarui informasi produk lelang Anda</p>
</div>

<form method="POST" action="" enctype="multipart/form-data">
    <!-- Form Section 1: Basic Info -->
    <div class="form-section">
        <h2 class="form-title">📋 Informasi Produk</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 1.5rem;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label for="name">Nama Produk</label>
            <input
                type="text"
                id="name"
                name="name"
                class="form-control"
                value="<?php echo htmlspecialchars($product['name']); ?>"
                required
            >
        </div>

        <div class="form-group">
            <label for="description">Deskripsi Produk</label>
            <textarea
                id="description"
                name="description"
                class="form-control"
                required
            ><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>

        <div class="two-columns">
            <div class="form-group">
                <label for="category">Kategori</label>
                <select id="category" name="category" class="form-control" required>
                    <option value="Elektronik" <?php echo $product['category'] === 'Elektronik' ? 'selected' : ''; ?>>Elektronik</option>
                    <option value="Fashion" <?php echo $product['category'] === 'Fashion' ? 'selected' : ''; ?>>Fashion</option>
                    <option value="Furniture" <?php echo $product['category'] === 'Furniture' ? 'selected' : ''; ?>>Furniture</option>
                    <option value="Otomotif" <?php echo $product['category'] === 'Otomotif' ? 'selected' : ''; ?>>Otomotif</option>
                    <option value="Hobi" <?php echo $product['category'] === 'Hobi' ? 'selected' : ''; ?>>Hobi</option>
                    <option value="Lainnya" <?php echo $product['category'] === 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                </select>
            </div>

            <div class="form-group">
                <label for="initial_price">Harga Awal (Rp)</label>
                <input
                    type="number"
                    id="initial_price"
                    name="initial_price"
                    class="form-control"
                    value="<?php echo $product['initial_price']; ?>"
                    min="1000"
                    step="1000"
                    required
                >
            </div>
        </div>
    </div>

    <!-- Form Section 2: Image -->
    <div class="form-section">
        <h2 class="form-title">🖼️ Gambar Produk</h2>

        <div style="margin-bottom: 1.5rem;">
            <p style="color: #999; font-size: 0.9rem;">Gambar saat ini:</p>
            <?php if (!empty($product['image'])): ?>
                <img src="<?php echo APP_URL; ?>/uploads/products/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="max-width: 200px; max-height: 200px; border-radius: 0.375rem;">
            <?php else: ?>
                <p style="color: #666;">Tidak ada gambar</p>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="image">Ganti Gambar (Opsional)</label>
            <input
                type="file"
                id="image"
                name="image"
                class="form-control"
                accept="image/jpeg, image/png, image/gif"
                data-preview="image-preview"
            >
            <small style="color: #999;">Format: JPG, PNG, GIF | Ukuran maksimal: 5MB</small>
        </div>

        <div id="image-preview" style="margin-top: 1rem;">
            <!-- Preview akan ditampilkan di sini -->
        </div>
    </div>

    <!-- Form Section 3: Info Lelang -->
    <div class="form-section">
        <h2 class="form-title">⏰ Informasi Lelang</h2>

        <div style="background-color: rgba(30, 58, 138, 0.2); border-left: 4px solid #3b82f6; padding: 1rem; border-radius: 0.375rem;">
            <div style="font-size: 0.85rem; color: #999;">ℹ️ Waktu Lelang</div>
            <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem; font-size: 0.85rem; color: #ccc;">
                <li>Mulai: <?php echo formatDateTime($product['start_time']); ?></li>
                <li>Berakhir: <?php echo formatDateTime($product['end_time']); ?></li>
                <li>Status: <?php echo ucfirst($product['status']); ?></li>
            </ul>
        </div>
    </div>

    <!-- Form Section 4: Actions -->
    <div class="form-section">
        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-accent" style="flex: 1;">
                ✓ Simpan Perubahan
            </button>
            <a href="<?php echo APP_URL; ?>/pages/products/my-products.php" class="btn btn-outline" style="flex: 1; text-align: center;">
                ✕ Batal
            </a>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>