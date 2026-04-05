<?php
/**
 * Halaman Buat Produk Lelang Baru
 */

$page_title = 'Buat Lelang Baru';

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../functions/helpers.php';
require_once __DIR__ . '/../../functions/product.php';

requireLogin();

$user = getUserSession();
$errors = [];
$image_filename = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $category = sanitize($_POST['category'] ?? '');
    $initial_price = floatval($_POST['initial_price'] ?? 0);
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';

    // Handle file upload
    try {
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $image_filename = uploadImage($_FILES['image']);
        } else {
            $errors[] = 'Gambar tidak boleh kosong';
        }
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }

    if (empty($errors)) {
        $result = addProduct(
            $conn,
            $user['id'],
            $name,
            $description,
            $category,
            $initial_price,
            $start_time,
            $end_time,
            $image_filename
        );

        if ($result['success']) {
            setFlashMessage('success', 'Lelang berhasil dibuat!');
            header('Location: ' . APP_URL . '/pages/products/my-products.php');
            exit;
        } else {
            $errors = $result['errors'];
            if ($image_filename) {
                deleteImage($image_filename);
            }
        }
    }
}

// Get minimum start time (now)
$min_start = date('Y-m-d\TH:i');
$default_end = date('Y-m-d\TH:i', strtotime('+7 days'));
?>

<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">
        ➕ Buat Lelang Baru
    </h1>
    <p style="color: #999;">Tambahkan produk baru untuk dilelang</p>
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
                placeholder="Contoh: Laptop HP 15 inch"
                required
            >
        </div>

        <div class="form-group">
            <label for="description">Deskripsi Produk</label>
            <textarea
                id="description"
                name="description"
                class="form-control"
                placeholder="Jelaskan detail produk Anda (kondisi, spesifikasi, dll)"
                required
            ></textarea>
        </div>

        <div class="two-columns">
            <div class="form-group">
                <label for="category">Kategori</label>
                <select id="category" name="category" class="form-control" required>
                    <option value="">-- Pilih Kategori --</option>
                    <option value="Elektronik">Elektronik</option>
                    <option value="Fashion">Fashion</option>
                    <option value="Furniture">Furniture</option>
                    <option value="Otomotif">Otomotif</option>
                    <option value="Hobi">Hobi</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>

            <div class="form-group">
                <label for="initial_price">Harga Awal (Rp)</label>
                <input
                    type="number"
                    id="initial_price"
                    name="initial_price"
                    class="form-control"
                    placeholder="100000"
                    min="1000"
                    step="1000"
                    required
                >
            </div>
        </div>
    </div>

    <!-- Form Section 2: Auction Time -->
    <div class="form-section">
        <h2 class="form-title">⏰ Waktu Lelang</h2>

        <div class="two-columns">
            <div class="form-group">
                <label for="start_time">Waktu Mulai</label>
                <input
                    type="datetime-local"
                    id="start_time"
                    name="start_time"
                    class="form-control"
                    min="<?php echo $min_start; ?>"
                    required
                >
                <small style="color: #999;">Minimal mulai sekarang</small>
            </div>

            <div class="form-group">
                <label for="end_time">Waktu Berakhir</label>
                <input
                    type="datetime-local"
                    id="end_time"
                    name="end_time"
                    class="form-control"
                    value="<?php echo $default_end; ?>"
                    required
                >
                <small style="color: #999;">Harus setelah waktu mulai</small>
            </div>
        </div>

        <div style="background-color: rgba(59, 130, 246, 0.1); border-left: 4px solid #3b82f6; padding: 1rem; border-radius: 0.375rem;">
            <div style="font-size: 0.85rem; color: #999;">ℹ️ Tips Waktu Lelang</div>
            <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem; font-size: 0.85rem; color: #ccc;">
                <li>Waktu lelang minimal 1 jam dari sekarang</li>
                <li>Waktu lelang maksimal 30 hari</li>
                <li>Lelang aktif sampai waktu berakhir dipenuhi</li>
            </ul>
        </div>
    </div>

    <!-- Form Section 3: Image -->
    <div class="form-section">
        <h2 class="form-title">🖼️ Gambar Produk</h2>

        <div class="form-group">
            <label for="image">Upload Gambar</label>
            <input
                type="file"
                id="image"
                name="image"
                class="form-control"
                accept="image/jpeg, image/png, image/gif"
                data-preview="image-preview"
                required
            >
            <small style="color: #999;">Format: JPG, PNG, GIF | Ukuran maksimal: 5MB</small>
        </div>

        <div id="image-preview" style="margin-top: 1rem;">
            <!-- Preview akan ditampilkan di sini -->
        </div>
    </div>

    <!-- Form Section 4: Actions -->
    <div class="form-section">
        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-accent" style="flex: 1;">
                ✓ Buat Lelang
            </button>
            <a href="<?php echo APP_URL; ?>/pages/products/my-products.php" class="btn btn-outline" style="flex: 1; text-align: center;">
                ✕ Batal
            </a>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>