<?php
require_once __DIR__ . '/../config/database.php';
startSession();

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: /pages/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username dan password tidak boleh kosong';
    } else {
        $user = getUserByUsername($connection, $username);

        if ($user && verifyPassword($password, $user['password'])) {
            if (!$user['is_active']) {
                $error = 'Akun Anda telah dinonaktifkan';
            } else {
                // Login success
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                logAudit($connection, $user['id'], 'LOGIN', 'User login');

                // Redirect to dashboard
                header('Location: /pages/dashboard.php');
                exit;
            }
        } else {
            $error = 'Username atau password salah';
            logAudit($connection, null, 'LOGIN_FAILED', 'Username: ' . $username);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Lelang Online</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0B0F1A 0%, #1a1f3a 100%);
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
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

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: #0B0F1A;
            font-weight: 700;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .login-header p {
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

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-group input:focus {
            outline: none;
            border-color: #1E3A8A;
            box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
        }

        .alert {
            padding: 12px 16px;
            border-radius: 5px;
            margin-bottom: 20px;
            background-color: #fee;
            color: #c00;
            border: 1px solid #fcc;
            font-size: 14px;
            animation: slideIn 0.3s ease-out;
        }

        .btn-login {
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
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(30, 58, 138, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .login-footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }

        .login-footer a {
            color: #1E3A8A;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-footer a:hover {
            color: #FACC15;
        }

        .demo-credentials {
            background: rgba(30, 58, 138, 0.1);
            border-left: 4px solid #1E3A8A;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 13px;
            color: #333;
        }

        .demo-credentials strong {
            color: #1E3A8A;
        }

        @media (max-width: 500px) {
            .login-card {
                padding: 25px;
            }

            .login-header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body style="background: linear-gradient(135deg, #0B0F1A 0%, #1a1f3a 100%);">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Lelang Online</h1>
                <p>Masuk ke akun Anda</p>
            </div>

            <?php if ($error): ?>
                <div class="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="loginForm">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        placeholder="Masukkan username Anda"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Masukkan password Anda"
                        required
                    >
                </div>

                <button type="submit" class="btn-login">Login</button>
            </form>

            <div class="login-footer">
                Belum punya akun? <a href="register.php">Daftar di sini</a>
            </div>
        </div>
    </div>

    <script src="/assets/js/main.js"></script>
</body>
</html>