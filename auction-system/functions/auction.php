<?php
/**
 * Auction Functions
 * 
 * Fungsi-fungsi untuk mengelola lelang dan penawaran (bid)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

/**
 * Tambah bid baru
 */
function addBid($conn, $product_id, $user_id, $bid_amount) {
    $errors = [];

    // Validasi input
    if (empty($bid_amount) || !is_numeric($bid_amount) || $bid_amount <= 0) {
        $errors[] = 'Jumlah penawaran harus angka positif';
    }

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    try {
        // Ambil data produk
        $product = getRow(
            $conn,
            "SELECT id, current_price, end_time, status, user_id FROM products WHERE id = ?",
            [$product_id]
        );

        if (!$product) {
            return ['success' => false, 'errors' => ['Produk tidak ditemukan']];
        }

        // Cek status produk
        if ($product['status'] !== 'active') {
            return ['success' => false, 'errors' => ['Lelang sudah berakhir']];
        }

        // Cek waktu lelang
        if (!isAuctionActive($product['end_time'])) {
            // Update status produk ke finished
            updateProductStatus($conn, $product_id, 'finished');
            return ['success' => false, 'errors' => ['Waktu lelang sudah habis']];
        }

        // Cek user tidak bisa bid produk miliknya sendiri
        if ($product['user_id'] == $user_id) {
            return ['success' => false, 'errors' => ['Anda tidak bisa mengajukan penawaran untuk produk Anda sendiri']];
        }

        // Cek penawaran harus lebih tinggi dari harga sekarang
        if ($bid_amount <= $product['current_price']) {
            return ['success' => false, 'errors' => ['Penawaran harus lebih tinggi dari Rp ' . number_format($product['current_price'], 0, ',', '.')]];
        }

        // Cek penawaran minimal (increment)
        $min_increment = $product['current_price'] * 0.05; // 5% dari harga sekarang
        if (($bid_amount - $product['current_price']) < $min_increment) {
            $min_bid = $product['current_price'] + $min_increment;
            return ['success' => false, 'errors' => ['Penawaran minimal adalah Rp ' . number_format($min_bid, 0, ',', '.')]];
        }

        // Simpan bid
        $result = executeQuery(
            $conn,
            "INSERT INTO bids (product_id, user_id, bid_amount) VALUES (?, ?, ?)",
            [$product_id, $user_id, $bid_amount]
        );

        // Update current_price dan highest_bidder di produk
        executeQuery(
            $conn,
            "UPDATE products SET current_price = ?, highest_bidder_id = ? WHERE id = ?",
            [$bid_amount, $user_id, $product_id]
        );

        return ['success' => true, 'bid_id' => $result['insert_id']];
    } catch (Exception $e) {
        return ['success' => false, 'errors' => ['Gagal menambah penawaran: ' . $e->getMessage()]];
    }
}

/**
 * Get bid terbaru untuk produk
 */
