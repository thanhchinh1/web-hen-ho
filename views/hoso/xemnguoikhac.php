<?php
require_once '../../models/session.php';
require_once '../../models/mProfile.php';

requireLogin(); // Yêu cầu đăng nhập để xem hồ sơ

$currentUserId = getCurrentUserId();

// Lấy ID người dùng cần xem từ URL
$profileId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($profileId === 0 || $profileId === $currentUserId) {
    // Không có ID hoặc đang xem chính mình
    header('Location: index.php');
    exit;
}

// Lấy thông tin hồ sơ
$profileModel = new Profile();
$profile = $profileModel->getProfile($profileId);

if (!$profile) {
    // Không tìm thấy hồ sơ
    header('Location: ../trangchu/index.php');
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
    <title>Hồ sơ - Kết Nối Yêu Thương</title>
    <link rel="stylesheet" href="../../public/css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="profile-container">
        <!-- Header -->
        <header class="profile-header">
           <a href="../trangchu/index.php" class="logo">
                <img src="../../public/img/logo.jpg" alt="Kết Nối Yêu Thương">
                <span class="logo-text">DuyenHub</span>
            </a>
            <div class="header-center">
                <a href="../trangchu/index.php" class="nav-item active">
                    <i class="fas fa-home"></i>
                    Trang chủ
                </a>
            </div>
            <div class="header-right">
                <a href="../../controller/logout.php" class="btn-logout">
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
                    <img src="<?php echo $avatarSrc; ?>" alt="<?php echo htmlspecialchars($profile['ten']); ?>" class="profile-avatar" id="userAvatar">
                    <h1 class="profile-name" id="userName"><?php echo htmlspecialchars($profile['ten']); ?></h1>
                    <p class="profile-info" id="userBasicInfo"><?php echo $age; ?> tuổi • <?php echo htmlspecialchars($profile['noiSong']); ?> • <?php echo htmlspecialchars($profile['tinhTrangHonNhan']); ?></p>
                </div>

                <!-- Action Buttons -->
                <div class="profile-actions">
                    <button class="btn-action btn-like" onclick="likeProfile(<?php echo $profileId; ?>)">
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
                            <span class="info-value"><?php echo htmlspecialchars($profile['gioiTinh']); ?></span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span class="info-label">Tuổi:</span>
                            <span class="info-value"><?php echo $age; ?> tuổi</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span class="info-label">Thành phố:</span>
                            <span class="info-value"><?php echo htmlspecialchars($profile['noiSong']); ?></span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-heart"></i>
                            <span class="info-label">Tình trạng hôn nhân:</span>
                            <span class="info-value"><?php echo htmlspecialchars($profile['tinhTrangHonNhan']); ?></span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-weight"></i>
                            <span class="info-label">Cân nặng:</span>
                            <span class="info-value"><?php echo htmlspecialchars($profile['canNang']); ?> kg</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-ruler-vertical"></i>
                            <span class="info-label">Chiều cao:</span>
                            <span class="info-value"><?php echo htmlspecialchars($profile['chieuCao']); ?> cm</span>
                        </div>
                    </div>
                </section>

                <!-- Career & Education -->
                <section class="detail-section">
                    <h2 class="section-title">Học vấn & Mục tiêu</h2>
                    
                    <div class="info-list">
                        <div class="info-item">
                            <i class="fas fa-graduation-cap"></i>
                            <span class="info-label">Học vấn:</span>
                            <span class="info-value"><?php echo htmlspecialchars($profile['hocVan']); ?></span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-bullseye"></i>
                            <span class="info-label">Mục tiêu:</span>
                            <span class="info-value"><?php echo htmlspecialchars($profile['mucTieuPhatTrien']); ?></span>
                        </div>
                    </div>
                </section>

                <!-- Interests -->
                <section class="detail-section">
                    <h2 class="section-title">Sở thích</h2>
                    <div class="interests-tags">
                        <?php foreach ($interests as $interest): ?>
                            <?php if (!empty(trim($interest))): ?>
                                <span class="interest-tag"><?php echo htmlspecialchars(trim($interest)); ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- About Me -->
                <section class="detail-section">
                    <h2 class="section-title">Về tôi</h2>
                    <p class="about-text">
                        <?php echo nl2br(htmlspecialchars($profile['moTa'])); ?>
                    </p>
                </section>
            </div>
        </div>
    </div>

    <script>
        // Like button AJAX
        function likeProfile(likedUserId) {
            fetch('../../controller/thich.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=like&likedUserId=' + likedUserId
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showNotification('Đã thích!');
                    // Có thể reload hoặc cập nhật giao diện
                } else {
                    showNotification('Bạn đã thả tim hoặc lỗi!');
                }
            });
        }
        
        // Report button
        document.querySelector('.btn-report').addEventListener('click', function() {
            if (confirm('Bạn có chắc muốn báo cáo hồ sơ này?')) {
                alert('Báo cáo đã được gửi. Cảm ơn bạn!');
            }
        });
        
        // Block button
        document.querySelector('.btn-block').addEventListener('click', function() {
            if (confirm('Bạn có chắc muốn chặn người dùng này?')) {
                alert('Đã chặn người dùng này!');
            }
        });
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
