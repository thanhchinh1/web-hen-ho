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
    <link rel="stylesheet" href="../../public/css/user-dropdown.css">
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
        }
        
        .header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 15px 0;
        }
        
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            text-decoration: none;
            font-size: 24px;
            font-weight: 700;
        }
        
        .logo img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }
        
        .btn-back {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-back:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .vip-status {
            background: white;
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .status-badge.vip {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        .status-badge.free {
            background: #e0e0e0;
            color: #666;
        }
        
        .hero {
            text-align: center;
            color: white;
            margin-bottom: 60px;
        }
        
        .hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
        }
        
        .hero p {
            font-size: 20px;
            opacity: 0.9;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            margin-bottom: 60px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }
        
        @media (max-width: 768px) {
            .features-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .feature-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
        }
        
        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
            margin-bottom: 20px;
        }
        
        .feature-card h3 {
            font-size: 22px;
            margin-bottom: 10px;
            color: #333;
        }
        
        .feature-card p {
            color: #666;
            line-height: 1.6;
        }
        
        .pricing-section {
            background: white;
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }
        
        .pricing-title {
            text-align: center;
            font-size: 36px;
            margin-bottom: 40px;
            color: #333;
        }
        
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .price-card {
            border: 2px solid #e0e0e0;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            transition: all 0.3s;
            position: relative;
        }
        
        .price-card:hover {
            border-color: #667eea;
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        
        .price-card.popular {
            border-color: #667eea;
            background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
        }
        
        .popular-badge {
            position: absolute;
            top: -12px;
            right: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .price-duration {
            font-size: 18px;
            color: #666;
            margin-bottom: 15px;
        }
        
        .price-amount {
            font-size: 42px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .price-amount span {
            font-size: 18px;
            color: #999;
        }
        
        .price-save {
            color: #f5576c;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        .btn-upgrade {
            width: 100%;
            padding: 15px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-upgrade:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-upgrade.current {
            background: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-container">
            <a href="../trangchu/index.php" class="logo">
                <img src="../../public/img/logo.jpg" alt="DuyenHub">
                <span>DuyenHub</span>
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
            <p style="color: #999; margin-top: 10px;">Hết hạn: <?php echo date('d/m/Y', strtotime($currentPackage['ngayHetHan'])); ?></p>
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
    <script src="../../public/js/user-dropdown.js"></script>
</body>
</html>
