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
            $stmt = $pdo->prepare("SELECT pr.name as prize_name, p.winning_index FROM participants p JOIN prizes pr ON p.prize_id = pr.id WHERE p.phone_number = ?");
            $stmt->execute([$phone]);
            $result = $stmt->fetch();

            if ($result) {
                // Phone already exists, show existing prize
                $_SESSION['current_prize'] = [
                    'name' => $result['prize_name']
                ];
                header('Location: index.php?screen=3');
            } else {
                // New phone number, select prize first and proceed to wheel
                // Fetch prizes from database with stock check
                $stmt = $pdo->prepare("SELECT id, name, display_order FROM prizes WHERE stock > 0 AND is_active = TRUE ORDER BY display_order ASC");
                $stmt->execute();
                $availablePrizes = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (empty($availablePrizes)) {
                    $_SESSION['error'] = 'Xin lỗi, hiện tại không còn quà tặng nào. Vui lòng thử lại sau!';
                    header('Location: index.php?screen=1');
                    exit();
                }

                $totalSegments = count($availablePrizes);
                $winningIndex = random_int(0, $totalSegments - 1);

                // Get the selected prize
                $selectedPrize = $availablePrizes[$winningIndex];

                // Debug log
                error_log("=== BACKEND DEBUG ===");
                error_log("Winning Index: " . $winningIndex);
                error_log("Prize: " . $selectedPrize['name']);
                error_log("Prize ID: " . $selectedPrize['id']);
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
            $stmt = $pdo->prepare("SELECT p.id, pr.name as prize_name, p.winning_index FROM participants p JOIN prizes pr ON p.prize_id = pr.id WHERE p.phone_number = ?");
            $stmt->execute([$phone]);
            $existingParticipant = $stmt->fetch();

            if ($existingParticipant) {
                // Phone already exists, redirect to screen 3 with existing prize
                $_SESSION['current_prize'] = [
                    'name' => $existingParticipant['prize_name']
                ];
                // Clear session data
                unset($_SESSION['selected_prize']);
                unset($_SESSION['winning_index']);
                header('Location: index.php?screen=3');
                exit();
            }

            // Insert new participant with prize and tracking info
            $stmt = $pdo->prepare("INSERT INTO participants (phone_number, prize_id, winning_index, ip_address, user_agent, session_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $phone,
                $selectedPrize['id'],
                $_SESSION['winning_index'],
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                session_id()
            ]);

            // Decrease stock
            $stmt = $pdo->prepare("UPDATE prizes SET stock = stock - 1 WHERE id = ? AND stock > 0");
            $stmt->execute([$selectedPrize['id']]);

            // Set prize in session
            $_SESSION['current_prize'] = ['name' => $selectedPrize['name']];
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
