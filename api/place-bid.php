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
if (!isset($data['product_id']) || !isset($data['bid_amount'])) {
    sendJSON(['success' => false, 'message' => 'Data tidak lengkap'], 400);
}

$productId = (int) $data['product_id'];
$bidAmount = (float) $data['bid_amount'];

// Validate bid amount
if ($bidAmount <= 0) {
    sendJSON(['success' => false, 'message' => 'Jumlah bid harus lebih dari 0'], 400);
}

// Place bid
$result = placeBid($connection, $productId, $user['id'], $bidAmount);

if ($result['success']) {
    logAudit($connection, $user['id'], 'BID_PLACED', 'Product ID: ' . $productId . ', Amount: ' . $bidAmount);
    sendJSON(['success' => true, 'message' => $result['message']]);
} else {
    sendJSON(['success' => false, 'message' => $result['message']], 400);
}
?>