-- Database: auction_system
-- Create tables untuk sistem lelang online

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    phone VARCHAR(20),
    address TEXT,
    profile_picture VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(50),
    image_path VARCHAR(255),
    starting_price DECIMAL(15, 2) NOT NULL,
    current_price DECIMAL(15, 2) NOT NULL,
    status ENUM('active', 'ended', 'cancelled') DEFAULT 'active',
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    winner_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (winner_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_end_time (end_time)
);

CREATE TABLE bids (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    bid_amount DECIMAL(15, 2) NOT NULL,
    bid_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_winner BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_user (user_id),
    UNIQUE KEY unique_bid (product_id, bid_time, user_id)
);

CREATE TABLE audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert admin user (password: admin123, gunakan PHP untuk hash)
INSERT INTO users (username, email, password, full_name, role, is_active) VALUES
('admin', 'admin@auctionsystem.local', '$2y$10$YourHashedPasswordHere', 'Admin User', 'admin', TRUE);