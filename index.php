<?php
// Lucky Draw Wheel App - Main Entry Point
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize session variables if not set
if (!isset($_SESSION['current_phone'])) {
    $_SESSION['current_phone'] = '';
}
if (!isset($_SESSION['current_prize'])) {
    $_SESSION['current_prize'] = null;
}
if (!isset($_SESSION['is_spinning'])) {
    $_SESSION['is_spinning'] = false;
}

// Get current screen from URL parameter or default to 1
$screen = isset($_GET['screen']) ? (int)$_GET['screen'] : 1;
$screen = in_array($screen, [1, 2, 3]) ? $screen : 1;

// Security check: Only allow access to screen 2 and 3 if phone number is validated
if (($screen == 2 || $screen == 3) && empty($_SESSION['current_phone'])) {
    // No valid phone number, redirect to screen 1
    header('Location: index.php?screen=1');
    exit();
}

// Additional check for screen 2: Must have winning index and selected prize
if ($screen == 2 && (!isset($_SESSION['winning_index']) || !isset($_SESSION['selected_prize']))) {
    // No winning index or selected prize, redirect to screen 1
    header('Location: index.php?screen=1');
    exit();
}

// Real-time check for screen 2: Verify selected prize is still available
if ($screen == 2 && isset($_SESSION['selected_prize'])) {
    try {
        require_once 'config.php';
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare("SELECT stock, is_active FROM prizes WHERE id = ?");
        $stmt->execute([$_SESSION['selected_prize']['id']]);
        $prizeData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$prizeData || $prizeData['stock'] <= 0 || !$prizeData['is_active']) {
            // Prize is no longer available, clear session and redirect
            unset($_SESSION['selected_prize']);
            unset($_SESSION['winning_index']);
            $_SESSION['alert_message'] = 'Xin lỗi, phần quà này đã hết hàng hoặc bị vô hiệu hóa. Vui lòng thử lại!';
            $_SESSION['alert_type'] = 'error';
            header('Location: index.php?screen=1');
            exit();
        }
    } catch(PDOException $e) {
        // Database error, redirect to screen 1
        unset($_SESSION['selected_prize']);
        unset($_SESSION['winning_index']);
        $_SESSION['alert_message'] = 'Lỗi hệ thống. Vui lòng thử lại!';
        $_SESSION['alert_type'] = 'error';
        header('Location: index.php?screen=1');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Vòng Quay May Mắn - VPBank Solution Day</title>
    <link rel="stylesheet" href="assets/css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php if (isset($_SESSION['alert_message'])): ?>
        <script>
            window.alertMessage = {
                message: '<?php echo addslashes($_SESSION['alert_message']); ?>',
                type: '<?php echo $_SESSION['alert_type'] ?? 'info'; ?>'
            };
        </script>
        <?php
        unset($_SESSION['alert_message']);
        unset($_SESSION['alert_type']);
        ?>
    <?php endif; ?>
    <?php if ($screen == 1): ?>
        <!-- Screen 1: Phone Number Input -->
        <div id="screen1" class="screen active">
            <form id="phoneForm" method="POST" action="process.php">
                <div class="form-group">
                    <div class="input-wrapper">
                        <input
                            type="tel"
                            id="phoneInput"
                            name="phone"
                            maxlength="11"
                            required
                        />
                    </div>
                    <div id="phoneError" class="error-message">
                        <!-- Error messages now handled by alert popup -->
                    </div>
                </div>

                <button type="submit" id="startButton" class="start-button">
                    <img src="assets/images/start-button.png" alt="Bắt đầu vòng quay" />
                </button>
            </form>
        </div>

    <?php elseif ($screen == 2): ?>
        <!-- Screen 2: Spin Wheel -->
        <div id="screen2" class="screen active">
            <!-- Wheel Section -->
            <div class="wheel-container">
                <div class="wheel-wrapper">
                    <img
                        id="wheelPointer"
                        src="assets/images/wheel-pointer.png"
                        alt="Pointer"
                        class="wheel-pointer"
                    />
                    <img class="wheel" src="assets/images/wheel.png" alt="Wheel" />
                </div>

                <div class="spin-button-section">
                    <form id="spinForm" method="POST" action="process.php">
                        <input type="hidden" name="action" value="spin">
                        <input type="hidden" name="phone" value="<?php echo htmlspecialchars($_SESSION['current_phone']); ?>">
                        <button type="submit" id="spinButton" class="spin-button">
                            <img src="assets/images/spin-button.png" alt="Quay" />
                        </button>
                    </form>
                </div>
            </div>
        </div>

    <?php elseif ($screen == 3): ?>
        <!-- Screen 3: Prize Display -->
        <div id="screen3" class="screen active">
            <div class="prize-section">
                <div class="prize-text-container">
                    <h2 id="prizeName" class="prize-name">
                        <?php echo htmlspecialchars($_SESSION['current_prize']['name'] ?? ''); ?>
                    </h2>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-spinner"></div>
        <p>Đang xử lý...</p>
    </div>

    <script>
        // Pass winning index and total segments from PHP to JavaScript
        <?php if (isset($_SESSION['winning_index'])): ?>
        window.winningIndex = <?php echo (int)$_SESSION['winning_index']; ?>;
        window.totalSegments = <?php echo isset($_SESSION['total_segments']) ? (int)$_SESSION['total_segments'] : 12; ?>;
        <?php endif; ?>
    </script>
    <script src="assets/js/main.js"></script>
</body>
</html>
