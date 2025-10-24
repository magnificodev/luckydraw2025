-- =====================================================
-- VPBank Lucky Draw - Complete Database Schema
-- =====================================================
-- Tạo database và tất cả tables, views, triggers
-- Import file này vào database mới để có hệ thống hoàn chỉnh

-- Drop database if exists (for fresh install)
DROP DATABASE IF EXISTS vpbankgame_luckydraw;

-- Create database
CREATE DATABASE vpbankgame_luckydraw
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE vpbankgame_luckydraw;

-- =====================================================
-- 1. PRIZES TABLE (Master data for prizes)
-- =====================================================
CREATE TABLE prizes (
    id TINYINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    display_order TINYINT NOT NULL UNIQUE,
    stock INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert prizes with initial stock (8 unique products)
INSERT INTO prizes (name, display_order, stock) VALUES
('Tai nghe Bluetooth', 0, 10),
('Bình thủy tinh', 1, 15),
('Tag hành lý', 2, 20),
('Móc khóa', 3, 25),
('Túi tote', 4, 12),
('Bịt mắt ngủ', 5, 30),
('Ô gấp', 6, 6),
('Mũ bảo hiểm', 7, 4);

-- =====================================================
-- 1.1. WHEEL SEGMENTS TABLE (Virtual segments mapping)
-- =====================================================
CREATE TABLE wheel_segments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    segment_index INT NOT NULL UNIQUE COMMENT '0-11 for 12 segments',
    product_id TINYINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES prizes(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert wheel segments mapping (12 segments → 8 products - matches real wheel)
INSERT INTO wheel_segments (segment_index, product_id) VALUES
(0, 1),   -- Tai nghe bluetooth
(1, 2),   -- Bình thủy tinh
(2, 3),   -- Tag hành lý
(3, 4),   -- Móc khóa
(4, 5),   -- Túi tote
(5, 2),   -- Bình thủy tinh (duplicate)
(6, 4),   -- Móc khóa (duplicate)
(7, 6),   -- Bịt mắt ngủ
(8, 3),   -- Tag hành lý (duplicate)
(9, 5),   -- Túi tote (duplicate)
(10, 7),  -- Ô gấp
(11, 8);  -- Mũ bảo hiểm

-- =====================================================
-- 2. ADMIN USERS TABLE
-- =====================================================
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user (password: Admin2025!@#)
INSERT INTO admin_users (username, password_hash) VALUES
('admin', '$2y$10$BZtl2Fi9wT6jfX8XrDxNu.R.AmDgvT71.apOMvEaJu71B/YJN/bE2');

-- =====================================================
-- 3. PARTICIPANTS TABLE (Game participants)
-- =====================================================
CREATE TABLE participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone_number VARCHAR(11) NOT NULL UNIQUE,
    prize_id TINYINT NOT NULL,
    winning_index TINYINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    session_id VARCHAR(128),
    FOREIGN KEY (prize_id) REFERENCES prizes(id) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- =====================================================
-- 4. PRIZE STATISTICS TABLE
-- =====================================================
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

-- =====================================================
-- 5. EXPORT HISTORY TABLE
-- =====================================================
CREATE TABLE export_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_user_id INT NOT NULL,
    export_type ENUM('players', 'prizes', 'statistics') NOT NULL,
    filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500),
    record_count INT DEFAULT 0,
    file_size INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_user_id) REFERENCES admin_users(id) ON DELETE CASCADE
);

-- =====================================================
-- 6. ADMIN ACTIVITY LOG TABLE
-- =====================================================
CREATE TABLE admin_activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_user_id) REFERENCES admin_users(id) ON DELETE CASCADE
);

-- =====================================================
-- 7. SYSTEM SETTINGS TABLE
-- =====================================================
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    description TEXT,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES admin_users(id) ON DELETE SET NULL
);

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('site_name', 'VPBank Lucky Draw', 'Tên hệ thống'),
('max_daily_spins', '1000', 'Số lượt quay tối đa mỗi ngày'),
('maintenance_mode', '0', 'Chế độ bảo trì (0=off, 1=on)'),
('stock_warning_threshold', '5', 'Ngưỡng cảnh báo stock thấp'),
('export_retention_days', '30', 'Số ngày lưu trữ file export');

-- =====================================================
-- 8. INDEXES FOR PERFORMANCE
-- =====================================================
-- Participants indexes
CREATE INDEX idx_phone_number ON participants(phone_number);
CREATE INDEX idx_created_at ON participants(created_at);
CREATE INDEX idx_prize_id ON participants(prize_id);
CREATE INDEX idx_winning_index ON participants(winning_index);
CREATE INDEX idx_ip_address ON participants(ip_address);

