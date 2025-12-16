<?php
require_once '../models/mSession.php';
require_once '../models/mVIP.php';
require_once '../models/mDiscount.php';

Session::start();

// Kiểm tra đăng nhập
if (!Session::get('user_id')) {
    Session::setFlash('vip_error', 'Vui lòng đăng nhập');
    header('Location: ../views/dangnhap/login.php');
    exit;
}

// Kiểm tra request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Session::setFlash('vip_error', 'Invalid request method');
    header('Location: ../views/goivip/index.php');
    exit;
}

$userId = Session::get('user_id');

// Validate input
$months = isset($_POST['months']) ? intval($_POST['months']) : 0;
$price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
$discountCode = isset($_POST['discount_code']) ? trim($_POST['discount_code']) : '';

// Validate required fields
if (!in_array($months, [1, 3, 6, 12])) {
    Session::setFlash('vip_error', 'Gói không hợp lệ');
    header('Location: ../views/goivip/thanhtoan.php?months=' . $months);
    exit;
}

$vipModel = new VIP();
$discountModel = new Discount();

// Verify price (considering discount)
$expectedPrice = $vipModel->getVIPPrice($months);

// If discount code is provided, verify it
if (!empty($discountCode)) {
    $discountResult = $discountModel->validateAndApplyDiscount($discountCode, $expectedPrice, $userId);
    
    if ($discountResult['success']) {
        $expectedPrice = $discountResult['finalPrice'];
    } else {
        Session::setFlash('vip_error', 'Mã giảm giá không hợp lệ: ' . $discountResult['message']);
        header('Location: ../views/goivip/thanhtoan.php?months=' . $months);
        exit;
    }
}

if (abs($price - $expectedPrice) > 0.01) { // Allow small floating point differences
    Session::setFlash('vip_error', 'Giá không hợp lệ');
    header('Location: ../views/goivip/thanhtoan.php?months=' . $months);
    exit;
}

// Create payment record
if (!$vipModel->createPayment($userId, $price)) {
    Session::setFlash('vip_error', 'Không thể tạo bản ghi thanh toán');
    header('Location: ../views/goivip/thanhtoan.php?months=' . $months);
    exit;
}

// Create or extend VIP package
if ($vipModel->createVIPPackage($userId, $months)) {
    // Increment discount usage if code was used
    if (!empty($discountCode)) {
        $discountModel->incrementUsage($discountCode);
    }
    
    // Clear discount session
    Session::delete('applied_discount');
    
    // Success
    $packageNames = [
        1 => '1 tháng',
        3 => '3 tháng',
        6 => '6 tháng',
        12 => '12 tháng'
    ];
    
    $message = 'Chúc mừng! Bạn đã nâng cấp thành công gói VIP ' . $packageNames[$months];
    if (!empty($discountCode)) {
        $message .= ' với mã giảm giá ' . $discountCode;
    }
    Session::setFlash('vip_success', $message);
    
    // Redirect to success page or home
    header('Location: ../views/goivip/index.php');
    exit;
} else {
    Session::setFlash('vip_error', 'Không thể nâng cấp tài khoản. Vui lòng thử lại');
    header('Location: ../views/goivip/thanhtoan.php?months=' . $months);
    exit;
}
?>
