<?php
// Handle AJAX requests FIRST before any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Start session and require auth for AJAX
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require_once '../config/admin-config.php';
    require_once 'includes/auth.php';
    requireAuth();

    header('Content-Type: application/json');

    if ($_POST['action'] === 'update_stock') {
        $prizeId = (int)$_POST['prize_id'];
        $stock = (int)$_POST['stock'];
        $isActive = (bool)$_POST['is_active'];

        try {
            $pdo->beginTransaction();

            // Use FOR UPDATE to prevent race condition when multiple admins update simultaneously
            $stmt = $pdo->prepare("SELECT id FROM prizes WHERE id = ? FOR UPDATE");
            $stmt->execute([$prizeId]);
            $prizeExists = $stmt->fetch();

            if (!$prizeExists) {
                $pdo->rollback();
                echo json_encode(['success' => false, 'message' => 'Phần quà không tồn tại']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE prizes SET stock = ?, is_active = ? WHERE id = ?");
            $result = $stmt->execute([$stock, $isActive, $prizeId]);

            if ($result) {
                $pdo->commit();
                echo json_encode(['success' => true, 'message' => 'Cập nhật thành công']);
            } else {
                $pdo->rollback();
                echo json_encode(['success' => false, 'message' => 'Không thể cập nhật dữ liệu']);
            }
        } catch(PDOException $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollback();
            }
            echo json_encode(['success' => false, 'message' => 'Lỗi database: ' . $e->getMessage()]);
        }
        exit;
    }
}

// Include header for regular page load (not AJAX)
require_once 'includes/header.php';

// Get prizes with statistics and pagination
try {
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? 'all';
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 5;
    $offset = ($page - 1) * $limit;

    $whereConditions = ["pr.name NOT LIKE '%(2)' AND pr.name NOT LIKE '%(3)' AND pr.name NOT LIKE '%(4)'"];
    $params = [];

    if (!empty($search)) {
        $whereConditions[] = "pr.name LIKE ?";
        $params[] = "%$search%";
    }

    if ($status === 'active') {
        $whereConditions[] = "pr.is_active = 1";
    } elseif ($status === 'inactive') {
        $whereConditions[] = "pr.is_active = 0";
    }

    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

    // Count total records for pagination
    $countSql = "
        SELECT COUNT(*) as total
        FROM prizes pr
        LEFT JOIN prize_statistics ps ON pr.id = ps.prize_id
        $whereClause
    ";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalRecords / $limit);

    // Only show unique products (not duplicates with (2) suffix) with pagination
    $sql = "
        SELECT pr.id, pr.name, pr.display_order, pr.stock, pr.is_active,
               COALESCE(ps.count, 0) as distributed_count
        FROM prizes pr
        LEFT JOIN prize_statistics ps ON pr.id = ps.prize_id
        $whereClause
        ORDER BY pr.display_order ASC
        LIMIT $limit OFFSET $offset
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $prizes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate statistics from ALL data (not just current page)
    $statsSql = "
        SELECT
            SUM(pr.stock) as total_stock,
            SUM(CASE WHEN pr.is_active = 1 THEN 1 ELSE 0 END) as active_count,
            SUM(CASE WHEN pr.is_active = 0 THEN 1 ELSE 0 END) as inactive_count
        FROM prizes pr
        WHERE pr.name NOT LIKE '%(2)' AND pr.name NOT LIKE '%(3)' AND pr.name NOT LIKE '%(4)'
    ";
    $statsStmt = $pdo->prepare($statsSql);
    $statsStmt->execute();
    $statistics = $statsStmt->fetch(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $error = "Lỗi khi tải dữ liệu: " . $e->getMessage();
    $prizes = [];
    $totalRecords = 0;
    $totalPages = 0;
    $statistics = [
        'total_stock' => 0,
        'active_count' => 0,
        'inactive_count' => 0
    ];
}
?>

<div class="stock-management">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-boxes"></i>
            Quản lý Stock
        </h2>
    </div>

    <!-- Summary Statistics -->
    <div class="row" style="margin-bottom: 20px;">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body" style="text-align: center;">
                    <h4 style="color: #02d15e; margin-bottom: 5px;">
                        <?php echo number_format($statistics['total_stock'] ?? 0); ?>
                    </h4>
                    <p style="color: #6c757d; margin: 0;">Tổng stock</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body" style="text-align: center;">
                    <h4 style="color: #17a2b8; margin-bottom: 5px;">
                        <?php echo $statistics['active_count'] ?? 0; ?>
                    </h4>
                    <p style="color: #6c757d; margin: 0;">Đang hoạt động</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body" style="text-align: center;">
                    <h4 style="color: #dc3545; margin-bottom: 5px;">
                        <?php echo $statistics['inactive_count'] ?? 0; ?>
                    </h4>
                    <p style="color: #6c757d; margin: 0;">Tạm dừng</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="search-filter">
        <form method="GET" class="filter-form">
            <div class="search-box">
                <input type="text" name="search" class="form-control search-input" placeholder="Tìm kiếm quà tặng..."
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>

            <div class="filter-group">
                <select name="status" class="form-control">
                    <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>Tất cả</option>
                    <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                    <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Tạm dừng</option>
                </select>
            </div>

            <div class="filter-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                    Tìm kiếm
                </button>
                <a href="manage-stock.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Xóa bộ lọc
                </a>
            </div>
        </form>
    </div>

    <!-- Prizes Table -->
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Tên quà tặng</th>
                    <th>Stock hiện tại</th>
                    <th>Đã phát</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($prizes)): ?>
                    <?php foreach ($prizes as $index => $prize): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($prize['name']); ?></strong>
                        </td>
                        <td>
                            <input type="number"
                                   class="form-control stock-input"
                                   value="<?php echo $prize['stock']; ?>"
                                   data-prize-id="<?php echo $prize['id']; ?>"
                                   min="0"
                                   style="width: 80px; display: inline-block;">
                        </td>
                        <td>
                            <span class="badge badge-info"><?php echo number_format($prize['distributed_count']); ?></span>
                        </td>
                        <td>
                            <label class="switch">
                                <input type="checkbox"
                                       class="active-toggle"
                                       data-prize-id="<?php echo $prize['id']; ?>"
                                       <?php echo $prize['is_active'] ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary save-stock-btn"
                                    data-prize-id="<?php echo $prize['id']; ?>">
                                <i class="fas fa-save"></i>
                                Lưu
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: #6c757d; padding: 40px;">
                            <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                            Không tìm thấy quà tặng nào
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
            <?php else: ?>
                <span class="btn btn-sm btn-disabled" title="Trang đầu">
                    <i class="fas fa-angle-double-left"></i>
                </span>
            <?php endif; ?>

            <!-- Previous page -->
            <?php if ($page > 1): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>"
                   class="btn btn-sm btn-secondary" title="Trang trước">
                    <i class="fas fa-chevron-left"></i>
                </a>
            <?php else: ?>
                <span class="btn btn-sm btn-disabled" title="Trang trước">
                    <i class="fas fa-chevron-left"></i>
                </span>
            <?php endif; ?>

            <!-- Page numbers -->
                           <?php
                           $num_links = 3; // Display maximum 3 page links
                           $start_page = $page - floor($num_links / 2);
                           $end_page = $page + floor($num_links / 2);

                           if ($start_page < 1) {
                               $start_page = 1;
                               $end_page = min($totalPages, $num_links);
                           }

                           if ($end_page > $totalPages) {
                               $end_page = $totalPages;
                               $start_page = max(1, $totalPages - $num_links + 1);
                           }

                           for ($i = $start_page; $i <= $end_page; $i++): ?>
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
            <?php else: ?>
                <span class="btn btn-sm btn-disabled" title="Trang sau">
                    <i class="fas fa-chevron-right"></i>
                </span>
            <?php endif; ?>

            <!-- Last page -->
            <?php if ($page < $totalPages): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $totalPages])); ?>"
                   class="btn btn-sm btn-secondary" title="Trang cuối">
                    <i class="fas fa-angle-double-right"></i>
                </a>
            <?php else: ?>
                <span class="btn btn-sm btn-disabled" title="Trang cuối">
                    <i class="fas fa-angle-double-right"></i>
                </span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 24px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: #02d15e;
}

