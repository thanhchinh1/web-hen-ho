<?php
require_once '../../models/mSession.php';

Session::start();

// Chuyển về trang chủ nếu đã đăng nhập
if (Session::isLoggedIn()) {
    header('Location: ../trangchu/index.php');
    exit;
}

$errors = Session::getFlash('forgot_errors') ?? [];
$successMessage = Session::getFlash('forgot_success');
$step = Session::get('forgot_password_step') ?? 1;
$userEmail = Session::get('forgot_user_email') ?? '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - Kết Nối Yêu Thương</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/quenmatkhau.css?v=<?php echo time(); ?>">

</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="header-container">
            <a href="../../index.php" class="logo">
                <img src="../../public/img/logo.jpg" alt="DuyenHub Logo">
                <span class="logo-text">DuyenHub</span>
            </a>
            <nav class="header-nav">
                <a href="login.php" class="nav-link">
                    <i class="fas fa-sign-in-alt"></i> Đăng nhập
                </a>
                <a href="../dangky/register.php" class="nav-link">
                    <i class="fas fa-user-plus"></i> Đăng ký
                </a>
            </nav>
        </div>
    </header>

    <div class="forgot-wrapper">
        <div class="forgot-container">
            <button class="back-btn" onclick="clearForgotSession()" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </button>
            
            <div class="forgot-header">
                <div class="header-icon">
                    <i class="fas fa-key"></i>
                </div>
                <h1>Quên mật khẩu?</h1>
                <p>Đừng lo lắng, chúng tôi sẽ giúp bạn lấy lại mật khẩu</p>
            </div>

            <div class="forgot-body">
                <?php if ($successMessage): ?>
                    <div class="success-container">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($successMessage); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="error-container">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="steps">
                    <div class="step <?php echo $step >= 1 ? 'active' : ''; ?> <?php echo $step > 1 ? 'completed' : ''; ?>">
                        <div class="step-number">1</div>
                        <div class="step-label">Xác minh</div>
                    </div>
                    <div class="step <?php echo $step >= 2 ? 'active' : ''; ?> <?php echo $step > 2 ? 'completed' : ''; ?>">
                        <div class="step-number">2</div>
                        <div class="step-label">Đặt lại</div>
                    </div>
                    <div class="step <?php echo $step >= 3 ? 'active' : ''; ?>">
                        <div class="step-number">3</div>
                        <div class="step-label">Hoàn tất</div>
                    </div>
                </div>

                <?php if ($step == 1): ?>
                    <!-- Bước 1: Nhập email -->
                    <div class="info-box">
                        <i class="fas fa-info-circle"></i> Nhập email hoặc số điện thoại bạn đã đăng ký để xác minh tài khoản
                    </div>

                    <form action="../../controller/cForgotPassword.php" method="POST">
                        <input type="hidden" name="step" value="1">
                        
                        <div class="form-group">
                            <label for="email">Email/Số điện thoại</label>
                            <div class="input-wrapper">
                                <input type="text" 
                                       id="email" 
                                       name="email" 
                                       class="form-control"
                                       placeholder="Nhập email hoặc số điện thoại"
                                       value="<?php echo htmlspecialchars($userEmail); ?>"
                                       required>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit">
                            <i class="fas fa-arrow-right"></i> Tiếp tục
                        </button>
                    </form>

                <?php elseif ($step == 2): ?>
                    <!-- Bước 2: Đặt mật khẩu mới -->
                    <div class="info-box info-account">
                        <i class="fas fa-shield-alt"></i> Tài khoản: <strong><?php echo htmlspecialchars($userEmail); ?></strong>
                    </div>

                    <form action="../../controller/cForgotPassword.php" method="POST" id="resetPasswordForm">
                        <input type="hidden" name="step" value="2">

                        <div class="form-group">
                            <label for="new_password">Mật khẩu mới <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <input type="password" 
                                       id="new_password" 
                                       name="new_password" 
                                       class="form-control"
                                       placeholder="Nhập mật khẩu mới"
                                       required>
                                <button type="button" class="password-toggle" onclick="togglePassword('new_password', 'toggleIcon1')">
                                    <i class="fas fa-eye" id="toggleIcon1"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Xác nhận mật khẩu <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <input type="password" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       class="form-control"
                                       placeholder="Nhập lại mật khẩu mới"
                                       required>
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', 'toggleIcon2')">
                                    <i class="fas fa-eye" id="toggleIcon2"></i>
                                </button>
                            </div>
                        </div>

                        <div class="password-requirements">
                            <strong>Yêu cầu mật khẩu:</strong>
                            <ul>
                                <li><i class="fas fa-check"></i> Tối thiểu 8 ký tự</li>
                                <li><i class="fas fa-check"></i> Có chữ thường (a-z)</li>
                                <li><i class="fas fa-check"></i> Có chữ hoa (A-Z)</li>
                                <li><i class="fas fa-check"></i> Có ký tự đặc biệt (!@#$%...)</li>
                            </ul>
                        </div>

                        <button type="submit" class="btn-submit">
                            <i class="fas fa-check"></i> Đặt lại mật khẩu
                        </button>
                    </form>

                <?php endif; ?>

            </div>
        </div>
    </div>

    <script>
        // Clear forgot password session and go back
        function clearForgotSession() {
            window.location.href = '../../controller/cClearForgotSession.php?redirect=' + encodeURIComponent('../views/dangnhap/login.php');
        }

        // Toggle password visibility
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Form validation
        <?php if ($step == 2): ?>
        document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Mật khẩu xác nhận không khớp!');
                return false;
            }

            if (newPassword.length < 8) {
                e.preventDefault();
                alert('Mật khẩu phải có ít nhất 8 ký tự!');
                return false;
            }

            // Check password strength
            const hasLower = /[a-z]/.test(newPassword);
            const hasUpper = /[A-Z]/.test(newPassword);
            const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(newPassword);

            if (!hasLower || !hasUpper || !hasSpecial) {
                e.preventDefault();
                alert('Mật khẩu phải có chữ thường, chữ hoa và ký tự đặc biệt!');
                return false;
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>
