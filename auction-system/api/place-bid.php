<?php
/**
 * API untuk Place Bid via AJAX
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/helpers.php';
require_once __DIR__ . '/../functions/auction.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Anda harus login untuk mengajukan penawaran']);
    exit;
}

// Get user session
$user = getUserSession();

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON body
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

$product_id = intval($input['product_id'] ?? 0);
$bid_amount = floatval($input['bid_amount'] ?? 0);

if (!$product_id || !$bid_amount) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Product ID dan bid amount diperlukan']);
    exit;
}

try {
    $result = addBid($conn, $product_id, $user['id'], $bid_amount);

    if ($result['success']) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Penawaran berhasil diajukan!',
            'bid_id' => $result['bid_id']
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => implode(', ', $result['errors'])
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}

?>