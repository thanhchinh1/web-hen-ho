<?php
require_once '../../models/mSession.php';
require_once '../../models/mProfile.php';
require_once '../../models/mLike.php';
require_once '../../models/mNotification.php';
require_once '../../models/mUser.php';

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
$currentUserProfile = $profileModel->getProfile($currentUserId);

// Đếm số ghép đôi mới (chưa nhắn tin)
$newMatchesCount = $notificationModel->getNewMatchesCount($currentUserId);

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
$blockedUserIds = $blockModel->getBlockedUserIds($currentUserId);

// Thêm người đã ghép đôi vào danh sách loại trừ
require_once '../../models/mMatch.php';
$matchModel = new MatchModel();
$myMatches = $matchModel->getMyMatches($currentUserId);
$matchedUserIds = array_map(function($match) {
    return $match['maNguoiDung'];
}, $myMatches);

// Kết hợp và thêm chính mình vào danh sách loại trừ
$excludeIds = array_unique(array_merge([$currentUserId], $likedUserIds, $whoLikedMeIds, $blockedUserIds, $matchedUserIds));

// Lấy danh sách hồ sơ để hiển thị (loại trừ những người đã like và được like)
$allProfiles = $profileModel->getAllProfiles(12, 0, $excludeIds);

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
</head>
<body>
    <?php if ($successMessage): ?>
    <div id="flashNotification" style="
        position: fixed;
        top: 100px;
        left: 50%;
        transform: translateX(-50%);
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 18px 35px;
        border-radius: 30px;
        font-size: 16px;
        font-weight: 600;
        box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
        z-index: 10000;
        animation: slideDown 0.3s ease;
    ">
        <i class="fas fa-check-circle" style="margin-right: 8px;"></i>
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
    <style>
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translate(-50%, -20px);
            }
            to {
                opacity: 1;
                transform: translate(-50%, 0);
            }
        }
    </style>
    <?php endif; ?>
    
    <?php if ($infoMessage): ?>
    <div id="infoNotification" style="
        position: fixed;
        top: 100px;
        left: 50%;
        transform: translateX(-50%);
        background: linear-gradient(135deg, #17a2b8 0%, #5BC0DE 100%);
        color: white;
        padding: 18px 35px;
        border-radius: 30px;
        font-size: 16px;
        font-weight: 600;
        box-shadow: 0 8px 25px rgba(23, 162, 184, 0.4);
        z-index: 10000;
        animation: slideDown 0.3s ease;
    ">
        <i class="fas fa-info-circle" style="margin-right: 8px;"></i>
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
    <div id="errorNotification" style="
        position: fixed;
        top: 100px;
        left: 50%;
        transform: translateX(-50%);
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
        padding: 18px 35px;
        border-radius: 30px;
        font-size: 16px;
        font-weight: 600;
        box-shadow: 0 8px 25px rgba(220, 53, 69, 0.4);
        z-index: 10000;
        animation: slideDown 0.3s ease;
    ">
        <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>
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
                    <a href="../nhantin/chat.php" class="nav-link">
                        <i class="fas fa-comment"></i>
                        Tin nhấn
                        <?php if ($newMatchesCount > 0): ?>
                            <span class="notification-badge"><?php echo $newMatchesCount; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="#" class="nav-link" onclick="openSearchModal(); return false;">
                        <i class="fas fa-search"></i>
                        Tìm kiếm
                    </a>
                    <a href="../hotro/index.php" class="nav-link">
                        <i class="fas fa-headset"></i>
                        Hỗ trợ
                        <?php if ($newRepliesCount > 0): ?>
                            <span class="notification-badge pulse"><?php echo $newRepliesCount; ?></span>
                        <?php endif; ?>
                    </a>
                </nav>
            </div>

            <div class="header-right">
                <a href="../../controller/cLogout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i>
                    Đăng Xuất
                </a>
                <div class="user-menu-wrapper">
                    <img src="../../<?php echo htmlspecialchars($currentUserProfile['avt']); ?>" alt="User" class="user-avatar" id="userAvatar">
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
    </header>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <div class="hero-text">
                <h1>Tìm kiếm một nửa yêu thương của bạn</h1>
                <p>Kết Nối Yêu Thương là nơi bạn có thể tìm thấy những người phù hợp, chia sẻ sở thích và bắt đầu những câu chuyện tình yêu đẹp.</p>
            </div>
            <div class="hero-illustration">
                <svg viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg" class="heart-illustration">
                    <path d="M200,350 C120,290 50,220 50,150 C50,100 80,70 130,70 C160,70 180,85 200,110 C220,85 240,70 270,70 C320,70 350,100 350,150 C350,220 280,290 200,350 Z" fill="#FFE5EC" stroke="#FF6B9D" stroke-width="3"/>
                    <circle cx="160" cy="180" r="35" fill="#FFD7BA"/>
                    <path d="M160,145 Q145,135 150,155 Q155,145 160,145 Q165,145 170,155 Q175,135 160,145 Z" fill="#5C3D2E"/>
                    <rect x="145" y="200" width="30" height="45" rx="5" fill="#98D8C8"/>
                    <circle cx="240" cy="180" r="35" fill="#FFD7BA"/>
                    <path d="M240,145 Q225,135 230,155 Q235,145 240,145 Q245,145 250,155 Q255,135 240,145 Z" fill="#2C1810"/>
                    <rect x="225" y="200" width="30" height="45" rx="5" fill="#5BC0DE"/>
                    <circle cx="200" cy="200" r="5" fill="#FF1744"/>
                </svg>
            </div>
        </div>
    </section>

    <!-- System Notifications Section -->
    <?php if (!empty($systemNotifications)): ?>
    <section class="notifications-section">
        <div class="section-header">
            <h2><i class="fas fa-bell"></i> Thông báo</h2>
        </div>
        <div class="notifications-container">
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
    </section>
    <?php endif; ?>

    <!-- Profiles Section -->
    <section class="profiles-section">
        <div class="section-header">
            <h2>Danh sách hồ sơ nổi bật</h2>
            <a href="../timkiem/ghepdoinhanh.php" class="btn-register-cta">Ghép Đôi Nhanh</a>
        </div>

        <div class="profiles-grid">
            <?php foreach ($allProfiles as $profile): ?>
                <?php 
                    $age = $profileModel->calculateAge($profile['ngaySinh']);
                    $avatarSrc = !empty($profile['avt']) ? '../../' . htmlspecialchars($profile['avt']) : 'https://i.pravatar.cc/200';
                    $isOnline = $userModel->isUserOnline($profile['maNguoiDung']);
                    $lastActivity = $userModel->getLastActivity($profile['maNguoiDung']);
                ?>
                <div class="profile-card" onclick="viewProfile(<?php echo $profile['maNguoiDung']; ?>)">
                    <div class="profile-avatar-wrapper">
                        <img src="<?php echo $avatarSrc; ?>" alt="<?php echo htmlspecialchars($profile['ten']); ?>">
                        <?php if ($isOnline): ?>
                            <div class="online-indicator pulse" title="Đang online"></div>
                        <?php endif; ?>
                    </div>
                    <div class="profile-info">
                        <h3><?php echo htmlspecialchars($profile['ten']); ?>, <?php echo $age; ?></h3>
                        <p class="profile-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($profile['noiSong']); ?></p>
                        <p class="profile-status"><?php echo htmlspecialchars($profile['mucTieuPhatTrien']); ?></p>
                        <?php if ($isOnline): ?>
                            <p class="last-seen online"><i class="fas fa-circle"></i> Đang hoạt động</p>
                        <?php elseif ($lastActivity && $lastActivity['minutesAgo'] !== null): ?>
                            <?php
                                $minutes = $lastActivity['minutesAgo'];
                                if ($minutes < 60) {
                                    $timeText = $minutes . ' phút trước';
                                } elseif ($minutes < 1440) {
                                    $timeText = floor($minutes / 60) . ' giờ trước';
                                } else {
                                    $timeText = floor($minutes / 1440) . ' ngày trước';
                                }
                            ?>
                            <p class="last-seen"><i class="far fa-clock"></i> <?php echo $timeText; ?></p>
                        <?php endif; ?>
                    </div>
                    <button class="btn-like" onclick="event.stopPropagation(); likeProfile(<?php echo $profile['maNguoiDung']; ?>)"><i class="fas fa-heart"></i></button>
                </div>
            <?php endforeach; ?>
        </div>

    </section>

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

                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3>Xem ai thích bạn</h3>
                    <p>Biết được ai đã thích hồ sơ của bạn</p>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3>Hồ sơ nổi bật</h3>
                    <p>Xuất hiện nhiều hơn với người dùng khác</p>
                </div>
            </div>

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
                        <a href="../goivip/thanhtoan.php?package=1" class="btn-select-package">
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
                                <option value="Khac">Khác</option>
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
                                <option value="Hẹn hò nghiêm túc">Hẹn hò nghiêm túc</option>
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
                        <div class="interests-grid">
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

                    <div class="modal-actions">
                        <button type="button" class="modal-btn modal-btn-search" onclick="performSearch()">Tìm kiếm</button>
                        <button type="button" class="modal-btn modal-btn-close" onclick="closeSearchModal()">Đóng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
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
            
            profiles.forEach(profile => {
                const card = document.createElement('div');
                card.className = 'profile-card';
                card.onclick = () => viewProfile(profile.id);
                
                const avatarSrc = profile.avatar.startsWith('public/') ? 
                    '../../' + profile.avatar : 
                    profile.avatar;
                
                // HTML cho online indicator
                let onlineIndicator = '';
                if (profile.isOnline) {
                    onlineIndicator = '<div class="online-indicator pulse" title="Đang online"></div>';
                }
                
                // HTML cho last seen
                let lastSeenHTML = '';
                if (profile.lastSeen === 'online') {
                    lastSeenHTML = '<p class="last-seen online"><i class="fas fa-circle"></i> Đang hoạt động</p>';
                } else if (profile.lastSeen) {
                    lastSeenHTML = `<p class="last-seen"><i class="far fa-clock"></i> ${profile.lastSeen}</p>`;
                }
                
                card.innerHTML = `
                    <div class="profile-avatar-wrapper">
                        <img src="${avatarSrc}" alt="${profile.name}">
                        ${onlineIndicator}
                    </div>
                    <div class="profile-info">
                        <h3>${profile.name}, ${profile.age}</h3>
                        <p class="profile-location"><i class="fas fa-map-marker-alt"></i> ${profile.location}</p>
                        <p class="profile-status">${profile.goal}</p>
                        ${lastSeenHTML}
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
            window.location.href = '../hoso/xemnguoikhac.php?id=' + userId;
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
                            window.location.href = '../../views/nhantin/chat.php?matchId=' + data.matchId;
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
                        
                        setTimeout(() => {
                            successNotif.remove();
                            // Reload trang để cập nhật danh sách
                            window.location.reload();
                        }, 1500);
                    }
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                notification.remove();
                console.error('Error:', error);
                alert('Có lỗi xảy ra, vui lòng thử lại!');
            });
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