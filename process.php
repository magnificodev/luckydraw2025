<?php
// Lucky Draw Wheel App - Process Handler
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'check_phone';
    $phone = trim($_POST['phone'] ?? '');

    // Validate phone number format
    if (empty($phone)) {
        $_SESSION['alert_message'] = 'Vui lòng nhập số điện thoại';
        $_SESSION['alert_type'] = 'error';
        header('Location: index.php?screen=1');
        exit();
    }

    if (!preg_match('/^0[0-9]{9,10}$/', $phone)) {
        $_SESSION['alert_message'] = 'Số điện thoại không đúng định dạng';
        $_SESSION['alert_type'] = 'error';
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
                // New phone number, select prize using virtual segments
                // Get all available products (with stock > 0)
                $stmt = $pdo->prepare("SELECT DISTINCT p.id, p.name FROM prizes p WHERE p.stock > 0 AND p.is_active = TRUE");
                $stmt->execute();
                $availableProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (empty($availableProducts)) {
                    $_SESSION['alert_message'] = 'Xin lỗi, hiện tại không còn quà tặng nào. Vui lòng thử lại sau!';
                    $_SESSION['alert_type'] = 'error';
                    header('Location: index.php?screen=1');
                    exit();
                }

                // Get all wheel segments that map to available products
                $stmt = $pdo->prepare("
                    SELECT ws.segment_index, ws.product_id, p.name, p.stock 
                    FROM wheel_segments ws 
                    JOIN prizes p ON ws.product_id = p.id 
                    WHERE p.stock > 0 AND p.is_active = TRUE 
                    ORDER BY ws.segment_index
                ");
                $stmt->execute();
                $availableSegments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (empty($availableSegments)) {
                    $_SESSION['alert_message'] = 'Xin lỗi, hiện tại không còn quà tặng nào. Vui lòng thử lại sau!';
                    $_SESSION['alert_type'] = 'error';
                    header('Location: index.php?screen=1');
                    exit();
                }

                // Random select from available segments (0-11)
                $totalSegments = 12;
                $winningIndex = random_int(0, 11);
                
                // Find the product for this segment
                $selectedProduct = null;
                foreach ($availableSegments as $segment) {
                    if ($segment['segment_index'] == $winningIndex) {
                        $selectedProduct = $segment;
                        break;
                    }
                }

                // If this segment's product is out of stock, find another available segment
                if (!$selectedProduct || $selectedProduct['stock'] <= 0) {
                    $availableSegmentIndices = array_column($availableSegments, 'segment_index');
                    $winningIndex = $availableSegmentIndices[array_rand($availableSegmentIndices)];
                    
                    foreach ($availableSegments as $segment) {
                        if ($segment['segment_index'] == $winningIndex) {
                            $selectedProduct = $segment;
                            break;
                        }
                    }
                }

                // Debug log
                error_log("=== BACKEND DEBUG (Virtual Segments) ===");
                error_log("Winning Index: " . $winningIndex);
                error_log("Product: " . $selectedProduct['name']);
                error_log("Product ID: " . $selectedProduct['product_id']);
                error_log("Total Segments: " . $totalSegments);
                error_log("Degrees per segment: " . (360 / $totalSegments));
                error_log("Available segments: " . count($availableSegments));
                error_log("=====================================");

                // Store for frontend animation and later persistence
                $_SESSION['winning_index'] = $winningIndex;
                $_SESSION['total_segments'] = $totalSegments;
                $_SESSION['selected_prize'] = [
                    'id' => $selectedProduct['product_id'],
                    'name' => $selectedProduct['name']
                ];

                header('Location: index.php?screen=2');
            }
        } catch(PDOException $e) {
            $_SESSION['alert_message'] = 'Lỗi cơ sở dữ liệu: ' . $e->getMessage();
            $_SESSION['alert_type'] = 'error';
            header('Location: index.php?screen=1');
        }

    } elseif ($action === 'spin') {
        // Use the prize that was already selected when entering screen 2
        if (!isset($_SESSION['selected_prize'])) {
            $_SESSION['alert_message'] = 'Lỗi: Không tìm thấy thông tin phần quà';
            $_SESSION['alert_type'] = 'error';
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
            $_SESSION['alert_message'] = 'Lỗi cơ sở dữ liệu: ' . $e->getMessage();
            $_SESSION['alert_type'] = 'error';
            header('Location: index.php?screen=1');
        }
    }
} else {
    // Redirect to screen 1 if no POST data
    header('Location: index.php?screen=1');
}
exit();
?>
