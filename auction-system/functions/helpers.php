<?php
/**
 * Helper Functions
 * 
 * File ini berisi fungsi-fungsi umum yang digunakan di seluruh aplikasi
 */

// ==================== INPUT VALIDATION & SANITIZATION ====================

/**
 * Sanitasi input dari user
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(stripslashes(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validasi email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validasi URL gambar
 */
function isValidImage($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, ALLOWED_EXTENSIONS);
}

/**
 * Generate random string
 */
function generateRandomString($length = 10) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $result = '';
    for ($i = 0; $i < $length; $i++) {
        $result .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $result;
}

// ==================== FILE UPLOAD ====================

/**
 * Upload gambar dengan validasi
 */
function uploadImage($file) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("File upload error");
    }

    // Validasi ukuran file
    if ($file['size'] > MAX_FILE_SIZE) {
        throw new Exception("Ukuran file terlalu besar (maksimal 5MB)");
    }

    // Validasi tipe file
    if (!isValidImage($file['name'])) {
        throw new Exception("Tipe file tidak diizinkan (hanya JPG, PNG, GIF)");
    }

    // Generate nama file yang aman
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = 'product_' . time() . '_' . generateRandomString(8) . '.' . $ext;
    $filepath = UPLOAD_DIR . $filename;

    // Buat direktori jika belum ada
    if (!file_exists(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    // Validasi MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($mime, $allowed_mimes)) {
        throw new Exception("File type tidak valid");
    }

    // Pindahkan file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception("Gagal mengunggah file");
    }

    return $filename;
}

/**
 * Hapus gambar
 */
function deleteImage($filename) {
    $filepath = UPLOAD_DIR . $filename;
    if (file_exists($filepath)) {
        unlink($filepath);
        return true;
    }
    return false;
}

// ==================== SESSION MANAGEMENT ====================

/**
 * Start session dengan timeout
 */
function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => SESSION_TIMEOUT,
            'path' => '/',
            'domain' => '',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        session_start();
    }

    // Cek session timeout
    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
            session_destroy();
            return false;
        }
    }

    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Cek apakah user sudah login
 */
function isLoggedIn() {
    startSecureSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Redirect ke login jika belum login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/pages/auth/login.php');
        exit;
    }
}

/**
 * Cek role admin
 */
function isAdmin() {
    if (!isLoggedIn()) {
        return false;
    }
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Set user session
 */
function setUserSession($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'];
}

/**
 * Get user session
 */
function getUserSession() {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'full_name' => $_SESSION['full_name'] ?? null,
        'role' => $_SESSION['role'] ?? null
    ];
}

/**
 * Logout
 */
function logout() {
    session_start();
    session_unset();
    session_destroy();
}

// ==================== DATABASE HELPERS ====================

/**
 * Get single row dari database
 */
function getRow($conn, $query, $params = []) {
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    if (!empty($params)) {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return $row;
}

/**
 * Get multiple rows dari database
 */
function getRows($conn, $query, $params = []) {
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    if (!empty($params)) {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();

    return $rows;
}

/**
 * Execute query INSERT, UPDATE, DELETE
 */
function executeQuery($conn, $query, $params = []) {
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    if (!empty($params)) {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $affected = $stmt->affected_rows;
    $last_id = $stmt->insert_id;
    $stmt->close();

    return ['affected_rows' => $affected, 'insert_id' => $last_id];
}

// ==================== MESSAGE & ALERT ====================

/**
 * Set flash message
 */
function setFlashMessage($type, $message) {
    startSecureSession();
    $_SESSION['flash_' . $type] = $message;
}

/**
 * Get flash message
 */
function getFlashMessage($type) {
    startSecureSession();
    $key = 'flash_' . $type;
    if (isset($_SESSION[$key])) {
        $message = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $message;
    }
    return null;
}

/**
 * Get semua flash messages
 */
function getAllFlashMessages() {
    startSecureSession();
    $messages = [];
    
    foreach (['success', 'error', 'warning', 'info'] as $type) {
        $msg = getFlashMessage($type);
        if ($msg) {
            $messages[$type] = $msg;
        }
    }
    
    return $messages;
}

// ==================== UTILITY ====================

/**
 * Format currency ke IDR
 */
function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

/**
 * Format datetime
 */
function formatDateTime($datetime) {
    return date('d M Y H:i', strtotime($datetime));
}

/**
 * Format date
 */
function formatDate($date) {
    return date('d M Y', strtotime($date));
}

/**
 * Hitung waktu tersisa (dalam detik)
 */
function getTimeRemaining($endTime) {
    $end = new DateTime($endTime);
    $now = new DateTime();
    $diff = $end->getTimestamp() - $now->getTimestamp();
    return max(0, $diff);
}

/**
 * Cek apakah lelang masih aktif
 */
function isAuctionActive($endTime) {
    return getTimeRemaining($endTime) > 0;
}

/**
 * Format waktu countdown
 */
function formatTimeRemaining($seconds) {
    if ($seconds <= 0) {
        return 'Waktu habis';
    }
    
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    
    if ($hours > 0) {
        return sprintf('%d jam %d menit', $hours, $minutes);
    } elseif ($minutes > 0) {
        return sprintf('%d menit %d detik', $minutes, $secs);
    } else {
        return sprintf('%d detik', $secs);
    }
}

?>