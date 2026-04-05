-- Database untuk Web Lelang Online
-- Dibuat dengan MySQL

-- Hapus database lama jika ada
DROP DATABASE IF EXISTS auction_system;
CREATE DATABASE auction_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE auction_system;

-- Tabel Users
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(100) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  full_name VARCHAR(150) NOT NULL,
  phone VARCHAR(20),
  address TEXT,
  role ENUM('admin', 'user') DEFAULT 'user',
  is_active TINYINT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_username (username),
  INDEX idx_email (email),
  INDEX idx_role (role)
);

-- Tabel Products (Barang Lelang)
CREATE TABLE products (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  name VARCHAR(200) NOT NULL,
  description TEXT NOT NULL,
  category VARCHAR(50),
  initial_price DECIMAL(12, 2) NOT NULL,
  current_price DECIMAL(12, 2) NOT NULL,
  image VARCHAR(255),
  start_time DATETIME NOT NULL,
  end_time DATETIME NOT NULL,
  status ENUM('active', 'finished', 'cancelled') DEFAULT 'active',
  highest_bidder_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (highest_bidder_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_user_id (user_id),
  INDEX idx_status (status),
  INDEX idx_end_time (end_time),
  INDEX idx_highest_bidder (highest_bidder_id)
);

-- Tabel Bids (Penawaran Lelang)
CREATE TABLE bids (
  id INT PRIMARY KEY AUTO_INCREMENT,
  product_id INT NOT NULL,
  user_id INT NOT NULL,
  bid_amount DECIMAL(12, 2) NOT NULL,
  bid_time DATETIME DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_product_id (product_id),
  INDEX idx_user_id (user_id),
  INDEX idx_bid_time (bid_time),
  UNIQUE KEY unique_highest_bid (product_id, bid_amount)
);

-- Tabel Auction History (Riwayat Lelang)
CREATE TABLE auction_history (
  id INT PRIMARY KEY AUTO_INCREMENT,
  product_id INT NOT NULL,
  winner_id INT,
  final_price DECIMAL(12, 2),
  total_bids INT DEFAULT 0,
  status ENUM('ongoing', 'completed', 'cancelled') DEFAULT 'ongoing',
  completed_at DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (winner_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_product_id (product_id),
  INDEX idx_winner_id (winner_id),
  INDEX idx_completed_at (completed_at)
);

-- Data Sample Admin
INSERT INTO users (username, email, password, full_name, phone, role) VALUES
('admin', 'admin@auction.com', '$2y$10$abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOP', 'Admin System', '081234567890', 'admin');

-- Data Sample User
INSERT INTO users (username, email, password, full_name, phone, role) VALUES
('user1', 'user1@auction.com', '$2y$10$abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOP', 'User Satu', '082111111111', 'user'),
('user2', 'user2@auction.com', '$2y$10$abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOP', 'User Dua', '082222222222', 'user');

-- Catatan: Password default untuk user adalah: password123
-- Gunakan password_hash() untuk produksi yang aman