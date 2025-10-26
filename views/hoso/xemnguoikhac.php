<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ - Kết Nối Yêu Thương</title>
    <link rel="stylesheet" href="../../public/css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="profile-container">
        <!-- Header -->
        <header class="profile-header">
            <div class="header-left">
                <i class="fas fa-users"></i>
                <span class="logo-text">Mạng Xã Hội</span>
            </div>
            <div class="header-center">
                <a href="../trangchu/index.php" class="nav-link">Trang chủ</a>
            </div>
            <div class="header-right">
                <a href="../dangnhap/index.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i>
                    Đăng Xuất
                </a>
            </div>
        </header>

        <!-- Back button -->
        <div class="back-button-container">
            <button class="btn-back" onclick="window.history.back()">
                <i class="fas fa-arrow-left"></i>
            </button>
        </div>

        <!-- Profile Content -->
        <div class="profile-content">
            <!-- Profile Header Section -->
            <div class="profile-hero">
                <div class="profile-avatar-section">
                    <img src="https://i.pravatar.cc/300?img=45" alt="Linh Nguyễn" class="profile-avatar" id="userAvatar">
                    <h1 class="profile-name" id="userName">Linh Nguyễn</h1>
                    <p class="profile-info" id="userBasicInfo">Sinh năm 1995 • Hồ Chí Minh • Độc thân</p>
                </div>

                <!-- Action Buttons -->
                <div class="profile-actions">
                    <button class="btn-action btn-like">
                        <i class="far fa-heart"></i>
                        Thả tim
                    </button>
                    <button class="btn-action btn-report">
                        <i class="far fa-flag"></i>
                        Báo cáo
                    </button>
                    <button class="btn-action btn-block">
                        <i class="fas fa-ban"></i>
                        Chặn
                    </button>
                    <button class="btn-action btn-more">
                        <i class="fas fa-ellipsis-h"></i>
                    </button>
                </div>
            </div>

            <!-- Profile Details -->
            <div class="profile-details">
                <!-- Personal Information -->
                <section class="detail-section">
                    <h2 class="section-title">Thông tin cá nhân</h2>
                    <div class="info-list">
                        <div class="info-item">
                            <i class="fas fa-venus-mars"></i>
                            <span class="info-label">Giới tính:</span>
                            <span class="info-value">Nữ</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span class="info-label">Ngày sinh:</span>
                            <span class="info-value">01/01/1995</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span class="info-label">Thành phố:</span>
                            <span class="info-value">Thành phố Hồ Chí Minh</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-heart"></i>
                            <span class="info-label">Tình trạng hôn nhân:</span>
                            <span class="info-value">Độc thân</span>
                        </div>
                    </div>
                </section>

                <!-- Career & Education -->
                <section class="detail-section">
                    <h2 class="section-title">Nghề nghiệp & Học vấn</h2>
                    
                    <div class="subsection">
                        <h3 class="subsection-title">
                            <i class="fas fa-briefcase"></i>
                            Nghề nghiệp
                        </h3>
                        <div class="career-item">
                            <h4>Quản lý dự án</h4>
                            <p>Công ty Công nghệ ABC (2018 - Hiện tại)</p>
                        </div>
                        <div class="career-item">
                            <h4>Phân tích dự liệu</h4>
                            <p>Tập đoàn Xây dựng XYZ (2015 - 2018)</p>
                        </div>
                    </div>

                    <div class="subsection">
                        <h3 class="subsection-title">
                            <i class="fas fa-graduation-cap"></i>
                            Học vấn
                        </h3>
                        <div class="education-item">
                            <h4>Cử nhân Quản trị kinh doanh</h4>
                            <p>Đại học Kinh tế Quốc dân (2011 - 2015)</p>
                        </div>
                        <div class="education-item">
                            <h4>Chứng chỉ Kỹ năng mềm</h4>
                            <p>Trung tâm Phát triển Kỹ năng Thanh niên (2014)</p>
                        </div>
                    </div>
                </section>

                <!-- Interests -->
                <section class="detail-section">
                    <h2 class="section-title">Sở thích</h2>
                    <div class="interests-tags">
                        <span class="interest-tag">Đọc sách</span>
                        <span class="interest-tag">Du lịch</span>
                        <span class="interest-tag">Nấu ăn</span>
                        <span class="interest-tag">Chụp ảnh</span>
                        <span class="interest-tag">Yoga</span>
                        <span class="interest-tag">Âm nhạc</span>
                    </div>
                </section>

                <!-- About Me -->
                <section class="detail-section">
                    <h2 class="section-title">Về tôi</h2>
                    <p class="about-text">
                        Xin chào! Tôi là một người yêu thích khám phá những điều mới mẻ trong cuộc sống. 
                        Tôi thích đi du lịch, khám phá các nền văn hóa khác nhau và thưởng thức ẩm thực địa phương. 
                        Trong thời gian rảnh, tôi thường đọc sách, tập yoga và nấu ăn. 
                        Tôi đang tìm kiếm một người bạn đồng hành chân thành để cùng nhau chia sẻ những khoảnh khắc đẹp trong cuộc sống.
                    </p>
                </section>

                <!-- Looking For -->
                <section class="detail-section">
                    <h2 class="section-title">Tôi đang tìm kiếm</h2>
                    <p class="about-text">
                        Một người chân thành, có trách nhiệm và yêu thương gia đình. 
                        Người có cùng sở thích về du lịch và khám phá. 
                        Độ tuổi từ 28-35, có công việc ổn định.
                    </p>
                </section>
            </div>
        </div>
    </div>

    <script>
        // Get user ID from URL
        const urlParams = new URLSearchParams(window.location.search);
        const userId = urlParams.get('id');

        // Sample user data
        const users = {
            1: {
                name: 'Linh Nguyễn',
                avatar: 'https://i.pravatar.cc/300?img=45',
                birth: '01/01/1995',
                city: 'TP.HCM',
                status: 'Độc thân',
                gender: 'Nữ'
            },
            2: {
                name: 'Trần Văn Hưng',
                avatar: 'https://i.pravatar.cc/300?img=33',
                birth: '15/05/1992',
                city: 'TP.HCM',
                status: 'Độc thân',
                gender: 'Nam'
            },
            3: {
                name: 'Lê Thu Thảo',
                avatar: 'https://i.pravatar.cc/300?img=28',
                birth: '20/08/1998',
                city: 'Đà Nẵng',
                status: 'Độc thân',
                gender: 'Nữ'
            },
            4: {
                name: 'Phạm Minh Đức',
                avatar: 'https://i.pravatar.cc/300?img=52',
                birth: '10/03/1990',
                city: 'Hà Nội',
                status: 'Độc thân',
                gender: 'Nam'
            }
        };

        // Load user data
        if (userId && users[userId]) {
            const user = users[userId];
            document.getElementById('userAvatar').src = user.avatar;
            document.getElementById('userName').textContent = user.name;
            document.getElementById('userBasicInfo').textContent = 
                `Sinh năm ${user.birth.split('/')[2]} • ${user.city} • ${user.status}`;
            
            // Update gender in info list
            document.querySelector('.info-value').textContent = user.gender;
            document.querySelectorAll('.info-value')[1].textContent = user.birth;
            document.querySelectorAll('.info-value')[2].textContent = 'Thành phố ' + user.city;
        }

        // Like button
        document.querySelector('.btn-like').addEventListener('click', function() {
            const icon = this.querySelector('i');
            if (icon.classList.contains('far')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                this.style.background = 'linear-gradient(135deg, #FF6B9D 0%, #FF8DB4 100%)';
                this.style.color = 'white';
                showNotification('Đã thích! 💖');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                this.style.background = 'white';
                this.style.color = '#FF6B9D';
                showNotification('Đã bỏ thích');
            }
        });

        // Report button
        document.querySelector('.btn-report').addEventListener('click', function() {
            if (confirm('Bạn có chắc muốn báo cáo người dùng này?')) {
                showNotification('Đã gửi báo cáo');
            }
        });

        // Block button
        document.querySelector('.btn-block').addEventListener('click', function() {
            if (confirm('Bạn có chắc muốn chặn người dùng này?')) {
                showNotification('Đã chặn người dùng');
                setTimeout(() => {
                    window.location.href = '../trangchu/index.php';
                }, 1500);
            }
        });

        function showNotification(message) {
            const notification = document.createElement('div');
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed;
                top: 100px;
                left: 50%;
                transform: translateX(-50%);
                background: #FF6B9D;
                color: white;
                padding: 15px 30px;
                border-radius: 25px;
                font-size: 16px;
                font-weight: 600;
                box-shadow: 0 5px 20px rgba(255,107,157,0.3);
                z-index: 10000;
                animation: slideDown 0.3s ease;
            `;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 2000);
        }
    </script>
</body>
</html>
