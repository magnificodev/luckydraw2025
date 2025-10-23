-- Database setup for Lucky Draw Wheel App
CREATE DATABASE IF NOT EXISTS vpbankgame_luckydraw CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE vpbankgame_luckydraw;

-- Table to store prizes with stock management
CREATE TABLE prizes (
    id TINYINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    display_order TINYINT NOT NULL UNIQUE,
    stock INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert prizes with initial stock
INSERT INTO prizes (name, display_order, stock) VALUES
('Tai nghe Bluetooth', 0, 10),
('Bình thủy tinh', 1, 15),
('Tag hành lý', 2, 20),
('Móc khóa', 3, 25),
('Túi tote', 4, 12),
('Bình thủy tinh (2)', 5, 8),
('Móc khóa (2)', 6, 18),
('Bịt mắt ngủ', 7, 30),
('Tag hành lý (2)', 8, 22),
('Túi tote (2)', 9, 14),
('Ô gấp', 10, 6),
('Mũ bảo hiểm', 11, 4);

-- Table to store participants and their prizes
CREATE TABLE participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone_number VARCHAR(11) NOT NULL UNIQUE,
    prize_id TINYINT NOT NULL,
    winning_index TINYINT NOT NULL, -- Store the winning index (0-11)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45), -- Support both IPv4 and IPv6
    user_agent TEXT,
    session_id VARCHAR(128),
    FOREIGN KEY (prize_id) REFERENCES prizes(id) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Table for analytics and statistics
CREATE TABLE prize_statistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prize_id TINYINT NOT NULL,
    count INT DEFAULT 1,
    last_won_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (prize_id) REFERENCES prizes(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    UNIQUE KEY unique_prize (prize_id)
);

-- Table for admin users
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user (password: Admin2025!@#)
INSERT INTO admin_users (username, password_hash) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Indexes for better performance
CREATE INDEX idx_phone_number ON participants(phone_number);
CREATE INDEX idx_created_at ON participants(created_at);
CREATE INDEX idx_prize_id ON participants(prize_id);
CREATE INDEX idx_winning_index ON participants(winning_index);
CREATE INDEX idx_ip_address ON participants(ip_address);

-- Index for analytics
CREATE INDEX idx_prize_stats ON prize_statistics(prize_id);
CREATE INDEX idx_last_won ON prize_statistics(last_won_at);

-- Trigger to update prize statistics when a participant is inserted
DELIMITER //
CREATE TRIGGER update_prize_stats_after_insert
AFTER INSERT ON participants
FOR EACH ROW
BEGIN
    INSERT INTO prize_statistics (prize_id, count, last_won_at)
    VALUES (NEW.prize_id, 1, NEW.created_at)
    ON DUPLICATE KEY UPDATE
        count = count + 1,
        last_won_at = NEW.created_at,
        updated_at = CURRENT_TIMESTAMP;
END//
DELIMITER ;
