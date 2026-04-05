<?php
/**
 * Product Functions
 * 
 * Fungsi-fungsi untuk mengelola produk lelang
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

/**
 * Tambah produk baru
 */
function addProduct($conn, $user_id, $name, $description, $category, $initial_price, $start_time, $end_time, $image) {
    $errors = [];

    // Validasi input
    if (empty($name)) {
        $errors[] = 'Nama produk tidak boleh kosong';
    }

    if (empty($description)) {
        $errors[] = 'Deskripsi tidak boleh kosong';
    }

    if (empty($category)) {
        $errors[] = 'Kategori tidak boleh kosong';
    }

    if (empty($initial_price) || !is_numeric($initial_price) || $initial_price <= 0) {
        $errors[] = 'Harga awal harus angka positif';
    }

    if (empty($start_time)) {
        $errors[] = 'Waktu mulai tidak boleh kosong';
    }

    if (empty($end_time)) {
        $errors[] = 'Waktu akhir tidak boleh kosong';
    }

    // Validasi waktu
    $start = new DateTime($start_time);
    $end = new DateTime($end_time);
    $now = new DateTime();

    if ($start < $now) {
        $errors[] = 'Waktu mulai tidak boleh di masa lalu';
    }

    if ($end <= $start) {
        $errors[] = 'Waktu akhir harus setelah waktu mulai';
    }

    if (empty($image)) {
        $errors[] = 'Gambar tidak boleh kosong';
    }

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    try {
        $result = executeQuery(
            $conn,
            "INSERT INTO products (user_id, name, description, category, initial_price, current_price, image, start_time, end_time, status) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$user_id, $name, $description, $category, $initial_price, $initial_price, $image, $start_time, $end_time, 'active']
        );

        // Buat auction history
        executeQuery(
            $conn,
            "INSERT INTO auction_history (product_id, status) VALUES (?, ?)",
            [$result['insert_id'], 'ongoing']
        );

        return ['success' => true, 'product_id' => $result['insert_id']];
    } catch (Exception $e) {
        return ['success' => false, 'errors' => ['Gagal menambah produk: ' . $e->getMessage()]];
    }
}

/**
 * Edit produk
 */
function updateProduct($conn, $product_id, $user_id, $name, $description, $category, $initial_price, $start_time, $end_time, $image = null) {
    $errors = [];

    // Validasi input
    if (empty($name)) {
        $errors[] = 'Nama produk tidak boleh kosong';
    }

    if (empty($description)) {
        $errors[] = 'Deskripsi tidak boleh kosong';
    }

    if (empty($category)) {
        $errors[] = 'Kategori tidak boleh kosong';
    }

    if (empty($initial_price) || !is_numeric($initial_price) || $initial_price <= 0) {
        $errors[] = 'Harga awal harus angka positif';
    }

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    try {
        // Cek apakah produk pemilik user
        $product = getRow($conn, "SELECT id, user_id, image FROM products WHERE id = ?", [$product_id]);

        if (!$product || $product['user_id'] != $user_id) {
            return ['success' => false, 'errors' => ['Anda tidak memiliki akses ke produk ini']];
        }

        // Update gambar jika ada
        if ($image) {
            deleteImage($product['image']);
        }

        $query = "UPDATE products SET name = ?, description = ?, category = ?, initial_price = ?, current_price = ?";
        $params = [$name, $description, $category, $initial_price, $initial_price];

        if ($image) {
            $query .= ", image = ?";
            $params[] = $image;
        }

        $query .= " WHERE id = ?";
        $params[] = $product_id;

        executeQuery($conn, $query, $params);

        return ['success' => true];
    } catch (Exception $e) {
        return ['success' => false, 'errors' => ['Gagal update produk: ' . $e->getMessage()]];
    }
}

/**
 * Hapus produk
 */
function deleteProduct($conn, $product_id, $user_id) {
    try {
        // Cek apakah produk pemilik user
        $product = getRow($conn, "SELECT id, user_id, image FROM products WHERE id = ?", [$product_id]);

        if (!$product || $product['user_id'] != $user_id) {
            return ['success' => false, 'errors' => ['Anda tidak memiliki akses ke produk ini']];
        }

        // Hapus gambar
        if ($product['image']) {
            deleteImage($product['image']);
        }

        // Hapus produk
        executeQuery($conn, "DELETE FROM products WHERE id = ?", [$product_id]);

        return ['success' => true];
    } catch (Exception $e) {
        return ['success' => false, 'errors' => ['Gagal hapus produk: ' . $e->getMessage()]];
    }
}

/**
 * Get produk by ID
 */
function getProduct($conn, $product_id) {
    try {
        return getRow(
            $conn,
            "SELECT p.*, u.username as seller_name, u.full_name as seller_fullname 
             FROM products p 
             LEFT JOIN users u ON p.user_id = u.id 
             WHERE p.id = ?",
            [$product_id]
        );
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Get semua produk dengan filter
 */
function getProducts($conn, $status = 'all', $search = '', $limit = 12, $offset = 0) {
    try {
        $query = "SELECT p.*, u.username as seller_name, COUNT(b.id) as bid_count 
                  FROM products p 
                  LEFT JOIN users u ON p.user_id = u.id 
                  LEFT JOIN bids b ON p.id = b.product_id 
                  WHERE 1 = 1";
        $params = [];

        if ($status !== 'all') {
            $query .= " AND p.status = ?";
            $params[] = $status;
        }

        if (!empty($search)) {
            $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $search_term = '%' . $search . '%';
            $params[] = $search_term;
            $params[] = $search_term;
        }

        $query .= " GROUP BY p.id ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return getRows($conn, $query, $params);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get produk yang dimiliki user
 */
function getUserProducts($conn, $user_id, $limit = 10, $offset = 0) {
    try {
        $query = "SELECT p.*, COUNT(b.id) as bid_count 
                  FROM products p 
                  LEFT JOIN bids b ON p.id = b.product_id 
                  WHERE p.user_id = ? 
                  GROUP BY p.id 
                  ORDER BY p.created_at DESC 
                  LIMIT ? OFFSET ?";
        
        return getRows($conn, $query, [$user_id, $limit, $offset]);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Hitung total produk
 */
function countProducts($conn, $status = 'all', $search = '') {
    try {
        $query = "SELECT COUNT(*) as total FROM products WHERE 1 = 1";
        $params = [];

        if ($status !== 'all') {
            $query .= " AND status = ?";
            $params[] = $status;
        }

        if (!empty($search)) {
            $query .= " AND (name LIKE ? OR description LIKE ?)";
            $search_term = '%' . $search . '%';
            $params[] = $search_term;
            $params[] = $search_term;
        }

        $result = getRow($conn, $query, $params);
        return $result['total'] ?? 0;
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Hitung status produk
 */
function countProductsByStatus($conn, $user_id = null) {
    try {
        $query = "SELECT status, COUNT(*) as count FROM products";
        $params = [];

        if ($user_id) {
            $query .= " WHERE user_id = ?";
            $params[] = $user_id;
        }

        $query .= " GROUP BY status";

        $results = getRows($conn, $query, $params);
        $counts = ['active' => 0, 'finished' => 0, 'cancelled' => 0];

        foreach ($results as $result) {
            $counts[$result['status']] = $result['count'];
        }

        return $counts;
    } catch (Exception $e) {
        return ['active' => 0, 'finished' => 0, 'cancelled' => 0];
    }
}

?>