<?php
// Helper Functions untuk Aplikasi Lelang

// ============================================
// SESSION & AUTHENTICATION FUNCTIONS
// ============================================

/**
 * Start secure session
 */
function startSession()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 86400,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        session_start();
    }
}

/**
 * Check if user is logged in
 */
function isLoggedIn()
{
    startSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user ID
 */
function getCurrentUserId()
{
    startSession();
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user data
 */
function getCurrentUser($connection)
{
    if (!isLoggedIn()) {
        return null;
    }

    $userId = getCurrentUserId();
    $stmt = $connection->prepare("SELECT * FROM users WHERE id = ? AND is_active = TRUE");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Require login (redirect if not logged in)
 */
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: /pages/login.php');
        exit;
    }
}

/**
 * Require admin role
 */
function requireAdmin($connection)
{
    requireLogin();
    $user = getCurrentUser($connection);
    if (!$user || $user['role'] !== 'admin') {
        header('Location: /pages/dashboard.php');
        exit;
    }
}

/**
 * Logout user
 */
function logoutUser()
{
    startSession();
    session_destroy();
    header('Location: /pages/login.php');
    exit;
}

// ============================================
// SECURITY FUNCTIONS
// ============================================

/**
 * Hash password using bcrypt
 */
function hashPassword($password)
{
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash)
{
    return password_verify($password, $hash);
}

/**
 * Sanitize input
 */
function sanitizeInput($input)
{
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

/**
 * Sanitize file name
 */
function sanitizeFileName($fileName)
{
    $fileName = basename($fileName);
    $fileName = preg_replace("/[^a-zA-Z0-9._-]/", "_", $fileName);
    return $fileName;
}

/**
 * Validate email
 */
function validateEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate phone number
 */
function validatePhone($phone)
{
    return preg_match('/^(\+62|0)[0-9]{9,12}$/', $phone);
}

/**
 * Generate CSRF token
 */
function generateCSRFToken()
{
    startSession();
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token)
{
    startSession();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ============================================
// DATABASE FUNCTIONS
// ============================================

/**
 * Get user by ID
 */
function getUserById($connection, $userId)
{
    $stmt = $connection->prepare("SELECT id, username, email, full_name, role, phone, address, profile_picture, created_at FROM users WHERE id = ? AND is_active = TRUE");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Get user by username
 */
function getUserByUsername($connection, $username)
{
    $stmt = $connection->prepare("SELECT * FROM users WHERE username = ? AND is_active = TRUE");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Get user by email
 */
function getUserByEmail($connection, $email)
{
    $stmt = $connection->prepare("SELECT * FROM users WHERE email = ? AND is_active = TRUE");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Create new user
 */
function createUser($connection, $data)
{
    $stmt = $connection->prepare(
        "INSERT INTO users (username, email, password, full_name, phone, address) 
         VALUES (?, ?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "ssssss",
        $data['username'],
        $data['email'],
        $data['password'],
        $data['full_name'],
        $data['phone'],
        $data['address']
    );

    return $stmt->execute() ? $connection->insert_id : false;
}

/**
 * Update user profile
 */
function updateUserProfile($connection, $userId, $data)
{
    $stmt = $connection->prepare(
        "UPDATE users SET full_name = ?, phone = ?, address = ?, profile_picture = ? 
         WHERE id = ?"
    );

    $stmt->bind_param(
        "ssssi",
        $data['full_name'],
        $data['phone'],
        $data['address'],
        $data['profile_picture'],
        $userId
    );

    return $stmt->execute();
}

// ============================================
// PRODUCT FUNCTIONS
// ============================================

/**
 * Get all products with filters
 */
function getAllProducts($connection, $filters = [])
{
    $query = "SELECT p.*, u.username, u.full_name, COUNT(DISTINCT b.id) as bid_count, MAX(b.bid_amount) as highest_bid 
              FROM products p
              LEFT JOIN users u ON p.user_id = u.id
              LEFT JOIN bids b ON p.id = b.product_id
              WHERE 1=1";

    $params = [];
    $types = "";

    // Filter by status
    if (!empty($filters['status'])) {
        $query .= " AND p.status = ?";
        $params[] = $filters['status'];
        $types .= "s";
    }

    // Filter by search
    if (!empty($filters['search'])) {
        $search = '%' . $filters['search'] . '%';
        $query .= " AND (p.title LIKE ? OR p.description LIKE ?)";
        $params[] = $search;
        $params[] = $search;
        $types .= "ss";
    }

    // Filter by category
    if (!empty($filters['category'])) {
        $query .= " AND p.category = ?";
        $params[] = $filters['category'];
        $types .= "s";
    }

    $query .= " GROUP BY p.id ORDER BY p.created_at DESC LIMIT 50";

    if (!empty($params)) {
        $stmt = $connection->prepare($query);
        $stmt->bind_param($types, ...$params);
    } else {
        $stmt = $connection->prepare($query);
    }

    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get product by ID
 */
function getProductById($connection, $productId)
{
    $stmt = $connection->prepare(
        "SELECT p.*, u.username, u.full_name, 
                COUNT(DISTINCT b.id) as bid_count, 
                MAX(b.bid_amount) as highest_bid,
                w.username as winner_username, w.full_name as winner_name
         FROM products p
         LEFT JOIN users u ON p.user_id = u.id
         LEFT JOIN bids b ON p.id = b.product_id
         LEFT JOIN users w ON p.winner_id = w.id
         WHERE p.id = ?
         GROUP BY p.id"
    );

    $stmt->bind_param("i", $productId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Get products by user
 */
function getProductsByUser($connection, $userId)
{
    $stmt = $connection->prepare(
        "SELECT p.*, COUNT(DISTINCT b.id) as bid_count, MAX(b.bid_amount) as highest_bid
         FROM products p
         LEFT JOIN bids b ON p.id = b.product_id
         WHERE p.user_id = ?
         GROUP BY p.id
         ORDER BY p.created_at DESC"
    );

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Create new product
 */
function createProduct($connection, $data)
{
    $stmt = $connection->prepare(
        "INSERT INTO products (user_id, title, description, category, image_path, starting_price, current_price, start_time, end_time, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $status = 'active';
    $stmt->bind_param(
        "issssddddss",
        $data['user_id'],
        $data['title'],
        $data['description'],
        $data['category'],
        $data['image_path'],
        $data['starting_price'],
        $data['starting_price'],
        $data['start_time'],
        $data['end_time'],
        $status
    );

    return $stmt->execute() ? $connection->insert_id : false;
}

/**
 * Update product
 */
function updateProduct($connection, $productId, $data)
{
    $stmt = $connection->prepare(
        "UPDATE products SET title = ?, description = ?, category = ?, image_path = ? WHERE id = ?"
    );

    $stmt->bind_param(
        "ssssi",
        $data['title'],
        $data['description'],
        $data['category'],
        $data['image_path'],
        $productId
    );

    return $stmt->execute();
}

/**
 * Delete product
 */
function deleteProduct($connection, $productId)
{
    $stmt = $connection->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    return $stmt->execute();
}

/**
 * Check if product is ended
 */
function isProductEnded($product)
{
    return strtotime($product['end_time']) < time();
}

/**
 * Update product status
 */
function updateProductStatus($connection, $productId, $status)
{
    $stmt = $connection->prepare("UPDATE products SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $productId);
    return $stmt->execute();
}

// ============================================
// BID FUNCTIONS
// ============================================

/**
 * Get bid history
 */
function getBidHistory($connection, $productId)
{
    $stmt = $connection->prepare(
        "SELECT b.*, u.username, u.full_name
         FROM bids b
         JOIN users u ON b.user_id = u.id
         WHERE b.product_id = ?
         ORDER BY b.bid_amount DESC, b.bid_time DESC"
    );

    $stmt->bind_param("i", $productId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get highest bid
 */
function getHighestBid($connection, $productId)
{
    $stmt = $connection->prepare(
        "SELECT * FROM bids WHERE product_id = ? ORDER BY bid_amount DESC LIMIT 1"
    );

    $stmt->bind_param("i", $productId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Place bid
 */
function placeBid($connection, $productId, $userId, $bidAmount)
{
    // Get product
    $product = getProductById($connection, $productId);
    
    if (!$product) {
        return ['success' => false, 'message' => 'Produk tidak ditemukan'];
    }

    // Check if auction is ended
    if (isProductEnded($product)) {
        return ['success' => false, 'message' => 'Lelang telah berakhir'];
    }

    // Check if product status is active
    if ($product['status'] !== 'active') {
        return ['success' => false, 'message' => 'Lelang tidak aktif'];
    }

    // Validate bid amount
    $minimumBid = $product['current_price'] + 1000;
    if ($bidAmount < $minimumBid) {
        return ['success' => false, 'message' => 'Bid harus minimal Rp' . number_format($minimumBid, 0, ',', '.')];
    }

    // Check if user is bidding on own product
    if ($product['user_id'] == $userId) {
        return ['success' => false, 'message' => 'Anda tidak bisa bid pada produk milik Anda sendiri'];
    }

    // Insert bid
    $stmt = $connection->prepare(
        "INSERT INTO bids (product_id, user_id, bid_amount) VALUES (?, ?, ?)"
    );

    $stmt->bind_param("iid", $productId, $userId, $bidAmount);

    if ($stmt->execute()) {
        // Update product current price
        $updateStmt = $connection->prepare(
            "UPDATE products SET current_price = ? WHERE id = ?"
        );
        $updateStmt->bind_param("di", $bidAmount, $productId);
        $updateStmt->execute();

        return ['success' => true, 'message' => 'Bid berhasil ditempatkan'];
    } else {
        return ['success' => false, 'message' => 'Gagal menempatkan bid'];
    }
}

/**
 * Get user bids
 */
function getUserBids($connection, $userId)
{
    $stmt = $connection->prepare(
        "SELECT b.*, p.title, p.image_path, p.end_time, p.status
         FROM bids b
         JOIN products p ON b.product_id = p.id
         WHERE b.user_id = ?
         ORDER BY b.bid_time DESC"
    );

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Finalize auction (set winner)
 */
function finalizeAuction($connection, $productId)
{
    $highestBid = getHighestBid($connection, $productId);
    
    if (!$highestBid) {
        // No bids, set status to ended without winner
        updateProductStatus($connection, $productId, 'ended');
        return ['success' => true, 'message' => 'Lelang berakhir tanpa pemenang'];
    }

    // Update product with winner
    $stmt = $connection->prepare(
        "UPDATE products SET winner_id = ?, status = ? WHERE id = ?"
    );

    $status = 'ended';
    $stmt->bind_param(
        "isi",
        $highestBid['user_id'],
        $status,
        $productId
    );

    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Lelang selesai, pemenang telah ditentukan'];
    } else {
        return ['success' => false, 'message' => 'Gagal menyelesaikan lelang'];
    }
}

// ============================================
// DASHBOARD FUNCTIONS
// ============================================

/**
 * Get dashboard stats
 */
function getDashboardStats($connection)
{
    $stats = [];

    // Total products
    $result = $connection->query("SELECT COUNT(*) as total FROM products WHERE status = 'active'");
    $stats['active_products'] = $result->fetch_assoc()['total'];

    // Total bids
    $result = $connection->query("SELECT COUNT(*) as total FROM bids");
    $stats['total_bids'] = $result->fetch_assoc()['total'];

    // Total users
    $result = $connection->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
    $stats['total_users'] = $result->fetch_assoc()['total'];

    // Auctions ended
    $result = $connection->query("SELECT COUNT(*) as total FROM products WHERE status = 'ended'");
    $stats['ended_auctions'] = $result->fetch_assoc()['total'];

    return $stats;
}

/**
 * Get user dashboard stats
 */
function getUserDashboardStats($connection, $userId)
{
    $stats = [];

    // My products
    $result = $connection->prepare("SELECT COUNT(*) as total FROM products WHERE user_id = ?");
    $result->bind_param("i", $userId);
    $result->execute();
    $stats['my_products'] = $result->get_result()->fetch_assoc()['total'];

    // My bids
    $result = $connection->prepare("SELECT COUNT(*) as total FROM bids WHERE user_id = ?");
    $result->bind_param("i", $userId);
    $result->execute();
    $stats['my_bids'] = $result->get_result()->fetch_assoc()['total'];

    // My wins
    $result = $connection->prepare("SELECT COUNT(*) as total FROM products WHERE winner_id = ?");
    $result->bind_param("i", $userId);
    $result->execute();
    $stats['my_wins'] = $result->get_result()->fetch_assoc()['total'];

    return $stats;
}

// ============================================
// FILE HANDLING FUNCTIONS
// ============================================

/**
 * Upload product image
 */
function uploadProductImage($file)
{
    // Validate file
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = basename($file['name']);
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        return ['success' => false, 'message' => 'Format file tidak diizinkan. Gunakan JPG, PNG, atau GIF'];
    }

    if ($file['size'] > 5000000) {
        return ['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 5MB'];
    }

    // Create uploads directory
    $uploadDir = __DIR__ . '/../uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Generate unique filename
    $newFilename = uniqid() . '.' . $ext;
    $filepath = $uploadDir . '/' . $newFilename;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $newFilename, 'path' => '/uploads/' . $newFilename];
    } else {
        return ['success' => false, 'message' => 'Gagal upload file'];
    }
}

/**
 * Delete file
 */
function deleteFile($filepath)
{
    $fullPath = __DIR__ . '/../' . $filepath;
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    return false;
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

/**
 * Format currency to IDR
 */
function formatCurrency($amount)
{
    return 'Rp' . number_format($amount, 0, ',', '.');
}

/**
 * Format date and time
 */
function formatDateTime($datetime)
{
    return date('d M Y H:i', strtotime($datetime));
}

/**
 * Format date
 */
function formatDate($date)
{
    return date('d M Y', strtotime($date));
}

/**
 * Get remaining time
 */
function getRemainingTime($endTime)
{
    $end = strtotime($endTime);
    $now = time();

    if ($end <= $now) {
        return ['status' => 'ended', 'text' => 'Lelang Berakhir'];
    }

    $diff = $end - $now;
    $days = floor($diff / 86400);
    $hours = floor(($diff % 86400) / 3600);
    $minutes = floor(($diff % 3600) / 60);
    $seconds = $diff % 60;

    return [
        'status' => 'active',
        'days' => $days,
        'hours' => $hours,
        'minutes' => $minutes,
        'seconds' => $seconds,
        'text' => sprintf("%d hari %d jam %d menit", $days, $hours, $minutes)
    ];
}

/**
 * Send JSON response
 */
function sendJSON($data, $statusCode = 200)
{
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

/**
 * Log audit action
 */
function logAudit($connection, $userId, $action, $description = "")
{
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
    $stmt = $connection->prepare(
        "INSERT INTO audit_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "isss",
        $userId,
        $action,
        $description,
        $ipAddress
    );

    $stmt->execute();
}

?>