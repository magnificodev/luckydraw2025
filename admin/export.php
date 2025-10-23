<?php
require_once 'includes/auth.php';
requireAuth();

$exportType = $_POST['export_type'] ?? $_GET['export_type'] ?? '';

if (empty($exportType)) {
    header('Location: dashboard.php');
    exit();
}

// Get current admin user
$admin = getCurrentAdmin();
if (!$admin) {
    header('Location: index.php');
    exit();
}

try {
    switch ($exportType) {
        case 'players':
            exportPlayers();
            break;
        case 'prizes':
            exportPrizes();
            break;
        case 'statistics':
            exportStatistics();
            break;
        default:
            throw new Exception('Loại export không hợp lệ');
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'Lỗi khi export: ' . $e->getMessage();
    header('Location: export.php');
    exit();
}

function exportPlayers() {
    global $pdo, $admin;

    $sql = "
        SELECT p.phone_number, pr.name as prize_name, p.created_at,
               p.ip_address, p.user_agent, p.winning_index
        FROM participants p
        JOIN prizes pr ON p.prize_id = pr.id
        ORDER BY p.created_at DESC
    ";

    $stmt = $pdo->query($sql);
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $recordCount = count($players);

    $filename = 'nguoi_choi_' . date('Y-m-d_H-i-s') . '.csv';

    // Log export activity
    logExportActivity($admin['id'], 'players', $filename, $recordCount);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    // Add BOM for UTF-8
    echo "\xEF\xBB\xBF";

    $output = fopen('php://output', 'w');

    // CSV Headers
    fputcsv($output, [
        'Số điện thoại',
        'Quà tặng',
        'Thời gian quay',
        'IP Address',
        'User Agent',
        'Winning Index'
    ]);

    // CSV Data
    foreach ($players as $player) {
        fputcsv($output, [
            $player['phone_number'],
            $player['prize_name'],
            date('d/m/Y H:i:s', strtotime($player['created_at'])),
            $player['ip_address'],
            $player['user_agent'],
            $player['winning_index']
        ]);
    }

    fclose($output);
    exit();
}

function exportPrizes() {
    global $pdo;

    $sql = "
        SELECT pr.name, pr.stock, pr.is_active,
               COALESCE(ps.count, 0) as distributed_count,
               ps.last_won_at
        FROM prizes pr
        LEFT JOIN prize_statistics ps ON pr.id = ps.prize_id
        ORDER BY pr.display_order ASC
    ";

    $stmt = $pdo->query($sql);
    $prizes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $filename = 'qua_tang_' . date('Y-m-d_H-i-s') . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    // Add BOM for UTF-8
    echo "\xEF\xBB\xBF";

    $output = fopen('php://output', 'w');

    // CSV Headers
    fputcsv($output, [
        'Tên quà',
        'Stock hiện tại',
        'Đã phát',
        'Trạng thái',
        'Lần cuối thắng'
    ]);

    // CSV Data
    foreach ($prizes as $prize) {
        fputcsv($output, [
            $prize['name'],
            $prize['stock'],
            $prize['distributed_count'],
            $prize['is_active'] ? 'Hoạt động' : 'Tạm dừng',
            $prize['last_won_at'] ? date('d/m/Y H:i:s', strtotime($prize['last_won_at'])) : 'Chưa có'
        ]);
    }

    fclose($output);
    exit();
}

function exportStatistics() {
    global $pdo;

    // Get daily statistics for the last 30 days
    $sql = "
        SELECT
            DATE(p.created_at) as date,
            COUNT(*) as total_players,
            COUNT(DISTINCT p.prize_id) as unique_prizes,
            GROUP_CONCAT(DISTINCT pr.name ORDER BY pr.name SEPARATOR ', ') as prizes_won
        FROM participants p
        JOIN prizes pr ON p.prize_id = pr.id
        WHERE p.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY DATE(p.created_at)
        ORDER BY p.created_at DESC
    ";

    $stmt = $pdo->query($sql);
    $dailyStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $filename = 'thong_ke_' . date('Y-m-d_H-i-s') . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    // Add BOM for UTF-8
    echo "\xEF\xBB\xBF";

    $output = fopen('php://output', 'w');

    // CSV Headers
    fputcsv($output, [
        'Ngày',
        'Tổng người chơi',
        'Số loại quà đã phát',
        'Danh sách quà đã phát'
    ]);

    // CSV Data
    foreach ($dailyStats as $stat) {
        fputcsv($output, [
            date('d/m/Y', strtotime($stat['date'])),
            $stat['total_players'],
            $stat['unique_prizes'],
            $stat['prizes_won']
        ]);
    }

    fclose($output);
    exit();
}

// Helper function to log export activity
function logExportActivity($adminId, $exportType, $filename, $recordCount) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            INSERT INTO export_history (admin_user_id, export_type, filename, record_count, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$adminId, $exportType, $filename, $recordCount]);

        // Also log admin activity
        $stmt = $pdo->prepare("
            INSERT INTO admin_activity_log (admin_user_id, action, description, ip_address, user_agent, created_at)
            VALUES (?, 'export', ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $adminId,
            "Exported $exportType data: $filename ($recordCount records)",
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);

    } catch(PDOException $e) {
        error_log("Failed to log export activity: " . $e->getMessage());
    }
}
?>
