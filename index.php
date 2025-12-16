<?php
session_start();
require_once 'models/mProfile.php';

// Lấy danh sách hồ sơ để hiển thị (không cần đăng nhập)
$profileModel = new Profile();
$allProfiles = $profileModel->getAllProfiles(20); // 4 cột x 5 dòng = 20 profiles
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ - Kết Nối Yêu Thương</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="public/css/home.css">
</head>
<body>
    <div class="page-wrapper">
    <!-- Header -->
    <header class="main-header">
        <div class="header-container">
            <div class="nav-left">
                <a href="index.php" class="logo">
                    <img src="./public/img/logo.jpg" alt="DuyenHub Logo">
                    <span class="logo-text">DuyenHub</span>
                </a>
                
                
            </div>

            <div class="nav-right">
                <a href="views/dangnhap/login.php" class="btn-logout btn-login">
                    <i class="fas fa-user"></i>
                    Đăng Nhập
                </a>
                <a href="views/dangky/register.php" class="btn-logout btn-register">
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
                <svg viewBox="0 0 500 500" xmlns="http://www.w3.org/2000/svg" class="heart-illustration">
                    <!-- Decorative background circles -->
                    <defs>
                        <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#FF6B9D;stop-opacity:0.3" />
                            <stop offset="100%" style="stop-color:#FF8DB4;stop-opacity:0.1" />
                        </linearGradient>
                        <linearGradient id="grad2" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#FFE5EC;stop-opacity:0.8" />
                            <stop offset="100%" style="stop-color:#FFF0F5;stop-opacity:0.4" />
                        </linearGradient>
                        <linearGradient id="heartGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#FF6B9D;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#FF4081;stop-opacity:1" />
                        </linearGradient>
                    </defs>
                    
                    <!-- Background decorative elements -->
                    <circle cx="80" cy="80" r="40" fill="url(#grad1)" opacity="0.6">
                        <animate attributeName="r" values="40;45;40" dur="3s" repeatCount="indefinite"/>
                    </circle>
                    <circle cx="420" cy="100" r="30" fill="url(#grad2)" opacity="0.5">
                        <animate attributeName="r" values="30;35;30" dur="4s" repeatCount="indefinite"/>
                    </circle>
                    <circle cx="70" cy="380" r="35" fill="url(#grad1)" opacity="0.4">
                        <animate attributeName="r" values="35;40;35" dur="3.5s" repeatCount="indefinite"/>
                    </circle>
                    
                    <!-- Main large heart in background -->
                    <path d="M250,420 C170,360 100,290 100,220 C100,170 130,140 180,140 C210,140 230,155 250,180 C270,155 290,140 320,140 C370,140 400,170 400,220 C400,290 330,360 250,420 Z" 
                          fill="url(#grad2)" opacity="0.3"/>
                    
                    <!-- Couple silhouettes - Modern minimalist style -->
                    <!-- Female figure (left) -->
                    <g id="female">
                        <!-- Head -->
                        <circle cx="200" cy="200" r="35" fill="#FFB6C1"/>
                        <!-- Hair -->
                        <path d="M200,165 Q165,165 165,200 Q165,220 180,225 Q180,210 200,210 Q220,210 220,225 Q235,220 235,200 Q235,165 200,165 Z" 
                              fill="#8B4513"/>
                        <!-- Body/Dress -->
                        <path d="M200,235 L180,260 Q175,320 185,350 L215,350 Q225,320 220,260 Z" 
                              fill="#FF6B9D" opacity="0.9"/>
                        <!-- Arms -->
                        <path d="M180,250 Q160,260 165,280" stroke="#FFB6C1" stroke-width="8" fill="none" stroke-linecap="round"/>
                        <path d="M220,250 Q235,260 240,270" stroke="#FFB6C1" stroke-width="8" fill="none" stroke-linecap="round"/>
                    </g>
                    
                    <!-- Male figure (right) -->
                    <g id="male">
                        <!-- Head -->
                        <circle cx="300" cy="195" r="38" fill="#F4C2A5"/>
                        <!-- Hair -->
                        <path d="M300,157 Q265,157 265,190 Q265,175 280,175 Q290,165 300,165 Q310,165 320,175 Q335,175 335,190 Q335,157 300,157 Z" 
                              fill="#2C1810"/>
                        <!-- Body/Shirt -->
                        <rect x="275" y="233" width="50" height="80" rx="8" fill="#5BC0DE" opacity="0.9"/>
                        <path d="M275,233 L280,253 L295,243 L300,253 L305,243 L320,253 L325,233 Z" 
                              fill="#4A9FBF"/>
                        <!-- Arms -->
                        <path d="M275,245 Q260,255 255,270" stroke="#F4C2A5" stroke-width="9" fill="none" stroke-linecap="round"/>
                        <path d="M325,245 Q340,250 340,265" stroke="#F4C2A5" stroke-width="9" fill="none" stroke-linecap="round"/>
                        <!-- Pants -->
                        <rect x="280" y="313" width="20" height="37" rx="5" fill="#34495E"/>
                        <rect x="300" y="313" width="20" height="37" rx="5" fill="#34495E"/>
                    </g>
                    
                    <!-- Connecting heart between couple -->
                    <path d="M250,280 C235,270 225,260 225,245 C225,235 230,230 237,230 C242,230 246,233 250,238 C254,233 258,230 263,230 C270,230 275,235 275,245 C275,260 265,270 250,280 Z" 
                          fill="url(#heartGrad)">
                        <animate attributeName="opacity" values="0.7;1;0.7" dur="2s" repeatCount="indefinite"/>
                    </path>
                    
                    <!-- Floating hearts animation -->
                    <g class="floating-hearts">
                        <path d="M100,350 C95,345 90,340 90,333 C90,328 93,325 97,325 C100,325 102,327 104,330 C106,327 108,325 111,325 C115,325 118,328 118,333 C118,340 113,345 104,350 Z" 
                              fill="#FF6B9D" opacity="0.6">
                            <animateTransform attributeName="transform" type="translate" values="0,0; -10,-40; -15,-80" dur="4s" repeatCount="indefinite"/>
                            <animate attributeName="opacity" values="0.6;0.3;0" dur="4s" repeatCount="indefinite"/>
                        </path>
                        
                        <path d="M380,320 C375,315 370,310 370,303 C370,298 373,295 377,295 C380,295 382,297 384,300 C386,297 388,295 391,295 C395,295 398,298 398,303 C398,310 393,315 384,320 Z" 
                              fill="#FFB6C1" opacity="0.5">
                            <animateTransform attributeName="transform" type="translate" values="0,0; 10,-50; 15,-100" dur="5s" repeatCount="indefinite"/>
                            <animate attributeName="opacity" values="0.5;0.2;0" dur="5s" repeatCount="indefinite"/>
                        </path>
                        
                        <path d="M140,280 C137,277 134,274 134,269 C134,265 136,263 139,263 C141,263 143,264 144,266 C145,264 147,263 149,263 C152,263 154,265 154,269 C154,274 151,277 144,280 Z" 
                              fill="#FF8DB4" opacity="0.7">
                            <animateTransform attributeName="transform" type="translate" values="0,0; -5,-60; -8,-120" dur="6s" repeatCount="indefinite"/>
                            <animate attributeName="opacity" values="0.7;0.3;0" dur="6s" repeatCount="indefinite"/>
                        </path>
                    </g>
                    
                    <!-- Sparkles -->
                    <circle cx="150" cy="150" r="3" fill="#FFD700" opacity="0.8">
                        <animate attributeName="opacity" values="0;1;0" dur="2s" repeatCount="indefinite"/>
                    </circle>
                    <circle cx="350" cy="170" r="2.5" fill="#FFD700" opacity="0.8">
                        <animate attributeName="opacity" values="0;1;0" dur="2.5s" repeatCount="indefinite" begin="0.5s"/>
                    </circle>
                    <circle cx="180" cy="320" r="2" fill="#FFD700" opacity="0.8">
                        <animate attributeName="opacity" values="0;1;0" dur="3s" repeatCount="indefinite" begin="1s"/>
                    </circle>
                    <circle cx="380" cy="280" r="3" fill="#FFD700" opacity="0.8">
                        <animate attributeName="opacity" values="0;1;0" dur="2s" repeatCount="indefinite" begin="1.5s"/>
                    </circle>
                </svg>
            </div>
        </div>
    </section>

    <!-- Profiles Section with 3-column layout -->
    <section class="profiles-section">
        <div class="section-header">
            <h2>Danh sách hồ sơ nổi bật</h2>
            <a href="views/ghepdoi/index.php" class="btn-register-cta">Ghép Đôi Nhanh</a>
        </div>

        <div class="profiles-grid">
            <?php foreach ($allProfiles as $profile): ?>
                <?php 
                    $age = $profileModel->calculateAge($profile['ngaySinh']);
                    $avatarSrc = !empty($profile['avt']) ? htmlspecialchars($profile['avt']) : 'https://i.pravatar.cc/180';
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
                    <button class="btn-like" onclick="event.stopPropagation(); likeProfile(<?php echo $profile['maNguoiDung']; ?>)">
                        <i class="fas fa-heart"></i>
                    </button>
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
                    <a href="https://www.facebook.com/profile.php?id=61583156011828" class="social-icon"><i class="fab fa-facebook-f"></i></a>
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
            // Lưu hành động like vào sessionStorage
            sessionStorage.setItem('pending_like_action', userId);
            
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
                    <button onclick="window.location.href='views/dangnhap/login.php?action=like&targetUser=' + ${userId}" style="
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

        // Header scroll behavior
        let lastScrollTop = 0;
        const header = document.querySelector('.main-header');
        
        window.addEventListener('scroll', function() {
            let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            // Add scrolled class for shadow effect
            if (scrollTop > 50) {
                header.classList.add('header-scrolled');
            } else {
                header.classList.remove('header-scrolled');
            }
            
            // Hide/show header on scroll
            if (scrollTop > lastScrollTop && scrollTop > 100) {
                // Scrolling down
                header.classList.add('header-hidden');
            } else {
                // Scrolling up
                header.classList.remove('header-hidden');
            }
            
            lastScrollTop = scrollTop;
        });
    </script>
    </div>
</body>
