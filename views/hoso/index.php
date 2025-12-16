<?php
require_once '../../models/mSession.php';
require_once '../../models/mProfile.php';
require_once '../../models/mVIP.php';

Session::start();

if (!Session::isLoggedIn()) {
    header('Location: ../dangnhap/login.php');
    exit;
}

// Kiểm tra role - nếu là admin thì chuyển về trang admin
$userRole = Session::get('user_role');
if ($userRole === 'admin') {
    header('Location: ../admin/index.php');
    exit;
}

$currentUserId = Session::getUserId();
$profileModel = new Profile();
$vipModel = new VIP();
$profile = $profileModel->getProfile($currentUserId);

// Kiểm tra VIP status
$isVIP = $vipModel->isVIP($currentUserId);

if (!$profile) {
    // Nếu chưa có hồ sơ, chuyển về trang thiết lập
    header('Location: thietlaphoso.php');
    exit;
}

$age = $profileModel->calculateAge($profile['ngaySinh']);
$avatarSrc = !empty($profile['avt']) ? '../../' . htmlspecialchars($profile['avt']) : 'https://i.pravatar.cc/300';
$interests = explode(', ', $profile['soThich']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ cá nhân - DuyenHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/hoso-view.css?v=<?php echo time(); ?>">
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
                    <a href="../nhantin/chat.php" class="menu-item">
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
                <a href="../../controller/cLogout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Đăng xuất</span>
                </a>
            </div>
        </div>
    </header>

    <div class="profile-view-wrapper">
        <div class="profile-view-container">
            <button class="back-btn" onclick="window.history.back()" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </button>

            <div class="profile-view-header">
                <h1>Hồ sơ cá nhân</h1>
            </div>
            <!-- Avatar Section -->
            <div class="avatar-section">
                <div class="avatar-preview">
                    <img src="<?php echo $avatarSrc; ?>" alt="Avatar" id="avatarImage">
                </div>
                <h2 class="profile-name"><?php echo htmlspecialchars($profile['ten']); ?></h2>
                <p class="profile-age"><?php echo $age; ?> tuổi</p>
                <?php if ($isVIP): ?>
                    <div class="vip-badge-display">
                        <i class="fas fa-crown"></i>
                        <span>Thành viên VIP</span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Profile Form View -->
            <div class="profile-form-view">
                    <section class="detail-section">
                        <h2 class="section-title">Thông tin cá nhân</h2>
                       
                        <div class="info-list">
                            <div class="info-item">
                                <i class="fas fa-venus-mars"></i>
                                <span class="info-label">Giới tính</span>
                                <span class="info-value"><?php echo htmlspecialchars($profile['gioiTinh']); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-calendar-alt"></i>
                                <span class="info-label">Ngày sinh</span>
                                <span class="info-value"><?php echo date('d/m/Y', strtotime($profile['ngaySinh'])); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span class="info-label">Nơi ở</span>
                                <span class="info-value"><?php echo htmlspecialchars($profile['noiSong']); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-heart"></i>
                                <span class="info-label">Tình trạng</span>
                                <span class="info-value"><?php echo htmlspecialchars($profile['tinhTrangHonNhan']); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-graduation-cap"></i>
                                <span class="info-label">Học vấn</span>
                                <span class="info-value"><?php echo htmlspecialchars($profile['hocVan']); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-bullseye"></i>
                                <span class="info-label">Mục tiêu</span>
                                <span class="info-value"><?php echo htmlspecialchars($profile['mucTieuPhatTrien']); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-weight"></i>
                                <span class="info-label">Cân nặng</span>
                                <span class="info-value"><?php echo htmlspecialchars($profile['canNang']); ?> kg</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-ruler-vertical"></i>
                                <span class="info-label">Chiều cao</span>
                                <span class="info-value"><?php echo htmlspecialchars($profile['chieuCao']); ?> cm</span>
                            </div>
                        </div>
                    </section>

                    <section class="detail-section">
                        <h2 class="section-title">Sở thích</h2>
                       
                        <div class="interests-list">
                            <?php foreach ($interests as $interest): ?>
                                <span class="interest-tag"><?php echo htmlspecialchars(trim($interest)); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <section class="detail-section">
                        <h2 class="section-title">Giới thiệu bản thân</h2>
                       
                        <div class="description-text">
                            <?php echo nl2br(htmlspecialchars($profile['moTa'])); ?>
                        </div>
                    </section>

                <!-- Action Buttons -->
                <div class="profile-actions">
                    <a href="chinhsua.php" class="btn-action btn-primary">
                        <i class="fas fa-edit"></i>
                        Chỉnh sửa hồ sơ
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function goBack() {
            window.history.back();
        }
    </script>
</body>
</html>