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
                // New phone number, proceed to wheel
                header('Location: index.php?screen=2');
            }
        } catch(PDOException $e) {
            $_SESSION['error'] = 'Lỗi cơ sở dữ liệu: ' . $e->getMessage();
            header('Location: index.php?screen=1');
        }

    } elseif ($action === 'spin') {
        // Spin the wheel
        $prizes = [
            ['name' => 'Bình thủy tinh', 'image' => 'binh-thuy-tinh.png'],
            ['name' => 'Bịt mắt ngủ', 'image' => 'bit-mat-ngu.png'],
            ['name' => 'Móc khóa', 'image' => 'moc-khoa.png'],
            ['name' => 'Mũ bảo hiểm', 'image' => 'mu-bao-hiem.png'],
            ['name' => 'Ô gấp', 'image' => 'o-gap.png'],
            ['name' => 'Tag hành lý', 'image' => 'tag-hanh-ly.png'],
            ['name' => 'Tai nghe Bluetooth', 'image' => 'tai-nghe.png'],
            ['name' => 'Túi tote', 'image' => 'tui-tote.png']
        ];

        // Randomly select a prize
        $selectedPrize = $prizes[array_rand($prizes)];

        try {
            $pdo = getDatabaseConnection();
            // Check if phone number already exists (double-check)
            $stmt = $pdo->prepare("SELECT id FROM participants WHERE phone_number = ?");
            $stmt->execute([$phone]);

            if ($stmt->fetch()) {
                $_SESSION['error'] = 'Số điện thoại này đã tham gia rồi';
                header('Location: index.php?screen=1');
                exit();
            }

            // Insert new participant with prize
            $stmt = $pdo->prepare("INSERT INTO participants (phone_number, prize_name, prize_image) VALUES (?, ?, ?)");
            $stmt->execute([$phone, $selectedPrize['name'], $selectedPrize['image']]);

            // Set prize in session
            $_SESSION['current_prize'] = $selectedPrize;
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
