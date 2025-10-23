<?php
require_once 'includes/auth.php';
requireAuth();

$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 4; // 4 items per page - tránh thanh cuộn
$offset = ($page - 1) * $limit;

try {
    // Debug: Check if PDO is available
    if (!isset($pdo)) {
        throw new Exception('Database connection not available');
    }

    // Get total count
    $countStmt = $pdo->query("
        SELECT COUNT(*) as total
        FROM export_history eh
        JOIN admin_users au ON eh.admin_user_id = au.id
    ");
    $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalRecords / $limit);

    // Get paginated data
    $stmt = $pdo->prepare("
        SELECT eh.*, au.username
        FROM export_history eh
        JOIN admin_users au ON eh.admin_user_id = au.id
        ORDER BY eh.created_at DESC
        LIMIT $limit OFFSET $offset
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

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="modal-pagination">
            <div class="modal-pagination-links">
                <!-- First page -->
                <?php if ($page > 1): ?>
                    <button class="btn btn-sm btn-secondary" onclick="loadExportHistory(1)" title="Trang đầu">
                        <i class="fas fa-angle-double-left"></i>
                    </button>
                <?php else: ?>
                    <span class="btn btn-sm btn-disabled" title="Trang đầu">
                        <i class="fas fa-angle-double-left"></i>
                    </span>
                <?php endif; ?>

                <!-- Previous page -->
                <?php if ($page > 1): ?>
                    <button class="btn btn-sm btn-secondary" onclick="loadExportHistory(<?php echo $page - 1; ?>)" title="Trang trước">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                <?php else: ?>
                    <span class="btn btn-sm btn-disabled" title="Trang trước">
                        <i class="fas fa-chevron-left"></i>
                    </span>
                <?php endif; ?>

                <!-- Page numbers -->
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <button class="btn btn-sm <?php echo $i == $page ? 'btn-primary' : 'btn-secondary'; ?>"
                            onclick="loadExportHistory(<?php echo $i; ?>)" title="Trang <?php echo $i; ?>">
                        <?php echo $i; ?>
                    </button>
                <?php endfor; ?>

                <!-- Next page -->
                <?php if ($page < $totalPages): ?>
                    <button class="btn btn-sm btn-secondary" onclick="loadExportHistory(<?php echo $page + 1; ?>)" title="Trang sau">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                <?php else: ?>
                    <span class="btn btn-sm btn-disabled" title="Trang sau">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                <?php endif; ?>

                <!-- Last page -->
                <?php if ($page < $totalPages): ?>
                    <button class="btn btn-sm btn-secondary" onclick="loadExportHistory(<?php echo $totalPages; ?>)" title="Trang cuối">
                        <i class="fas fa-angle-double-right"></i>
                    </button>
                <?php else: ?>
                    <span class="btn btn-sm btn-disabled" title="Trang cuối">
                        <i class="fas fa-angle-double-right"></i>
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    <?php endif;

} catch(PDOException $e) {
    error_log("Export history AJAX error: " . $e->getMessage());
    echo '<div class="no-history"><p>Lỗi khi tải lịch sử export: ' . htmlspecialchars($e->getMessage()) . '</p></div>';
} catch(Exception $e) {
    error_log("Export history AJAX error: " . $e->getMessage());
    echo '<div class="no-history"><p>Lỗi khi tải lịch sử export: ' . htmlspecialchars($e->getMessage()) . '</p></div>';
}
?>
