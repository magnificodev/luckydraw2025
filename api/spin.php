<?php
require_once 'config.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get data from POST
$input = json_decode(file_get_contents('php://input'), true);
$phone = isset($input['phone']) ? trim($input['phone']) : '';

// Validate phone number format
if (empty($phone)) {
    echo json_encode(['error' => 'Số điện thoại không được để trống']);
    exit();
}

if (!preg_match('/^0[0-9]{9,10}$/', $phone)) {
    echo json_encode(['error' => 'Số điện thoại không đúng định dạng']);
    exit();
}

// Define 8 prizes with equal probability
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
    // Check if phone number already exists (double-check)
    $stmt = $pdo->prepare("SELECT id FROM participants WHERE phone_number = ?");
    $stmt->execute([$phone]);

    if ($stmt->fetch()) {
        echo json_encode(['error' => 'Số điện thoại này đã tham gia rồi']);
        exit();
    }

    // Insert new participant with prize
    $stmt = $pdo->prepare("INSERT INTO participants (phone_number, prize_name, prize_image) VALUES (?, ?, ?)");
    $stmt->execute([$phone, $selectedPrize['name'], $selectedPrize['image']]);

    echo json_encode([
        'success' => true,
        'prize_name' => $selectedPrize['name'],
        'prize_image' => $selectedPrize['image']
    ]);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
}
?>
