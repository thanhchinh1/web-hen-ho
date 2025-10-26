<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết Nối Yêu Thương - Tìm kiếm tình yêu đích thực</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="public/css/landing.css">
</head>
<body class="landing-page">
    <!-- Header -->
    <header class="landing-header">
        <nav class="landing-nav">
            <div class="nav-brand">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="logo-icon">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" fill="#ff6b9d"/>
                </svg>
            </div>
            <ul class="nav-menu">
                <li><a href="#home" class="nav-link active">Trang chủ</a></li>
                <li><a href="#features" class="nav-link">Khám phá</a></li>
                <li><a href="#contact" class="nav-link">Tin nhắn</a></li>
                <li><a href="#contact" class="nav-link">Tìm Kiếm</a></li>
            </ul>
            <div class="nav-buttons">
                <a href="views/dangnhap/index.php" class="btn-outline">Đăng nhập</a>
                <a href="views/dangky/index.php" class="btn-primary">Đăng ký</a>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero-landing" id="home">
        <div class="hero-container">
            <div class="hero-text">
                <h1>Tìm kiếm một nửa yêu thương của bạn</h1>
                <p>Kết Nối Yêu Thương là nơi bạn có thể tìm thấy những người phù hợp, chia sẻ sở thích và bắt đầu những câu chuyện tình yêu đẹp.</p>
            </div>
            <div class="hero-image">
                <svg viewBox="0 0 500 500" xmlns="http://www.w3.org/2000/svg" class="illustration">
                    <!-- Heart Shape Background -->
                    <path d="M250,450 C150,380 50,280 50,180 C50,120 90,80 150,80 C190,80 220,100 250,130 C280,100 310,80 350,80 C410,80 450,120 450,180 C450,280 350,380 250,450 Z" fill="#FF6B9D" opacity="0.2"/>
                    
                    <!-- Female Character -->
                    <ellipse cx="200" cy="250" rx="60" ry="80" fill="#5C3D2E"/>
                    <circle cx="200" cy="200" r="50" fill="#FFD7BA"/>
                    <rect x="180" y="230" width="40" height="60" rx="5" fill="#4A90E2"/>
                    <rect x="185" y="290" width="15" height="50" fill="#8B6F47"/>
                    <rect x="200" y="290" width="15" height="50" fill="#8B6F47"/>
                    
                    <!-- Male Character -->
                    <circle cx="300" cy="200" r="50" fill="#FFD7BA"/>
                    <path d="M300,150 Q280,140 285,160 Q290,145 300,150 Q310,145 315,160 Q320,140 300,150 Z" fill="#2C1810"/>
                    <rect x="280" y="230" width="40" height="60" rx="5" fill="#FFB6C1"/>
                    <rect x="285" y="290" width="15" height="50" fill="#4169E1"/>
                    <rect x="300" y="290" width="15" height="50" fill="#4169E1"/>
                    
                    <!-- Kiss/Heart effect -->
                    <path d="M240,210 L260,210 L250,225 Z" fill="#FF1744"/>
                    <circle cx="235" cy="215" r="3" fill="#FF1744"/>
                    <circle cx="245" cy="215" r="3" fill="#FF1744"/>
                    <circle cx="255" cy="215" r="3" fill="#FF1744"/>
                    <circle cx="265" cy="215" r="3" fill="#FF1744"/>
                </svg>
            </div>
        </div>
    </section>

    <!-- Features Preview -->
    <section class="features-preview" id="features">
        <div class="section-container">
            <h2>Danh sách hẹ sơ nổi bật</h2>
            <a href="views/dangky/index.php" class="btn-success btn-help">Ghép đôi nhanh</a>
            
            <div class="profiles-grid">
                <!-- Profile Card 1 -->
                <div class="profile-preview-card">
                    <div class="profile-preview-avatar">
                        <img src="https://i.pravatar.cc/300?img=5" alt="Nguyễn Thị Mai">
                    </div>
                    <div class="profile-preview-info">
                        <h3>Nguyễn Thị Mai, 1995</h3>
                        <p class="location">Hà Nội</p>
                        <p class="status">Độc thân</p>
                    </div>
                    <button class="btn-like-preview">
                        <i class="fas fa-heart"></i>
                    </button>
                </div>

                <!-- Profile Card 2 -->
                <div class="profile-preview-card">
                    <div class="profile-preview-avatar">
                        <img src="https://i.pravatar.cc/300?img=12" alt="Trần Văn Hưng">
                    </div>
                    <div class="profile-preview-info">
                        <h3>Trần Văn Hưng, 1992</h3>
                        <p class="location">TP.HCM</p>
                        <p class="status">Độc thân</p>
                    </div>
                    <button class="btn-like-preview">
                        <i class="fas fa-heart"></i>
                    </button>
                </div>

                <!-- Profile Card 3 -->
                <div class="profile-preview-card">
                    <div class="profile-preview-avatar">
                        <img src="https://i.pravatar.cc/300?img=9" alt="Lê Thu Thảo">
                    </div>
                    <div class="profile-preview-info">
                        <h3>Lê Thu Thảo, 1998</h3>
                        <p class="location">Đà Nẵng</p>
                        <p class="status">Độc thân</p>
                    </div>
                    <button class="btn-like-preview">
                        <i class="fas fa-heart"></i>
                    </button>
                </div>

                <!-- Profile Card 4 -->
                <div class="profile-preview-card">
                    <div class="profile-preview-avatar">
                        <img src="https://i.pravatar.cc/300?img=33" alt="Phạm Minh Đức">
                    </div>
                    <div class="profile-preview-info">
                        <h3>Phạm Minh Đức, 1990</h3>
                        <p class="location">Hà Nội</p>
                        <p class="status">Độc thân</p>
                    </div>
                    <button class="btn-like-preview">
                        <i class="fas fa-heart"></i>
                    </button>
                </div>

                <!-- Profile Card 5 -->
                <div class="profile-preview-card">
                    <div class="profile-preview-avatar">
                        <img src="https://i.pravatar.cc/300?img=20" alt="Đinh Hà Phương">
                    </div>
                    <div class="profile-preview-info">
                        <h3>Đinh Hà Phương, 1996</h3>
                        <p class="location">Cần Thơ</p>
                        <p class="status">Độc thân</p>
                    </div>
                    <button class="btn-like-preview">
                        <i class="fas fa-heart"></i>
                    </button>
                </div>

                <!-- Profile Card 6 -->
                <div class="profile-preview-card">
                    <div class="profile-preview-avatar">
                        <img src="https://i.pravatar.cc/300?img=15" alt="Hoàng Gia Bảo">
                    </div>
                    <div class="profile-preview-info">
                        <h3>Hoàng Gia Bảo, 1993</h3>
                        <p class="location">TP.HCM</p>
                        <p class="status">Độc thân</p>
                    </div>
                    <button class="btn-like-preview">
                        <i class="fas fa-heart"></i>
                    </button>
                </div>

                <!-- Profile Card 7 -->
                <div class="profile-preview-card">
                    <div class="profile-preview-avatar">
                        <img src="https://i.pravatar.cc/300?img=25" alt="Đặng Hải Yến">
                    </div>
                    <div class="profile-preview-info">
                        <h3>Đặng Hải Yến, 1997</h3>
                        <p class="location">Hải Phòng</p>
                        <p class="status">Độc thân</p>
                    </div>
                    <button class="btn-like-preview">
                        <i class="fas fa-heart"></i>
                    </button>
                </div>

                <!-- Profile Card 8 -->
                <div class="profile-preview-card">
                    <div class="profile-preview-avatar">
                        <img src="https://i.pravatar.cc/300?img=30" alt="Bùi Thanh Long">
                    </div>
                    <div class="profile-preview-info">
                        <h3>Bùi Thanh Long, 1989</h3>
                        <p class="location">Huế</p>
                        <p class="status">Độc thân</p>
                    </div>
                    <button class="btn-like-preview">
                        <i class="fas fa-heart"></i>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="landing-footer" id="contact">
        <div class="footer-container">
            <div class="footer-brand">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="footer-logo">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" fill="#5BC0DE"/>
                </svg>
            </div>
            
            <div class="footer-links">
                <a href="#about">Về chúng tôi</a>
                <a href="#support">Hỗ trợ</a>
                <a href="#legal">Pháp lý</a>
            </div>
            
            <div class="footer-social">
                <a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2024 Kết Nối Yêu Thương. Mọi quyền được bảo lưu.</p>
        </div>
    </footer>

    <script src="public/js/script.js"></script>
</body>
</html>