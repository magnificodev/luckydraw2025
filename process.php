<?php
// Lucky Draw Wheel App - Process Handler
session_start();
require_once 'config.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'check_phone';
    $phone = trim($_POST['phone'] ?? '');

    // Validate phone number format
    if (empty($phone)) {
        $_SESSION['error'] = 'Vui lòng nhập số điện thoại';
        header('Location: index.php?screen=1');
        exit();
    }

    if (!preg_match('/^0[0-9]{9,10}$/', $phone)) {
        $_SESSION['error'] = 'Số điện thoại không đúng định dạng';
        header('Location: index.php?screen=1');
        exit();
    }

    $_SESSION['current_phone'] = $phone;

    if ($action === 'check_phone') {
        // Check if phone number already exists
        try {
            $pdo = getDatabaseConnection();
            $stmt = $pdo->prepare("SELECT prize_name, prize_image FROM participants WHERE phone_number = ?");
            $stmt->execute([$phone]);
            $result = $stmt->fetch();

            if ($result) {
                // Phone already exists, show existing prize
                $_SESSION['current_prize'] = [
                    'name' => $result['prize_name'],
                    'image' => $result['prize_image']
                ];
                header('Location: index.php?screen=3');
            } else {
                // New phone number, select prize first and proceed to wheel
                // Define available prize catalog (12 prizes for 12 segments)
                // Order matches the actual wheel layout (clockwise from top)
                $prizes = [
                    ['name' => 'Tai nghe Bluetooth', 'image' => 'tai-nghe.png'],        // Index 0
                    ['name' => 'Bình thủy tinh', 'image' => 'binh-thuy-tinh.png'],      // Index 1
                    ['name' => 'Tag hành lý', 'image' => 'tag-hanh-ly.png'],           // Index 2
                    ['name' => 'Móc khóa', 'image' => 'moc-khoa.png'],                  // Index 3
                    ['name' => 'Túi tote', 'image' => 'tui-tote.png'],                  // Index 4
                    ['name' => 'Bình thủy tinh', 'image' => 'binh-thuy-tinh.png'],      // Index 5
                    ['name' => 'Móc khóa', 'image' => 'moc-khoa.png'],                  // Index 6
                    ['name' => 'Bịt mắt ngủ', 'image' => 'bit-mat-ngu.png'],            // Index 7
                    ['name' => 'Tag hành lý', 'image' => 'tag-hanh-ly.png'],           // Index 8
                    ['name' => 'Túi tote', 'image' => 'tui-tote.png'],                  // Index 9
                    ['name' => 'Ô gấp', 'image' => 'o-gap.png'],                        // Index 10
                    ['name' => 'Mũ bảo hiểm', 'image' => 'mu-bao-hiem.png']             // Index 11
                ];

                // Wheel has 12 segments; generate winning index in range [0, 11]
                $totalSegments = 12;
                $winningIndex = random_int(0, $totalSegments - 1);

                // Map winning index to an actual prize entry
                $selectedPrize = $prizes[$winningIndex];

                // Debug log
                error_log("=== BACKEND DEBUG ===");
                error_log("Winning Index: " . $winningIndex);
                error_log("Prize: " . $selectedPrize['name']);
                error_log("Total Segments: " . $totalSegments);
                error_log("Degrees per segment: " . (360 / $totalSegments));
                error_log("====================");

                // Store for frontend animation and later persistence
                $_SESSION['winning_index'] = $winningIndex;
                $_SESSION['total_segments'] = $totalSegments;
                $_SESSION['selected_prize'] = $selectedPrize;

                header('Location: index.php?screen=2');
            }
        } catch(PDOException $e) {
            $_SESSION['error'] = 'Lỗi cơ sở dữ liệu: ' . $e->getMessage();
            header('Location: index.php?screen=1');
        }

    } elseif ($action === 'spin') {
        // Use the prize that was already selected when entering screen 2
        if (!isset($_SESSION['selected_prize'])) {
            $_SESSION['error'] = 'Lỗi: Không tìm thấy thông tin phần quà';
            header('Location: index.php?screen=1');
            exit();
        }

        $selectedPrize = $_SESSION['selected_prize'];

        try {
            $pdo = getDatabaseConnection();
            // Double-check: Ensure phone number hasn't been used since entering screen 2
            $stmt = $pdo->prepare("SELECT id, prize_name, prize_image FROM participants WHERE phone_number = ?");
            $stmt->execute([$phone]);
            $existingParticipant = $stmt->fetch();

            if ($existingParticipant) {
                // Phone already exists, redirect to screen 3 with existing prize
                $_SESSION['current_prize'] = [
                    'name' => $existingParticipant['prize_name'],
                    'image' => $existingParticipant['prize_image']
                ];
                // Clear session data
                unset($_SESSION['selected_prize']);
                unset($_SESSION['winning_index']);
                header('Location: index.php?screen=3');
                exit();
            }

            // Insert new participant with prize
            $stmt = $pdo->prepare("INSERT INTO participants (phone_number, prize_name, prize_image) VALUES (?, ?, ?)");
            $stmt->execute([$phone, $selectedPrize['name'], $selectedPrize['image']]);

            // Set prize in session
            $_SESSION['current_prize'] = $selectedPrize;
            // Clear selected prize and winning index from session
            unset($_SESSION['selected_prize']);
            unset($_SESSION['winning_index']);
            header('Location: index.php?screen=3');

        } catch(PDOException $e) {
            $_SESSION['error'] = 'Lỗi cơ sở dữ liệu: ' . $e->getMessage();
            header('Location: index.php?screen=1');
        }
    }
} else {
    // Redirect to screen 1 if no POST data
    header('Location: index.php?screen=1');
}
exit();
?>
