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
$otpSentMessage = Session::getFlash('otp_sent');
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
                    <div class="step-item <?php echo $step >= 1 ? 'active' : ''; ?> <?php echo $step > 1 ? 'completed' : ''; ?>">
                        <div class="step-circle">
                            <?php if ($step > 1): ?>
                                <i class="fas fa-check"></i>
                            <?php else: ?>
                                1
                            <?php endif; ?>
                        </div>
                        <div class="step-label">Xác minh Email</div>
                    </div>
                    <div class="step-line <?php echo $step >= 2 ? 'active' : ''; ?>"></div>
                    <div class="step-item <?php echo $step >= 2 ? 'active' : ''; ?> <?php echo $step > 2 ? 'completed' : ''; ?>">
                        <div class="step-circle">
                            <?php if ($step > 2): ?>
                                <i class="fas fa-check"></i>
                            <?php else: ?>
                                2
                            <?php endif; ?>
                        </div>
                        <div class="step-label">Nhập OTP</div>
                    </div>
                    <div class="step-line <?php echo $step >= 3 ? 'active' : ''; ?>"></div>
                    <div class="step-item <?php echo $step >= 3 ? 'active' : ''; ?>">
                        <div class="step-circle">3</div>
                        <div class="step-label">Đặt lại MK</div>
                    </div>
                </div>

                <?php if ($otpSentMessage): ?>
                    <div class="success-container" style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 8px; margin: 10px 0; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-check-circle" style="font-size: 20px;"></i>
                        <span><?php echo htmlspecialchars($otpSentMessage); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($step == 1): ?>
                    <!-- Bước 1: Nhập email -->
                    <div class="info-box">
                        <i class="fas fa-info-circle"></i> Nhập email bạn đã đăng ký
                    </div>

                    <form action="../../controller/cForgotPassword.php" method="POST">
                        <input type="hidden" name="step" value="1">
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="input-wrapper">
                                <input type="text" 
                                       id="email" 
                                       name="email" 
                                       class="form-control"
                                       placeholder="Nhập email"
                                       value="<?php echo htmlspecialchars($userEmail); ?>"
                                       required>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit">
                            <i class="fas fa-paper-plane"></i> Gửi mã OTP
                        </button>
                    </form>

                <?php elseif ($step == 2): ?>
                    <!-- Bước 2: Nhập OTP -->
                    <div class="info-box info-account">
                        <i class="fas fa-envelope"></i> Mã OTP đã được gửi đến: <strong><?php echo htmlspecialchars($userEmail); ?></strong>
                    </div>

                    <form action="../../controller/cForgotPassword.php" method="POST" id="otpForm">
                        <input type="hidden" name="step" value="2">

                        <div class="form-group">
                            <label>Mã OTP (6 số)</label>
                            <div class="otp-input-group" style="display: flex; gap: 10px; justify-content: center; margin: 30px 0;">
                                <input type="text" class="otp-input" maxlength="1" name="otp1" id="otp1" required autofocus style="width: 50px; height: 60px; text-align: center; font-size: 24px; font-weight: bold; border: 2px solid #ddd; border-radius: 8px;">
                                <input type="text" class="otp-input" maxlength="1" name="otp2" id="otp2" required style="width: 50px; height: 60px; text-align: center; font-size: 24px; font-weight: bold; border: 2px solid #ddd; border-radius: 8px;">
                                <input type="text" class="otp-input" maxlength="1" name="otp3" id="otp3" required style="width: 50px; height: 60px; text-align: center; font-size: 24px; font-weight: bold; border: 2px solid #ddd; border-radius: 8px;">
                                <input type="text" class="otp-input" maxlength="1" name="otp4" id="otp4" required style="width: 50px; height: 60px; text-align: center; font-size: 24px; font-weight: bold; border: 2px solid #ddd; border-radius: 8px;">
                                <input type="text" class="otp-input" maxlength="1" name="otp5" id="otp5" required style="width: 50px; height: 60px; text-align: center; font-size: 24px; font-weight: bold; border: 2px solid #ddd; border-radius: 8px;">
                                <input type="text" class="otp-input" maxlength="1" name="otp6" id="otp6" required style="width: 50px; height: 60px; text-align: center; font-size: 24px; font-weight: bold; border: 2px solid #ddd; border-radius: 8px;">
                            </div>
                            <input type="hidden" name="otp_code" id="otp_code">
                        </div>

                        <div style="text-align: center; margin: 20px 0; color: #666; font-size: 14px;">
                            ⏱️ Mã có hiệu lực trong: <span id="countdown" style="font-weight: bold; color: #FF6B6B;">10:00</span>
                        </div>

                        <button type="submit" class="btn-submit">
                            <i class="fas fa-check"></i> Xác thực OTP
                        </button>

                        <div style="text-align: center; margin-top: 15px;">
                            <button type="button" class="btn-link" onclick="resendOTP()" style="background: none; border: none; color: #FF6B6B; text-decoration: underline; cursor: pointer;">
                                <i class="fas fa-redo"></i> Gửi lại mã OTP
                            </button>
                        </div>
                    </form>

                <?php elseif ($step == 3): ?>
                    <!-- Bước 3: Đặt mật khẩu mới -->
                    <div class="info-box info-account">
                        <i class="fas fa-shield-alt"></i> Tài khoản: <strong><?php echo htmlspecialchars($userEmail); ?></strong>
                    </div>

                    <form action="../../controller/cForgotPassword.php" method="POST" id="resetPasswordForm">
                        <input type="hidden" name="step" value="3">

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

        // Validate email format
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        // Form validation
        <?php if ($step == 1): ?>
        // Validation cho form nhập email
        document.querySelector('form').addEventListener('submit', function(e) {
            const emailInput = document.getElementById('email');
            const email = emailInput.value.trim();
            
            if (!email) {
                e.preventDefault();
                alert('Vui lòng nhập email!');
                emailInput.focus();
                return;
            }
            
            if (!validateEmail(email)) {
                e.preventDefault();
                alert('Email không đúng định dạng! Vui lòng nhập email hợp lệ.');
                emailInput.focus();
                return;
            }
        });
        <?php elseif ($step == 2): ?>
        // OTP Input Auto-focus và validation
        const otpInputs = document.querySelectorAll('.otp-input');
        
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                const value = e.target.value;
                
                // Chỉ cho phép số
                if (!/^\d*$/.test(value)) {
                    e.target.value = '';
                    return;
                }
                
                // Auto focus ô tiếp theo
                if (value && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            });
            
            // Xử lý phím Backspace
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });
            
            // Xử lý paste
            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text');
                const digits = pastedData.replace(/\D/g, '').slice(0, 6);
                
                digits.split('').forEach((digit, i) => {
                    if (otpInputs[i]) {
                        otpInputs[i].value = digit;
                    }
                });
                
                if (digits.length > 0) {
                    otpInputs[Math.min(digits.length, 5)].focus();
                }
            });
        });
        
        // Submit form - ghép các số OTP lại
        document.getElementById('otpForm').addEventListener('submit', (e) => {
            let otpCode = '';
            otpInputs.forEach(input => {
                otpCode += input.value;
            });
            
            if (otpCode.length !== 6) {
                e.preventDefault();
                alert('Vui lòng nhập đủ 6 số!');
                return;
            }
            
            document.getElementById('otp_code').value = otpCode;
        });
        
        // Countdown timer (10 phút = 600 giây)
        let timeLeft = 600;
        const countdownEl = document.getElementById('countdown');
        
        const countdown = setInterval(() => {
            timeLeft--;
            
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            countdownEl.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            // Cảnh báo khi còn 2 phút
            if (timeLeft <= 120) {
                countdownEl.style.color = '#dc3545';
            }
            
            // Hết thời gian
            if (timeLeft <= 0) {
                clearInterval(countdown);
                countdownEl.textContent = 'Đã hết hạn';
                alert('Mã OTP đã hết hạn! Vui lòng gửi lại mã mới.');
            }
        }, 1000);
        
        // Resend OTP function
        function resendOTP() {
            if (!confirm('Bạn có chắc muốn gửi lại mã OTP?')) {
                return;
            }
            window.location.href = '../../controller/cResendForgotOTP.php';
        }
        
        <?php elseif ($step == 3): ?>
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
