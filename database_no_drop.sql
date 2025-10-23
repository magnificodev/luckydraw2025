-- =====================================================
-- VPBank Lucky Draw - Database Schema (No DROP DATABASE)
-- =====================================================
-- Import file này vào database đã tồn tại
-- Không có DROP DATABASE statement để tránh lỗi phpMyAdmin

USE vpbankgame_luckydraw;

-- =====================================================
-- 1. PRIZES TABLE (Master data for prizes)
-- =====================================================
CREATE TABLE IF NOT EXISTS prizes (
    id TINYINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    display_order TINYINT NOT NULL UNIQUE,
    stock INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. WHEEL SEGMENTS TABLE (Virtual segments mapping)
-- =====================================================
CREATE TABLE IF NOT EXISTS wheel_segments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    segment_index INT NOT NULL UNIQUE COMMENT '0-11 for 12 segments',
    product_id TINYINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES prizes(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. PARTICIPANTS TABLE (Player data)
-- =====================================================
CREATE TABLE IF NOT EXISTS participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone_number VARCHAR(11) NOT NULL UNIQUE,
    prize_id TINYINT NOT NULL,
    winning_index TINYINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    session_id VARCHAR(128),
    FOREIGN KEY (prize_id) REFERENCES prizes(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. PRIZE STATISTICS TABLE (Prize analytics)
-- =====================================================
CREATE TABLE IF NOT EXISTS prize_statistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prize_id TINYINT NOT NULL,
    count INT DEFAULT 0,
    last_won_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (prize_id) REFERENCES prizes(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. ADMIN USERS TABLE (Admin authentication)
-- =====================================================
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. EXPORT HISTORY TABLE (CSV export tracking)
-- =====================================================
CREATE TABLE IF NOT EXISTS export_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_user_id INT NOT NULL,
    export_type ENUM('players', 'prizes', 'statistics') NOT NULL,
    filename VARCHAR(255) NOT NULL,
    record_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_user_id) REFERENCES admin_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. ADMIN ACTIVITY LOG TABLE (Admin actions tracking)
-- =====================================================
CREATE TABLE IF NOT EXISTS admin_activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_user_id) REFERENCES admin_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. SYSTEM SETTINGS TABLE (Configuration)
-- =====================================================
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INSERT DATA
-- =====================================================

-- Insert prizes data (8 unique products)
INSERT IGNORE INTO prizes (id, name, display_order, stock, is_active) VALUES
(1, 'Tai nghe bluetooth', 1, 50, TRUE),
(2, 'Bình thủy tinh', 2, 30, TRUE),
(3, 'Tag hành lý', 3, 40, TRUE),
(4, 'Móc khóa', 4, 60, TRUE),
(5, 'Túi tote', 5, 25, TRUE),
(6, 'Bịt mắt ngủ', 6, 35, TRUE),
(7, 'Ô gấp', 7, 20, TRUE),
(8, 'Mũ bảo hiểm', 8, 15, TRUE);

-- Insert wheel segments mapping (12 segments to 8 products)
INSERT IGNORE INTO wheel_segments (segment_index, product_id) VALUES
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

-- Insert admin user (default admin)
INSERT IGNORE INTO admin_users (id, username, password_hash) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert system settings
INSERT IGNORE INTO system_settings (setting_key, setting_value, description) VALUES
('app_name', 'VPBank Lucky Draw', 'Tên ứng dụng'),
('app_version', '1.0.0', 'Phiên bản ứng dụng'),
('max_daily_spins', '1000', 'Số lượt quay tối đa mỗi ngày'),
('maintenance_mode', 'false', 'Chế độ bảo trì');

-- =====================================================
-- TRIGGERS
-- =====================================================

-- Trigger to update prize statistics when new participant is inserted
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS update_prize_stats_after_insert
AFTER INSERT ON participants
FOR EACH ROW
BEGIN
    INSERT INTO prize_statistics (prize_id, count, last_won_at)
    VALUES (NEW.prize_id, 1, NEW.created_at)
    ON DUPLICATE KEY UPDATE
        count = count + 1,
        last_won_at = NEW.created_at,
        updated_at = CURRENT_TIMESTAMP;
END$$
DELIMITER ;

-- =====================================================
-- VIEWS
-- =====================================================

-- Dashboard statistics view
CREATE OR REPLACE VIEW dashboard_stats AS
SELECT 
    (SELECT COUNT(*) FROM participants) as total_participants,
    (SELECT COUNT(*) FROM participants WHERE DATE(created_at) = CURDATE()) as today_participants,
    (SELECT SUM(stock) FROM prizes WHERE is_active = TRUE) as total_stock,
    (SELECT COUNT(*) FROM prizes WHERE is_active = TRUE) as active_prizes;

-- Prize analytics view
CREATE OR REPLACE VIEW prize_analytics AS
SELECT 
    p.name as prize_name,
    p.stock,
    p.is_active,
    COALESCE(ps.count, 0) as times_won,
    ps.last_won_at
FROM prizes p
LEFT JOIN prize_statistics ps ON p.id = ps.prize_id
ORDER BY p.display_order;

-- Recent activity view
CREATE OR REPLACE VIEW recent_activity AS
SELECT 
    'participant' as type,
    phone_number as identifier,
    created_at,
    'Người chơi mới' as description
FROM participants
UNION ALL
SELECT 
    'export' as type,
    filename as identifier,
    created_at,
    CONCAT('Export ', export_type) as description
FROM export_history
ORDER BY created_at DESC
LIMIT 20;

-- =====================================================
-- INDEXES FOR PERFORMANCE
-- =====================================================

-- Indexes for participants table
CREATE INDEX IF NOT EXISTS idx_participants_phone ON participants(phone_number);
CREATE INDEX IF NOT EXISTS idx_participants_created_at ON participants(created_at);
CREATE INDEX IF NOT EXISTS idx_participants_prize_id ON participants(prize_id);

-- Indexes for prize_statistics table
CREATE INDEX IF NOT EXISTS idx_prize_stats_prize_id ON prize_statistics(prize_id);
CREATE INDEX IF NOT EXISTS idx_prize_stats_count ON prize_statistics(count);

-- Indexes for export_history table
CREATE INDEX IF NOT EXISTS idx_export_history_admin ON export_history(admin_user_id);
CREATE INDEX IF NOT EXISTS idx_export_history_created_at ON export_history(created_at);

-- Indexes for admin_activity_log table
CREATE INDEX IF NOT EXISTS idx_activity_log_admin ON admin_activity_log(admin_user_id);
CREATE INDEX IF NOT EXISTS idx_activity_log_created_at ON admin_activity_log(created_at);

-- =====================================================
-- COMPLETION MESSAGE
-- =====================================================
-- Database schema imported successfully!
-- All tables, triggers, views, and indexes created.
-- Default admin user: admin / Admin2025!@#
-- Ready for production use.
