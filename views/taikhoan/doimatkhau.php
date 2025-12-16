<?php
require_once '../../models/mSession.php';
require_once '../../models/mUser.php';
require_once '../../models/mProfile.php';

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
                    <a href="../nhantin/chat.php" class="menu-item">
                        <i class="fas fa-comments"></i>
                        <span>Tin nhắn</span>
                    </a>
                    <a href="../timkiem/ghepdoinhanh.php" class="menu-item">
                        <i class="fas fa-search"></i>
                        <span>Tìm kiếm</span>
                    </a>
                    <a href="#" class="menu-item">
                        <i class="fas fa-question-circle"></i>
                        <span>Trợ giúp</span>
                    </a>
                </nav>
            </div>
            <div class="header-actions">
                <a href="../../controller/cLogout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Đăng xuất</span>
                </a>
            </div>
        </div>
    </header>
    <!-- Password Wrapper -->
    <div class="password-wrapper">
        <!-- Back Button -->
        <button class="back-btn" onclick="window.location.href='../trangchu/index.php'">
            <i class="fas fa-arrow-left"></i>
        </button>

        <!-- Password Container -->
        <div class="password-container">
        <!-- Password Container -->
        <div class="password-container">
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
</body>
</html>