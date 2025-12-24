<?php
require_once '../../models/mSession.php';
require_once '../../models/mUser.php';
require_once '../../models/mProfile.php';
require_once '../../models/mNotification.php';
require_once '../../models/mMessage.php';

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

$userId = Session::getUserId();
$userEmail = Session::getUserEmail();

// Lấy profile để hiển thị avatar
$profileModel = new Profile();
$profile = $profileModel->getProfile($userId);
$avatarPath = !empty($profile['avt']) ? $profile['avt'] : 'public/img/default-avatar.jpg';

// Đếm số ghép đôi mới và tin nhắn chưa đọc
$notificationModel = new Notification();
$newMatchesCount = $notificationModel->getNewMatchesCount($userId);
$messageModel = new Message();
$unreadMessagesCount = $messageModel->getTotalUnreadCount($userId);

// Lấy flash messages
$successMessage = Session::getFlash('success_message');
$errorMessage = Session::getFlash('error_message');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi mật khẩu - DuyenHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/doimatkhau.css?v=<?php echo time(); ?>">
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="header-container">
            <div class="header-left">
                <a href="../trangchu/index.php" class="logo">
                    <img src="../../public/img/logo.jpg" alt="DuyenHub Logo">
                    <span class="logo-text">DuyenHub</span>
                </a>
                <nav class="header-menu">
                    <a href="../trangchu/index.php" class="menu-item active">
                        <i class="fas fa-home"></i>
                        <span>Trang chủ</span>
                    </a>
                    <a href="../nhantin/message.php" class="menu-item" style="position: relative;">
                        <i class="fas fa-comments"></i>
                        <span>Tin nhắn</span>
                        <?php if ($unreadMessagesCount > 0): ?>
                        <span class="notification-badge" id="messagesBadge" style="position: absolute; top: -5px; right: -5px; background: #ff4757; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold;"><?php echo $unreadMessagesCount; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="../timkiem/ghepdoinhanh.php" class="menu-item" style="position: relative;">
                        <i class="fas fa-search"></i>
                        <span>Tìm kiếm</span>
                        <?php if ($newMatchesCount > 0): ?>
                        <span class="notification-badge" id="matchesBadge" style="position: absolute; top: -5px; right: -5px; background: #ff6b9d; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold;"><?php echo $newMatchesCount; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="#" class="menu-item">
                        <i class="fas fa-question-circle"></i>
                        <span>Trợ giúp</span>
                    </a>
                </nav>
            </div>
            <div class="header-actions">
                <!-- Đã xóa nút đăng xuất để đồng bộ với yêu cầu -->
            </div>
        </div>
    </header>
    <!-- Password Wrapper -->
    <div class="password-wrapper">
        <!-- Password Container -->
        <div class="password-container" style="position:relative;">
            <!-- Back Button -->
            <button class="back-btn" onclick="window.history.back()">
                <i class="fas fa-arrow-left"></i>
            </button>
            <!-- Page Header -->
            <div class="page-header">
                <div class="header-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h1>Đổi mật khẩu</h1>
                <p>Cập nhật mật khẩu để bảo mật tài khoản của bạn</p>
            </div>

            <!-- Alert Messages -->
            <?php if ($successMessage): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo htmlspecialchars($successMessage); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($errorMessage): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($errorMessage); ?></span>
                </div>
            <?php endif; ?>

            <!-- Password Requirements -->
            <div class="info-box">
                <h4>
                    <i class="fas fa-info-circle"></i>
                    Yêu cầu mật khẩu mới
                </h4>
                <ul>
                    <li><i class="fas fa-check"></i> Mật khẩu phải có ít nhất <strong>8 ký tự</strong></li>
                    <li><i class="fas fa-check"></i> Bao gồm ít nhất <strong>1 chữ thường</strong> (a-z)</li>
                    <li><i class="fas fa-check"></i> Bao gồm ít nhất <strong>1 chữ hoa</strong> (A-Z)</li>
                    <li><i class="fas fa-check"></i> Bao gồm ít nhất <strong>1 ký tự đặc biệt</strong> (!@#$%^&*...)</li>
                </ul>
            </div>

            <!-- Change Password Form -->
            <form method="POST" action="../../controller/cChangePassword.php" id="changePasswordForm">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo Session::generateCSRFToken(); ?>">
                
                <div class="form-section">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-lock"></i>
                            Mật khẩu hiện tại <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="password" 
                                   name="current_password" 
                                   id="currentPassword"
                                   class="form-input"
                                   placeholder="Nhập mật khẩu hiện tại"
                                   required>
                            <i class="fas fa-eye toggle-password" onclick="togglePassword('currentPassword')"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-key"></i>
                            Mật khẩu mới <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="password" 
                                   name="new_password" 
                                   id="newPassword"
                                   class="form-input"
                                   placeholder="Nhập mật khẩu mới"
                                   required>
                            <i class="fas fa-eye toggle-password" onclick="togglePassword('newPassword')"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-check-circle"></i>
                            Xác nhận mật khẩu mới <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <input type="password" 
                                   name="confirm_password" 
                                   id="confirmPassword"
                                   class="form-input"
                                   placeholder="Nhập lại mật khẩu mới"
                                   required>
                            <i class="fas fa-eye toggle-password" onclick="togglePassword('confirmPassword')"></i>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Đổi mật khẩu
                </button>
            </form>
        </div>
    </div>


    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling;
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Mật khẩu mới và xác nhận mật khẩu không khớp!');
                return false;
            }
            
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*]).{8,}$/;
            if (!passwordRegex.test(newPassword)) {
                e.preventDefault();
                alert('Mật khẩu mới không đáp ứng yêu cầu!\n\n' +
                      'Mật khẩu phải có:\n' +
                      '- Ít nhất 8 ký tự\n' +
                      '- Ít nhất 1 chữ thường (a-z)\n' +
                      '- Ít nhất 1 chữ hoa (A-Z)\n' +
                      '- Ít nhất 1 ký tự đặc biệt (!@#$%^&*...)');
                return false;
            }
        });
    </script>

    <!-- Script cập nhật trạng thái online -->
    <script>
        function updateOnlineStatus() {
            fetch('../../controller/cUpdateOnlineStatus.php', {method: 'POST'})
            .then(response => response.json())
            .catch(error => console.error('Error:', error));
        }
        updateOnlineStatus();
        setInterval(updateOnlineStatus, 120000);
    </script>

    <!-- Script check thông báo -->
    <script>
        let lastNotificationCount = <?php echo ($newMatchesCount + $unreadMessagesCount); ?>;
        function checkNotifications() {
            fetch('../../controller/cCheckNotifications.php', {method: 'GET', cache: 'no-cache'})
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const messagesBadge = document.getElementById('messagesBadge');
                    if (data.unreadMessages > 0) {
                        if (messagesBadge) {
                            messagesBadge.textContent = data.unreadMessages;
                        } else {
                            const messagesLink = document.querySelector('a[href="../nhantin/message.php"]');
                            if (messagesLink) {
                                const badge = document.createElement('span');
                                badge.id = 'messagesBadge';
                                badge.textContent = data.unreadMessages;
                                badge.style.cssText = 'position: absolute; top: -5px; right: -5px; background: #ff4757; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold;';
                                messagesLink.appendChild(badge);
                            }
                        }
                    } else if (messagesBadge) messagesBadge.remove();
                    const matchesBadge = document.getElementById('matchesBadge');
                    if (data.newMatches > 0) {
                        if (matchesBadge) {
                            matchesBadge.textContent = data.newMatches;
                        } else {
                            const searchLink = document.querySelector('a[href="../timkiem/ghepdoinhanh.php"]');
                            if (searchLink) {
                                const badge = document.createElement('span');
                                badge.id = 'matchesBadge';
                                badge.textContent = data.newMatches;
                                badge.style.cssText = 'position: absolute; top: -5px; right: -5px; background: #ff6b9d; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold;';
                                searchLink.appendChild(badge);
                            }
                        }
                    } else if (matchesBadge) matchesBadge.remove();
                    const currentTotal = data.unreadMessages + data.newMatches;
                    if (currentTotal > lastNotificationCount) {
                        let message = '';
                        if (data.unreadMessages > 0 && data.newMatches > 0) {
                            message = `💬 ${data.unreadMessages} tin nhắn mới và 💕 ${data.newMatches} ghép đôi mới!`;
                        } else if (data.unreadMessages > 0) {
                            message = `💬 Bạn có ${data.unreadMessages} tin nhắn mới!`;
                        } else if (data.newMatches > 0) {
                            message = `💕 Bạn có ${data.newMatches} ghép đôi mới!`;
                        }
                        if (message) {
                            const notif = document.createElement('div');
                            notif.style.cssText = 'position: fixed; top: 80px; right: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 18px 25px; border-radius: 15px; font-size: 15px; font-weight: 600; box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4); z-index: 10000; cursor: pointer; animation: slideInRight 0.5s ease; max-width: 350px;';
                            notif.innerHTML = `
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <i class="fas fa-bell" style="font-size: 24px;"></i>
                                    <div>
                                        <div style="font-size: 16px; margin-bottom: 4px;">${message}</div>
                                        <div style="font-size: 12px; opacity: 0.9;">Click để xem ngay →</div>
                                    </div>
                                </div>
                            `;
                            notif.onclick = () => { window.location.href = '../nhantin/message.php'; };
                            document.body.appendChild(notif);
                            setTimeout(() => { notif.style.animation = 'slideOutRight 0.3s ease'; setTimeout(() => notif.remove(), 300); }, 2000);
                        }
                    }
                    lastNotificationCount = currentTotal;
                }
            }).catch(error => console.error('Error:', error));
        }
        setTimeout(checkNotifications, 2000);
        setInterval(checkNotifications, 500);
    </script>
    <style>
        @keyframes slideInRight { from { transform: translateX(400px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes slideOutRight { from { transform: translateX(0); opacity: 1; } to { transform: translateX(400px); opacity: 0; } }
    </style>
    <script>
        window.addEventListener('beforeunload', function() {
            navigator.sendBeacon('../../controller/cSetOffline.php');
        });
    </script>
</body>
</html>