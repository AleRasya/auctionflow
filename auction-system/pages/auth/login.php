<?php
/**
 * Halaman Login
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $result = loginUser($conn, $username, $password);

    if ($result['success']) {
        setFlashMessage('success', 'Login berhasil!');
        header('Location: ' . APP_URL . '/pages/dashboard/index.php');
        exit;
    } else {
        $errors = $result['errors'];
    }
}

$messages = getAllFlashMessages();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Auction System</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
    <style>
        .auth-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #0B0F1A 0%, #1a1f2e 100%);
        }

        .auth-card {
            background-color: #0f1419;
            border: 2px solid #1E3A8A;
            border-radius: 0.75rem;
            width: 100%;
            max-width: 400px;
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

        .form-group input:focus {
            border-color: #FACC15 !important;
        }

        .btn-login {
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
        }

        .btn-login:hover {
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
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>🎯 AUCTION</h1>
                <p>Web Lelang Online Terpercaya</p>
            </div>

            <div class="auth-divider"></div>

            <?php if (!empty($messages['success'])): ?>
                <div class="alert alert-success">
                    <?php echo $messages['success']; ?>
                </div>
            <?php endif; ?>

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
                <div class="form-group">
                    <label for="username">Username atau Email</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-control"
                        placeholder="Masukkan username Anda"
                        required
                        autocomplete="username"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="Masukkan password Anda"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <button type="submit" class="btn-login">Login</button>
            </form>

            <div class="auth-link">
                <p>Belum punya akun? <a href="<?php echo APP_URL; ?>/pages/auth/register.php">Daftar di sini</a></p>
            </div>

            <div style="text-align: center; margin-top: 2rem; font-size: 0.8rem; color: #666;">
                <p>Demo Account:</p>
                <p>Username: <strong>user1</strong> | Password: <strong>password123</strong></p>
            </div>
        </div>
    </div>
</body>
</html>