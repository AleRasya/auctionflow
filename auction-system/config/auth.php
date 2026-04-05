<?php
/**
 * Authentication Functions
 * 
 * Fungsi-fungsi untuk proses login, register, dan validasi user
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../functions/helpers.php';

/**
 * Register user baru
 */
function registerUser($conn, $username, $email, $password, $confirm_password, $full_name, $phone = null) {
    $errors = [];

    // Validasi input
    if (empty($username)) {
        $errors[] = 'Username tidak boleh kosong';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username minimal 3 karakter';
    }

    if (empty($email)) {
        $errors[] = 'Email tidak boleh kosong';
    } elseif (!isValidEmail($email)) {
        $errors[] = 'Format email tidak valid';
    }

    if (empty($password)) {
        $errors[] = 'Password tidak boleh kosong';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter';
    } elseif ($password !== $confirm_password) {
        $errors[] = 'Password tidak sesuai';
    }

    if (empty($full_name)) {
        $errors[] = 'Nama lengkap tidak boleh kosong';
    }

    // Cek username sudah terdaftar
    $existing_user = getRow($conn, "SELECT id FROM users WHERE username = ? OR email = ?", [$username, $email]);
    if ($existing_user) {
        $errors[] = 'Username atau email sudah terdaftar';
    }

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    // Hash password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    try {
        $result = executeQuery(
            $conn,
            "INSERT INTO users (username, email, password, full_name, phone, role) VALUES (?, ?, ?, ?, ?, ?)",
            [$username, $email, $password_hash, $full_name, $phone, 'user']
        );

        return ['success' => true, 'user_id' => $result['insert_id']];
    } catch (Exception $e) {
        return ['success' => false, 'errors' => ['Gagal mendaftar: ' . $e->getMessage()]];
    }
}

/**
 * Login user
 */
function loginUser($conn, $username, $password) {
    $errors = [];

    if (empty($username)) {
        $errors[] = 'Username tidak boleh kosong';
    }

    if (empty($password)) {
        $errors[] = 'Password tidak boleh kosong';
    }

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    try {
        // Cari user
        $user = getRow($conn, "SELECT id, username, email, password, full_name, phone, role, is_active FROM users WHERE username = ?", [$username]);

        if (!$user) {
            return ['success' => false, 'errors' => ['Username atau password salah']];
        }

        // Cek apakah user aktif
        if ($user['is_active'] == 0) {
            return ['success' => false, 'errors' => ['Akun Anda telah dinonaktifkan']];
        }

        // Verifikasi password
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'errors' => ['Username atau password salah']];
        }

        // Set session
        setUserSession($user);

        return ['success' => true];
    } catch (Exception $e) {
        return ['success' => false, 'errors' => ['Terjadi kesalahan: ' . $e->getMessage()]];
    }
}

/**
 * Get user by ID
 */
function getUserById($conn, $id) {
    try {
        return getRow($conn, "SELECT id, username, email, full_name, phone, address, role, is_active, created_at FROM users WHERE id = ?", [$id]);
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Update profil user
 */
function updateUserProfile($conn, $user_id, $full_name, $phone, $address) {
    $errors = [];

    if (empty($full_name)) {
        $errors[] = 'Nama lengkap tidak boleh kosong';
    }

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    try {
        executeQuery(
            $conn,
            "UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?",
            [$full_name, $phone, $address, $user_id]
        );

        $_SESSION['full_name'] = $full_name;

        return ['success' => true];
    } catch (Exception $e) {
        return ['success' => false, 'errors' => ['Gagal update profil: ' . $e->getMessage()]];
    }
}

?>