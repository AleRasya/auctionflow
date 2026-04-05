<?php
require_once __DIR__ . '/../config/database.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    sendJSON(['success' => false, 'message' => 'Method not allowed']);
}

// Require login
requireLogin();

// Get user
$user = getCurrentUser($connection);
if (!$user) {
    http_response_code(401);
    sendJSON(['success' => false, 'message' => 'Unauthorized']);
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($data['product_id'])) {
    sendJSON(['success' => false, 'message' => 'Product ID tidak ditemukan'], 400);
}

$productId = (int) $data['product_id'];

// Get product
$product = getProductById($connection, $productId);
if (!$product) {
    sendJSON(['success' => false, 'message' => 'Produk tidak ditemukan'], 404);
}

// Check authorization
if ($product['user_id'] != $user['id'] && $user['role'] !== 'admin') {
    http_response_code(403);
    sendJSON(['success' => false, 'message' => 'Anda tidak memiliki izin untuk menghapus produk ini']);
}

// Check if product has bids
if ($product['bid_count'] > 0) {
    sendJSON(['success' => false, 'message' => 'Tidak bisa menghapus produk yang sudah memiliki bid'], 400);
}

// Delete image if exists
if (!empty($product['image_path'])) {
    deleteFile($product['image_path']);
}

// Delete product
if (deleteProduct($connection, $productId)) {
    logAudit($connection, $user['id'], 'PRODUCT_DELETED', 'Product ID: ' . $productId);
    sendJSON(['success' => true, 'message' => 'Produk berhasil dihapus']);
} else {
    sendJSON(['success' => false, 'message' => 'Gagal menghapus produk'], 500);
}
?>