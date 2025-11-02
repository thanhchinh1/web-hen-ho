<?php
require_once '../../models/mSession.php';
require_once '../../models/mUser.php';

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        .main-header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 0;
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
            gap: 12px;
        }

        .logo img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
        }

        .logo-text {
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .btn-back {
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-back:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        /* Main Content */
        .content-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .password-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .card-header i {
            font-size: 50px;
            margin-bottom: 15px;
        }

        .card-header h1 {
            font-size: 28px;
            margin-bottom: 8px;
        }

        .card-header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .card-body {
            padding: 40px 30px;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .info-box {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid #667eea;
        }

        .info-box h4 {
            color: #667eea;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .info-box ul {
            margin: 0;
            padding-left: 20px;
            color: #666;
            font-size: 13px;
            line-height: 1.8;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .required {
            color: #e74c3c;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            z-index: 1;
        }

        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 12px 45px 12px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        input[type="password"]:focus,
        input[type="text"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            transition: color 0.3s;
        }

        .toggle-password:hover {
            color: #667eea;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .form-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .form-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        .password-strength {
            margin-top: 8px;
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            overflow: hidden;
            display: none;
        }

        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: all 0.3s;
        }

        .strength-weak { background: #dc3545; width: 33%; }
        .strength-medium { background: #ffc107; width: 66%; }
        .strength-strong { background: #28a745; width: 100%; }

        .password-hint {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="header-container">
            <a href="../trangchu/index.php" class="logo">
                <img src="../../public/img/logo.jpg" alt="DuyenHub">
                <span class="logo-text">DuyenHub</span>
            </a>
            <a href="../trangchu/index.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <div class="content-wrapper">
        <div class="password-card">
            <div class="card-header">
                <i class="fas fa-key"></i>
                <h1>Đổi mật khẩu</h1>
                <p>Cập nhật mật khẩu của bạn</p>
            </div>

            <div class="card-body">
                <?php if ($successMessage): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($successMessage); ?>
                    </div>
                <?php endif; ?>

                <?php if ($errorMessage): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($errorMessage); ?>
                    </div>
                <?php endif; ?>

                <div class="info-box">
                    <h4><i class="fas fa-info-circle"></i> Yêu cầu mật khẩu mới</h4>
                    <ul>
                        <li>Mật khẩu phải có ít nhất <strong>8 ký tự</strong></li>
                        <li>Bao gồm ít nhất <strong>1 chữ thường</strong> (a-z)</li>
                        <li>Bao gồm ít nhất <strong>1 chữ hoa</strong> (A-Z)</li>
                        <li>Bao gồm ít nhất <strong>1 ký tự đặc biệt</strong> (!@#$%^&*...)</li>
                    </ul>
                </div>

                <form action="../../controller/cChangePassword.php" method="POST" id="changePasswordForm">
                    <input type="hidden" name="csrf_token" value="<?php echo Session::getCSRFToken(); ?>">

                    <div class="form-group">
                        <label>
                            Mật khẩu hiện tại <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="current_password" id="currentPassword" placeholder="Nhập mật khẩu hiện tại" required>
                            <i class="fas fa-eye toggle-password" onclick="togglePassword('currentPassword', this)"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>
                            Mật khẩu mới <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-key input-icon"></i>
                            <input type="password" name="new_password" id="newPassword" placeholder="Nhập mật khẩu mới" required oninput="checkPasswordStrength(this.value)">
                            <i class="fas fa-eye toggle-password" onclick="togglePassword('newPassword', this)"></i>
                        </div>
                        <div class="password-strength" id="passwordStrength">
                            <div class="password-strength-bar" id="strengthBar"></div>
                        </div>
                        <div class="password-hint" id="passwordHint"></div>
                    </div>

                    <div class="form-group">
                        <label>
                            Xác nhận mật khẩu mới <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock-open input-icon"></i>
                            <input type="password" name="confirm_password" id="confirmPassword" placeholder="Nhập lại mật khẩu mới" required>
                            <i class="fas fa-eye toggle-password" onclick="togglePassword('confirmPassword', this)"></i>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-check"></i> Đổi mật khẩu
                    </button>
                </form>

                <div class="form-footer">
                    <p><a href="../trangchu/index.php"><i class="fas fa-home"></i> Về trang chủ</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword(inputId, icon) {
            const input = document.getElementById(inputId);
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

        // Check password strength
        function checkPasswordStrength(password) {
            const strengthBar = document.getElementById('strengthBar');
            const strengthContainer = document.getElementById('passwordStrength');
            const hint = document.getElementById('passwordHint');
            
            if (password.length === 0) {
                strengthContainer.style.display = 'none';
                hint.textContent = '';
                return;
            }
            
            strengthContainer.style.display = 'block';
            
            let strength = 0;
            const checks = {
                length: password.length >= 8,
                lower: /[a-z]/.test(password),
                upper: /[A-Z]/.test(password),
                special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
            };
            
            strength = Object.values(checks).filter(Boolean).length;
            
            strengthBar.className = 'password-strength-bar';
            
            if (strength <= 2) {
                strengthBar.classList.add('strength-weak');
                hint.textContent = '❌ Mật khẩu yếu';
                hint.style.color = '#dc3545';
            } else if (strength === 3) {
                strengthBar.classList.add('strength-medium');
                hint.textContent = '⚠️ Mật khẩu trung bình';
                hint.style.color = '#ffc107';
            } else {
                strengthBar.classList.add('strength-strong');
                hint.textContent = '✅ Mật khẩu mạnh';
                hint.style.color = '#28a745';
            }
        }

        // Form validation
        document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            // Check if passwords match
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Mật khẩu mới và xác nhận mật khẩu không khớp!');
                return false;
            }
            
            // Check password strength
            const checks = {
                length: newPassword.length >= 8,
                lower: /[a-z]/.test(newPassword),
                upper: /[A-Z]/.test(newPassword),
                special: /[!@#$%^&*(),.?":{}|<>]/.test(newPassword)
            };
            
            if (!checks.length) {
                e.preventDefault();
                alert('Mật khẩu phải có ít nhất 8 ký tự!');
                return false;
            }
            
            if (!checks.lower) {
                e.preventDefault();
                alert('Mật khẩu phải có ít nhất 1 chữ thường (a-z)!');
                return false;
            }
            
            if (!checks.upper) {
                e.preventDefault();
                alert('Mật khẩu phải có ít nhất 1 chữ hoa (A-Z)!');
                return false;
            }
            
            if (!checks.special) {
                e.preventDefault();
                alert('Mật khẩu phải có ít nhất 1 ký tự đặc biệt (!@#$%^&*...)!');
                return false;
            }
        });
    </script>
</body>
</html>
