<?php
require_once '../../models/session.php';
require_once '../../controller/thich.php';

requireLogin();
$currentUserId = getCurrentUserId();
$thichController = new ThichController();
$likedByUsers = $thichController->getNguoiThichBan($currentUserId);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Người đã thích bạn - Kết Nối Yêu Thương</title>
    <link rel="stylesheet" href="../../public/css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="main-header">
        <div class="header-container">
            <a href="../trangchu/index.php" class="logo">
                <img src="../../public/img/logo.jpg" alt="Kết Nối Yêu Thương">
                <span class="logo-text">DuyenHub</span>
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
            <h2>Danh sách người đã thích bạn</h2>
        </div>
        <div class="profiles-grid">
            <?php if (empty($likedByUsers)): ?>
                <p style="grid-column: 1/-1; text-align: center; color: #999; font-size: 18px;">Chưa có ai thả tim bạn!</p>
            <?php else: ?>
                <?php foreach ($likedByUsers as $profile): ?>
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
                        <a href="../hoso/xemnguoikhac.php?id=<?php echo $profile['maNguoiDung']; ?>" class="btn-logout" style="margin-top:10px;display:inline-block;">Xem hồ sơ</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
