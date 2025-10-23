<?php
// Lucky Draw Wheel App - Main Entry Point
session_start();

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
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Vòng Quay May Mắn - VPBank Solution Day</title>
    <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>
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
                    <div id="phoneError" class="error-message<?php echo isset($_SESSION['error']) ? ' show' : ''; ?>">
                    <?php
                        if (isset($_SESSION['error'])) {
                            echo htmlspecialchars($_SESSION['error']);
                            unset($_SESSION['error']);
                        }
                    ?>
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
                    <div id="wheel" class="wheel">
                        <img src="assets/images/wheel.png" alt="Wheel" />
                    </div>
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
                <div class="prize-image-container">
                    <img
                        id="prizeImage"
                        src="assets/images/gifts/<?php echo htmlspecialchars($_SESSION['current_prize']['image'] ?? ''); ?>"
                        alt="Phần quà"
                        class="prize-image"
                    />
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-spinner"></div>
        <p>Đang xử lý...</p>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
