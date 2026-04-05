<?php
require_once __DIR__ . '/../config/database.php';
startSession();

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: /pages/dashboard.php');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input
    $username = sanitizeInput($_POST['username'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');

    // Validation
    if (empty($username)) {
        $errors[] = 'Username tidak boleh kosong';
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $errors[] = 'Username harus 3-20 karakter';
    } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
        $errors[] = 'Username hanya boleh mengandung huruf, angka, underscore, dan dash';
    }

    if (empty($email)) {
        $errors[] = 'Email tidak boleh kosong';
    } elseif (!validateEmail($email)) {
        $errors[] = 'Format email tidak valid';
    }

    if (empty($password)) {
        $errors[] = 'Password tidak boleh kosong';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter';
    }

    if ($password !== $password_confirm) {
        $errors[] = 'Password tidak sesuai';
    }

    if (empty($full_name)) {
        $errors[] = 'Nama lengkap tidak boleh kosong';
    }

    // Check if username exists
    if (!getUserByUsername($connection, $username)) {
        // User doesn't exist, which is good for registration
    } else {
        $errors[] = 'Username sudah terdaftar';
    }

    // Check if email exists
    if (!getUserByEmail($connection, $email)) {
        // Email doesn't exist, which is good for registration
    } else {
        $errors[] = 'Email sudah terdaftar';
    }

    // If no errors, create user
    if (empty($errors)) {
        $hashed_password = hashPassword($password);
        
        $data = [
            'username' => $username,
            'email' => $email,
            'password' => $hashed_password,
            'full_name' => $full_name,
            'phone' => $phone,
            'address' => $address
        ];

        if (createUser($connection, $data)) {
            $success = true;
            logAudit($connection, null, 'USER_REGISTERED', 'Username: ' . $username);
            
            // Clear form
            $username = $email = $full_name = $phone = $address = '';
        } else {
            $errors[] = 'Gagal membuat akun. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Sistem Lelang Online</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0B0F1A 0%, #1a1f3a 100%);
        }
        
        .register-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 500px;
            padding: 40px;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .register-header h1 {
            color: #0B0F1A;
            font-weight: 700;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .register-header p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #0B0F1A;
            font-weight: 500;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #1E3A8A;
            box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 5px;
            margin-bottom: 20px;
            animation: slideIn 0.3s ease-out;
        }

        .alert-danger {
            background-color: #fee;
            color: #c00;
            border: 1px solid #fcc;
        }

        .alert-success {
            background-color: #efe;
            color: #060;
            border: 1px solid #cfc;
        }

        .alert ul {
            margin: 0;
            padding-left: 20px;
        }

        .alert li {
            margin-bottom: 5px;
            font-size: 14px;
        }

        .btn-register {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #1E3A8A 0%, #1a2f70 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(30, 58, 138, 0.3);
        }

        .btn-register:active {
            transform: translateY(0);
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }

        .login-link a {
            color: #1E3A8A;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: #FACC15;
        }

        .password-note {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }

        @media (max-width: 600px) {
            .register-card {
                padding: 25px;
            }

            .register-header h1 {
                font-size: 24px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body style="background: linear-gradient(135deg, #0B0F1A 0%, #1a1f3a 100%);">
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h1>Daftar Akun</h1>
                <p>Bergabunglah dengan komunitas lelang online kami</p>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <strong>Sukses!</strong> Akun Anda telah berhasil dibuat. Silakan <a href="login.php" style="color: #060;">login di sini</a>.
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <strong>Gagal Mendaftar:</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" id="registerForm" novalidate>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        value="<?php echo htmlspecialchars($username ?? ''); ?>"
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
                        value="<?php echo htmlspecialchars($email ?? ''); ?>"
                        placeholder="email@example.com"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="full_name">Nama Lengkap</label>
                    <input 
                        type="text" 
                        id="full_name" 
                        name="full_name" 
                        value="<?php echo htmlspecialchars($full_name ?? ''); ?>"
                        placeholder="Nama Anda"
                        required
                    >
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Minimal 6 karakter"
                            required
                        >
                        <div class="password-note">Gunakan kombinasi huruf, angka, dan simbol</div>
                    </div>

                    <div class="form-group">
                        <label for="password_confirm">Konfirmasi Password</label>
                        <input 
                            type="password" 
                            id="password_confirm" 
                            name="password_confirm" 
                            placeholder="Ulangi password"
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="phone">Nomor Telepon (Opsional)</label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone" 
                        value="<?php echo htmlspecialchars($phone ?? ''); ?>"
                        placeholder="+62812345678"
                    >
                </div>

                <div class="form-group">
                    <label for="address">Alamat (Opsional)</label>
                    <textarea 
                        id="address" 
                        name="address" 
                        placeholder="Alamat lengkap Anda"
                    ><?php echo htmlspecialchars($address ?? ''); ?></textarea>
                </div>

                <button type="submit" class="btn-register">Daftar Sekarang</button>
            </form>

            <div class="login-link">
                Sudah punya akun? <a href="login.php">Login di sini</a>
            </div>
        </div>
    </div>

    <script src="/assets/js/main.js"></script>
</body>
</html>