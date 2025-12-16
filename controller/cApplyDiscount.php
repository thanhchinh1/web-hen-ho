<?php
require_once __DIR__ . '/../models/mSession.php';
require_once __DIR__ . '/../models/mDiscount.php';
require_once __DIR__ . '/../models/mVIP.php';

header('Content-Type: application/json');

Session::start();

// Kiểm tra đăng nhập
if (!Session::get('user_id')) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng đăng nhập'
    ]);
    exit;
}

// Kiểm tra method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

$userId = Session::get('user_id');
$couponCode = isset($_POST['coupon_code']) ? trim(strtoupper($_POST['coupon_code'])) : '';
$months = isset($_POST['months']) ? intval($_POST['months']) : 1;

// Validate input
if (empty($couponCode)) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng nhập mã giảm giá'
    ]);
    exit;
}

if (!in_array($months, [1, 3, 6, 12])) {
    echo json_encode([
        'success' => false,
        'message' => 'Gói dịch vụ không hợp lệ'
    ]);
    exit;
}

try {
    $vipModel = new VIP();
    $discountModel = new Discount();
    
    // Lấy giá gốc
    $originalPrice = $vipModel->getVIPPrice($months);
    
    // Áp dụng mã giảm giá
    $result = $discountModel->validateAndApplyDiscount($couponCode, $originalPrice, $userId);
    
    if ($result['success']) {
        // Lưu thông tin vào session
        Session::set('applied_discount', [
            'code' => $couponCode,
            'discountAmount' => $result['discountAmount'],
            'finalPrice' => $result['finalPrice'],
            'couponInfo' => $result['coupon']
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => $result['message'],
            'originalPrice' => number_format($originalPrice, 0, ',', '.'),
            'discountAmount' => number_format($result['discountAmount'], 0, ',', '.'),
            'finalPrice' => number_format($result['finalPrice'], 0, ',', '.'),
            'discountAmountRaw' => $result['discountAmount'],
            'finalPriceRaw' => $result['finalPrice']
        ]);
    } else {
        echo json_encode($result);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
    ]);
}
?>
