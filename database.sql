-- Database setup for Lucky Draw Wheel App
CREATE DATABASE IF NOT EXISTS vpbankgame_luckydraw CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE vpbankgame_luckydraw;

-- Table to store participants and their prizes
CREATE TABLE participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone_number VARCHAR(11) NOT NULL UNIQUE,
    prize_name VARCHAR(100) NOT NULL,
    winning_index TINYINT NOT NULL, -- Store the winning index (0-11)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45), -- Support both IPv4 and IPv6
    user_agent TEXT,
    session_id VARCHAR(128)
);

-- Table for analytics and statistics
CREATE TABLE prize_statistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prize_name VARCHAR(100) NOT NULL,
    winning_index TINYINT NOT NULL,
    count INT DEFAULT 1,
    last_won_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_prize (prize_name, winning_index)
);

-- Indexes for better performance
CREATE INDEX idx_phone_number ON participants(phone_number);
CREATE INDEX idx_created_at ON participants(created_at);
CREATE INDEX idx_prize_name ON participants(prize_name);
CREATE INDEX idx_winning_index ON participants(winning_index);
CREATE INDEX idx_ip_address ON participants(ip_address);

-- Index for analytics
CREATE INDEX idx_prize_stats ON prize_statistics(prize_name, winning_index);
CREATE INDEX idx_last_won ON prize_statistics(last_won_at);
