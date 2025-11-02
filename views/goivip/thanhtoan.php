<?php
require_once '../../models/mSession.php';
require_once '../../models/mVIP.php';

Session::start();

// Kiểm tra đăng nhập
if (!Session::get('user_id')) {
    header('Location: ../dangnhap/login.php');
    exit;
}

$userId = Session::get('user_id');
$months = isset($_GET['months']) ? intval($_GET['months']) : 1;

// Validate months
if (!in_array($months, [1, 3, 6, 12])) {
    $months = 1;
}

$vipModel = new VIP();
$price = $vipModel->getVIPPrice($months);

$packageNames = [
    1 => 'Gói 1 Tháng',
    3 => 'Gói 3 Tháng',
    6 => 'Gói 6 Tháng',
    12 => 'Gói 12 Tháng'
];

$successMessage = Session::getFlash('vip_success');
$errorMessage = Session::getFlash('vip_error');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán VIP - DuyenHub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/vip-page.css">
    <link rel="stylesheet" href="../../public/css/payment-page.css">
</head>
<body>
    <div class="page-wrapper">
    <header class="main-header">
        <div class="header-container">
            <a href="../trangchu/index.php" class="logo">
                <img src="../../public/img/logo.jpg" alt="DuyenHub">
                <span class="logo-text">DuyenHub</span>
            </a>
            <div style="display: flex; align-items: center; gap: 15px;">
                <a href="index.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại
                </a>
            </div>
        </div>
    </header>
    
    <div class="container">
        
        <div class="payment-card">
            <div class="payment-header">
                <h1><i class="fas fa-crown"></i> Thanh toán VIP</h1>
                <p>Hoàn tất thanh toán để nâng cấp tài khoản</p>
            </div>
            
            <div class="payment-body">
                <?php if ($successMessage): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($successMessage); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($errorMessage): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($errorMessage); ?>
                    </div>
                <?php endif; ?>
                
                <div class="order-summary">
                    <h3>Tóm tắt đơn hàng</h3>
                    <div class="summary-row">
                        <span>Gói dịch vụ</span>
                        <span><?php echo $packageNames[$months]; ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Thời hạn</span>
                        <span><?php echo $months; ?> tháng</span>
                    </div>
                    <div class="summary-row">
                        <span>Tổng thanh toán</span>
                        <span><?php echo number_format($price, 0, ',', '.'); ?>đ</span>
                    </div>
                </div>
                
                <form method="POST" action="../../controller/cUpgradeVIP.php" id="paymentForm">
                    <input type="hidden" name="months" value="<?php echo $months; ?>">
                    <input type="hidden" name="price" value="<?php echo $price; ?>">
                    <input type="hidden" name="payment_method" value="bank_transfer">
                    <input type="hidden" name="fullname" value="Khách hàng">
                    <input type="hidden" name="phone" value="0000000000">
                    
                    <div class="qr-payment-section">
                        <h3 style="text-align: center; margin-bottom: 20px; color: #333;">
                            <i class="fas fa-qrcode"></i> Quét mã QR để thanh toán
                        </h3>
                        
                        <div class="qr-container">
                            <img src="../../public/img/qr-payment.png" alt="QR Code thanh toán" class="qr-code">
                        </div>
                        
                        <div class="payment-instructions">
                            <h4><i class="fas fa-info-circle"></i> Hướng dẫn thanh toán:</h4>
                            <ol>
                                <li>Mở ứng dụng ngân hàng trên điện thoại</li>
                                <li>Chọn chức năng quét mã QR</li>
                                <li>Quét mã QR bên trên</li>
                                <li>Kiểm tra số tiền: <strong><?php echo number_format($price, 0, ',', '.'); ?>đ</strong></li>
                                <li>Nội dung: <strong>VIP <?php echo $months; ?>T - ID<?php echo $userId; ?></strong></li>
                                <li>Xác nhận thanh toán</li>
                                <li>Sau khi chuyển khoản thành công, nhấn nút bên dưới</li>
                            </ol>
                        </div>
                        
                        <div class="transfer-info">
                            <p><strong>Số tiền:</strong> <?php echo number_format($price, 0, ',', '.'); ?>đ</p>
                            <p><strong>Nội dung CK:</strong> VIP <?php echo $months; ?>T - ID<?php echo $userId; ?></p>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-pay">
                        <i class="fas fa-check-circle"></i> Tôi đã thanh toán
                    </button>
                    
                    <div class="secure-notice">
                        <i class="fas fa-shield-alt"></i>
                        <p>Sau khi xác nhận, tài khoản VIP sẽ được kích hoạt ngay lập tức. Nếu có vấn đề, vui lòng liên hệ hỗ trợ.</p>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>
</body>
</html>
