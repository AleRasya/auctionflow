<?php
/**
 * Halaman Register
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../functions/helpers.php';

// Jika sudah login, redirect ke dashboard
if (isLoggedIn()) {
    header('Location: ' . APP_URL . '/pages/dashboard/index.php');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = sanitize($_POST['full_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');

    $result = registerUser($conn, $username, $email, $password, $confirm_password, $full_name, $phone);

    if ($result['success']) {
        $success = true;
        setFlashMessage('success', 'Pendaftaran berhasil! Silakan login dengan akun Anda.');
        header('Location: ' . APP_URL . '/pages/auth/login.php');
        exit;
    } else {
        $errors = $result['errors'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Auction System</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
    <style>
        .auth-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #0B0F1A 0%, #1a1f2e 100%);
            padding: 1rem;
        }

        .auth-card {
            background-color: #0f1419;
            border: 2px solid #1E3A8A;
            border-radius: 0.75rem;
            width: 100%;
            max-width: 500px;
            padding: 3rem 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #FACC15;
            margin-bottom: 0.5rem;
        }

        .auth-header p {
            color: #999;
            font-size: 0.9rem;
        }

        .auth-divider {
            height: 2px;
            background: linear-gradient(90deg, transparent, #1E3A8A, transparent);
            margin: 2rem 0;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-row.full {
            grid-template-columns: 1fr;
        }

        .btn-register {
            width: 100%;
            background-color: #1E3A8A;
            color: white;
            padding: 0.75rem;
            border: none;
            border-radius: 0.375rem;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .btn-register:hover {
            background-color: #152d5e;
            box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3);
        }

        .auth-link {
            text-align: center;
            margin-top: 1.5rem;
        }

        .auth-link p {
            color: #999;
            font-size: 0.9rem;
        }

        .auth-link a {
            color: #FACC15;
            font-weight: 600;
            transition: color 0.3s;
        }

        .auth-link a:hover {
            color: #f0d115;
        }

        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>🎯 AUCTION</h1>
                <p>Daftar Akun Baru</p>
            </div>

            <div class="auth-divider"></div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul style="margin: 0; padding-left: 1.5rem;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-row full">
                    <div class="form-group">
                        <label for="full_name">Nama Lengkap</label>
                        <input
                            type="text"
                            id="full_name"
                            name="full_name"
                            class="form-control"
                            placeholder="Nama lengkap Anda"
                            required
                        >
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            class="form-control"
                            placeholder="Minimal 3 karakter"
                            required
                        >
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            placeholder="Email Anda"
                            required
                        >
                    </div>
                </div>

                <div class="form-row full">
                    <div class="form-group">
                        <label for="phone">No. Telepon</label>
                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            class="form-control"
                            placeholder="08xxxxxxxxxx"
                        >
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="Minimal 6 karakter"
                            required
                        >
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Konfirmasi Password</label>
                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            class="form-control"
                            placeholder="Ulangi password"
                            required
                        >
                    </div>
                </div>

                <button type="submit" class="btn-register">Daftar Akun</button>
            </form>

            <div class="auth-link">
                <p>Sudah punya akun? <a href="<?php echo APP_URL; ?>/pages/auth/login.php">Login di sini</a></p>
            </div>
        </div>
    </div>
</body>
</html>