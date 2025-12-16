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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/thanhtoan.css?v=<?php echo time(); ?>">
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="header-container">
            <a href="../trangchu/index.php" class="logo">
                <img src="../../public/img/logo.jpg" alt="DuyenHub Logo">
                <span class="logo-text">DuyenHub</span>
            </a>
            
        </div>
    </header>

    <!-- Payment Wrapper -->
    <div class="payment-wrapper">
        <!-- Back Button -->
        <button class="back-btn" onclick="window.location.href='index.php'">
            <i class="fas fa-arrow-left"></i>
        </button>

        <!-- Payment Container -->
        <div class="payment-container">
        <!-- Payment Container -->
        <div class="payment-container">
            <!-- Payment Header -->
            <div class="payment-header">
                <div class="header-icon">
                    <i class="fas fa-crown"></i>
                </div>
                <h1>Thanh toán VIP</h1>
                <p>Hoàn tất thanh toán để nâng cấp tài khoản</p>
            </div>
            
            <!-- Messages -->
            <?php if ($successMessage): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo htmlspecialchars($successMessage); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($errorMessage): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($errorMessage); ?></span>
                </div>
            <?php endif; ?>
            
            <!-- Order Summary -->
            <div class="order-summary">
                <h3><i class="fas fa-receipt"></i> Tóm tắt đơn hàng</h3>
                <div class="summary-content">
                    <div class="summary-row">
                        <span class="label">Gói dịch vụ</span>
                        <span class="value"><?php echo $packageNames[$months]; ?></span>
                    </div>
                    <div class="summary-row">
                        <span class="label">Thời hạn</span>
                        <span class="value"><?php echo $months; ?> tháng</span>
                    </div>
                    <div class="summary-row total">
                        <span class="label">Tổng thanh toán</span>
                        <span class="value"><?php echo number_format($price, 0, ',', '.'); ?>đ</span>
                    </div>
                </div>
            </div>
            
            <!-- Payment Form -->
            <form method="POST" action="../../controller/cUpgradeVIP.php" id="paymentForm">
                <input type="hidden" name="months" value="<?php echo $months; ?>">
                <input type="hidden" name="price" value="<?php echo $price; ?>">
                <input type="hidden" name="payment_method" value="bank_transfer">
                <input type="hidden" name="fullname" value="Khách hàng">
                <input type="hidden" name="phone" value="0000000000">
                
                <!-- QR Payment Section -->
                <div class="qr-payment-section">
                    <h3><i class="fas fa-qrcode"></i> Quét mã QR để thanh toán</h3>
                    
                    <div class="qr-container">
                        <div class="qr-wrapper">
                            <img src="../../public/img/qr-payment.png" alt="QR Code thanh toán" class="qr-code">
                        </div>
                    </div>
                    
                    <div class="payment-info-box">
                        <div class="info-row">
                            <span class="info-label"><i class="fas fa-money-bill-wave"></i> Số tiền:</span>
                            <span class="info-value"><?php echo number_format($price, 0, ',', '.'); ?>đ</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label"><i class="fas fa-comment-alt"></i> Nội dung CK:</span>
                            <span class="info-value">VIP <?php echo $months; ?>T - ID<?php echo $userId; ?></span>
                        </div>
                    </div>
                    
                    <div class="payment-instructions">
                        <h4><i class="fas fa-info-circle"></i> Hướng dẫn thanh toán</h4>
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
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="btn-pay">
                    <i class="fas fa-check-circle"></i> Tôi đã thanh toán
                </button>
                
                <!-- Security Notice -->
                <div class="secure-notice">
                    <i class="fas fa-shield-alt"></i>
                    <p>Sau khi xác nhận, tài khoản VIP sẽ được kích hoạt ngay lập tức. Nếu có vấn đề, vui lòng liên hệ hỗ trợ.</p>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-top">
                <div class="footer-links">
                    <a href="#">Về chúng tôi</a>
                    <a href="#">Hỗ trợ</a>
                    <a href="#">Pháp lý</a>
                </div>
                <div class="footer-social">
                    <a href="https://www.facebook.com/profile.php?id=61583156011828" class="social-icon" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; Kết Nối Yêu Thương. Mọi quyền được bảo lưu.</p>
            </div>
        </div>
    </footer>
</body>
</html>