input:checked + .slider:before {
    transform: translateX(26px);
}

.row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

@media (max-width: 768px) {
    .row {
        grid-template-columns: 1fr;
    }
}


/* Search and Filter Form */
.search-filter {
    width: 100%;
}

.filter-form {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    align-items: center;
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

@media (max-width: 768px) {
    .filter-form {
        flex-direction: column;
        align-items: stretch;
    }

    .filter-form .search-box {
        min-width: auto;
    }
}

/* Pagination Styles */
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

.pagination-links .btn-disabled {
    background-color: #f8f9fa;
    border-color: #e9ecef;
    color: #adb5bd;
    cursor: not-allowed;
    opacity: 0.6;
}

.pagination-links .btn-disabled:hover {
    background-color: #f8f9fa;
    border-color: #e9ecef;
    color: #adb5bd;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

@media (max-width: 768px) {
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

/* Make distributed count badge larger and more visible */
.badge.badge-info {
    font-size: 0.9rem !important;
    font-weight: 700 !important;
    padding: 8px 12px !important;
    border-radius: 6px !important;
    background-color: #17a2b8 !important;
    color: white !important;
    min-width: 40px !important;
    text-align: center !important;
    display: inline-block !important;
}
</style>

<script>
function updateStock(prizeId) {
    const row = document.querySelector(`input[data-prize-id="${prizeId}"]`).closest('tr');
    const stock = row.querySelector('.stock-input').value;
    const isActive = row.querySelector('.active-toggle').checked;

    const formData = new FormData();
    formData.append('action', 'update_stock');
    formData.append('prize_id', prizeId);
    formData.append('stock', stock);
    formData.append('is_active', isActive ? '1' : '0');

    fetch('manage-stock.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Cập nhật thành công!', 'success');
        } else {
            showAlert('Có lỗi xảy ra: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showAlert('Có lỗi xảy ra khi cập nhật', 'error');
        console.error('Error:', error);
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
