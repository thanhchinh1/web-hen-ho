<?php
require_once '../../models/session.php';
require_once '../../models/mProfile.php';
require_once '../../models/mLike.php';

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

$likeModel = new LikeModel();
$isLiked = $likeModel->isLiked($currentUserId, $profileId);
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
            <div class="header-left">
                <i class="fas fa-users"></i>
                <span class="logo-text">Mạng Xã Hội</span>
            </div>
            <div class="header-center">
                <a href="../trangchu/index.php" class="nav-link">Trang chủ</a>
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
                    <button class="btn-action btn-like <?php echo $isLiked ? 'active' : ''; ?>" onclick="toggleProfileLike(<?php echo $profileId; ?>, this)" aria-pressed="<?php echo $isLiked ? 'true' : 'false'; ?>">
                        <i class="<?php echo $isLiked ? 'fas' : 'far'; ?> fa-heart"></i>
                        <span class="btn-text"><?php echo $isLiked ? 'Đã thích' : 'Thả tim'; ?></span>
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
        const reportButton = document.querySelector('.btn-report');
        const blockButton = document.querySelector('.btn-block');

        reportButton.addEventListener('click', function () {
            if (confirm('Bạn có chắc muốn báo cáo hồ sơ này?')) {
                showToast('Đã gửi báo cáo. Cảm ơn bạn!', 'info');
            }
        });

        blockButton.addEventListener('click', function () {
            if (confirm('Bạn có chắc muốn chặn người dùng này?')) {
                showToast('Đã chặn người dùng.', 'success');
                setTimeout(() => {
                    window.location.href = '../trangchu/index.php';
                }, 1500);
            }
        });

        function toggleProfileLike(userId, button) {
            if (!button) return;

            const formData = new FormData();
            formData.append('user_id', userId);

            button.disabled = true;

            fetch('../../controller/like_toggle.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        showToast(data.message, 'error');
                        return;
                    }

                    updateProfileLikeButton(button, data.liked);
                    showToast(data.message, data.liked ? 'success' : 'info');
                })
                .catch(() => {
                    showToast('Không thể kết nối tới máy chủ. Vui lòng thử lại!', 'error');
                })
                .finally(() => {
                    button.disabled = false;
                });
        }

        function updateProfileLikeButton(button, liked) {
            const icon = button.querySelector('i');
            const text = button.querySelector('.btn-text');

            button.classList.toggle('active', liked);
            button.setAttribute('aria-pressed', liked ? 'true' : 'false');

            if (icon) {
                if (liked) {
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                } else {
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                }
            }

            if (text) {
                text.textContent = liked ? 'Đã thích' : 'Thả tim';
            }
        }

        function showToast(message, type = 'info') {
            const existing = document.querySelector('.toast-notification');
            if (existing) {
                existing.remove();
            }

            const colors = {
                success: '#28a745',
                error: '#dc3545',
                info: '#5BC0DE'
            };

            const toast = document.createElement('div');
            toast.className = 'toast-notification';
            toast.textContent = message;
            toast.style.cssText = `
                position: fixed;
                top: 100px;
                left: 50%;
                transform: translateX(-50%);
                padding: 14px 28px;
                border-radius: 999px;
                font-size: 15px;
                font-weight: 600;
                color: #fff;
                background: ${colors[type] || colors.info};
                box-shadow: 0 12px 30px rgba(0,0,0,0.15);
                z-index: 11000;
                animation: fadeInDown 0.2s ease;
            `;

            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.animation = 'fadeOutUp 0.2s ease forwards';
                toast.addEventListener('animationend', () => toast.remove());
            }, 1800);
        }
    </script>
</body>
</html>
