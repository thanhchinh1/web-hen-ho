<?php
// Set timezone to Vietnam
date_default_timezone_set('Asia/Ho_Chi_Minh');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../../models/mSession.php';
require_once '../../models/mProfile.php';
require_once '../../models/mLike.php';
require_once '../../models/mNotification.php';
require_once '../../models/mUser.php';
require_once '../../models/mVIP.php';

Session::start();

// Kiểm tra đăng nhập
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

// Lấy thông tin hồ sơ người dùng hiện tại
$profileModel = new Profile();
$likeModel = new Like();
$notificationModel = new Notification();
$userModel = new User();
$vipModel = new VIP();
$currentUserProfile = $profileModel->getProfile($currentUserId);

// Đếm số ghép đôi mới (chưa nhắn tin)
$newMatchesCount = $notificationModel->getNewMatchesCount($currentUserId);

// Đếm số tin nhắn chưa đọc
require_once '../../models/mMessage.php';
$messageModel = new Message();
$unreadMessagesCount = $messageModel->getTotalUnreadCount($currentUserId);

// Nếu chưa thiết lập hồ sơ, chuyển đến trang thiết lập
if (!$currentUserProfile) {
    header('Location: ../hoso/thietlaphoso.php');
    exit;
}

// Lấy danh sách ID cần loại trừ
$likedUserIds = $likeModel->getLikedUserIds($currentUserId); // Người mình đã thích
$whoLikedMeIds = $likeModel->getUserIdsWhoLikedMe($currentUserId); // Người đã thích mình

// Thêm người đã block vào danh sách loại trừ
require_once '../../models/mBlock.php';
$blockModel = new Block();
$blockedUserIds = $blockModel->getBlockedUserIds($currentUserId); // Người mình đã chặn
$whoBlockedMeIds = $blockModel->getUserIdsWhoBlockedMe($currentUserId); // Người đã chặn mình

// Thêm người đã ghép đôi vào danh sách loại trừ
require_once '../../models/mMatch.php';
$matchModel = new MatchModel();
$myMatches = $matchModel->getMyMatches($currentUserId);
$matchedUserIds = array_map(function($match) {
    return $match['maNguoiDung'];
}, $myMatches);

// Kết hợp và thêm chính mình vào danh sách loại trừ (bao gồm cả người đã chặn và bị chặn)
$excludeIds = array_unique(array_merge(
    [$currentUserId], 
    $likedUserIds, 
    $whoLikedMeIds, 
    $blockedUserIds, 
    $whoBlockedMeIds, 
    $matchedUserIds
));

// Lấy danh sách hồ sơ để hiển thị 
$allProfiles = $profileModel->getAllProfiles(12, 0, $excludeIds);

// Lấy thông tin giới hạn lượt thích
$likeLimitInfo = $likeModel->canLikeMore($currentUserId);

// Lấy thông báo hệ thống từ admin
$systemNotifications = $notificationModel->getSystemNotifications(3);

// Lấy phản hồi hỗ trợ từ admin
require_once '../../models/mSupport.php';
$supportModel = new Support();
$newRepliesCount = $supportModel->countNewReplies($currentUserId);
$latestReplies = $supportModel->getLatestReplies($currentUserId, 3);

// Lấy flash message nếu có
$successMessage = Session::getFlash('success_message');
$errorMessage = Session::getFlash('error_message');
$infoMessage = Session::getFlash('info_message');

$isVIP = $vipModel->isVIP($currentUserId);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ - DuyenHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/trangchu.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../../public/css/search-modal.css">
    <style>
        /* Ngăn browser tự động scroll về đầu trang khi back */
        html {
            scroll-behavior: auto !important;
        }
    </style>
    <script>
        // Khôi phục scroll position NGAY LẬP TỨC trước khi trang render
        (function() {
            const entries = performance.getEntriesByType('navigation');
            const isBackNavigation = entries.length > 0 && entries[0].type === 'back_forward';
            
            if (isBackNavigation) {
                const savedScrollPosition = sessionStorage.getItem('trangchu_scrollPosition');
                if (savedScrollPosition) {
                    // Áp dụng scroll ngay lập tức
                    history.scrollRestoration = 'manual'; // Tắt auto scroll restoration của browser
                    window.scrollTo(0, parseInt(savedScrollPosition));
                }
            }
        })();
    </script>
