<?php
require_once 'includes/auth.php';
requireAuth();

$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20; // 20 items per page - chỉ hiển thị 20 file gần nhất
$offset = ($page - 1) * $limit;

try {
    // Debug: Check if PDO is available
    if (!isset($pdo)) {
        throw new Exception('Database connection not available');
    }

    // Get only the 20 most recent export files (no pagination needed)
    $stmt = $pdo->prepare("
        SELECT eh.*, au.username
        FROM export_history eh
        JOIN admin_users au ON eh.admin_user_id = au.id
        ORDER BY eh.created_at DESC
        LIMIT 20
    ");
    $stmt->execute();
    $exportHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($exportHistory)): ?>
        <div class="no-history">
            <i class="fas fa-inbox" style="font-size: 3rem; color: #6c757d; margin-bottom: 15px; display: block;"></i>
            <h4 style="color: #6c757d; margin-bottom: 10px;">Chưa có lịch sử export</h4>
            <p style="color: #6c757d; margin: 0;">Hãy thực hiện export CSV để xem lịch sử tại đây</p>
        </div>
    <?php else: ?>
        <div class="export-history">
            <?php foreach ($exportHistory as $export): ?>
            <div class="history-item">
                <div class="history-info">
                    <i class="fas fa-file-csv"></i>
                    <div>
                        <strong><?php echo htmlspecialchars($export['filename']); ?></strong>
                        <small>
                            <?php
                            $typeNames = [
                                'players' => 'Danh sách người chơi',
                                'prizes' => 'Thống kê quà tặng',
                                'statistics' => 'Thống kê hàng ngày'
                            ];
                            echo $typeNames[$export['export_type']] ?? $export['export_type'];
                            ?> -
                            <?php echo date('d/m/Y H:i', strtotime($export['created_at'])); ?> -
                            <?php echo number_format($export['record_count']); ?> bản ghi
                        </small>
                    </div>
                </div>
                <div class="history-actions">
                    <button class="btn btn-sm btn-secondary" onclick="downloadExport('<?php echo $export['filename']; ?>')">
                        <i class="fas fa-download"></i>
                        Tải lại
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif;

} catch(PDOException $e) {
    error_log("Export history AJAX error: " . $e->getMessage());
    echo '<div class="no-history"><p>Lỗi khi tải lịch sử export: ' . htmlspecialchars($e->getMessage()) . '</p></div>';
} catch(Exception $e) {
    error_log("Export history AJAX error: " . $e->getMessage());
    echo '<div class="no-history"><p>Lỗi khi tải lịch sử export: ' . htmlspecialchars($e->getMessage()) . '</p></div>';
}
?>
