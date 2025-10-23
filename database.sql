-- Database setup for Lucky Draw Wheel App
CREATE DATABASE IF NOT EXISTS vpbankgame_luckydraw CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE vpbankgame_luckydraw;

-- Table to store participants and their prizes
CREATE TABLE participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone_number VARCHAR(11) NOT NULL UNIQUE,
    prize_name VARCHAR(100) NOT NULL,
    prize_image VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Index for faster phone number lookups
CREATE INDEX idx_phone_number ON participants(phone_number);
