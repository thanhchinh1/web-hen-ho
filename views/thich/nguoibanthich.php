<?php
require_once '../../models/session.php';
require_once '../../controller/thich.php';

requireLogin();
$currentUserId = getCurrentUserId();
$thichController = new ThichController();
$likedUsers = $thichController->getNguoiBanThich($currentUserId);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Người bạn đã thích - Kết Nối Yêu Thương</title>
    <link rel="stylesheet" href="../../public/css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="main-header">
        <div class="header-container">
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
        </div>
    </header>
    <div class="profiles-section">
        <div class="section-header">
            <h2>Danh sách người bạn đã thích</h2>
        </div>
        <div class="profiles-grid">
            <?php if (empty($likedUsers)): ?>
                <p style="grid-column: 1/-1; text-align: center; color: #999; font-size: 18px;">Bạn chưa thả tim ai!</p>
            <?php else: ?>
                <?php foreach ($likedUsers as $profile): ?>
                    <?php 
                        $profileModel = new Profile();
                        $age = $profileModel->calculateAge($profile['ngaySinh']);
                        $avatarSrc = !empty($profile['avt']) ? '../../' . htmlspecialchars($profile['avt']) : 'https://i.pravatar.cc/200';
                    ?>
                    <div class="profile-card">
                        <div class="profile-avatar-wrapper">
                            <img src="<?php echo $avatarSrc; ?>" alt="<?php echo htmlspecialchars($profile['ten']); ?>">
                        </div>
                        <div class="profile-info">
                            <h3><?php echo htmlspecialchars($profile['ten']); ?>, <?php echo $age; ?></h3>
                            <p class="profile-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($profile['noiSong']); ?></p>
                            <p class="profile-status"><?php echo htmlspecialchars($profile['mucTieuPhatTrien']); ?></p>
                        </div>
                        <a href="../../hoso/hosonguoidathich.php" class="btn-logout" style="margin-top:10px;display:inline-block;">Xem hồ sơ</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