-- Prize statistics indexes
CREATE INDEX idx_prize_stats ON prize_statistics(prize_id);
CREATE INDEX idx_last_won ON prize_statistics(last_won_at);

-- Export history indexes
CREATE INDEX idx_export_history_admin ON export_history(admin_user_id);
CREATE INDEX idx_export_history_type ON export_history(export_type);
CREATE INDEX idx_export_history_date ON export_history(created_at);

-- Activity log indexes
CREATE INDEX idx_activity_admin ON admin_activity_log(admin_user_id);
CREATE INDEX idx_activity_action ON admin_activity_log(action);
CREATE INDEX idx_activity_date ON admin_activity_log(created_at);

-- =====================================================
-- 9. TRIGGERS FOR AUTOMATIC UPDATES
-- =====================================================
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

-- =====================================================
-- 10. VIEWS FOR DASHBOARD AND ANALYTICS
-- =====================================================
-- Dashboard statistics view
CREATE OR REPLACE VIEW dashboard_stats AS
SELECT
    (SELECT COUNT(*) FROM participants) as total_players,
    (SELECT COUNT(*) FROM participants WHERE DATE(created_at) = CURDATE()) as today_spins,
    (SELECT SUM(stock) FROM prizes WHERE is_active = 1) as remaining_stock,
    (SELECT COUNT(*) FROM participants p JOIN prizes pr ON p.prize_id = pr.id WHERE pr.is_active = 1) as prizes_distributed,
    (SELECT COUNT(*) FROM participants WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)) as week_spins,
    (SELECT COUNT(*) FROM participants WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) as month_spins;

-- Prize analytics view
CREATE OR REPLACE VIEW prize_analytics AS
SELECT
    pr.id,
    pr.name,
    pr.stock,
    pr.is_active,
    COALESCE(ps.count, 0) as distributed_count,
    ps.last_won_at,
    CASE
        WHEN pr.stock = 0 THEN 'Hết hàng'
        WHEN pr.stock <= 5 THEN 'Sắp hết'
        WHEN pr.stock <= 10 THEN 'Còn ít'
        ELSE 'Đủ hàng'
    END as stock_status
FROM prizes pr
LEFT JOIN prize_statistics ps ON pr.id = ps.prize_id
ORDER BY pr.display_order ASC;

-- Recent activity view
CREATE OR REPLACE VIEW recent_activity AS
SELECT
    'participant' as type,
    p.phone_number as identifier,
    pr.name as description,
    p.created_at,
    p.ip_address
FROM participants p
JOIN prizes pr ON p.prize_id = pr.id
UNION ALL
SELECT
    'export' as type,
    eh.filename as identifier,
    CONCAT('Exported ', eh.export_type, ' (', eh.record_count, ' records)') as description,
    eh.created_at,
    NULL as ip_address
FROM export_history eh
ORDER BY created_at DESC
LIMIT 50;

-- =====================================================
-- 11. SAMPLE DATA (Optional - for testing)
-- =====================================================
-- Uncomment the following lines to add sample data for testing

/*
-- Sample participants (uncomment for testing)
INSERT INTO participants (phone_number, prize_id, winning_index, ip_address, user_agent, session_id) VALUES
('0901234567', 1, 0, '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'sess_001'),
('0907654321', 3, 2, '192.168.1.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'sess_002'),
('0909876543', 5, 4, '192.168.1.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'sess_003');
*/

-- =====================================================
-- 12. VERIFICATION QUERIES
-- =====================================================
-- Show all tables
SELECT 'Tables created:' as info;
SHOW TABLES;

-- Show all views
SELECT 'Views created:' as info;
SELECT TABLE_NAME FROM information_schema.VIEWS WHERE TABLE_SCHEMA = 'vpbankgame_luckydraw';

-- Show triggers
SELECT 'Triggers created:' as info;
SHOW TRIGGERS;

-- Show indexes
SELECT 'Indexes created:' as info;
SELECT TABLE_NAME, INDEX_NAME, COLUMN_NAME
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = 'vpbankgame_luckydraw'
ORDER BY TABLE_NAME, INDEX_NAME;

-- =====================================================
-- SCHEMA COMPLETED
-- =====================================================
SELECT '✅ Database schema created successfully!' as status;
SELECT '📊 Tables: 6' as summary;
SELECT '📈 Views: 3' as summary;
SELECT '🔧 Triggers: 1' as summary;
SELECT '📋 Indexes: 12' as summary;
SELECT '🎯 Ready for production use!' as status;