function getLatestBid($conn, $product_id) {
    try {
        return getRow(
            $conn,
            "SELECT b.*, u.username, u.full_name 
             FROM bids b 
             LEFT JOIN users u ON b.user_id = u.id 
             WHERE b.product_id = ? 
             ORDER BY b.bid_time DESC LIMIT 1",
            [$product_id]
        );
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Get semua bid untuk produk
 */
function getProductBids($conn, $product_id, $limit = 10) {
    try {
        $query = "SELECT b.*, u.username, u.full_name 
                  FROM bids b 
                  LEFT JOIN users u ON b.user_id = u.id 
                  WHERE b.product_id = ? 
                  ORDER BY b.bid_amount DESC, b.bid_time DESC 
                  LIMIT ?";
        
        return getRows($conn, $query, [$product_id, $limit]);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Hitung jumlah bid untuk produk
 */
function countProductBids($conn, $product_id) {
    try {
        $result = getRow($conn, "SELECT COUNT(*) as total FROM bids WHERE product_id = ?", [$product_id]);
        return $result['total'] ?? 0;
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Get bid history user
 */
function getUserBids($conn, $user_id, $limit = 20, $offset = 0) {
    try {
        $query = "SELECT b.*, p.name as product_name, p.image, p.status as product_status, p.highest_bidder_id 
                  FROM bids b 
                  LEFT JOIN products p ON b.product_id = p.id 
                  WHERE b.user_id = ? 
                  ORDER BY b.bid_time DESC 
                  LIMIT ? OFFSET ?";
        
        return getRows($conn, $query, [$user_id, $limit, $offset]);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Update status produk
 */
function updateProductStatus($conn, $product_id, $status) {
    try {
        executeQuery($conn, "UPDATE products SET status = ? WHERE id = ?", [$status, $product_id]);

        // Update auction history
        if ($status === 'finished') {
            $product = getProduct($conn, $product_id);
            executeQuery(
                $conn,
                "UPDATE auction_history SET status = ?, winner_id = ?, final_price = ?, completed_at = NOW() WHERE product_id = ?",
                ['completed', $product['highest_bidder_id'], $product['current_price'], $product_id]
            );
        }

        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Auto-close auction yang sudah expired
 */
function autoCloseExpiredAuctions($conn) {
    try {
        $query = "UPDATE products SET status = 'finished' 
                  WHERE status = 'active' AND end_time <= NOW()";
        
        $stmt = $conn->prepare($query);
        if ($stmt->execute()) {
            // Update auction history untuk produk yang sudah selesai
            $finished_products = getRows(
                $conn,
                "SELECT id, highest_bidder_id, current_price FROM products 
                 WHERE status = 'finished' AND id IN (
                    SELECT product_id FROM auction_history WHERE status = 'ongoing'
                 )",
                []
            );

            foreach ($finished_products as $product) {
                executeQuery(
                    $conn,
                    "UPDATE auction_history SET status = 'completed', winner_id = ?, final_price = ?, completed_at = NOW() WHERE product_id = ?",
                    [$product['highest_bidder_id'], $product['current_price'], $product['id']]
                );
            }

            return true;
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get auction history detail
 */
function getAuctionHistory($conn, $product_id) {
    try {
        return getRow(
            $conn,
            "SELECT ah.*, p.name as product_name, p.image, p.category, w.username as winner_name, w.full_name as winner_fullname 
             FROM auction_history ah 
             LEFT JOIN products p ON ah.product_id = p.id 
             LEFT JOIN users w ON ah.winner_id = w.id 
             WHERE ah.product_id = ?",
            [$product_id]
        );
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Get dashboard statistics
 */
function getDashboardStats($conn, $user_id = null) {
    try {
        $stats = [];

        // Total products
        if ($user_id) {
            $result = getRow($conn, "SELECT COUNT(*) as total FROM products WHERE user_id = ?", [$user_id]);
        } else {
            $result = getRow($conn, "SELECT COUNT(*) as total FROM products", []);
        }
        $stats['total_products'] = $result['total'] ?? 0;

        // Active products
        if ($user_id) {
            $result = getRow($conn, "SELECT COUNT(*) as total FROM products WHERE user_id = ? AND status = 'active'", [$user_id]);
        } else {
            $result = getRow($conn, "SELECT COUNT(*) as total FROM products WHERE status = 'active'", []);
        }
        $stats['active_products'] = $result['total'] ?? 0;

        // Total bids
        if ($user_id) {
            $result = getRow($conn, "SELECT COUNT(*) as total FROM bids WHERE user_id = ?", [$user_id]);
        } else {
            $result = getRow($conn, "SELECT COUNT(*) as total FROM bids", []);
        }
        $stats['total_bids'] = $result['total'] ?? 0;

        // Winning auctions (for user)
        if ($user_id) {
            $result = getRow($conn, "SELECT COUNT(*) as total FROM auction_history WHERE winner_id = ? AND status = 'completed'", [$user_id]);
            $stats['winning_auctions'] = $result['total'] ?? 0;
        }

        // Total users (for admin)
        if (!$user_id) {
            $result = getRow($conn, "SELECT COUNT(*) as total FROM users", []);
            $stats['total_users'] = $result['total'] ?? 0;
        }

        return $stats;
    } catch (Exception $e) {
        return [];
    }
}

?>