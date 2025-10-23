-- Database improvements for Admin Panel
USE vpbankgame_luckydraw;

-- Add export history table
CREATE TABLE IF NOT EXISTS export_history (
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

-- Add indexes for better performance
CREATE INDEX IF NOT EXISTS idx_export_history_admin ON export_history(admin_user_id);
CREATE INDEX IF NOT EXISTS idx_export_history_type ON export_history(export_type);
CREATE INDEX IF NOT EXISTS idx_export_history_date ON export_history(created_at);

-- Add admin activity log table
CREATE TABLE IF NOT EXISTS admin_activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_user_id) REFERENCES admin_users(id) ON DELETE CASCADE
);

-- Add indexes for activity log
CREATE INDEX IF NOT EXISTS idx_activity_admin ON admin_activity_log(admin_user_id);
CREATE INDEX IF NOT EXISTS idx_activity_action ON admin_activity_log(action);
CREATE INDEX IF NOT EXISTS idx_activity_date ON admin_activity_log(created_at);

-- Add system settings table
CREATE TABLE IF NOT EXISTS system_settings (
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
('export_retention_days', '30', 'Số ngày lưu trữ file export')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- Create view for dashboard statistics
CREATE OR REPLACE VIEW dashboard_stats AS
SELECT 
    (SELECT COUNT(*) FROM participants) as total_players,
    (SELECT COUNT(*) FROM participants WHERE DATE(created_at) = CURDATE()) as today_spins,
    (SELECT SUM(stock) FROM prizes WHERE is_active = 1) as remaining_stock,
    (SELECT COUNT(*) FROM participants p JOIN prizes pr ON p.prize_id = pr.id WHERE pr.is_active = 1) as prizes_distributed,
    (SELECT COUNT(*) FROM participants WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)) as week_spins,
    (SELECT COUNT(*) FROM participants WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) as month_spins;

-- Create view for prize analytics
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