</head>
<body>
    <?php if ($successMessage): ?>
    <div id="flashNotification" class="flash-success">
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars($successMessage); ?>
    </div>
    <script>
        setTimeout(() => {
            const notification = document.getElementById('flashNotification');
            if (notification) {
                notification.style.opacity = '0';
                notification.style.transform = 'translate(-50%, -20px)';
                setTimeout(() => notification.remove(), 300);
            }
        }, 4000);
    </script>
    <?php endif; ?>
    
    <?php if ($infoMessage): ?>
    <div id="infoNotification" class="flash-info">
        <i class="fas fa-info-circle"></i>
        <?php echo htmlspecialchars($infoMessage); ?>
    </div>
    <script>
        setTimeout(() => {
            const notification = document.getElementById('infoNotification');
            if (notification) {
                notification.style.opacity = '0';
                notification.style.transform = 'translate(-50%, -20px)';
                setTimeout(() => notification.remove(), 300);
            }
        }, 4000);
    </script>
    <?php endif; ?>
    
    <?php if ($errorMessage): ?>
    <div id="errorNotification" class="flash-error">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo htmlspecialchars($errorMessage); ?>
    </div>
    <script>
        setTimeout(() => {
            const notification = document.getElementById('errorNotification');
            if (notification) {
                notification.style.opacity = '0';
                notification.style.transform = 'translate(-50%, -20px)';
                setTimeout(() => notification.remove(), 300);
            }
        }, 4000);
    </script>
    <?php endif; ?>
    
    <div class="page-wrapper">
    <!-- Header -->
    <header class="main-header">
        <div class="header-container">
            <div class="header-left">
                <a href="../trangchu/index.php" class="logo">
                    <img src="../../public/img/logo.jpg" alt="Kết Nối Yêu Thương">
                    <span class="logo-text">DuyenHub</span>
                </a>
                <nav class="main-nav">
                    <a href="../trangchu/index.php" class="nav-link active">
                        <i class="fas fa-home"></i>
                        Trang chủ
                    </a>
                    <a href="../nhantin/message.php" class="nav-link">
                        <i class="fas fa-comments"></i>
                        Tin nhắn
                        <?php 
                        $totalNotifications = $newMatchesCount + $unreadMessagesCount;
                        if ($totalNotifications > 0): 
                        ?>
                            <span class="notification-badge"><?php echo $totalNotifications; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="#" class="nav-link" onclick="openSearchModal(); return false;">
                        <i class="fas fa-search"></i>
                        Tìm kiếm
                    </a>
                    <a href="#" class="nav-link">
                        <i class="fas fa-question-circle"></i>
                        <span>Trợ giúp</span>
                    </a>
                </nav>
            </div>

            <div class="header-right">
                <a href="#" class="btn-logout" onclick="confirmLogout(event)">
                    <i class="fas fa-sign-out-alt"></i>
                    Đăng Xuất
                </a>
                <div class="user-menu-wrapper">
                    <img src="../../<?php echo htmlspecialchars($currentUserProfile['avt']); ?>" alt="User" class="user-avatar" id="userAvatar">
                    <?php if ($isVIP): ?>
                        <span class="vip-badge-header">VIP</span>
                    <?php endif; ?>
                    <div class="user-dropdown" id="userDropdown" style="display:none;">
                        <a href="../goivip/index.php" class="user-dropdown-item">
                            <i class="fas fa-crown"></i>
                            <span>Nâng cấp tài khoản</span>
                            <span class="vip-badge"></span>
                        </a>
                        <a href="../hoso/index.php" class="user-dropdown-item">
                            <i class="fas fa-user"></i>
                            Xem hồ sơ cá nhân
                        </a>
                        <a href="../hoso/chinhsua.php" class="user-dropdown-item">
                            <i class="fas fa-edit"></i>
                            Chỉnh sửa hồ sơ
                        </a>
                        <a href="../thich/nguoithichban.php" class="user-dropdown-item">
                            <i class="fas fa-heart"></i>
                            <span>Xem danh sách thích bạn</span>
                            <span class="vip-badge"></span>
                        </a>
                        <a href="../thich/nguoibanthich.php" class="user-dropdown-item">
                            <i class="fas fa-user-friends"></i>
                            <span>Xem danh sách người bạn thích</span>
                            <span class="vip-badge"></span>
                        </a>
                        <a href="../chan/danhsachchan.php" class="user-dropdown-item">
                            <i class="fas fa-ban"></i>
                            Danh sách chặn
                        </a>
                        <a href="../taikhoan/doimatkhau.php" class="user-dropdown-item">
                            <i class="fas fa-key"></i>
                            Đổi mật khẩu
                        </a>
                    </div>
                </div>
                <script>
                    const avatar = document.getElementById('userAvatar');
                    const dropdown = document.getElementById('userDropdown');
                    avatar.addEventListener('click', function(e) {
                        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
                    });
                    document.addEventListener('click', function(e) {
                        if (!avatar.contains(e.target) && !dropdown.contains(e.target)) {
                            dropdown.style.display = 'none';
                        }
                    });
                </script>
            </div>
        </div>

    <script>
    function confirmLogout(e) {
        e.preventDefault();
        if (confirm('Bạn có chắc chắn muốn đăng xuất?')) {
            window.location.href = '../../controller/cLogout.php';
        }
    }
    </script>
    </header>

    <!-- Hero Section -->
    <section class="hero-section">
        <img id="heroSlideshow" src="../../public/img/header1.jpg" alt="1Love - Chỉ một tình yêu" class="hero-bg-image">
        <div class="hero-content">
            <div class="hero-text">
                <h1 style="color:#fff;">Tìm kiếm một nửa yêu thương của bạn</h1>
                <p style="color:#fff;">Kết Nối Yêu Thương là nơi bạn có thể tìm thấy những người phù hợp, chia sẻ sở thích và bắt đầu những câu chuyện tình yêu đẹp.</p>
            </div>
        </div>
    </section>

    <!-- System Notifications Section -->
    <script>
    // Danh sách các ảnh header
    //const headerImages = [
        //'../../public/img/header1.jpg',
        //'../../public/img/header3.jpg'

    //];
    let currentHeader = 0;
    setInterval(() => {
        currentHeader = (currentHeader + 1) % headerImages.length;
        const img = document.getElementById('heroSlideshow');
        if (img) {
            img.style.opacity = 0;
            setTimeout(() => {
                img.src = headerImages[currentHeader];
                img.style.opacity = 1;
            }, 400);
        }
    }, 5000);
    </script>
    <style>
    .hero-bg-image {
        transition: opacity 0.4s;
    }
    </style>
    <?php if (!empty($systemNotifications)): ?>
    <section class="notifications-section">
        <div class="section-header">
            <h2><i class="fas fa-bell"></i> Thông báo</h2>
        </div>
        <div class="notifications-carousel-wrapper">
            <div class="notifications-carousel" id="notificationsCarousel">
                <?php foreach ($systemNotifications as $notification): ?>
                    <?php 
                        $iconClass = '';
                        $notifClass = '';
                        switch($notification['loai']) {
                            case 'warning':
                                $iconClass = 'fa-exclamation-triangle';
                                $notifClass = 'warning';
                                break;
                            case 'promotion':
                                $iconClass = 'fa-gift';
                                $notifClass = 'promotion';
                                break;
                            case 'maintenance':
                                $iconClass = 'fa-tools';
                                $notifClass = 'maintenance';
                                break;
                            default:
                                $iconClass = 'fa-info-circle';
                                $notifClass = 'info';
                        }
                    ?>
                    <div class="notification-card <?php echo $notifClass; ?>">
                        <div class="notification-icon">
                            <i class="fas <?php echo $iconClass; ?>"></i>
                        </div>
                        <div class="notification-content">
                            <h3><?php echo htmlspecialchars($notification['tieuDe']); ?></h3>
                            <p><?php echo htmlspecialchars($notification['noiDung']); ?></p>
                            <span class="notification-time">
                                <i class="far fa-clock"></i>
                                <?php 
                                    $time = strtotime($notification['thoiDiemGui'] ?? $notification['thoiDiemTao']);
                                    $now = time();
                                    $diff = $now - $time;
                                    if ($diff < 3600) {
                                        echo floor($diff / 60) . ' phút trước';
                                    } elseif ($diff < 86400) {
                                        echo floor($diff / 3600) . ' giờ trước';
                                    } else {
                                        echo date('d/m/Y', $time);
                                    }
                                ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="carousel-controls">
                <button id="carouselPrev" class="carousel-btn">&#10094;</button>
                <button id="carouselNext" class="carousel-btn">&#10095;</button>
            </div>
        </div>
    </section>
    </div>
    <script>
    // Carousel/slider for notifications
    document.addEventListener('DOMContentLoaded', function() {
        const carousel = document.getElementById('notificationsCarousel');
        const cards = carousel ? carousel.querySelectorAll('.notification-card') : [];
        let current = 0;
        const prevBtn = document.getElementById('carouselPrev');
        const nextBtn = document.getElementById('carouselNext');
        function showCard(idx) {
            cards.forEach((card, i) => {
                // Hiển thị 2 card liên tiếp
                if (cards.length === 1) {
                    card.style.display = (i === idx) ? 'block' : 'none';
                } else {
                    card.style.display = (i === idx || i === (idx + 1) % cards.length) ? 'block' : 'none';
                }
            });
        }
        let repeatCount = 0;
        function nextCard() {
            repeatCount++;
            if (repeatCount < 2) {
                showCard(current);
            } else {
                repeatCount = 0;
                // Tăng current lên 1 (bước nhảy 1, luôn hiển thị 2 card liên tiếp)
                current = (current + 1) % cards.length;
                showCard(current);
            }
        }
        function prevCard() {
            repeatCount = 0;
            current = (current - 1 + cards.length) % cards.length;
            showCard(current);
        }
        if (cards.length > 0) {
            showCard(current);
            if (nextBtn && prevBtn) {
                nextBtn.onclick = nextCard;
                prevBtn.onclick = prevCard;
            }
            setInterval(nextCard, 3000); // Auto slide every 3s, hiển thị 2 thông báo cùng lúc
        }
    });
    </script>
    <?php endif; ?>

    <!-- Profiles Section -->
    <section class="profiles-section">
        <?php if (!$likeLimitInfo['isVIP']): ?>
        <!-- Like Limit Info cho Non-VIP -->
        <div style="
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
        ">
            <div style="display: flex; align-items: center; gap: 20px;">
                <div style="
                    font-size: 48px;
                    background: rgba(255, 255, 255, 0.2);
                    width: 80px;
                    height: 80px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    backdrop-filter: blur(10px);
                ">
                    💝
                </div>
                <div>
                    <h3 style="margin: 0 0 8px 0; font-size: 24px; font-weight: 700;">
                        Lượt thích của bạn
                    </h3>
                    <p style="margin: 0; font-size: 16px; opacity: 0.95;">
                        Bạn còn <strong style="font-size: 20px;"><?php echo $likeLimitInfo['remaining']; ?></strong> / <?php echo $likeLimitInfo['limit']; ?> lượt thích
                    </p>
                </div>
            </div>
            <div>
                <a href="../goivip/index.php" style="
                    display: inline-block;
                    padding: 14px 32px;
                    background: white;
                    color: #667eea;
                    text-decoration: none;
                    border-radius: 50px;
                    font-weight: 700;
                    font-size: 16px;
                    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
                    transition: all 0.3s ease;
                " onmouseover="this.style.transform='translateY(-3px) scale(1.05)'; this.style.boxShadow='0 6px 20px rgba(0, 0, 0, 0.3)'" onmouseout="this.style.transform=''; this.style.boxShadow='0 4px 15px rgba(0, 0, 0, 0.2)'">
                    <i class="fas fa-crown"></i> Nâng cấp VIP - Thích không giới hạn
                </a>
            </div>
        </div>
        <?php else: ?>
        <!-- VIP Badge -->
        <div style="
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 15px 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(240, 147, 251, 0.3);
            display: flex;
            align-items: center;
            gap: 15px;
        ">
            <div style="font-size: 36px;">⭐</div>
            <div>
                <h3 style="margin: 0; font-size: 20px; font-weight: 700;">
                    Tài khoản VIP
                </h3>
                <p style="margin: 0; font-size: 14px; opacity: 0.95;">
                    Bạn có thể thích không giới hạn! 💕
                </p>
            </div>
        </div>
        <?php endif; ?>

        <div class="section-header">
            <h2>Danh sách hồ sơ nổi bật</h2>
            <a href="../timkiem/ghepdoinhanh.php" class="btn-register-cta">
                <i class="fas fa-bolt"></i> Ghép Đôi Nhanh <i class="fas fa-heart"></i>
            </a>
        </div>

        <div class="profiles-grid">
            <?php if (empty($allProfiles)): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                    <div style="font-size: 80px; color: #ddd; margin-bottom: 20px;">
                        <i class="fas fa-heart-broken"></i>
                    </div>
                    <h3 style="color: #7f8c8d; font-size: 24px; margin-bottom: 10px;">Không còn hồ sơ mới</h3>
                    <p style="color: #95a5a6; font-size: 16px;">Bạn đã xem hết tất cả các hồ sơ hiện có. Hãy quay lại sau để khám phá thêm!</p>
                </div>
            <?php else: ?>
                <?php foreach ($allProfiles as $profile): ?>
                    <?php 
                        $age = $profileModel->calculateAge($profile['ngaySinh']);
                        $avatarSrc = !empty($profile['avt']) ? '../../' . htmlspecialchars($profile['avt']) : 'https://i.pravatar.cc/200';
                    ?>
                    <div class="profile-card" data-user-id="<?php echo $profile['maNguoiDung']; ?>" onclick="viewProfile(<?php echo $profile['maNguoiDung']; ?>)">
                        <div class="profile-avatar-wrapper">
                            <img src="<?php echo $avatarSrc; ?>" alt="<?php echo htmlspecialchars($profile['ten']); ?>">
                        </div>
                        <div class="profile-info">
                            <h3><?php echo htmlspecialchars($profile['ten']); ?>, <?php echo $age; ?></h3>
                            <p class="profile-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($profile['noiSong']); ?></p>
                            <p class="profile-status"><?php echo htmlspecialchars($profile['mucTieuPhatTrien']); ?></p>
                        </div>
                        <button class="btn-like" onclick="event.stopPropagation(); likeProfile(<?php echo $profile['maNguoiDung']; ?>)"><i class="fas fa-heart"></i></button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </section>
    <div class="reload-btn-wrapper">
        <button onclick="reloadProfilesAjax();" class="btn-reload">
            Làm mới danh sách
        </button>
    </div>

    <script>
    // Làm mới danh sách hồ sơ nổi bật bằng AJAX, không reload trang
    function reloadProfilesAjax() {
        const btn = document.querySelector('.btn-reload');
        btn.disabled = true;
        btn.textContent = 'Đang làm mới...';
        
        // Reset về trạng thái trang chủ
        isSearchResult = false;
        
        fetch('../../controller/cSearch.php', {
            method: 'POST',
            body: new URLSearchParams({ action: 'random_profiles' })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.profiles) {
                updateProfilesGrid(data.profiles);
                showNotification('Đã làm mới danh sách hồ sơ!', 'success');
            } else {
                showNotification('Không thể làm mới danh sách!', 'error');
            }
        })
        .catch(() => showNotification('Có lỗi xảy ra!', 'error'))
        .finally(() => {
            btn.disabled = false;
            btn.textContent = 'Làm mới danh sách hồ sơ';
        });
    }
    </script>

    <!-- VIP Upgrade Section -->
    <section class="vip-upgrade-section">
        <div class="vip-upgrade-container">
            <div class="vip-header">
                <i class="fas fa-crown vip-crown-icon"></i>
                <h2>Nâng cấp tài khoản VIP</h2>
                <p class="vip-subtitle">Trải nghiệm đầy đủ tính năng cao cấp và tăng cơ hội tìm được nửa kia của bạn</p>
            </div>

            <div class="vip-benefits">
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-heart-pulse"></i>
                    </div>
                    <h3>Ghép đôi thông minh</h3>
                    <p>Thuật toán AI tìm kiếm người phù hợp nhất</p>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-infinity"></i>
                    </div>
                    <h3>Thích không giới hạn</h3>
                    <p>Không giới hạn số lượt thích mỗi ngày</p>
                </div>

            </div>
            <!-- Reload Button Section -->
       

            <div class="vip-pricing-section">
                <h3 class="pricing-title">Chọn gói phù hợp với bạn</h3>
                <div class="pricing-grid">
                    <!-- Gói 1 Tháng -->
                    <div class="pricing-card">
                        <div class="pricing-header">
                            <h4>1 Tháng</h4>
                        </div>
                        <div class="pricing-price">
                            <span class="price">99.000đ</span>
                            <span class="period">/tháng</span>
                        </div>
                        <a href="../goivip/thanhtoan.php?package=1" class="btn-select-package btn-select-package-one">
                            <i class="fas fa-crown"></i>
                            Nâng cấp ngay
                        </a>

                    </div>

                    <!-- Gói 3 Tháng - Phổ biến -->
                    <div class="pricing-card popular">
                        <div class="badge-popular">
                            <i class="fas fa-fire"></i> Phổ biến nhất
                        </div>
                        <div class="pricing-header">
                            <h4>3 Tháng</h4>
                        </div>
                        <div class="pricing-price">
                            <span class="price">249.000đ</span>
                            <span class="period">/3 tháng</span>
                        </div>
                        <div class="pricing-save">
                            <i class="fas fa-tag"></i> Tiết kiệm 16%
                        </div>
                        <a href="../goivip/thanhtoan.php?package=3" class="btn-select-package">
                            <i class="fas fa-crown"></i>
                            Nâng cấp ngay
                        </a>
                    </div>

                    <!-- Gói 6 Tháng -->
                    <div class="pricing-card">
                        <div class="pricing-header">
                            <h4>6 Tháng</h4>
                        </div>
                        <div class="pricing-price">
                            <span class="price">449.000đ</span>
                            <span class="period">/6 tháng</span>
                        </div>
                        <div class="pricing-save">
                            <i class="fas fa-tag"></i> Tiết kiệm 24%
                        </div>
                        <a href="../goivip/thanhtoan.php?package=6" class="btn-select-package">
                            <i class="fas fa-crown"></i>
                            Nâng cấp ngay
                        </a>
                    </div>

                    <!-- Gói 12 Tháng - Giá trị nhất -->
                    <div class="pricing-card best-value">
                        <div class="badge-best">
                            <i class="fas fa-star"></i> Giá trị nhất
                        </div>
                        <div class="pricing-header">
                            <h4>12 Tháng</h4>
                        </div>
                        <div class="pricing-price">
                            <span class="price">799.000đ</span>
                            <span class="period">/năm</span>
                        </div>
                        <div class="pricing-save">
                            <i class="fas fa-tag"></i> Tiết kiệm 33%
                        </div>
                        <a href="../goivip/thanhtoan.php?package=12" class="btn-select-package">
                            <i class="fas fa-crown"></i>
                            Nâng cấp ngay
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-top">
                <div class="footer-links">
                    <a href="../pages/about.php">Về chúng tôi</a>
                    <a href="../pages/support.php">Hỗ trợ</a>
                    <a href="../pages/lagel.php">Pháp lý</a>
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

    <!-- Search Modal -->
    <div class="search-modal-overlay" id="searchModal">
        <div class="search-modal">
            <div class="modal-header">
                <h2>Tìm kiếm </h2>
                <button class="modal-close" onclick="closeSearchModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">

                <form id="searchForm">
                    <div class="modal-form-grid">
                        <div class="modal-form-group">
                            <label>Giới tính</label>
                            <select class="modal-form-select" id="gender">
                                <option value="">Tất cả</option>
                                <option value="Nam">Nam</option>
                                <option value="Nữ">Nữ</option>
                            </select>
                        </div>

                        <div class="modal-form-group">
                            <label>Tình trạng hôn nhân</label>
                            <select class="modal-form-select" id="status">
                                <option value="">Tất cả</option>
                                <option value="Độc thân">Độc thân</option>
                                <option value="Đã ly hôn">Đã ly hôn</option>
                                <option value="Mẹ đơn thân">Mẹ đơn thân</option>
                                <option value="Cha đơn thân">Cha đơn thân</option>
                            </select>
                        </div>

                        <div class="modal-form-group">
                            <label>Mục tiêu phát triển</label>
                            <select class="modal-form-select" id="purpose">
                                <option value="">Tất cả</option>
                                <option value="Hẹn hò">Hẹn hò</option>
                                <option value="Kết bạn">Kết bạn</option>
                                <option value="Kết hôn">Kết hôn</option>
                                <option value="Tìm hiểu">Tìm hiểu</option>
                            </select>
                        </div>

                        <div class="modal-form-group">
                            <label>Nơi sống</label>
                            <select class="modal-form-select" id="city">
                                <option value="">Tất cả</option>
                                <option value="TP Hồ Chí Minh">TP Hồ Chí Minh</option>
                                <option value="Hà Nội">Hà Nội</option>
                                <option value="Đà Nẵng">Đà Nẵng</option>
                                <option value="Hải Phòng">Hải Phòng</option>
                                <option value="Cần Thơ">Cần Thơ</option>
                                <option value="An Giang">An Giang</option>
                                <option value="Bà Rịa - Vũng Tàu">Bà Rịa - Vũng Tàu</option>
                                <option value="Bắc Giang">Bắc Giang</option>
                                <option value="Bắc Kạn">Bắc Kạn</option>
                                <option value="Bạc Liêu">Bạc Liêu</option>
                                <option value="Bắc Ninh">Bắc Ninh</option>
                                <option value="Bến Tre">Bến Tre</option>
                                <option value="Bình Định">Bình Định</option>
                                <option value="Bình Dương">Bình Dương</option>
                                <option value="Bình Phước">Bình Phước</option>
                                <option value="Bình Thuận">Bình Thuận</option>
                                <option value="Cà Mau">Cà Mau</option>
                                <option value="Cao Bằng">Cao Bằng</option>
                                <option value="Đắk Lắk">Đắk Lắk</option>
                                <option value="Đắk Nông">Đắk Nông</option>
                                <option value="Điện Biên">Điện Biên</option>
                                <option value="Đồng Nai">Đồng Nai</option>
                                <option value="Đồng Tháp">Đồng Tháp</option>
                                <option value="Gia Lai">Gia Lai</option>
                                <option value="Hà Giang">Hà Giang</option>
                                <option value="Hà Nam">Hà Nam</option>
                                <option value="Hà Tĩnh">Hà Tĩnh</option>
                                <option value="Hải Dương">Hải Dương</option>
                                <option value="Hậu Giang">Hậu Giang</option>
                                <option value="Hòa Bình">Hòa Bình</option>
                                <option value="Hưng Yên">Hưng Yên</option>
                                <option value="Khánh Hòa">Khánh Hòa</option>
                                <option value="Kiên Giang">Kiên Giang</option>
                                <option value="Kon Tum">Kon Tum</option>
                                <option value="Lai Châu">Lai Châu</option>
                                <option value="Lâm Đồng">Lâm Đồng</option>
                                <option value="Lạng Sơn">Lạng Sơn</option>
                                <option value="Lào Cai">Lào Cai</option>
                                <option value="Long An">Long An</option>
                                <option value="Nam Định">Nam Định</option>
                                <option value="Nghệ An">Nghệ An</option>
                                <option value="Ninh Bình">Ninh Bình</option>
                                <option value="Ninh Thuận">Ninh Thuận</option>
                                <option value="Phú Thọ">Phú Thọ</option>
                                <option value="Phú Yên">Phú Yên</option>
                                <option value="Quảng Bình">Quảng Bình</option>
                                <option value="Quảng Nam">Quảng Nam</option>
                                <option value="Quảng Ngãi">Quảng Ngãi</option>
                                <option value="Quảng Ninh">Quảng Ninh</option>
                                <option value="Quảng Trị">Quảng Trị</option>
                                <option value="Sóc Trăng">Sóc Trăng</option>
                                <option value="Sơn La">Sơn La</option>
                                <option value="Tây Ninh">Tây Ninh</option>
                                <option value="Thái Bình">Thái Bình</option>
                                <option value="Thái Nguyên">Thái Nguyên</option>
                                <option value="Thanh Hóa">Thanh Hóa</option>
                                <option value="Thừa Thiên Huế">Thừa Thiên Huế</option>
                                <option value="Tiền Giang">Tiền Giang</option>
                                <option value="Trà Vinh">Trà Vinh</option>
                                <option value="Tuyên Quang">Tuyên Quang</option>
                                <option value="Vĩnh Long">Vĩnh Long</option>
                                <option value="Vĩnh Phúc">Vĩnh Phúc</option>
                                <option value="Yên Bái">Yên Bái</option>
                            </select>
                        </div>

                        <div class="modal-form-group">
                            <label>Tuổi</label>
                            <select class="modal-form-select" id="age">
                                <option value="">Tất cả</option>
                                <option value="18-25">18 - 25 tuổi</option>
                                <option value="26-30">26 - 30 tuổi</option>
                                <option value="31-35">31 - 35 tuổi</option>
                                <option value="36-40">36 - 40 tuổi</option>
                                <option value="41-50">41 - 50 tuổi</option>
                                <option value="51-100">Trên 50 tuổi</option>
                            </select>
                        </div>
                    </div>

                    <!-- Interests Section -->
                    <div class="modal-interests-section">
                        <h3>Sở thích (chọn nhiều)</h3>
                        <div style="margin-bottom: 10px;">
                            <label class="interest-checkbox" style="font-weight:600;">
                                <input type="checkbox" id="all-interests-checkbox"> Tất cả sở thích
                            </label>
                        </div>
                        <div class="interests-grid" id="interestsGrid">
                            <label class="interest-checkbox">
                                <input type="checkbox" value="Đọc sách">
                                <label>Đọc sách</label>
                            </label>
                            <label class="interest-checkbox">
                                <input type="checkbox" value="Xem phim">
                                <label>Xem phim</label>
                            </label>
                            <label class="interest-checkbox">
                                <input type="checkbox" value="Nghe nhạc">
                                <label>Nghe nhạc</label>
                            </label>
                            <label class="interest-checkbox">
                                <input type="checkbox" value="Du lịch">
                                <label>Du lịch</label>
                            </label>
                            <label class="interest-checkbox">
                                <input type="checkbox" value="Thể thao">
                                <label>Thể thao</label>
                            </label>
                            <label class="interest-checkbox">
                                <input type="checkbox" value="Nấu ăn">
                                <label>Nấu ăn</label>
                            </label>
                            <label class="interest-checkbox">
                                <input type="checkbox" value="Chụp ảnh">
                                <label>Chụp ảnh</label>
                            </label>
                            <label class="interest-checkbox">
                                <input type="checkbox" value="Học ngoại ngữ">
                                <label>Học ngoại ngữ</label>
                            </label>
                            <label class="interest-checkbox">
                                <input type="checkbox" value="Làm vườn">
                                <label>Làm vườn</label>
                            </label>
                            <label class="interest-checkbox">
                                <input type="checkbox" value="Chơi game">
                                <label>Chơi game</label>
                            </label>
                            <label class="interest-checkbox">
                                <input type="checkbox" value="Thiền">
                                <label>Thiền</label>
                            </label>
                            <label class="interest-checkbox">
                                <input type="checkbox" value="Vẽ">
                                <label>Vẽ</label>
                            </label>
                            <label class="interest-checkbox">
                                <input type="checkbox" value="Khiêu vũ">
                                <label>Khiêu vũ</label>
                            </label>
                            <label class="interest-checkbox">
                                <input type="checkbox" value="Ca hát">
                                <label>Ca hát</label>
                            </label>
                            <label class="interest-checkbox">
                                <input type="checkbox" value="Tập gym">
                                <label>Tập gym</label>
                            </label>
                            <label class="interest-checkbox">
                                <input type="checkbox" value="Bơi lội">
                                <label>Bơi lội</label>
                            </label>
                            <label class="interest-checkbox">
                                <input type="checkbox" value="Leo núi">
                                <label>Leo núi</label>
                            </label>
                            <label class="interest-checkbox">
                                <input type="checkbox" value="Cắm trại">
                                <label>Cắm trại</label>
                            </label>
                            <label class="interest-checkbox">
                                <input type="checkbox" value="Mua sắm">
                                <label>Mua sắm</label>
                            </label>
                            <label class="interest-checkbox">
                                <input type="checkbox" value="Thời trang">
                                <label>Thời trang</label>
                            </label>
                        </div>
                    </div>
    <script>
    // Chức năng chọn tất cả sở thích
    document.addEventListener('DOMContentLoaded', function() {
        const allCheckbox = document.getElementById('all-interests-checkbox');
        const interestsGrid = document.getElementById('interestsGrid');
        if (allCheckbox && interestsGrid) {
            allCheckbox.addEventListener('change', function() {
                const checkboxes = interestsGrid.querySelectorAll('input[type="checkbox"]');
                checkboxes.forEach(cb => cb.checked = allCheckbox.checked);
            });
            // Nếu tất cả đều được chọn thủ công thì cũng check vào "Tất cả"
            interestsGrid.addEventListener('change', function() {
                const checkboxes = interestsGrid.querySelectorAll('input[type="checkbox"]');
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                allCheckbox.checked = allChecked;
            });
        }
    });
    </script>

                    <div class="modal-actions">
                        <button type="button" class="modal-btn modal-btn-search" onclick="performSearch()">Tìm kiếm</button>
                        <button type="button" class="modal-btn modal-btn-close" onclick="closeSearchModal()">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Biến theo dõi trạng thái hiển thị
        let isSearchResult = false; // false = trang chủ, true = kết quả tìm kiếm

        // Open search modal
        function openSearchModal() {
            document.getElementById('searchModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        // Close search modal
        function closeSearchModal() {
            document.getElementById('searchModal').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        document.getElementById('searchModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeSearchModal();
            }
        });

        // Perform search
        function performSearch() {
            const gender = document.getElementById('gender').value;
            const status = document.getElementById('status').value;
            const purpose = document.getElementById('purpose').value;
            const city = document.getElementById('city').value;
            const age = document.getElementById('age').value;
            
            // Lấy tất cả sở thích đã chọn
            const interestCheckboxes = document.querySelectorAll('.interest-checkbox input[type="checkbox"]:checked');
            const interests = Array.from(interestCheckboxes).map(cb => cb.value);

            // Show loading notification
            const loadingNotif = document.createElement('div');
            loadingNotif.id = 'loadingNotif';
            loadingNotif.innerHTML = `
                <div style="
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background: white;
                    padding: 30px 50px;
                    border-radius: 15px;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                    z-index: 10001;
                    text-align: center;
                ">
                    <div style="font-size: 48px; color: #5BC0DE; margin-bottom: 15px;">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                    <h3 style="margin: 0; color: #2C3E50;">Đang tìm kiếm...</h3>
                </div>
                <div style="
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0,0,0,0.5);
                    z-index: 10000;
                "></div>
            `;
            document.body.appendChild(loadingNotif);

            // Gửi request AJAX
            const formData = new FormData();
            formData.append('action', 'search');
            formData.append('gender', gender);
            formData.append('status', status);
            formData.append('purpose', purpose);
            formData.append('city', city);
            formData.append('age', age);
            formData.append('interests', JSON.stringify(interests));
            
            fetch('../../controller/cSearch.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Xóa loading
                document.getElementById('loadingNotif').remove();
                
                if (data.success && data.count > 0) {
                    // Đóng modal
                    closeSearchModal();
                    
                    // Đánh dấu đang xem kết quả tìm kiếm
                    isSearchResult = true;
                    
                    // Cập nhật grid với kết quả tìm kiếm
                    updateProfilesGrid(data.profiles);
                    
                    // Hiển thị thông báo thành công
                    showNotification(data.message, 'success');
                } else {
                    // Không tìm thấy kết quả
                    showNotification(data.message || 'Không tìm thấy kết quả phù hợp!', 'warning');
                }
            })
            .catch(error => {
                document.getElementById('loadingNotif').remove();
                console.error('Error:', error);
                showNotification('Có lỗi xảy ra, vui lòng thử lại!', 'error');
            });
        }

        // Cập nhật grid hiển thị kết quả
        function updateProfilesGrid(profiles) {
            const grid = document.querySelector('.profiles-grid');
            grid.innerHTML = '';
            
            // Cập nhật text nút "Làm mới" nếu cần
            const reloadBtn = document.querySelector('.btn-reload');
            if (reloadBtn) {
                if (isSearchResult) {
                    reloadBtn.textContent = 'Quay về trang chủ';
                } else {
                    reloadBtn.textContent = 'Làm mới danh sách';
                }
            }
            
            // Kiểm tra nếu không có hồ sơ nào
            if (profiles.length === 0) {
                grid.innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                        <div style="font-size: 80px; color: #ddd; margin-bottom: 20px;">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3 style="color: #7f8c8d; font-size: 24px; margin-bottom: 10px;">
                            ${isSearchResult ? 'Đã xem hết kết quả tìm kiếm' : 'Không còn hồ sơ mới'}
                        </h3>
                        <p style="color: #95a5a6; font-size: 16px;">
                            ${isSearchResult ? 'Bạn đã xem hết tất cả kết quả. Thử tìm kiếm với tiêu chí khác hoặc quay về trang chủ!' : 'Bạn đã xem hết tất cả các hồ sơ hiện có. Hãy quay lại sau để khám phá thêm!'}
                        </p>
                    </div>
                `;
                return;
            }
            
            profiles.forEach(profile => {
                const card = document.createElement('div');
                card.className = 'profile-card';
                card.setAttribute('data-user-id', profile.id);
                card.onclick = () => viewProfile(profile.id);
                
                const avatarSrc = profile.avatar.startsWith('public/') ? 
                    '../../' + profile.avatar : 
                    profile.avatar;
                
                card.innerHTML = `
                    <div class="profile-avatar-wrapper">
                        <img src="${avatarSrc}" alt="${profile.name}">
                    </div>
                    <div class="profile-info">
                        <h3>${profile.name}, ${profile.age}</h3>
                        <p class="profile-location"><i class="fas fa-map-marker-alt"></i> ${profile.location}</p>
                        <p class="profile-status">${profile.goal}</p>
                    </div>
                    <button class="btn-like" onclick="event.stopPropagation(); likeProfile(${profile.id})">
                        <i class="fas fa-heart"></i>
                    </button>
                `;
                
                grid.appendChild(card);
            });
            
            // Scroll to results
            grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // Hiển thị thông báo
        function showNotification(message, type = 'info') {
            const colors = {
                success: '#28a745',
                error: '#dc3545',
                warning: '#ffc107',
                info: '#17a2b8'
            };
            
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };
            
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 100px;
                left: 50%;
                transform: translateX(-50%);
                background: ${colors[type]};
                color: white;
                padding: 15px 30px;
                border-radius: 25px;
                font-size: 16px;
                font-weight: 600;
                box-shadow: 0 5px 20px rgba(0,0,0,0.3);
                z-index: 10002;
                animation: slideDown 0.3s ease;
            `;
            notification.innerHTML = `<i class="fas ${icons[type]}" style="margin-right: 8px;"></i>${message}`;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translate(-50%, -20px)';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // View profile
        function viewProfile(userId) {
            // Lưu vị trí cuộn hiện tại vào sessionStorage
            sessionStorage.setItem('trangchu_scrollPosition', window.pageYOffset || document.documentElement.scrollTop);
            
            // Lưu danh sách hồ sơ hiện tại
            const profilesGrid = document.querySelector('.profiles-grid');
            if (profilesGrid) {
                sessionStorage.setItem('trangchu_profilesHTML', profilesGrid.innerHTML);
            }
            
            window.location.href = '../hoso/xemnguoikhac.php?id=' + userId;
        }

        // Load more profiles
        function loadMoreProfiles(count = 1) {
            fetch('../../controller/cLoadMoreProfiles.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'count=' + count
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.profiles.length > 0) {
                    const profilesGrid = document.querySelector('.profiles-grid');
                    
                    data.profiles.forEach((profile, index) => {
                        // Tạo profile card mới
                        const card = document.createElement('div');
                        card.className = 'profile-card';
                        card.setAttribute('data-user-id', profile.maNguoiDung);
                        card.onclick = function() { viewProfile(profile.maNguoiDung); };
                        
                        card.innerHTML = `
                            <div class="profile-avatar-wrapper">
                                <img src="../../${profile.avt}" alt="${profile.ten}">
                            </div>
                            <div class="profile-info">
                                <h3>${profile.ten}, ${profile.tuoi}</h3>
                                <p class="profile-location"><i class="fas fa-map-marker-alt"></i> ${profile.noiSong}</p>
                                <p class="profile-status">${profile.mucTieuPhatTrien}</p>
                            </div>
                            <button class="btn-like" onclick="event.stopPropagation(); likeProfile(${profile.maNguoiDung})"><i class="fas fa-heart"></i></button>
                        `;
                        
                        // Animation mượt mà hơn với cubic-bezier và slide from bottom
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(30px) scale(0.95)';
                        profilesGrid.appendChild(card);
                        
                        // Trigger animation với delay nhỏ cho mỗi card
                        setTimeout(() => {
                            card.style.transition = 'all 0.8s cubic-bezier(0.34, 1.56, 0.64, 1)';
                            card.style.opacity = '1';
                            card.style.transform = 'translateY(0) scale(1)';
                        }, 150 + (index * 100));
                    });
                } else if (data.success && data.profiles.length === 0) {
                    // Không còn hồ sơ để hiển thị - không làm gì cả
                    console.log('Không còn hồ sơ mới để hiển thị');
                }
            })
            .catch(error => {
                console.error('Error loading more profiles:', error);
            });
        }

        // Like button with AJAX
        function likeProfile(userId) {
            // Hiển thị loading
            const notification = document.createElement('div');
            notification.innerHTML = `
                <div style="
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background: white;
                    padding: 30px 50px;
                    border-radius: 15px;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                    z-index: 10000;
                    text-align: center;
                ">
                    <div style="font-size: 48px; color: #5BC0DE; margin-bottom: 15px;">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                    <h3 style="margin: 0; color: #2C3E50;">Đang xử lý...</h3>
                </div>
                <div style="
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0,0,0,0.5);
                    z-index: 9999;
                "></div>
            `;
            document.body.appendChild(notification);
            
            // Gửi request AJAX với CSRF token
            fetch('../../controller/cLike.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=like&targetUserId=' + userId + '&csrf_token=<?php echo Session::getCSRFToken(); ?>'
            })
            .then(response => response.json())
            .then(data => {
                notification.remove();
                
                if (data.success) {
                    // Kiểm tra có ghép đôi thành công không
                    if (data.matched) {
                        // Hiển thị thông báo ghép đôi thành công
                        const matchNotif = document.createElement('div');
                        matchNotif.innerHTML = `
                            <div style="
                                position: fixed;
                                top: 50%;
                                left: 50%;
                                transform: translate(-50%, -50%);
                                background: white;
                                padding: 40px 60px;
                                border-radius: 20px;
                                box-shadow: 0 15px 50px rgba(0,0,0,0.3);
                                z-index: 10000;
                                text-align: center;
                                max-width: 400px;
                            ">
                                <div style="font-size: 60px; margin-bottom: 20px;">
                                    🎉💕
                                </div>
                                <h2 style="margin: 0 0 15px 0; color: #FF6B9D; font-size: 28px;">
                                    ${data.message}
                                </h2>
                                <p style="margin: 0; color: #7F8C8D; font-size: 16px;">
                                    Đang chuyển đến trang trò chuyện...
                                </p>
                            </div>
                            <div style="
                                position: fixed;
                                top: 0;
                                left: 0;
                                right: 0;
                                bottom: 0;
                                background: rgba(0,0,0,0.6);
                                z-index: 9999;
                            "></div>
                        `;
                        document.body.appendChild(matchNotif);
                        
                        // Chuyển đến trang chat sau 2 giây
                        setTimeout(() => {
                            window.location.href = '../../views/nhantin/message.php?matchId=' + data.matchId;
                        }, 2000);
                    } else {
                        // Chỉ thích thôi, chưa ghép đôi
                        const successNotif = document.createElement('div');
                        successNotif.innerHTML = `
                            <div style="
                                position: fixed;
                                top: 50%;
                                left: 50%;
                                transform: translate(-50%, -50%);
                                background: white;
                                padding: 30px 50px;
                                border-radius: 15px;
                                box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                                z-index: 10000;
                                text-align: center;
                            ">
                                <div style="font-size: 48px; color: #FF6B9D; margin-bottom: 15px;">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <h3 style="margin: 0; color: #2C3E50;">${data.message}</h3>
                            </div>
                            <div style="
                                position: fixed;
                                top: 0;
                                left: 0;
                                right: 0;
                                bottom: 0;
                                background: rgba(0,0,0,0.5);
                                z-index: 9999;
                            "></div>
                        `;
                        document.body.appendChild(successNotif);
                        
                        // Cập nhật số lượt thích còn lại nếu có
                        if (data.remaining !== undefined && data.remaining !== null) {
                            updateLikeCounter(data.remaining);
                        }
                        
                        // Thêm fade out animation cho notification
                        setTimeout(() => {
                            successNotif.style.transition = 'opacity 0.3s ease';
                            successNotif.style.opacity = '0';
                        }, 550);
                        
                        setTimeout(() => {
                            successNotif.remove();
                            // Xóa profile card khỏi DOM thay vì reload trang
                            const profileCard = document.querySelector('.profile-card[data-user-id="' + userId + '"]');
                            if (profileCard) {
                                // Animation mượt mà hơn: fade out + slide up + scale
                                profileCard.style.transition = 'all 0.45s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
                                profileCard.style.opacity = '0';
                                profileCard.style.transform = 'translateY(-30px) scale(0.85) rotateX(10deg)';
                                profileCard.style.filter = 'blur(3px)';
                                
                                // Xóa card sau khi animation hoàn tất
                                setTimeout(() => {
                                    profileCard.remove();
                                    
                                    // CHỈ tải thêm hồ sơ mới khi KHÔNG phải kết quả tìm kiếm
                                    if (!isSearchResult) {
                                        setTimeout(() => loadMoreProfiles(1), 100);
                                    }
                                }, 300);
                            }
                        }, 850);
                    }
                } else {
                    // Xử lý lỗi
                    if (data.limitReached && data.requireVIP) {
                        // Hết lượt thích - hiển thị thông báo nâng cấp VIP
                        const vipNotif = document.createElement('div');
                        vipNotif.innerHTML = `
                            <div style="
                                position: fixed;
                                top: 50%;
                                left: 50%;
                                transform: translate(-50%, -50%);
                                background: white;
                                padding: 40px 50px;
                                border-radius: 20px;
                                box-shadow: 0 15px 50px rgba(0,0,0,0.3);
                                z-index: 10000;
                                text-align: center;
                                max-width: 450px;
                            ">
                                <div style="font-size: 60px; margin-bottom: 20px;">
                                    ⭐💎
                                </div>
                                <h2 style="margin: 0 0 15px 0; color: #FF6B9D; font-size: 24px;">
                                    Bạn đã hết lượt thích!
                                </h2>
                                <p style="margin: 0 0 25px 0; color: #7F8C8D; font-size: 16px; line-height: 1.6;">
                                    Bạn đã sử dụng hết <strong>${data.limit} lượt thích</strong> miễn phí.<br>
                                    Nâng cấp VIP để thích <strong>không giới hạn</strong>!
                                </p>
                                <div style="display: flex; gap: 15px; justify-content: center;">
                                    <button onclick="closeVIPNotification()" style="
                                        padding: 12px 30px;
                                        border: 2px solid #95A5A6;
                                        background: white;
                                        color: #7F8C8D;
                                        border-radius: 25px;
                                        font-size: 16px;
                                        font-weight: 600;
                                        cursor: pointer;
                                        transition: all 0.3s ease;
                                    " onmouseover="this.style.background='#ECF0F1'" onmouseout="this.style.background='white'">
                                        Để sau
                                    </button>
                                    <button onclick="window.location.href='../goivip/index.php'" style="
                                        padding: 12px 30px;
                                        border: none;
                                        background: linear-gradient(135deg, #FF6B9D, #FF4D6D);
                                        color: white;
                                        border-radius: 25px;
                                        font-size: 16px;
                                        font-weight: 600;
                                        cursor: pointer;
                                        transition: all 0.3s ease;
                                        box-shadow: 0 4px 15px rgba(255, 107, 157, 0.3);
                                    " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(255, 107, 157, 0.4)'" onmouseout="this.style.transform=''; this.style.boxShadow='0 4px 15px rgba(255, 107, 157, 0.3)'">
                                        Nâng cấp VIP ⭐
                                    </button>
                                </div>
                            </div>
                            <div onclick="closeVIPNotification()" style="
                                position: fixed;
                                top: 0;
                                left: 0;
                                right: 0;
                                bottom: 0;
                                background: rgba(0,0,0,0.6);
                                z-index: 9999;
                                cursor: pointer;
                            "></div>
                        `;
                        vipNotif.id = 'vipNotification';
                        document.body.appendChild(vipNotif);
                    } else {
                        alert(data.message);
                    }
                }
            })
            .catch(error => {
                notification.remove();
                console.error('Error:', error);
                alert('Có lỗi xảy ra, vui lòng thử lại!');
            });
        }
        
        // Function đóng VIP notification
        function closeVIPNotification() {
            const vipNotif = document.getElementById('vipNotification');
            if (vipNotif) {
                vipNotif.style.transition = 'opacity 0.3s ease';
                vipNotif.style.opacity = '0';
                setTimeout(() => vipNotif.remove(), 300);
            }
        }
        
        // Function cập nhật số lượt thích còn lại
        function updateLikeCounter(remaining) {
            // Tìm phần tử hiển thị số lượt thích còn lại
            const counterElement = document.querySelector('p > strong[style*="font-size: 20px"]');
            if (counterElement) {
                counterElement.textContent = remaining;
                
                // Hiệu ứng pulse khi cập nhật
                counterElement.style.transition = 'all 0.3s ease';
                counterElement.style.transform = 'scale(1.3)';
                counterElement.style.color = '#FFD700';
                
                setTimeout(() => {
                    counterElement.style.transform = 'scale(1)';
                    counterElement.style.color = '';
                }, 300);
                
                // Nếu hết lượt thích, reload trang để hiển thị thông báo
                if (remaining <= 0) {
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                }
            }
        }
    </script>

    <!-- Script cập nhật trạng thái online -->
    <script>
        // Cập nhật trạng thái online mỗi 2 phút
        function updateOnlineStatus() {
            fetch('../../controller/cUpdateOnlineStatus.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Online status updated:', data.timestamp);
                }
            })
            .catch(error => {
                console.error('Error updating online status:', error);
            });
        }

        // Cập nhật ngay khi trang load
        updateOnlineStatus();

        // Cập nhật mỗi 2 phút (120000ms)
        setInterval(updateOnlineStatus, 120000);

        // Cập nhật khi user tương tác
        let activityTimeout;
        function resetActivityTimer() {
            clearTimeout(activityTimeout);
            activityTimeout = setTimeout(updateOnlineStatus, 5000);
        }

        // Lắng nghe các sự kiện tương tác
        ['mousedown', 'keydown', 'scroll', 'touchstart'].forEach(event => {
            document.addEventListener(event, resetActivityTimer, true);
        });
    </script>

    <!-- Script check thông báo real-time -->
    <script>
        let lastNotificationCount = <?php echo ($newMatchesCount + $unreadMessagesCount); ?>;
        
        // Check và cập nhật số thông báo mới
        function checkNotifications() {
            fetch('../../controller/cCheckNotifications.php', {
                method: 'GET',
                cache: 'no-cache'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const badge = document.querySelector('.nav-link[href="../nhantin/message.php"] .notification-badge');
                    const navLink = document.querySelector('.nav-link[href="../nhantin/message.php"]');
                    
                    // Kiểm tra nếu có thông báo mới (số tăng lên)
                    if (data.total > lastNotificationCount && lastNotificationCount >= 0) {
                        showNewNotificationAlert(data);
                    }
                    
                    lastNotificationCount = data.total;
                    
                    if (data.total > 0) {
                        // Có thông báo mới
                        if (badge) {
                            // Cập nhật số
                            if (badge.textContent !== data.total.toString()) {
                                badge.textContent = data.total;
                                // Animation nhấp nháy khi có thông báo mới
                                badge.style.animation = 'none';
                                setTimeout(() => {
                                    badge.style.animation = 'pulse 1s ease-in-out 3';
                                }, 10);
                            }
                        } else {
                            // Tạo badge mới nếu chưa có
                            const newBadge = document.createElement('span');
                            newBadge.className = 'notification-badge';
                            newBadge.textContent = data.total;
                            newBadge.style.animation = 'pulse 1s ease-in-out 3';
                            navLink.appendChild(newBadge);
                        }
                    } else {
                        // Không có thông báo, xóa badge
                        if (badge) {
                            badge.remove();
                        }
                    }
                    
                    console.log('Notifications checked:', data);
                }
            })
            .catch(error => {
                console.error('Error checking notifications:', error);
            });
        }
        
        // Hiển thị thông báo popup khi có tin nhắn/match mới
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
                notification.style.cssText = `
                    position: fixed;
                    top: 80px;
                    right: 20px;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 18px 25px;
                    border-radius: 15px;
                    font-size: 15px;
                    font-weight: 600;
                    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
                    z-index: 10000;
                    cursor: pointer;
                    animation: slideInRight 0.5s ease;
                    max-width: 350px;
                `;
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
                
                // Tự động ẩn sau 2 giây
                setTimeout(() => {
                    notification.style.animation = 'slideOutRight 0.3s ease';
                    setTimeout(() => notification.remove(), 300);
                }, 2000);
            }
        }

        // Check ngay khi trang load (sau 0.5 giây)
        setTimeout(checkNotifications, 500);

        // Check mỗi 0.5 giây (500ms) - REAL-TIME TỨC THÌ!
        setInterval(checkNotifications, 500);

        // Check khi user quay lại tab (visibility change)
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                checkNotifications();
            }
        });

        // Check khi user focus vào window
        window.addEventListener('focus', checkNotifications);
    </script>

    <style>
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.2);
                opacity: 0.8;
            }
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
    </style>

    <!-- Contact Admin Modal -->
    <div id="contactAdminModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.7); z-index:10000; align-items:center; justify-content:center; overflow-y:auto;">
        <div style="background:#fff; border-radius:20px; padding:40px; max-width:600px; width:90%; position:relative; box-shadow:0 10px 40px rgba(0,0,0,0.3); margin:20px;">
            <button onclick="closeContactAdmin()" style="position:absolute; top:15px; right:15px; background:transparent; border:none; font-size:28px; cursor:pointer; color:#999; transition:color 0.3s;">&times;</button>
            
            <div style="text-align:center; margin-bottom:25px;">
                <i class="fas fa-headset" style="font-size:50px; color:#FF6B9D; margin-bottom:15px;"></i>
                <h2 style="color:#2c3e50; font-size:24px; font-weight:600; margin-bottom:10px;">Liên hệ với Admin</h2>
                <p style="color:#7f8c8d; font-size:14px;">Gửi yêu cầu hỗ trợ hoặc liên hệ trực tiếp</p>
            </div>

            <!-- Form gửi yêu cầu hỗ trợ -->
            <form id="supportForm" style="margin-bottom:25px;">
                <div style="margin-bottom:20px;">
                    <label style="display:block; margin-bottom:8px; color:#2c3e50; font-weight:500; font-size:14px;">
                        <i class="fas fa-tag" style="color:#FF6B9D; margin-right:5px;"></i>Loại yêu cầu
                    </label>
                    <select name="type" style="width:100%; padding:12px; border:2px solid #e0e0e0; border-radius:10px; font-size:14px; font-family:'Poppins', sans-serif; transition:all 0.3s;">
                        <option value="general">Câu hỏi chung</option>
                        <option value="payment">Thanh toán</option>
                        <option value="technical">Kỹ thuật</option>
                        <option value="report">Báo cáo</option>
                        <option value="other">Khác</option>
                    </select>
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block; margin-bottom:8px; color:#2c3e50; font-weight:500; font-size:14px;">
                        <i class="fas fa-heading" style="color:#FF6B9D; margin-right:5px;"></i>Tiêu đề
                    </label>
                    <input type="text" name="title" placeholder="Nhập tiêu đề yêu cầu" required
                           style="width:100%; padding:12px; border:2px solid #e0e0e0; border-radius:10px; font-size:14px; font-family:'Poppins', sans-serif; transition:all 0.3s;">
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block; margin-bottom:8px; color:#2c3e50; font-weight:500; font-size:14px;">
                        <i class="fas fa-align-left" style="color:#FF6B9D; margin-right:5px;"></i>Nội dung
                    </label>
                    <textarea name="content" rows="5" placeholder="Mô tả chi tiết vấn đề của bạn..." required
                              style="width:100%; padding:12px; border:2px solid #e0e0e0; border-radius:10px; font-size:14px; font-family:'Poppins', sans-serif; resize:vertical; transition:all 0.3s;"></textarea>
                </div>

                <button type="submit" style="width:100%; padding:14px; background:linear-gradient(135deg, #FF6B9D, #ff4d6d); color:#fff; border:none; border-radius:10px; font-size:16px; font-weight:600; cursor:pointer; transition:all 0.3s; font-family:'Poppins', sans-serif;">
                    <i class="fas fa-paper-plane" style="margin-right:8px;"></i>Gửi yêu cầu hỗ trợ
                </button>
            </form>

            <div style="border-top:2px dashed #e0e0e0; padding-top:20px; margin-top:20px;">
                <p style="text-align:center; color:#7f8c8d; font-size:13px; margin-bottom:15px;">Hoặc liên hệ trực tiếp qua:</p>
                
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <a href="https://www.facebook.com/profile.php?id=61583156011828" target="_blank" 
                       style="display:flex; align-items:center; gap:10px; padding:12px; background:#f8f9fa; border-radius:10px; text-decoration:none; transition:all 0.3s;">
                        <div style="width:35px; height:35px; background:linear-gradient(135deg, #FF6B9D, #ff4d6d); border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff;">
                            <i class="fab fa-facebook-messenger"></i>
                        </div>
                        <div style="flex:1;">
                            <div style="font-size:12px; color:#2c3e50; font-weight:600;">Messenger</div>
                        </div>
                    </a>
                    
                    <a href="mailto:support@duyenhub.vn" 
                       style="display:flex; align-items:center; gap:10px; padding:12px; background:#f8f9fa; border-radius:10px; text-decoration:none; transition:all 0.3s;">
                        <div style="width:35px; height:35px; background:linear-gradient(135deg, #3498db, #2980b9); border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff;">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div style="flex:1;">
                            <div style="font-size:12px; color:#2c3e50; font-weight:600;">Email</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openContactAdmin() {
            const modal = document.getElementById('contactAdminModal');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeContactAdmin() {
            const modal = document.getElementById('contactAdminModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            // Reset form
            document.getElementById('supportForm').reset();
        }

        // Close modal when clicking outside
        document.getElementById('contactAdminModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeContactAdmin();
            }
        });

        // Handle support form submission
        document.getElementById('supportForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang gửi...';
            
            fetch('../../controller/cCreateSupport.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const successDiv = document.createElement('div');
                    successDiv.style.cssText = 'position:fixed; top:20px; left:50%; transform:translateX(-50%); background:linear-gradient(135deg, #27ae60, #229954); color:#fff; padding:15px 30px; border-radius:10px; box-shadow:0 4px 15px rgba(0,0,0,0.2); z-index:10001; font-family:Poppins, sans-serif; animation:slideDown 0.3s ease;';
                    successDiv.innerHTML = '<i class="fas fa-check-circle" style="margin-right:10px;"></i>' + data.message;
                    document.body.appendChild(successDiv);
                    
                    setTimeout(() => {
                        successDiv.remove();
                        closeContactAdmin();
                    }, 3000);
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra. Vui lòng thử lại!');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });

        // Add focus styles
        document.querySelectorAll('#supportForm input, #supportForm textarea, #supportForm select').forEach(el => {
            el.addEventListener('focus', function() {
                this.style.borderColor = '#FF6B9D';
                this.style.boxShadow = '0 0 0 3px rgba(255, 107, 157, 0.1)';
            });
            el.addEventListener('blur', function() {
                this.style.borderColor = '#e0e0e0';
                this.style.boxShadow = 'none';
            });
        });
    </script>
    
    <script>
        // Khôi phục vị trí cuộn và danh sách hồ sơ khi back về trang
        (function() {
            // Kiểm tra xem có phải đang back về không
            const entries = performance.getEntriesByType('navigation');
            const isBackNavigation = entries.length > 0 && entries[0].type === 'back_forward';
            
            if (!isBackNavigation) return;
            
            // Khôi phục vị trí cuộn NGAY LẬP TỨC (trước cả khi DOM ready)
            const savedScrollPosition = sessionStorage.getItem('trangchu_scrollPosition');
            if (savedScrollPosition) {
                // Áp dụng ngay lập tức
                window.scrollTo(0, parseInt(savedScrollPosition));
                document.documentElement.scrollTop = parseInt(savedScrollPosition);
                document.body.scrollTop = parseInt(savedScrollPosition);
            }
            
            // Khôi phục danh sách hồ sơ sau khi DOM ready
            document.addEventListener('DOMContentLoaded', function() {
                const savedProfilesHTML = sessionStorage.getItem('trangchu_profilesHTML');
                const profilesGrid = document.querySelector('.profiles-grid');
                
                if (savedProfilesHTML && profilesGrid) {
                    profilesGrid.innerHTML = savedProfilesHTML;
                    
                    // Gắn lại sự kiện onclick cho các card
                    const profileCards = profilesGrid.querySelectorAll('.profile-card');
                    profileCards.forEach(card => {
                        const userId = card.getAttribute('data-user-id');
                        card.onclick = function() { viewProfile(userId); };
                        
                        // Gắn lại sự kiện cho nút like
                        const likeBtn = card.querySelector('.btn-like');
                        if (likeBtn) {
                            likeBtn.onclick = function(e) { 
                                e.stopPropagation(); 
                                likeProfile(userId); 
                            };
                        }
                    });
                }
                
                // Khôi phục lại vị trí cuộn sau khi DOM render xong
                if (savedScrollPosition) {
                    window.scrollTo(0, parseInt(savedScrollPosition));
                }
            });
            
            // Đảm bảo vị trí cuộn được giữ nguyên sau khi tất cả resources load xong
            window.addEventListener('load', function() {
                if (savedScrollPosition) {
                    setTimeout(() => {
                        window.scrollTo(0, parseInt(savedScrollPosition));
                        // Xóa sau khi đã restore hoàn toàn
                        sessionStorage.removeItem('trangchu_scrollPosition');
                    }, 100);
                }
            });
        })();
        
        // Xóa cache khi reload trang (F5 hoặc Ctrl+R)
        window.addEventListener('pageshow', function(event) {
            const entries = performance.getEntriesByType('navigation');
            const isReload = entries.length > 0 && entries[0].type === 'reload';
            
            if (isReload) {
                sessionStorage.removeItem('trangchu_profilesHTML');
                sessionStorage.removeItem('trangchu_scrollPosition');
            }
        });
    </script>
    
    <style>
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }
    </style>
    </div>
</body>
</html>