<?php
require_once '../../models/mSession.php';
require_once '../../models/mProfile.php';
require_once '../../models/mVIP.php';
require_once '../../models/mNotification.php';
require_once '../../models/mMessage.php';

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

// Đếm số ghép đôi mới và tin nhắn chưa đọc
$notificationModel = new Notification();
$newMatchesCount = $notificationModel->getNewMatchesCount($currentUserId);
$messageModel = new Message();
$unreadMessagesCount = $messageModel->getTotalUnreadCount($currentUserId);

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
                    <a href="../nhantin/message.php" class="menu-item" style="position: relative;">
                        <i class="fas fa-comments"></i>
                        <span>Tin nhắn</span>
                        <?php if ($unreadMessagesCount > 0): ?>
                        <span class="notification-badge" id="messagesBadge" style="position: absolute; top: -5px; right: -5px; background: #ff4757; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold;"><?php echo $unreadMessagesCount; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="../timkiem/ghepdoinhanh.php" class="menu-item" style="position: relative;">
                        <i class="fas fa-search"></i>
                        <span>Tìm kiếm</span>
                        <?php if ($newMatchesCount > 0): ?>
                        <span class="notification-badge" id="matchesBadge" style="position: absolute; top: -5px; right: -5px; background: #ff6b9d; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold;"><?php echo $newMatchesCount; ?></span>
                        <?php endif; ?>
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

    <div class="profile-view-wrapper">
        <div class="profile-view-container">
            <button class="back-btn" onclick="window.location.href='../trangchu/index.php';" title="Quay lại">
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

    <!-- Script cập nhật trạng thái online -->
    <script>
        function updateOnlineStatus() {
            fetch('../../controller/cUpdateOnlineStatus.php', {
                method: 'POST'
            }).then(response => response.json())
            .catch(error => console.error('Error updating online status:', error));
        }
        updateOnlineStatus();
        setInterval(updateOnlineStatus, 120000);
        let activityTimeout;
        function resetActivityTimer() {
            clearTimeout(activityTimeout);
            activityTimeout = setTimeout(updateOnlineStatus, 5000);
        }
        ['mousedown', 'keydown', 'scroll', 'touchstart'].forEach(event => {
            document.addEventListener(event, resetActivityTimer, true);
        });
    </script>

    <!-- Script check thông báo real-time -->
    <script>
        let lastNotificationCount = <?php echo ($newMatchesCount + $unreadMessagesCount); ?>;
        function checkNotifications() {
            fetch('../../controller/cCheckNotifications.php', {
                method: 'GET',
                cache: 'no-cache'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const messagesBadge = document.getElementById('messagesBadge');
                    if (data.unreadMessages > 0) {
                        if (messagesBadge) {
                            messagesBadge.textContent = data.unreadMessages;
                        } else {
                            const messagesLink = document.querySelector('a[href="../nhantin/message.php"]');
                            if (messagesLink && !messagesLink.querySelector('.notification-badge')) {
                                const badge = document.createElement('span');
                                badge.id = 'messagesBadge';
                                badge.className = 'notification-badge';
                                badge.textContent = data.unreadMessages;
                                badge.style.cssText = 'position: absolute; top: -5px; right: -5px; background: #ff4757; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold;';
                                messagesLink.appendChild(badge);
                            }
                        }
                    } else if (messagesBadge) {
                        messagesBadge.remove();
                    }
                    const matchesBadge = document.getElementById('matchesBadge');
                    if (data.newMatches > 0) {
                        if (matchesBadge) {
                            matchesBadge.textContent = data.newMatches;
                        } else {
                            const searchLink = document.querySelector('a[href="../timkiem/ghepdoinhanh.php"]');
                            if (searchLink && !searchLink.querySelector('.notification-badge')) {
                                const badge = document.createElement('span');
                                badge.id = 'matchesBadge';
                                badge.className = 'notification-badge';
                                badge.textContent = data.newMatches;
                                badge.style.cssText = 'position: absolute; top: -5px; right: -5px; background: #ff6b9d; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold;';
                                searchLink.appendChild(badge);
                            }
                        }
                    } else if (matchesBadge) {
                        matchesBadge.remove();
                    }
                    const currentTotal = data.unreadMessages + data.newMatches;
                    if (currentTotal > lastNotificationCount) {
                        showNewNotificationAlert(data);
                    }
                    lastNotificationCount = currentTotal;
                }
            })
            .catch(error => console.error('Error checking notifications:', error));
        }
        function showNewNotificationAlert(data) {
            let message = '';
            if (data.unreadMessages > 0 && data.newMatches > 0) {
                message = `💬 ${data.unreadMessages} tin nhắn mới và 💕 ${data.newMatches} ghép đôi mới!`;
            } else if (data.unreadMessages > 0) {
                message = `💬 Bạn có ${data.unreadMessages} tin nhắn mới!`;
            } else if (data.newMatches > 0) {
                message = `💕 Bạn có ${data.newMatches} ghép đôi mới!`;
            }
            if (message) {
                const notification = document.createElement('div');
                notification.style.cssText = `position: fixed; top: 80px; right: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 18px 25px; border-radius: 15px; font-size: 15px; font-weight: 600; box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4); z-index: 10000; cursor: pointer; animation: slideInRight 0.5s ease; max-width: 350px;`;
                notification.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <i class="fas fa-bell" style="font-size: 24px;"></i>
                        <div>
                            <div style="font-size: 16px; margin-bottom: 4px;">${message}</div>
                            <div style="font-size: 12px; opacity: 0.9;">Click để xem ngay →</div>
                        </div>
                    </div>
                `;
                notification.onclick = function() {
                    window.location.href = '../nhantin/message.php';
                };
                document.body.appendChild(notification);
                setTimeout(() => {
                    notification.style.animation = 'slideOutRight 0.3s ease';
                    setTimeout(() => notification.remove(), 300);
                }, 2000);
            }
        }
        setTimeout(checkNotifications, 2000);
        setInterval(checkNotifications, 500);
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) checkNotifications();
        });
        window.addEventListener('focus', checkNotifications);
    </script>
    <style>
        @keyframes slideInRight {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(400px); opacity: 0; }
        }
    </style>
    <script>
        window.addEventListener('beforeunload', function() {
            navigator.sendBeacon('../../controller/cSetOffline.php');
        });
    </script>
</body>
</html>