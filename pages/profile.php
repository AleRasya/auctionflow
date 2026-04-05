<?php
require_once __DIR__ . '/../config/database.php';
requireLogin();

$user = getCurrentUser($connection);

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');

    // Validation
    if (empty($full_name)) {
        $error = 'Nama lengkap tidak boleh kosong';
    } elseif (strlen($full_name) < 3) {
        $error = 'Nama minimal 3 karakter';
    } else {
        $data = [
            'full_name' => $full_name,
            'phone' => $phone,
            'address' => $address,
            'profile_picture' => $user['profile_picture']
        ];

        if (updateUserProfile($connection, $user['id'], $data)) {
            $success = true;
            // Refresh user data
            $user = getCurrentUser($connection);
            logAudit($connection, $user['id'], 'PROFILE_UPDATED', 'User updated profile');
        } else {
            $error = 'Gagal mengupdate profil';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Sistem Lelang Online</title>
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
            <h1>Profil Saya</h1>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <strong>Sukses!</strong> Profil berhasil diperbarui.
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <strong>Error!</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="card mb-30">
                <div class="card-header">Informasi Akun</div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div>
                            <strong>Username:</strong><br>
                            <span><?php echo htmlspecialchars($user['username']); ?></span>
                        </div>
                        <div>
                            <strong>Email:</strong><br>
                            <span><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                        <div>
                            <strong>Role:</strong><br>
                            <span><?php echo ucfirst($user['role']); ?></span>
                        </div>
                        <div>
                            <strong>Terdaftar:</strong><br>
                            <span><?php echo formatDateTime($user['created_at']); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Edit Profil</div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label for="full_name">Nama Lengkap *</label>
                            <input 
                                type="text" 
                                id="full_name" 
                                name="full_name" 
                                value="<?php echo htmlspecialchars($user['full_name']); ?>"
                                required
                            >
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Nomor Telepon</label>
                                <input 
                                    type="tel" 
                                    id="phone" 
                                    name="phone" 
                                    value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                    placeholder="+62812345678"
                                >
                            </div>

                            <div class="form-group">
                                <label for="email">Email (Tidak bisa diubah)</label>
                                <input 
                                    type="email" 
                                    value="<?php echo htmlspecialchars($user['email']); ?>"
                                    disabled
                                >
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address">Alamat</label>
                            <textarea 
                                id="address" 
                                name="address" 
                                placeholder="Alamat lengkap Anda"
                            ><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            <a href="/pages/dashboard.php" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Security Section -->
            <div class="card mt-30">
                <div class="card-header">🔒 Keamanan</div>
                <div class="card-body">
                    <p>Untuk mengubah password, silakan hubungi administrator atau gunakan fitur "Lupa Password" di halaman login.</p>
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