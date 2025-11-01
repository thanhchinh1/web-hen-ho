<?php
require_once '../../models/session.php';
require_once '../../models/mProfile.php';

requireLogin(); // Yêu cầu đăng nhập

$currentUserId = getCurrentUserId();
$profileModel = new Profile();
$profile = $profileModel->getProfile($currentUserId);

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
    <title>Hồ sơ cá nhân - Kết Nối Yêu Thương</title>
    <link rel="stylesheet" href="../../public/css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="profile-header">
        <a href="../trangchu/index.php" class="logo">
            <svg viewBox="0 0 24 24" fill="none" class="logo-icon">
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" fill="#ff6b9d"/>
            </svg>
        </a>
        <div class="header-center">
            <a href="../trangchu/index.php" class="nav-link">Trang chủ</a>
        </div>
        <div class="header-right">
            <a href="../../controller/logout.php" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i>
                Đăng Xuất
            </a>
        </div>
    </header>
    <div class="profile-content">
        <div class="profile-hero">
            <div class="profile-avatar-section">
                <img src="<?php echo $avatarSrc; ?>" alt="Avatar" class="profile-avatar">
            </div>
            <h2 class="profile-name"><?php echo htmlspecialchars($profile['ten']); ?></h2>
            <p class="profile-info"><?php echo $age; ?> tuổi • <?php echo htmlspecialchars($profile['noiSong']); ?> • <?php echo htmlspecialchars($profile['tinhTrangHonNhan']); ?></p>
        </div>
        <div class="profile-details">
            <section class="detail-section">
                <h2 class="section-title">Thông tin cá nhân</h2>
                <div class="info-list">
                    <div class="info-item">
                        <i class="fas fa-venus-mars"></i>
                        <span class="info-label">Giới tính:</span>
                        <span class="info-value"><?php echo htmlspecialchars($profile['gioiTinh']); ?></span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span class="info-label">Tuổi:</span>
                        <span class="info-value"><?php echo $age; ?> tuổi</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span class="info-label">Thành phố:</span>
                        <span class="info-value"><?php echo htmlspecialchars($profile['noiSong']); ?></span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-heart"></i>
                        <span class="info-label">Tình trạng hôn nhân:</span>
                        <span class="info-value"><?php echo htmlspecialchars($profile['tinhTrangHonNhan']); ?></span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-graduation-cap"></i>
                        <span class="info-label">Học vấn:</span>
                        <span class="info-value"><?php echo htmlspecialchars($profile['hocVan']); ?></span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-weight"></i>
                        <span class="info-label">Cân nặng:</span>
                        <span class="info-value"><?php echo htmlspecialchars($profile['canNang']); ?> kg</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-ruler-vertical"></i>
                        <span class="info-label">Chiều cao:</span>
                        <span class="info-value"><?php echo htmlspecialchars($profile['chieuCao']); ?> cm</span>
                    </div>
                </div>
            </section>
            <section class="detail-section">
                <h2 class="section-title">Sở thích</h2>
                <div class="interests-tags">
                    <?php foreach ($interests as $interest): ?>
                        <span class="interest-tag"><?php echo htmlspecialchars($interest); ?></span>
                    <?php endforeach; ?>
                </div>
            </section>
            <section class="detail-section">
                <h2 class="section-title">Giới thiệu bản thân</h2>
                <div class="about-text">
                    <?php echo nl2br(htmlspecialchars($profile['moTa'])); ?>
                </div>
            </section>
        </div>
    </div>
</body>
</html>