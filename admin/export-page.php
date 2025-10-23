<?php
require_once 'includes/header.php';
?>

<div class="export-management">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-download"></i>
            Export CSV
        </h2>
    </div>

    <div class="export-options">
        <div class="export-card">
            <div class="export-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="export-content">
                <h3>Danh sách Người chơi</h3>
                <p>Xuất danh sách tất cả người chơi với thông tin chi tiết</p>
                <ul class="export-features">
                    <li>Số điện thoại</li>
                    <li>Quà tặng đã nhận</li>
                    <li>Thời gian quay</li>
                    <li>IP Address</li>
                    <li>User Agent</li>
                </ul>
                <button class="btn btn-primary" onclick="exportCSV('players')">
                    <i class="fas fa-download"></i>
                    Export Người chơi
                </button>
            </div>
        </div>

        <div class="export-card">
            <div class="export-icon">
                <i class="fas fa-gift"></i>
            </div>
            <div class="export-content">
                <h3>Thống kê Quà tặng</h3>
                <p>Xuất thông tin chi tiết về tất cả quà tặng</p>
                <ul class="export-features">
                    <li>Tên quà tặng</li>
                    <li>Stock hiện tại</li>
                    <li>Số lượng đã phát</li>
                    <li>Trạng thái hoạt động</li>
                    <li>Lần cuối thắng</li>
                </ul>
                <button class="btn btn-primary" onclick="exportCSV('prizes')">
                    <i class="fas fa-download"></i>
                    Export Quà tặng
                </button>
            </div>
        </div>

        <div class="export-card">
            <div class="export-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="export-content">
                <h3>Thống kê Hàng ngày</h3>
                <p>Xuất báo cáo thống kê 30 ngày gần nhất</p>
                <ul class="export-features">
                    <li>Số người chơi theo ngày</li>
                    <li>Số loại quà đã phát</li>
                    <li>Danh sách quà đã phát</li>
                    <li>Xu hướng hoạt động</li>
                </ul>
                <button class="btn btn-primary" onclick="exportCSV('statistics')">
                    <i class="fas fa-download"></i>
                    Export Thống kê
                </button>
            </div>
        </div>
    </div>

    <!-- Export History -->
    <div class="card" style="margin-top: 30px;">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-history"></i>
                Lịch sử Export
            </h3>
        </div>
        <div class="card-body">
            <div class="export-history">
                <?php
                // Get export history (limit to 3 for display)
                try {
                    $stmt = $pdo->prepare("
                        SELECT eh.*, au.username
                        FROM export_history eh
                        JOIN admin_users au ON eh.admin_user_id = au.id
                        ORDER BY eh.created_at DESC
                        LIMIT 3
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

                        <!-- View All Button -->
                        <div class="view-all-section" style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #e9ecef;">
                            <button class="btn btn-primary" onclick="openExportHistoryModal()">
                                <i class="fas fa-list"></i>
                                Xem tất cả
                            </button>
                        </div>
                    <?php endif;
                } catch(PDOException $e) {
                    echo '<div class="no-history"><p>Lỗi khi tải lịch sử export</p></div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Export History Modal -->
<div id="exportHistoryModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 800px; width: 90%;">
        <div class="modal-header">
            <div class="modal-title-section">
                <h3>
                    <i class="fas fa-history"></i>
                    Lịch sử Export đầy đủ
                </h3>
                <div class="modal-note" style="margin-top: 8px; font-size: 0.85rem; color: #6c757d;">
                    <i class="fas fa-info-circle" style="color: #02d15e; margin-right: 6px;"></i>
                    Chỉ hiển thị 20 file export gần nhất
                </div>
            </div>
            <button class="modal-close" onclick="closeExportHistoryModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div id="exportHistoryContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<style>
.export-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.export-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border: 1px solid #e9ecef;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.export-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.export-icon {
    width: 60px;
    height: 60px;
    background: #e8f5e8;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    color: #02d15e;
    font-size: 1.5rem;
}

.export-content h3 {
    color: #2c3e50;
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 10px;
}

.export-content p {
    color: #6c757d;
    margin-bottom: 15px;
    line-height: 1.5;
}

.export-features {
    list-style: none;
    padding: 0;
    margin-bottom: 20px;
}

.export-features li {
    color: #6c757d;
    font-size: 0.9rem;
    padding: 4px 0;
    position: relative;
    padding-left: 20px;
}

.export-features li:before {
    content: "✓";
    position: absolute;
    left: 0;
    color: #02d15e;
    font-weight: bold;
}

.export-content {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.export-content .btn {
    margin-top: auto;
    width: 100%;
    padding: 12px 20px;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.export-history {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.no-history {
    text-align: center;
    padding: 40px 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 2px dashed #dee2e6;
}

.history-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.history-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.history-info i {
    color: #02d15e;
    font-size: 1.2rem;
}

.history-info strong {
    color: #2c3e50;
    display: block;
    margin-bottom: 4px;
}

.history-info small {
    color: #6c757d;
    font-size: 0.8rem;
}

.history-actions {
    display: flex;
    gap: 10px;
}

.history-actions .btn {
    cursor: pointer;
    transition: all 0.2s ease;
}

.history-actions .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Modal Styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    max-height: 80vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f8f9fa;
}

.modal-header h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.25rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.modal-header h3 i {
    color: #02d15e;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #02d15e;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.2s ease;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Specific styling for export history modal close button */
#exportHistoryModal .modal-header i {
    color: #02d15e !important;
}

.modal-title-section {
    display: flex;
    flex-direction: column;
    flex: 1;
}

.modal-close:hover {
    background: #e9ecef;
    color: #02d15e;
    border-radius: 50%;
}

.modal-body {
    padding: 20px;
    overflow-y: auto;
    flex: 1;
}

/* Pagination in Modal */
.modal-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 15px 0;
    border-top: 1px solid #e9ecef;
    background-color: #f8f9fa;
    margin: 0 -20px -20px -20px;
}

.modal-pagination-links {
    display: flex;
    gap: 8px;
    align-items: center;
}

.modal-pagination-links .btn {
    min-width: 40px;
    height: 40px;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease;
    border: 1px solid #dee2e6;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    user-select: none;
}

.modal-pagination-links .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    cursor: pointer;
}

.modal-pagination-links .btn-primary {
    background-color: #02d15e;
    border-color: #02d15e;
    color: white;
    cursor: pointer;
}

.modal-pagination-links .btn-primary:hover {
    background-color: #01b84d;
    border-color: #01b84d;
    cursor: pointer;
}

.modal-pagination-links .btn-secondary {
    background-color: white;
    border-color: #dee2e6;
    color: #6c757d;
    cursor: pointer;
}

.modal-pagination-links .btn-secondary:hover {
    background-color: #f8f9fa;
    border-color: #02d15e;
    color: #02d15e;
    cursor: pointer;
}

@media (max-width: 768px) {
    .export-options {
        grid-template-columns: 1fr;
    }

    .history-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }

    .history-actions {
        width: 100%;
        justify-content: flex-end;
    }
}
</style>

<script>
function exportCSV(type) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'export.php';

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'export_type';
    input.value = type;

    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);

    // Show loading message
    showAlert('Đang tạo file CSV...', 'info');
}

function downloadExport(filename) {
    // Create a link to download the file
    const link = document.createElement('a');
    link.href = 'download.php?file=' + encodeURIComponent(filename);
    link.download = filename;
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function openExportHistoryModal() {
    document.getElementById('exportHistoryModal').style.display = 'flex';
    loadExportHistory();
}

function closeExportHistoryModal() {
    document.getElementById('exportHistoryModal').style.display = 'none';
}

function loadExportHistory() {
    const content = document.getElementById('exportHistoryContent');
    content.innerHTML = '<div style="text-align: center; padding: 20px;"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>';

    fetch('export-history-ajax.php')
        .then(response => response.text())
        .then(data => {
            content.innerHTML = data;
        })
        .catch(error => {
            content.innerHTML = '<div style="text-align: center; padding: 20px; color: #dc3545;"><i class="fas fa-exclamation-triangle"></i> Lỗi khi tải dữ liệu</div>';
        });
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('exportHistoryModal');
    if (event.target === modal) {
        closeExportHistoryModal();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
