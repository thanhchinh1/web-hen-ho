<?php
require_once '../../models/session.php';
redirectIfLoggedIn(); // Chuyển về trang chủ nếu đã đăng nhập

$errors = isset($_SESSION['login_errors']) ? $_SESSION['login_errors'] : [];
$formData = isset($_SESSION['login_data']) ? $_SESSION['login_data'] : [];
// Xóa errors và data sau khi đã lấy
unset($_SESSION['login_errors']);
unset($_SESSION['login_data']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Kết Nối Yêu Thương</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/login.css">
</head>
<body>
    <div class="login-container">
        <button class="close-btn" onclick="window.location.href='../../index.php'" aria-label="Đóng">
            <i class="fas fa-times"></i>
        </button>

        <div class="login-header">
            <h1>Chào Mừng Trở Lại!</h1>
            <p>Vui lòng đăng nhập vào tài khoản của bạn để tiếp tục tìm kiếm người đặc biệt.</p>
            
            <?php if (isset($_GET['redirect']) && $_GET['redirect'] === 'profile'): ?>
                <div class="info-container" style="background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 8px; margin: 10px 0;">
                    <i class="fas fa-info-circle"></i> Vui lòng đăng nhập để xem hồ sơ này
                </div>
            <?php endif; ?>
            
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

        <form action="../../controller/login.php<?php echo isset($_GET['redirect']) && isset($_GET['id']) ? '?redirect=' . htmlspecialchars($_GET['redirect']) . '&id=' . htmlspecialchars($_GET['id']) : ''; ?>" method="POST" id="loginForm">
            <div class="form-group">
                <label for="email">Email/SĐT</label>
                <div class="input-wrapper">
                    <input 
                        type="text" 
                        id="email" 
                        name="email" 
                        class="form-control" 
                        placeholder="email@example.com"
                        value="<?php echo isset($formData['email']) ? htmlspecialchars($formData['email']) : ''; ?>"
                        required
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <div class="input-wrapper">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        placeholder="••••••••"
                        required
                    >
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
                <div class="forgot-password">
                    <a href="../admin/doimatkhau.php">Quên mật khẩu?</a>
                </div>
            </div>

            <button type="submit" class="btn-login">
                Đăng nhập
            </button>
        </form>

        <div class="divider">
            <span>hoặc đăng nhập với</span>
        </div>

        <div class="social-login">
            <button class="btn-social facebook" onclick="loginWithFacebook()">
                <i class="fab fa-facebook-f"></i>
            </button>
            <button class="btn-social google" onclick="loginWithGoogle()">
                <i class="fab fa-google"></i>
            </button>
            <button class="btn-social twitter" onclick="loginWithTwitter()">
                <i class="fab fa-twitter"></i>
            </button>
        </div>

        <div class="signup-link">
            Chưa có tài khoản? <a href="../dangky/register.php">Đăng ký</a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
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

        function loginWithFacebook() {
            alert('Tính năng đăng nhập bằng Facebook sẽ sớm được cập nhật!');
        }

        function loginWithGoogle() {
            alert('Tính năng đăng nhập bằng Google sẽ sớm được cập nhật!');
        }

        function loginWithTwitter() {
            alert('Tính năng đăng nhập bằng Twitter sẽ sớm được cập nhật!');
        }

        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('Vui lòng nhập đầy đủ thông tin!');
                return;
            }
            // Form sẽ được submit tự động
        });
    </script>
</body>
</html>