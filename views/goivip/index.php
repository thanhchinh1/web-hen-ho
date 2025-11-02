<?php
require_once '../../models/mSession.php';
require_once '../../models/mVIP.php';
require_once '../../models/mProfile.php';

Session::start();

// Kiểm tra đăng nhập
if (!Session::get('user_id')) {
    header('Location: ../dangnhap/login.php');
    exit;
}

$userId = Session::get('user_id');
$vipModel = new VIP();
$isVIP = $vipModel->isVIP($userId);
$currentPackage = $vipModel->getCurrentVIPPackage($userId);
$daysRemaining = $vipModel->getDaysRemaining($userId);

// Lấy profile để hiển thị avatar
$profileModel = new Profile();
$profile = $profileModel->getProfile($userId);
$avatarPath = !empty($profile['avt']) ? $profile['avt'] : 'public/img/default-avatar.jpg';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nâng cấp VIP - DuyenHub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/vip-page.css">
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
                <a href="../trangchu/index.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại
                </a>
            </div>
        </div>
    </header>
    
    <div class="container">
        <?php if ($isVIP): ?>
        <div class="vip-status">
            <span class="status-badge vip">
                <i class="fas fa-crown"></i>
                Tài khoản VIP
            </span>
            <h2>Bạn đang là thành viên VIP</h2>
            <p>Gói VIP của bạn còn <strong><?php echo $daysRemaining; ?> ngày</strong></p>
            <p style="color: rgba(255,255,255,0.8); margin-top: 10px;">Hết hạn: <?php echo date('d/m/Y', strtotime($currentPackage['ngayHetHan'])); ?></p>
        </div>
        <?php else: ?>
        <div class="hero">
            <h1><i class="fas fa-crown"></i> Nâng cấp VIP</h1>
            <p>Mở khóa tất cả tính năng đặc biệt và trải nghiệm hẹn hò tốt nhất</p>
        </div>
        <?php endif; ?>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-heart-pulse"></i>
                </div>
                <h3>Ghép đôi thông minh</h3>
                <p>Thuật toán AI tìm kiếm người phù hợp nhất với bạn dựa trên sở thích và tính cách</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-infinity"></i>
                </div>
                <h3>Thích không giới hạn</h3>
                <p>Không giới hạn số lượng like mỗi ngày, tự do khám phá nhiều người hơn</p>
            </div>
        </div>
        
        <div class="pricing-section">
            <h2 class="pricing-title">Chọn gói phù hợp với bạn</h2>
            
            <div class="pricing-grid">
                <div class="price-card">
                    <div class="price-duration">1 Tháng</div>
                    <div class="price-amount">99.000đ<span>/tháng</span></div>
                    <a href="thanhtoan.php?months=1" class="btn-upgrade">
                        Nâng cấp ngay
                    </a>
                </div>
                
                <div class="price-card popular">
                    <span class="popular-badge">Phổ biến nhất</span>
                    <div class="price-duration">3 Tháng</div>
                    <div class="price-amount">249.000đ<span>/3 tháng</span></div>
                    <div class="price-save">Tiết kiệm 16%</div>
                    <a href="thanhtoan.php?months=3" class="btn-upgrade">
                        Nâng cấp ngay
                    </a>
                </div>
                
                <div class="price-card">
                    <div class="price-duration">6 Tháng</div>
                    <div class="price-amount">449.000đ<span>/6 tháng</span></div>
                    <div class="price-save">Tiết kiệm 24%</div>
                    <a href="thanhtoan.php?months=6" class="btn-upgrade">
                        Nâng cấp ngay
                    </a>
                </div>
                
                <div class="price-card">
                    <div class="price-duration">12 Tháng</div>
                    <div class="price-amount">799.000đ<span>/năm</span></div>
                    <div class="price-save">Tiết kiệm 33%</div>
                    <a href="thanhtoan.php?months=12" class="btn-upgrade">
                        Nâng cấp ngay
                    </a>
                </div>
            </div>
            
            <?php if ($isVIP): ?>
            <p style="text-align: center; color: #666; margin-top: 20px;">
                <i class="fas fa-info-circle"></i> Gia hạn gói VIP sẽ cộng dồn thêm thời gian vào gói hiện tại của bạn
            </p>
            <?php endif; ?>
        </div>
    </div>
    </div>
    <script src="../../public/js/user-dropdown.js"></script>
</body>
</html>
