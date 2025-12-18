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
$originalPrice = $vipModel->getVIPPrice($months);

// Kiểm tra mã giảm giá đã áp dụng
$appliedDiscount = Session::get('applied_discount');
$discountAmount = 0;
$price = $originalPrice;

if ($appliedDiscount && isset($appliedDiscount['finalPrice'])) {
    $discountAmount = $appliedDiscount['discountAmount'];
    $price = $appliedDiscount['finalPrice'];
}

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
        <!-- Payment Container -->
        <div class="payment-container" style="position:relative;">
            <!-- Back Button -->
            <button class="back-btn" onclick="window.history.back()">
                <i class="fas fa-arrow-left"></i>
            </button>
            
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
                    <div class="summary-row">
                        <span class="label">Giá gốc</span>
                        <span class="value" id="originalPrice"><?php echo number_format($originalPrice, 0, ',', '.'); ?>đ</span>
                    </div>
                    <?php if ($discountAmount > 0): ?>
                    <div class="summary-row discount-row">
                        <span class="label">
                            <i class="fas fa-tag"></i> Giảm giá
                            <?php if ($appliedDiscount): ?>
                                <span class="discount-code">(<?php echo htmlspecialchars($appliedDiscount['code']); ?>)</span>
                            <?php endif; ?>
                        </span>
                        <span class="value discount-value" id="discountAmount">-<?php echo number_format($discountAmount, 0, ',', '.'); ?>đ</span>
                    </div>
                    <?php endif; ?>
                    <div class="summary-row total">
                        <span class="label">Tổng thanh toán</span>
                        <span class="value" id="finalPrice"><?php echo number_format($price, 0, ',', '.'); ?>đ</span>
                    </div>
                </div>
            </div>
            
            <!-- Discount Code Section -->
            <div class="discount-section">
                <h3><i class="fas fa-ticket-alt"></i> Mã giảm giá</h3>
                <div class="discount-input-wrapper">
                    <input type="text" 
                           id="couponCode" 
                           placeholder="Nhập mã giảm giá" 
                           class="discount-input"
                           value="<?php echo $appliedDiscount ? htmlspecialchars($appliedDiscount['code']) : ''; ?>">
                    <button type="button" id="applyDiscountBtn" class="btn-apply-discount">
                        <i class="fas fa-check"></i> Áp dụng
                    </button>
                </div>
                <div id="discountMessage" class="discount-message"></div>
            </div>
            
            <!-- Payment Form -->
            <form method="POST" action="../../controller/cUpgradeVIP.php" id="paymentForm">
                <input type="hidden" name="months" value="<?php echo $months; ?>">
                <input type="hidden" name="price" id="finalPriceInput" value="<?php echo $price; ?>">
                <input type="hidden" name="discount_code" id="discountCodeInput" value="<?php echo $appliedDiscount ? htmlspecialchars($appliedDiscount['code']) : ''; ?>">
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
            </form>
        </div>
    </div>

   
    
    <script>
    // Apply Discount Code
    document.getElementById('applyDiscountBtn').addEventListener('click', function() {
        const couponCode = document.getElementById('couponCode').value.trim();
        const months = <?php echo $months; ?>;
        const messageDiv = document.getElementById('discountMessage');
        const applyBtn = this;
        
        if (!couponCode) {
            messageDiv.className = 'discount-message error';
            messageDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Vui lòng nhập mã giảm giá';
            return;
        }
        
        // Disable button
        applyBtn.disabled = true;
        applyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
        
        // Send AJAX request
        fetch('../../controller/cApplyDiscount.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `coupon_code=${encodeURIComponent(couponCode)}&months=${months}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI
                messageDiv.className = 'discount-message success';
                messageDiv.innerHTML = `<i class="fas fa-check-circle"></i> ${data.message}`;
                
                // Update prices
                document.getElementById('finalPrice').textContent = data.finalPrice + 'đ';
                document.getElementById('finalPriceInput').value = data.finalPriceRaw;
                document.getElementById('discountCodeInput').value = couponCode;
                
                // Show discount row if not exists
                const summaryContent = document.querySelector('.summary-content');
                const discountRow = summaryContent.querySelector('.discount-row');
                
                if (discountRow) {
                    discountRow.querySelector('.discount-value').textContent = '-' + data.discountAmount + 'đ';
                    discountRow.querySelector('.discount-code').textContent = '(' + couponCode + ')';
                } else {
                    const totalRow = summaryContent.querySelector('.total');
                    const newDiscountRow = document.createElement('div');
                    newDiscountRow.className = 'summary-row discount-row';
                    newDiscountRow.innerHTML = `
                        <span class="label">
                            <i class="fas fa-tag"></i> Giảm giá
                            <span class="discount-code">(${couponCode})</span>
                        </span>
                        <span class="value discount-value">-${data.discountAmount}đ</span>
                    `;
                    summaryContent.insertBefore(newDiscountRow, totalRow);
                }
                
                // Reload page to update session
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                messageDiv.className = 'discount-message error';
                messageDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${data.message}`;
            }
        })
        .catch(error => {
            messageDiv.className = 'discount-message error';
            messageDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Có lỗi xảy ra, vui lòng thử lại';
        })
        .finally(() => {
            applyBtn.disabled = false;
            applyBtn.innerHTML = '<i class="fas fa-check"></i> Áp dụng';
        });
    });
    
    // Allow Enter key to apply discount
    document.getElementById('couponCode').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('applyDiscountBtn').click();
        }
    });
    </script>
</body>
</html>
