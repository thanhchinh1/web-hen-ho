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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/goivip.css?v=<?php echo time(); ?>">
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="header-container">
            <div class="header-left">
                <a href="../trangchu/index.php" class="logo">
                    <img src="../../public/img/logo.jpg" alt="DuyenHub Logo">
                    <span class="logo-text">DuyenHub</span>
                </a>
                <nav class="header-menu">
                    <a href="../trangchu/index.php" class="menu-item active">
                        <i class="fas fa-home"></i>
                        <span>Trang chủ</span>
                    </a>
                    <a href="../nhantin/message.php" class="menu-item">
                        <i class="fas fa-comments"></i>
                        <span>Tin nhắn</span>
                    </a>
                    <a href="../timkiem/ghepdoinhanh.php" class="menu-item">
                        <i class="fas fa-search"></i>
                        <span>Tìm kiếm</span>
                    </a>
                    <a href="#" class="menu-item">
                        <i class="fas fa-question-circle"></i>
                        <span>Trợ giúp</span>
                    </a>
                </nav>
            </div>
            <div class="header-actions">
                <!-- Đã xóa nút đăng xuất để đồng bộ với yêu cầu -->
            </div>
        </div>
    </header>

    <!-- VIP Wrapper -->
    <div class="vip-wrapper">
        <!-- VIP Container -->
        <div class="vip-container" style="position:relative;">
            <!-- Back Button -->
            <button class="back-btn" onclick="window.history.back()">
                <i class="fas fa-arrow-left"></i>
            </button>
            
            <?php if ($isVIP): ?>
            <!-- VIP Status Section -->
            <div class="vip-status-card">
                <div class="vip-badge">
                    <i class="fas fa-crown"></i>
                    <span>Tài khoản VIP</span>
                </div>
                <h2 class="status-title">Bạn đang là thành viên VIP</h2>
                <div class="status-info">
                    <p class="days-remaining">Còn lại: <strong><?php echo $daysRemaining; ?> ngày</strong></p>
                    <p class="expiry-date">Hết hạn: <?php echo date('d/m/Y', strtotime($currentPackage['ngayHetHan'])); ?></p>
                </div>
            </div>
            <?php else: ?>
            <!-- Hero Section -->
            <div class="vip-hero">
                <div class="hero-icon">
                    <i class="fas fa-crown"></i>
                </div>
                <h1 class="hero-title">Nâng cấp VIP</h1>
                <p class="hero-subtitle">Mở khóa tất cả tính năng đặc biệt và trải nghiệm hẹn hò tốt nhất</p>
            </div>
            <?php endif; ?>
            
            <!-- Features Grid -->
            <div class="features-section">
                <h2 class="section-title">Đặc quyền thành viên VIP</h2>
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
            </div>
            
            <!-- Pricing Section -->
            <div class="pricing-section">
                <h2 class="section-title">Chọn gói phù hợp với bạn</h2>
                
                <div class="pricing-grid">
                    <!-- 1 Month Package -->
                    <div class="price-card">
                        <div class="price-header">
                            <div class="price-duration">1 Tháng</div>
                        </div>
                        <div class="price-body">
                            <div class="price-amount">
                                <span class="amount">99.000đ</span>
                                <span class="period">/tháng</span>
                            </div>
                            <a href="thanhtoan.php?months=1" class="btn-upgrade">
                                <i class="fas fa-crown"></i> Nâng cấp ngay
                            </a>
                        </div>
                    </div>
                    
                    <!-- 3 Months Package (Popular) -->
                    <div class="price-card popular">
                        <span class="popular-badge">
                            <i class="fas fa-fire"></i> Phổ biến nhất
                        </span>
                        <div class="price-header">
                            <div class="price-duration">3 Tháng</div>
                        </div>
                        <div class="price-body">
                            <div class="price-amount">
                                <span class="amount">249.000đ</span>
                                <span class="period">/3 tháng</span>
                            </div>
                            <div class="price-save">
                                <i class="fas fa-tag"></i> Tiết kiệm 16%
                            </div>
                            <a href="thanhtoan.php?months=3" class="btn-upgrade">
                                <i class="fas fa-crown"></i> Nâng cấp ngay
                            </a>
                        </div>
                    </div>
                    
                    <!-- 6 Months Package -->
                    <div class="price-card">
                        <div class="price-header">
                            <div class="price-duration">6 Tháng</div>
                        </div>
                        <div class="price-body">
                            <div class="price-amount">
                                <span class="amount">449.000đ</span>
                                <span class="period">/6 tháng</span>
                            </div>
                            <div class="price-save">
                                <i class="fas fa-tag"></i> Tiết kiệm 24%
                            </div>
                            <a href="thanhtoan.php?months=6" class="btn-upgrade">
                                <i class="fas fa-crown"></i> Nâng cấp ngay
                            </a>
                        </div>
                    </div>
                    
                    <!-- 12 Months Package -->
                    <div class="price-card best-value">
                        <span class="value-badge">
                            <i class="fas fa-star"></i> Giá trị nhất
                        </span>
                        <div class="price-header">
                            <div class="price-duration">12 Tháng</div>
                        </div>
                        <div class="price-body">
                            <div class="price-amount">
                                <span class="amount">799.000đ</span>
                                <span class="period">/năm</span>
                            </div>
                            <div class="price-save">
                                <i class="fas fa-tag"></i> Tiết kiệm 33%
                            </div>
                            <a href="thanhtoan.php?months=12" class="btn-upgrade">
                                <i class="fas fa-crown"></i> Nâng cấp ngay
                            </a>
                        </div>
                    </div>
                </div>
                
                <?php if ($isVIP): ?>
                <div class="renewal-notice">
                    <i class="fas fa-info-circle"></i>
                    <p>Gia hạn gói VIP sẽ cộng dồn thêm thời gian vào gói hiện tại của bạn</p>
                </div>
                <?php endif; ?>
            </div>
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
