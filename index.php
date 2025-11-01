<?php
session_start();
require_once 'models/mProfile.php';

// Lấy danh sách hồ sơ để hiển thị (không cần đăng nhập)
$profileModel = new Profile();
$allProfiles = $profileModel->getAllProfiles(12);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ - Kết Nối Yêu Thương</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="public/css/home.css">
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="header-container">
            <a href="index.php" class="logo">
                <img src="./public/img/logo.jpg" alt="">
                <span class="logo-text">DuyenHub</span>
            </a>

            <div class="nav-right">
                <a href="views/dangnhap/login.php" class="btn-logout" style="margin-left: 10px; background: #2196F3;">
                    <i class="fas fa-user"></i>
                    Đăng Nhập
                </a>
                <a href="views/dangky/register.php" class="btn-logout" style="margin-left: 10px; background: #3f9452ff;">
                    <i class="fas fa-user-plus"></i>
                    Đăng Ký
                </a>
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
        </div>

        <div class="profiles-grid">
            <?php foreach ($allProfiles as $profile): ?>
                <?php 
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

    <script>
        // View profile - yêu cầu đăng nhập
        function viewProfile(userId) {
            // Lưu ID hồ sơ muốn xem vào sessionStorage
            sessionStorage.setItem('redirect_to_profile', userId);
            // Chuyển đến trang đăng nhập
            window.location.href = 'views/dangnhap/login.php?redirect=profile&id=' + userId;
        }

        // Like button - yêu cầu đăng nhập
        function likeProfile(userId) {
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
                    <div style="font-size: 48px; color: #FF6B9D; margin-bottom: 15px;">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3 style="margin: 0 0 10px 0; color: #2C3E50;">Vui lòng đăng nhập</h3>
                    <p style="margin: 0 0 20px 0; color: #666;">Bạn cần đăng nhập để thích hồ sơ này</p>
                    <button onclick="window.location.href='views/dangnhap/login.php'" style="
                        padding: 10px 30px;
                        background: #FF6B9D;
                        color: white;
                        border: none;
                        border-radius: 8px;
                        font-size: 14px;
                        font-weight: 600;
                        cursor: pointer;
                        margin-right: 10px;
                    ">Đăng nhập</button>
                    <button onclick="this.closest('div').parentElement.remove()" style="
                        padding: 10px 30px;
                        background: #6c757d;
                        color: white;
                        border: none;
                        border-radius: 8px;
                        font-size: 14px;
                        font-weight: 600;
                        cursor: pointer;
                    ">Đóng</button>
                </div>
                <div onclick="this.parentElement.remove()" style="
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0,0,0,0.5);
                    z-index: 9999;
                    cursor: pointer;
                "></div>
            `;
            document.body.appendChild(notification);
        }
    </script>
</body>
