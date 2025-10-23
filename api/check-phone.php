<?php
require_once 'config.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get phone number from POST data
$input = json_decode(file_get_contents('php://input'), true);
$phone = isset($input['phone']) ? trim($input['phone']) : '';

// Validate phone number format (Vietnamese mobile numbers)
if (empty($phone)) {
    echo json_encode(['error' => 'Số điện thoại không được để trống']);
    exit();
}

// Vietnamese phone number validation: 10-11 digits, starts with 0
if (!preg_match('/^0[0-9]{9,10}$/', $phone)) {
    echo json_encode(['error' => 'Số điện thoại không đúng định dạng. Vui lòng nhập số điện thoại Việt Nam (10-11 số, bắt đầu bằng 0)']);
    exit();
}

try {
    // Check if phone number already exists in database
    $stmt = $pdo->prepare("SELECT prize_name, prize_image FROM participants WHERE phone_number = ?");
    $stmt->execute([$phone]);
    $result = $stmt->fetch();

    if ($result) {
        // Phone number exists, return existing prize
        echo json_encode([
            'exists' => true,
            'prize_name' => $result['prize_name'],
            'prize_image' => $result['prize_image']
        ]);
    } else {
        // Phone number doesn't exist, can proceed to spin
        echo json_encode(['exists' => false]);
    }

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
}
?>
