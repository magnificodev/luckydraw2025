<?php
require_once 'includes/header.php';

// Get statistics
try {
    // Total players
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM participants");
    $totalPlayers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Prizes distributed
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM participants");
    $prizesDistributed = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Remaining stock
    $stmt = $pdo->query("SELECT SUM(stock) as total FROM prizes WHERE is_active = 1");
    $remainingStock = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // Today's spins
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM participants WHERE DATE(created_at) = CURDATE()");
    $todaySpins = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Recent players (last 8)
    $stmt = $pdo->query("
        SELECT p.phone_number, pr.name as prize_name, p.created_at
        FROM participants p
        JOIN prizes pr ON p.prize_id = pr.id
        ORDER BY p.created_at DESC
        LIMIT 8
    ");
    $recentPlayers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prize statistics
    $stmt = $pdo->query("
        SELECT pr.name, ps.count, pr.stock, pr.is_active
        FROM prizes pr
        LEFT JOIN prize_statistics ps ON pr.id = ps.prize_id
        ORDER BY ps.count DESC, pr.name ASC
    ");
    $prizeStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $error = "Lỗi khi tải dữ liệu: " . $e->getMessage();
}
?>

<div class="dashboard">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-tachometer-alt"></i>
            Dashboard
        </h2>
    </div>

    <!-- Statistics Cards -->
    <div class="dashboard-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-value"><?php echo number_format($totalPlayers); ?></div>
            <div class="stat-label">Tổng người chơi</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-gift"></i>
            </div>
            <div class="stat-value"><?php echo number_format($prizesDistributed); ?></div>
            <div class="stat-label">Quà đã phát</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="stat-value"><?php echo number_format($remainingStock); ?></div>
            <div class="stat-label">Quà còn lại</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-sync-alt"></i>
            </div>
            <div class="stat-value"><?php echo number_format($todaySpins); ?></div>
            <div class="stat-label">Quay hôm nay</div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Players -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clock"></i>
                        Người chơi gần đây
                    </h3>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Số điện thoại</th>
                                <th>Quà tặng</th>
                                <th>Thời gian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentPlayers)): ?>
                                <?php foreach ($recentPlayers as $player): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($player['phone_number']); ?></td>
                                    <td><?php echo htmlspecialchars($player['prize_name']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($player['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" style="text-align: center; color: #6c757d;">
                                        Chưa có người chơi nào
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Prize Statistics -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i>
                        Thống kê quà tặng
                    </h3>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tên quà</th>
                                <th>Đã phát</th>
                                <th>Còn lại</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($prizeStats)): ?>
                                <?php foreach ($prizeStats as $prize): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($prize['name']); ?></td>
                                    <td><?php echo number_format($prize['count'] ?? 0); ?></td>
                                    <td><?php echo number_format($prize['stock']); ?></td>
                                    <td>
                                        <?php if ($prize['is_active']): ?>
                                            <span class="badge badge-success status-badge">Hoạt động</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger status-badge">Tạm dừng</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; color: #6c757d;">
                                        Chưa có dữ liệu
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-top: 20px;
}

.col-md-6 {
    min-width: 0;
}

@media (max-width: 768px) {
    .row {
        grid-template-columns: 1fr;
    }
}

.status-badge {
    min-width: 100px !important;
    width: 100px !important;
    text-align: center !important;
    display: inline-block !important;
    padding: 4px 8px !important;
    font-size: 0.75rem !important;
    font-weight: 600 !important;
    border-radius: 3px !important;
    height: auto !important;
    line-height: 1.2 !important;
    box-sizing: border-box !important;
    white-space: nowrap !important;
}
</style>

<?php require_once 'includes/footer.php'; ?>
