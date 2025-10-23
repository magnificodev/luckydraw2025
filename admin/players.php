<?php
require_once 'includes/header.php';

// Get players with search and filter
try {
    $search = $_GET['search'] ?? '';
    $prizeFilter = $_GET['prize'] ?? 'all';
    $dateFrom = $_GET['date_from'] ?? '';
    $dateTo = $_GET['date_to'] ?? '';
    $page = (int)($_GET['page'] ?? 1);
    $limit = 5;
    $offset = ($page - 1) * $limit;

    $whereConditions = [];
    $params = [];

    if (!empty($search)) {
        $whereConditions[] = "p.phone_number LIKE ?";
        $params[] = "%$search%";
    }

    if ($prizeFilter !== 'all') {
        $whereConditions[] = "p.prize_id = ?";
        $params[] = $prizeFilter;
    }

    if (!empty($dateFrom)) {
        $whereConditions[] = "DATE(p.created_at) >= ?";
        $params[] = $dateFrom;
    }

    if (!empty($dateTo)) {
        $whereConditions[] = "DATE(p.created_at) <= ?";
        $params[] = $dateTo;
    }

    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

    // Get total count
    $countSql = "
        SELECT COUNT(*) as total
        FROM participants p
        JOIN prizes pr ON p.prize_id = pr.id
        $whereClause
    ";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalPlayers = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalPlayers / $limit);

    // Get players data
    $sql = "
        SELECT p.id, p.phone_number, pr.name as prize_name, p.created_at,
               p.ip_address, p.user_agent, p.winning_index
        FROM participants p
        JOIN prizes pr ON p.prize_id = pr.id
        $whereClause
        ORDER BY p.created_at DESC
        LIMIT $limit OFFSET $offset
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get prizes for filter dropdown
    $prizeStmt = $pdo->query("SELECT id, name FROM prizes ORDER BY name ASC");
    $prizes = $prizeStmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $error = "Lỗi khi tải dữ liệu: " . $e->getMessage();
    $players = [];
    $prizes = [];
    $totalPlayers = 0;
    $totalPages = 0;
}
?>

<div class="players-management">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-users"></i>
            Danh sách Người chơi
        </h2>
        <div class="card-actions">
            <span class="badge badge-info">Tổng: <?php echo number_format($totalPlayers); ?> người</span>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="search-filter">
        <form method="GET" class="filter-form">
            <div class="search-box">
                <input type="text" name="search" class="form-control search-input"
                       placeholder="Tìm kiếm số điện thoại..."
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>

            <div class="filter-group">
                <select name="prize" class="form-control">
                    <option value="all">Tất cả quà tặng</option>
                    <?php foreach ($prizes as $prize): ?>
                    <option value="<?php echo $prize['id']; ?>"
                            <?php echo $prizeFilter == $prize['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($prize['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <input type="date" name="date_from" class="form-control"
                       placeholder="Từ ngày" value="<?php echo htmlspecialchars($dateFrom); ?>">
            </div>

            <div class="filter-group">
                <input type="date" name="date_to" class="form-control"
                       placeholder="Đến ngày" value="<?php echo htmlspecialchars($dateTo); ?>">
            </div>

            <div class="filter-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                    Tìm kiếm
                </button>
                <a href="players.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Xóa bộ lọc
                </a>
            </div>
        </form>
    </div>

    <!-- Players Table -->
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Số điện thoại</th>
                    <th>Quà tặng</th>
                    <th>Thời gian quay</th>
                    <th>IP Address</th>
                    <th>Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($players)): ?>
                    <?php foreach ($players as $index => $player): ?>
                    <tr>
                        <td><?php echo $offset + $index + 1; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($player['phone_number']); ?></strong>
                        </td>
                        <td>
                            <span class="prize-name"><?php echo htmlspecialchars($player['prize_name']); ?></span>
                        </td>
                        <td>
                            <div class="datetime">
                                <div><?php echo date('d/m/Y', strtotime($player['created_at'])); ?></div>
                                <small style="color: #6c757d;"><?php echo date('H:i:s', strtotime($player['created_at'])); ?></small>
                            </div>
                        </td>
                        <td>
                            <code><?php echo htmlspecialchars($player['ip_address']); ?></code>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary"
                                    onclick="showPlayerDetails(<?php echo htmlspecialchars(json_encode($player)); ?>)">
                                <i class="fas fa-eye"></i>
                                Xem
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: #6c757d; padding: 40px;">
                            <i class="fas fa-users" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                            Không tìm thấy người chơi nào
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <div class="pagination-links">
            <!-- First page -->
            <?php if ($page > 1): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>"
                   class="btn btn-sm btn-secondary" title="Trang đầu">
                    <i class="fas fa-angle-double-left"></i>
                </a>
            <?php endif; ?>

            <!-- Previous page -->
            <?php if ($page > 1): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>"
                   class="btn btn-sm btn-secondary" title="Trang trước">
                    <i class="fas fa-chevron-left"></i>
                </a>
            <?php endif; ?>

            <!-- Page numbers -->
            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"
                   class="btn btn-sm <?php echo $i == $page ? 'btn-primary' : 'btn-secondary'; ?>"
                   title="Trang <?php echo $i; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <!-- Next page -->
            <?php if ($page < $totalPages): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>"
                   class="btn btn-sm btn-secondary" title="Trang sau">
                    <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>

            <!-- Last page -->
            <?php if ($page < $totalPages): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $totalPages])); ?>"
                   class="btn btn-sm btn-secondary" title="Trang cuối">
                    <i class="fas fa-angle-double-right"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Player Details Modal -->
