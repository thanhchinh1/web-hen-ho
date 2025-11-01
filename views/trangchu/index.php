<?php
require_once '../../models/mSession.php';
require_once '../../models/mProfile.php';

Session::start();

// Kiểm tra đăng nhập
if (!Session::isLoggedIn()) {
    header('Location: ../dangnhap/login.php');
    exit;
}

$currentUserId = Session::getUserId();
$profileModel = new Profile();

// Kiểm tra xem đã có hồ sơ chưa
if (!$profileModel->hasProfile($currentUserId)) {
    // Chưa có hồ sơ -> chuyển đến trang thiết lập hồ sơ
    header('Location: ../hoso/thietlaphoso.php');
    exit;
}

// Lấy hồ sơ của người dùng hiện tại
$currentUserProfile = $profileModel->getProfile($currentUserId);

// Lấy danh sách hồ sơ để hiển thị
$allProfiles = $profileModel->getAllProfiles(12);
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
                <a href="../nhantin/chat.php" class="nav-item">
                    <i class="fas fa-comment"></i>
                    Tin nhắn
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
                           <a href="../hoso/thietlaphoso.php" class="user-dropdown-item vip">
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
            <a href="../timkiem/ketqua.php" class="btn-quick-match">Ghép đôi nhanh</a>
        </div>

        <div class="profiles-grid">
            <?php foreach ($allProfiles as $profile): ?>
                <?php 
                    // Bỏ qua hiển thị chính mình
                    if ($profile['maNguoiDung'] == $currentUserId) continue;
                    
                    $age = $profileModel->calculateAge($profile['ngaySinh']);
                    $avatarSrc = !empty($profile['avt']) ? '../../' . htmlspecialchars($profile['avt']) : 'https://i.pravatar.cc/200';
                ?>
                <div class="profile-card" onclick="viewProfile(<?php echo $profile['maNguoiDung']; ?>)">
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

            // Show notification
            const notification = document.createElement('div');
            notification.textContent = 'Đang tìm kiếm... 🔍';
            notification.style.cssText = `
                position: fixed;
                top: 100px;
                left: 50%;
                transform: translateX(-50%);
                background: #5BC0DE;
                color: white;
                padding: 15px 30px;
                border-radius: 25px;
                font-size: 16px;
                font-weight: 600;
                box-shadow: 0 5px 20px rgba(91,192,222,0.3);
                z-index: 10001;
            `;
            document.body.appendChild(notification);
            setTimeout(() => {
                notification.remove();
                closeSearchModal();
            }, 2000);

            console.log('Search params:', { gender, status, purpose, city, interest, age });
        }

        // View profile
        function viewProfile(userId) {
            window.location.href = '../hoso/xemnguoikhac.php?id=' + userId;
        }

        // Like button animation
        function likeProfile(userId) {
            const notification = document.createElement('div');
            notification.textContent = 'Đã thích! 💙';
            notification.style.cssText = 'position:fixed;top:100px;left:50%;transform:translateX(-50%);background:#FF6B9D;color:white;padding:15px 30px;border-radius:25px;font-size:16px;font-weight:600;box-shadow:0 5px 20px rgba(255,107,157,0.3);z-index:10000';
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 2000);
            
            // TODO: Gửi request lên server để lưu like
            console.log('Liked user:', userId);
        }
    </script>
    </div>
</body>
</html>