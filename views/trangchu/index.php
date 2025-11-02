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

// Kết hợp và thêm chính mình vào danh sách loại trừ
$excludeIds = array_unique(array_merge([$currentUserId], $likedUserIds, $whoLikedMeIds, $blockedUserIds));

// Lấy danh sách hồ sơ để hiển thị (loại trừ những người đã like và được like)
$allProfiles = $profileModel->getAllProfiles(12, 0, $excludeIds);

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
    <title>Trang chủ - Kết Nối Yêu Thương</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/home.css">
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
            <a href="../trangchu/index.php" class="logo">
                <img src="../../public/img/logo.jpg" alt="Kết Nối Yêu Thương">
                <span class="logo-text">DuyenHub</span>
            </a>

            <nav class="nav-center">
                <a href="#" class="nav-item active">
                    <i class="fas fa-home"></i>
                    Trang chủ
                </a>
                <a href="../nhantin/chat.php" class="nav-item" style="position: relative;">
                    <i class="fas fa-comment"></i>
                    Tin nhắn
                    <?php if ($newMatchesCount > 0): ?>
                        <span class="notification-badge"><?php echo $newMatchesCount; ?></span>
                    <?php endif; ?>
                </a>
                <a href="#" class="nav-item" onclick="openSearchModal(); return false;">
                    <i class="fas fa-search"></i>
                    Tìm kiếm
                </a>
            </nav>

            <div class="nav-right">
                <a href="../../controller/cLogout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i>
                    Đăng Xuất
                </a>
                <div class="user-menu-wrapper" style="position: relative;">
                    <img src="../../<?php echo htmlspecialchars($currentUserProfile['avt']); ?>" alt="User" class="user-avatar" id="userAvatar" style="cursor:pointer;">
                    <div class="user-dropdown" id="userDropdown" style="display:none;">
                        <a href="../hoso/index.php" class="user-dropdown-item">
                            <i class="fas fa-user"></i>
                            Hồ sơ của tôi
                        </a>
                        <a href="../hoso/chinhsua.php" class="user-dropdown-item">
                            <i class="fas fa-edit"></i>
                            Chỉnh sửa hồ sơ
                        </a>
                        <a href="../taikhoan/doimatkhau.php" class="user-dropdown-item">
                            <i class="fas fa-key"></i>
                            Đổi mật khẩu
                        </a>
                     
                        <a href="../thich/nguoithichban.php" class="user-dropdown-item">
                            <i class="fas fa-heart"></i>
                            Xem danh sách thích bạn
                        </a>
                        <a href="../thich/nguoibanthich.php" class="user-dropdown-item">
                            <i class="fas fa-user-friends"></i>
                            Xem danh sách người bạn thích
                        </a>
                        <a href="../chan/danhsach.php" class="user-dropdown-item">
                            <i class="fas fa-ban"></i>
                            Danh sách chặn
                        </a>
                        <a href="../goivip/index.php" class="user-dropdown-item vip">
                            <i class="fas fa-crown"></i>
                            Nâng cấp tài khoản
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

    <!-- Profiles Section -->
    <section class="profiles-section">
        <div class="section-header">
            <h2>Danh sách hồ sơ nổi bật</h2>
            <a href="../timkiem/ghepdoinhanh.php" class="btn-quick-match">Ghép đôi nhanh</a>
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
                    <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Kết Nối Yêu Thương. Mọi quyền được bảo lưu.</p>
            </div>
        </div>
    </footer>

    <!-- Search Modal -->
    <div class="search-modal-overlay" id="searchModal">
        <div class="search-modal">
            <div class="modal-header">
                <h2>Tìm kiếm nâng cao</h2>
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
                                <option value="male">Nam</option>
                                <option value="female">Nữ</option>
                                <option value="other">Khác</option>
                            </select>
                        </div>

                        <div class="modal-form-group">
                            <label>Hôn nhân</label>
                            <select class="modal-form-select" id="status">
                                <option value="">Tất cả</option>
                                <option value="single">Độc thân</option>
                                <option value="divorced">Đã ly hôn</option>
                                <option value="widowed">Góa</option>
                            </select>
                        </div>

                        <div class="modal-form-group">
                            <label>Mục tiêu</label>
                            <select class="modal-form-select" id="purpose">
                                <option value="">Tất cả</option>
                                <option value="relationship">Hẹn hò nghiêm túc</option>
                                <option value="friendship">Kết bạn</option>
                                <option value="marriage">Kết hôn</option>
                                <option value="casual">Tìm hiểu</option>
                            </select>
                        </div>

                        <div class="modal-form-group">
                            <label>Thành Phố</label>
                            <select class="modal-form-select" id="city">
                                <option value="">Tất cả</option>
                                <option value="hcm">TP. Hồ Chí Minh</option>
                                <option value="hn">Hà Nội</option>
                                <option value="dn">Đà Nẵng</option>
                                <option value="hp">Hải Phòng</option>
                                <option value="ct">Cần Thơ</option>
                            </select>
                        </div>

                        <div class="modal-form-group">
                            <label>Sở thích</label>
                            <select class="modal-form-select" id="interest">
                                <option value="">Tất cả</option>
                                <option value="travel">Du lịch</option>
                                <option value="music">Âm nhạc</option>
                                <option value="sport">Thể thao</option>
                                <option value="cooking">Nấu ăn</option>
                                <option value="reading">Đọc sách</option>
                                <option value="movie">Xem phim</option>
                            </select>
                        </div>

                        <div class="modal-form-group">
                            <label>Tuổi</label>
                            <select class="modal-form-select" id="age">
                                <option value="">Tất cả</option>
                                <option value="18-25">18 đến 25</option>
                                <option value="25-30">25 đến 30</option>
                                <option value="30-35">30 đến 35</option>
                                <option value="35-40">35 đến 40</option>
                                <option value="40+">40+</option>
                            </select>
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
            const interest = document.getElementById('interest').value;
            const age = document.getElementById('age').value;

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
            fetch('../../controller/cSearch.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=search&gender=${gender}&status=${status}&purpose=${purpose}&city=${city}&interest=${interest}&age=${age}`
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
    </div>
</body>
</html>