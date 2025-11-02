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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .payment-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .payment-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .payment-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .payment-header p {
            opacity: 0.9;
        }
        
        .payment-body {
            padding: 40px;
        }
        
        .order-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        
        .order-summary h3 {
            margin-bottom: 15px;
            color: #333;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .summary-row:last-child {
            border-bottom: none;
            font-weight: 700;
            font-size: 20px;
            color: #667eea;
            padding-top: 15px;
        }
        
        .payment-methods {
            margin-bottom: 30px;
        }
        
        .qr-payment-section {
            margin-bottom: 30px;
        }
        
        .qr-container {
            background: white;
            padding: 20px;
            border-radius: 15px;
            border: 3px dashed #667eea;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .qr-code {
            max-width: 300px;
            width: 100%;
            height: auto;
            border-radius: 10px;
        }
        
        .payment-instructions {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .payment-instructions h4 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 16px;
        }
        
        .payment-instructions ol {
            margin-left: 20px;
            color: #555;
        }
        
        .payment-instructions li {
            margin-bottom: 8px;
            line-height: 1.6;
        }
        
        .transfer-info {
            background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #667eea;
            margin-bottom: 20px;
        }
        
        .transfer-info p {
            margin-bottom: 10px;
            font-size: 15px;
            color: #333;
        }
        
        .transfer-info p:last-child {
            margin-bottom: 0;
        }
        
        .payment-methods h3 {
            margin-bottom: 20px;
            color: #333;
        }
        
        .method-option {
            border: 2px solid #e0e0e0;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .method-option:hover {
            border-color: #667eea;
        }
        
        .method-option.selected {
            border-color: #667eea;
            background: #f0f3ff;
        }
        
        .method-option input[type="radio"] {
            width: 20px;
            height: 20px;
        }
        
        .method-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }
        
        .method-info {
            flex: 1;
        }
        
        .method-info h4 {
            margin-bottom: 5px;
            color: #333;
        }
        
        .method-info p {
            color: #666;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn-pay {
            width: 100%;
            padding: 15px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-back {
            display: inline-block;
            margin-bottom: 20px;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
        }
        
        .btn-back:hover {
            opacity: 0.8;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .secure-notice {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }
        
        .secure-notice i {
            color: #4caf50;
            font-size: 24px;
        }
        
        .secure-notice p {
            color: #2e7d32;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="btn-back">
            <i class="fas fa-arrow-left"></i>
            Quay lại
        </a>
        
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
</body>
</html>
