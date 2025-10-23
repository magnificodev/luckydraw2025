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

        // Debug logging
        error_log("Stock update request - Prize ID: $prizeId, Stock: $stock, Active: " . ($isActive ? 'true' : 'false'));

        try {
            $stmt = $pdo->prepare("UPDATE prizes SET stock = ?, is_active = ? WHERE id = ?");
            $result = $stmt->execute([$stock, $isActive, $prizeId]);

            if ($result) {
                error_log("Stock update successful for prize ID: $prizeId");
                echo json_encode(['success' => true, 'message' => 'Cập nhật thành công']);
            } else {
                error_log("Stock update failed for prize ID: $prizeId");
                echo json_encode(['success' => false, 'message' => 'Không thể cập nhật dữ liệu']);
            }
        } catch(PDOException $e) {
            error_log("Stock update error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi database: ' . $e->getMessage()]);
        }
        exit;
    }
}

// Include header for regular page load (not AJAX)
require_once 'includes/header.php';

// Get prizes with statistics
try {
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? 'all';

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

    // Only show unique products (not duplicates with (2) suffix)
    $sql = "
        SELECT pr.id, pr.name, pr.display_order, pr.stock, pr.is_active,
               COALESCE(ps.count, 0) as distributed_count
        FROM prizes pr
        LEFT JOIN prize_statistics ps ON pr.id = ps.prize_id
        $whereClause
        ORDER BY pr.display_order ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $prizes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $error = "Lỗi khi tải dữ liệu: " . $e->getMessage();
    $prizes = [];
}
?>

<div class="stock-management">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-boxes"></i>
            Quản lý Stock
        </h2>
    </div>

    <!-- Search and Filter -->
    <div class="search-filter">
        <div class="search-box">
            <input type="text" class="form-control search-input" placeholder="Tìm kiếm quà tặng..."
                   value="<?php echo htmlspecialchars($search); ?>"
                   onkeyup="if(event.key==='Enter') this.form.submit()">
        </div>
        <div class="filter-group">
            <select name="status" class="form-control" onchange="this.form.submit()">
                <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>Tất cả</option>
                <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Tạm dừng</option>
            </select>
            <button type="button" class="btn btn-primary refresh-btn" onclick="refreshPage()" title="Làm mới">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
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

    <!-- Summary -->
    <div class="row" style="margin-top: 20px;">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body" style="text-align: center;">
                    <h4 style="color: #02d15e; margin-bottom: 5px;">
                        <?php echo number_format(array_sum(array_column($prizes, 'stock'))); ?>
                    </h4>
                    <p style="color: #6c757d; margin: 0;">Tổng stock</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body" style="text-align: center;">
                    <h4 style="color: #17a2b8; margin-bottom: 5px;">
                        <?php echo count(array_filter($prizes, function($p) { return $p['is_active']; })); ?>
                    </h4>
                    <p style="color: #6c757d; margin: 0;">Đang hoạt động</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body" style="text-align: center;">
                    <h4 style="color: #dc3545; margin-bottom: 5px;">
                        <?php echo count(array_filter($prizes, function($p) { return !$p['is_active']; })); ?>
                    </h4>
                    <p style="color: #6c757d; margin: 0;">Tạm dừng</p>
                </div>
            </div>
        </div>
    </div>
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

/* Refresh button styling to match form controls height */
button.refresh-btn.btn.btn-primary {
    height: 44px !important;
    width: 44px !important;
    padding: 0 !important;
    gap: 0 !important;
    min-width: 44px !important;
    max-width: 44px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 8px !important;
    border: 2px solid #e9ecef !important;
    background-color: #02d15e !important;
    color: white !important;
    transition: all 0.3s ease !important;
    flex-shrink: 0 !important;
}

button.refresh-btn.btn.btn-primary:hover {
    background-color: #01b84d !important;
    border-color: #02d15e !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 0 0 3px rgba(2, 209, 94, 0.1) !important;
}

button.refresh-btn.btn.btn-primary:active {
    transform: translateY(0) !important;
    box-shadow: 0 1px 2px rgba(2, 209, 94, 0.3) !important;
}

button.refresh-btn.btn.btn-primary i {
    font-size: 16px !important;
    margin: 0 !important;
    padding: 0 !important;
}
</style>

<script>
function refreshPage() {
    window.location.href = 'manage-stock.php';
}

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
