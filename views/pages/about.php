<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Về chúng tôi - DuyenHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/trangchu.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../../public/css/search-modal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php session_start(); ?>
    <div class="page-wrapper">
        <!-- Header (match public homepage) -->
        <header class="main-header">
            <div class="header-container">
                <div class="nav-left">
                    <a href="/index.php" class="logo">
                        <img src="../../public/img/logo.jpg" alt="DuyenHub Logo">
                        <span class="logo-text">DuyenHub</span>
                    </a>
                    <nav class="main-nav">
                        <a href="/index.php" class="nav-link active">
                            <i class="fas fa-home"></i>
                            Trang chủ
                        </a>
                        <a href="/index.php?page=about" class="nav-link">
                            <i class="fas fa-users"></i>
                            Về chúng tôi
                        </a>
                        <a href="/index.php?page=support" class="nav-link">
                            <i class="fas fa-headset"></i>
                            Hỗ trợ
                        </a>
                        <a href="/index.php?page=legal" class="nav-link">
                            <i class="fas fa-gavel"></i>
                            Pháp lý
                        </a>
                    </nav>
                </div>

                <div class="nav-right">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="/views/dangnhap/login.php" class="btn-logout btn-login">
                        <i class="fas fa-user"></i>
                        Đăng Nhập
                    </a>
                    <a href="/views/dangky/register.php" class="btn-logout btn-register">
                        <i class="fas fa-user-plus"></i>
                        Đăng Ký
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-content">
                <div class="hero-text">
                    <h1><span style="color:#FF6B9D">💖</span> Về Chúng Tôi</h1>
                    <p style="font-size:18px;line-height:1.7;margin-top:18px;">DuyenHub ra đời với mong muốn tạo nên một không gian hẹn hò an toàn và chân thành, nơi những con người xa lạ có thể tìm thấy sự đồng điệu giữa nhịp sống bận rộn. Mỗi kết nối tại DuyenHub không chỉ bắt đầu từ một cái nhìn, mà được nuôi dưỡng bằng sự tôn trọng và thấu hiểu.</p>
                    <p style="font-size:18px;line-height:1.7;margin-top:18px;">Sứ mệnh của chúng tôi là xây dựng một nền tảng hẹn hò văn minh, đề cao sự riêng tư, bảo mật thông tin và khuyến khích những mối quan hệ nghiêm túc, bền vững. Chúng tôi tin rằng một cuộc gặp gỡ đúng lúc có thể mở ra những câu chuyện rất đẹp.</p>
                    <div style="margin-top:28px;font-size:17px;">
                        <b><span style="color:#FF6B9D">📩</span> Liên hệ:</b><br>
                        Email: <a href="mailto:support@duyenhub.vn">support@duyenhub.vn</a><br>
                    </div>
                    <div style="margin-top:18px;font-weight:600;font-size:18px;">DuyenHub – nơi những kết nối bắt đầu từ sự chân thành.</div>
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

        <!-- Footer -->
        <footer class="main-footer">
            <div class="footer-container">
                <div class="footer-top">
                    <div class="footer-links">
                        <a href="/index.php?page=about">Về chúng tôi</a>
                        <a href="/index.php?page=support">Hỗ trợ</a>
                        <a href="/index.php?page=legal">Pháp lý</a>
                    </div>
                    <div class="footer-social">
                        <a href="https://www.facebook.com/profile.php?id=61583156011828" class="social-icon" target="_blank"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="footer-bottom">
                    <p>&copy; <?php echo date('Y'); ?> DuyenHub. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>