<?php
/**
 * Halaman Profil User
 */

$page_title = 'Profil Saya';

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../functions/helpers.php';

requireLogin();

$user = getUserSession();
$profile = getUserById($conn, $user['id']);
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $full_name = sanitize($_POST['full_name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $address = sanitize($_POST['address'] ?? '');

        $result = updateUserProfile($conn, $user['id'], $full_name, $phone, $address);

        if ($result['success']) {
            $success = true;
            setFlashMessage('success', 'Profil berhasil diperbarui!');
            $profile = getUserById($conn, $user['id']);
        } else {
            $errors = $result['errors'];
        }
    }
}
?>

<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">
        👤 Profil Saya
    </h1>
    <p style="color: #999;">Kelola informasi akun Anda</p>
</div>

<!-- Profile Information Card -->
<div class="card">
    <div class="card-header">
        <h2 style="margin: 0; color: white;">👤 Informasi Akun</h2>
    </div>

    <div class="card-body">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid #333;">
            <div>
                <div style="font-size: 0.85rem; color: #999; text-transform: uppercase;">Username</div>
                <div style="font-size: 1.1rem; color: #fff; font-weight: 600; margin-top: 0.5rem;">
                    <?php echo htmlspecialchars($profile['username']); ?>
                </div>
            </div>
            <div>
                <div style="font-size: 0.85rem; color: #999; text-transform: uppercase;">Email</div>
                <div style="font-size: 1.1rem; color: #fff; font-weight: 600; margin-top: 0.5rem;">
                    <?php echo htmlspecialchars($profile['email']); ?>
                </div>
            </div>
            <div>
                <div style="font-size: 0.85rem; color: #999; text-transform: uppercase;">Role</div>
                <div style="font-size: 1.1rem; color: #fff; font-weight: 600; margin-top: 0.5rem;">
                    <?php echo ucfirst($profile['role']); ?>
                </div>
            </div>
            <div>
                <div style="font-size: 0.85rem; color: #999; text-transform: uppercase;">Status</div>
                <div style="font-size: 1.1rem; color: #fff; font-weight: 600; margin-top: 0.5rem;">
                    <?php echo ($profile['is_active'] ? 'Aktif' : 'Nonaktif'); ?>
                </div>
            </div>
            <div>
                <div style="font-size: 0.85rem; color: #999; text-transform: uppercase;">Tanggal Daftar</div>
                <div style="font-size: 1.1rem; color: #fff; font-weight: 600; margin-top: 0.5rem;">
                    <?php echo formatDate($profile['created_at']); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Form -->
<form method="POST" action="" style="margin-top: 2rem;">
    <div class="form-section">
        <h2 class="form-title">✏️ Edit Profil</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 1.5rem;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <input type="hidden" name="action" value="update_profile">

        <div class="form-group">
            <label for="full_name">Nama Lengkap</label>
            <input
                type="text"
                id="full_name"
                name="full_name"
                class="form-control"
                value="<?php echo htmlspecialchars($profile['full_name']); ?>"
                required
            >
        </div>

        <div class="form-group">
            <label for="phone">No. Telepon</label>
            <input
                type="tel"
                id="phone"
                name="phone"
                class="form-control"
                value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>"
                placeholder="08xxxxxxxxxx"
            >
        </div>

        <div class="form-group">
            <label for="address">Alamat</label>
            <textarea
                id="address"
                name="address"
                class="form-control"
                placeholder="Alamat lengkap Anda"
                rows="4"
            ><?php echo htmlspecialchars($profile['address'] ?? ''); ?></textarea>
        </div>

        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-accent">
                ✓ Simpan Perubahan
            </button>
            <button type="reset" class="btn btn-outline">
                ↻ Reset Form
            </button>
        </div>
    </div>
</form>

<!-- Account Statistics -->
<div style="margin-top: 2rem;">
    <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem;">📊 Statistik Akun</h2>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
        <?php
        $stats = getDashboardStats($conn, $user['id']);
        ?>
        <div class="stat-card">
            <div class="stat-icon">📦</div>
            <div class="stat-value"><?php echo $stats['total_products']; ?></div>
            <div class="stat-label">Produk Dibuat</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-value"><?php echo $stats['total_bids']; ?></div>
            <div class="stat-label">Total Penawaran</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🏆</div>
            <div class="stat-value"><?php echo $stats['winning_auctions'] ?? '0'; ?></div>
            <div class="stat-label">Lelang Dimenangkan</div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>