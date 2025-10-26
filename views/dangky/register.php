<?php
require_once '../../models/session.php';
redirectIfLoggedIn(); // Chuyển về trang chủ nếu đã đăng nhập

$errors = isset($_SESSION['register_errors']) ? $_SESSION['register_errors'] : [];
$formData = isset($_SESSION['register_data']) ? $_SESSION['register_data'] : [];
// Xóa errors và data sau khi đã lấy
unset($_SESSION['register_errors']);
unset($_SESSION['register_data']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản - Kết Nối Yêu Thương</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/register.css">
</head>
<body>
    <div class="register-container">
        <button class="close-btn" onclick="window.location.href='../../index.php'" aria-label="Đóng">
            <i class="fas fa-times"></i>
        </button>

        <div class="register-header">
            <h1>Đăng ký tài khoản</h1>
            <p>Hãy tham gia cộng đồng của chúng tôi để tìm thấy tình yêu!</p>
            
            <?php if (!empty($errors)): ?>
                <div class="error-container" style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 8px; margin: 10px 0;">
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <form action="../../controller/register.php" method="POST" id="registerForm">
            <div class="form-group">
                <label for="email">Email/SĐT</label>
                <div class="input-wrapper">
                    <input 
                        type="text" 
                        id="email" 
                        name="email" 
                        class="form-control" 
                        placeholder="Nhập email hoặc số điện thoại"
                        value="<?php echo isset($formData['email']) ? htmlspecialchars($formData['email']) : ''; ?>"
                        required
                    >
                </div>
                <span class="error-message">Vui lòng nhập email hoặc số điện thoại hợp lệ</span>
            </div>

            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <div class="input-wrapper">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        placeholder="Nhập mật khẩu"
                        required
                    >
                    <button type="button" class="password-toggle" onclick="togglePassword('password', 'toggleIcon1')">
                        <i class="fas fa-eye" id="toggleIcon1"></i>
                    </button>
                </div>
                <div class="password-strength" id="passwordStrength">
                    <div class="strength-bar">
                        <div class="strength-fill" id="strengthFill"></div>
                    </div>
                    <span class="strength-text" id="strengthText"></span>
                </div>
                <span class="error-message">Mật khẩu phải có ít nhất 6 ký tự</span>
            </div>

            <div class="form-group">
                <label for="confirm_password">Xác nhận mật khẩu</label>
                <div class="input-wrapper">
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        class="form-control" 
                        placeholder="Nhập lại mật khẩu"
                        required
                    >
                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', 'toggleIcon2')">
                        <i class="fas fa-eye" id="toggleIcon2"></i>
                    </button>
                </div>
                <span class="error-message">Mật khẩu xác nhận không khớp</span>
            </div>

            <button type="submit" class="btn-register">
                Đăng ký
            </button>

            <div class="terms">
                Bằng việc đăng ký, bạn đồng ý với 
                <a href="#">Điều khoản dịch vụ</a> và 
                <a href="#">Chính sách bảo mật</a> của chúng tôi
            </div>
        </form>

        <div class="divider">
            <span>hoặc đăng ký với</span>
        </div>

        <div class="social-register">
            <button class="btn-social facebook" onclick="registerWithFacebook()">
                <i class="fab fa-facebook-f"></i>
            </button>
            <button class="btn-social google" onclick="registerWithGoogle()">
                <i class="fab fa-google"></i>
            </button>
            <button class="btn-social twitter" onclick="registerWithTwitter()">
                <i class="fab fa-twitter"></i>
            </button>
        </div>

        <div class="login-link">
            Đã có tài khoản? <a href="../dangnhap/login.php">Đăng nhập</a>
        </div>
    </div>

    <script>
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

        // Password strength checker
        const passwordInput = document.getElementById('password');
        const strengthContainer = document.getElementById('passwordStrength');
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            
            if (password.length === 0) {
                strengthContainer.classList.remove('active');
                return;
            }
            
            strengthContainer.classList.add('active');
            
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z\d]/.test(password)) strength++;
            
            strengthFill.className = 'strength-fill';
            
            if (strength <= 2) {
                strengthFill.classList.add('weak');
                strengthText.textContent = 'Mật khẩu yếu';
                strengthText.style.color = '#dc3545';
            } else if (strength <= 4) {
                strengthFill.classList.add('medium');
                strengthText.textContent = 'Mật khẩu trung bình';
                strengthText.style.color = '#ffc107';
            } else {
                strengthFill.classList.add('strong');
                strengthText.textContent = 'Mật khẩu mạnh';
                strengthText.style.color = '#28a745';
            }
        });

        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            // Remove all error states
            document.querySelectorAll('.form-group').forEach(group => {
                group.classList.remove('error');
            });
            
            let isValid = true;
            
            // Validate email/phone
            const email = document.getElementById('email').value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const phoneRegex = /^[0-9]{10,11}$/;
            
            if (!emailRegex.test(email) && !phoneRegex.test(email)) {
                document.getElementById('email').closest('.form-group').classList.add('error');
                isValid = false;
            }
            
            // Validate password
            const password = document.getElementById('password').value;
            if (password.length < 6) {
                document.getElementById('password').closest('.form-group').classList.add('error');
                isValid = false;
            }
            
            // Validate confirm password
            const confirmPassword = document.getElementById('confirm_password').value;
            if (password !== confirmPassword) {
                document.getElementById('confirm_password').closest('.form-group').classList.add('error');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
            // Form sẽ được submit tự động nếu isValid = true
        });

        function registerWithFacebook() {
            alert('Tính năng đăng ký bằng Facebook sẽ sớm được cập nhật!');
        }

        function registerWithGoogle() {
            alert('Tính năng đăng ký bằng Google sẽ sớm được cập nhật!');
        }

        function registerWithTwitter() {
            alert('Tính năng đăng ký bằng Twitter sẽ sớm được cập nhật!');
        }
    </script>
</body>
</html>