<div id="playerModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <i class="fas fa-user"></i>
            <h3>Chi tiết Người chơi</h3>
            <button onclick="closePlayerModal()" style="background: none; border: none; font-size: 1.5rem; color: #6c757d; cursor: pointer; margin-left: auto;">&times;</button>
        </div>
        <div class="modal-body" id="playerDetails">
            <!-- Player details will be loaded here -->
        </div>
    </div>
</div>

<style>
.prize-name {
    color: #02d15e;
    font-weight: 600;
}

.datetime {
    line-height: 1.2;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 15px 0;
    border-top: 1px solid #e9ecef;
    background-color: #f8f9fa;
    margin-top: 0;
}

.pagination-links {
    display: flex;
    gap: 8px;
    align-items: center;
}

.pagination-links .btn {
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

.pagination-links .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    cursor: pointer;
}

.pagination-links .btn:active {
    transform: translateY(0);
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.pagination-links .btn-primary {
    background-color: #02d15e;
    border-color: #02d15e;
    color: white;
    cursor: pointer;
}

.pagination-links .btn-primary:hover {
    background-color: #01b84d;
    border-color: #01b84d;
    cursor: pointer;
}

.pagination-links .btn-secondary {
    background-color: white;
    border-color: #dee2e6;
    color: #6c757d;
    cursor: pointer;
}

.pagination-links .btn-secondary:hover {
    background-color: #f8f9fa;
    border-color: #02d15e;
    color: #02d15e;
    cursor: pointer;
}

.filter-form {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    align-items: center;
    width: 100%;
}

.search-filter {
    width: 100%;
}

.filter-form .search-box {
    flex: 1;
    min-width: 200px;
}

.filter-form .filter-group {
    display: flex;
    gap: 10px;
    align-items: center;
}

.filter-group .btn {
    height: 44px;
    padding: 0 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

/* Fix filter dropdown arrow spacing and remove default arrow */
select.form-control {
    -webkit-appearance: none !important; /* For Safari/Chrome */
    -moz-appearance: none !important;    /* For Firefox */
    appearance: none !important;         /* Standard */
    padding-right: 35px !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e") !important;
    background-position: right 12px center !important;
    background-repeat: no-repeat !important;
    background-size: 16px !important;
}

.card-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Fix filter dropdown arrow spacing and remove default arrow */
select.form-control {
    -webkit-appearance: none !important; /* For Safari/Chrome */
    -moz-appearance: none !important;    /* For Firefox */
    appearance: none !important;         /* Standard */
    padding-right: 35px !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e") !important;
    background-position: right 12px center !important;
    background-repeat: no-repeat !important;
    background-size: 16px !important;
}

@media (max-width: 768px) {
    .filter-form {
        flex-direction: column;
        align-items: stretch;
    }

    .filter-form .search-box {
        min-width: auto;
    }

    .pagination {
        padding: 12px 0;
    }

    .pagination-links {
        gap: 6px;
    }

    .pagination-links .btn {
        min-width: 36px;
        height: 36px;
        font-size: 0.9rem;
    }
}
</style>

<script>
function showPlayerDetails(player) {
    const modal = document.getElementById('playerModal');
    const details = document.getElementById('playerDetails');

    details.innerHTML = `
        <div class="player-details">
            <div class="detail-row">
                <label>Số điện thoại:</label>
                <span>${player.phone_number}</span>
            </div>
            <div class="detail-row">
                <label>Quà tặng:</label>
                <span class="prize-name">${player.prize_name}</span>
            </div>
            <div class="detail-row">
                <label>Thời gian quay:</label>
                <span>${new Date(player.created_at).toLocaleString('vi-VN')}</span>
            </div>
            <div class="detail-row">
                <label>IP Address:</label>
                <code>${player.ip_address}</code>
            </div>
            <div class="detail-row">
                <label>Winning Index:</label>
                <span>${player.winning_index}</span>
            </div>
            <div class="detail-row">
                <label>User Agent:</label>
                <small style="word-break: break-all; color: #6c757d;">${player.user_agent || 'N/A'}</small>
            </div>
        </div>
    `;

    modal.classList.add('show');
}

function closePlayerModal() {
    const modal = document.getElementById('playerModal');
    modal.classList.remove('show');
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    const modal = document.getElementById('playerModal');
    if (e.target === modal) {
        closePlayerModal();
    }
});
</script>

<style>
.player-details {
    display: grid;
    gap: 15px;
}

.detail-row {
    display: grid;
    grid-template-columns: 150px 1fr;
    gap: 10px;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #f8f9fa;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-row label {
    font-weight: 600;
    color: #2c3e50;
}

.detail-row span {
    color: #6c757d;
}

@media (max-width: 480px) {
    .detail-row {
        grid-template-columns: 1fr;
        gap: 5px;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
