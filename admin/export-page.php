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
                <div class="history-item">
                    <div class="history-info">
                        <i class="fas fa-file-csv"></i>
                        <div>
                            <strong>nguoi_choi_2025-01-15_14-30-25.csv</strong>
                            <small>Danh sách người chơi - 15/01/2025 14:30</small>
                        </div>
                    </div>
                    <div class="history-actions">
                        <button class="btn btn-sm btn-secondary">
                            <i class="fas fa-download"></i>
                            Tải lại
                        </button>
                    </div>
                </div>

                <div class="history-item">
                    <div class="history-info">
                        <i class="fas fa-file-csv"></i>
                        <div>
                            <strong>qua_tang_2025-01-15_14-25-10.csv</strong>
                            <small>Thống kê quà tặng - 15/01/2025 14:25</small>
                        </div>
                    </div>
                    <div class="history-actions">
                        <button class="btn btn-sm btn-secondary">
                            <i class="fas fa-download"></i>
                            Tải lại
                        </button>
                    </div>
                </div>
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

.export-history {
    display: flex;
    flex-direction: column;
    gap: 15px;
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
</script>

<?php require_once 'includes/footer.php'; ?>
