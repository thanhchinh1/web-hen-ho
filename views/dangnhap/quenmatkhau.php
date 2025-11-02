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
    <title>Quên mật khẩu - DuyenHub</title>
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
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .forgot-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
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

        .forgot-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .forgot-header i {
            font-size: 50px;
            margin-bottom: 15px;
        }

        .forgot-header h1 {
            font-size: 28px;
            margin-bottom: 8px;
        }

        .forgot-header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .forgot-body {
            padding: 40px 30px;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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

        .input-wrapper {
            position: relative;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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

        .back-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .step {
            flex: 1;
            text-align: center;
            position: relative;
            padding: 10px;
        }

        .step::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 50%;
            width: 100%;
            height: 2px;
            background: #e0e0e0;
            z-index: 0;
        }

        .step:first-child::before {
            display: none;
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            color: #999;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }

        .step.active .step-number {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .step.completed .step-number {
            background: #28a745;
            color: white;
        }

        .step-label {
            font-size: 12px;
            color: #999;
        }

        .step.active .step-label {
            color: #667eea;
            font-weight: 600;
        }

        .info-box {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-header">
            <i class="fas fa-key"></i>
            <h1>Quên mật khẩu?</h1>
            <p>Đừng lo lắng, chúng tôi sẽ giúp bạn lấy lại mật khẩu</p>
        </div>

        <div class="forgot-body">
            <?php if ($successMessage): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($successMessage); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul style="margin: 0; padding-left: 20px;">
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
                        <input type="text" 
                               id="email" 
                               name="email" 
                               placeholder="Nhập email hoặc số điện thoại"
                               value="<?php echo htmlspecialchars($userEmail); ?>"
                               required>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-arrow-right"></i> Tiếp tục
                    </button>
                </form>

            <?php elseif ($step == 2): ?>
                <!-- Bước 2: Đặt mật khẩu mới -->
                <div class="info-box">
                    <i class="fas fa-shield-alt"></i> Tài khoản: <strong><?php echo htmlspecialchars($userEmail); ?></strong>
                </div>

                <form action="../../controller/cForgotPassword.php" method="POST">
                    <input type="hidden" name="step" value="2">

                    <div class="form-group">
                        <label for="new_password">Mật khẩu mới <span style="color: #e74c3c;">*</span></label>
                        <input type="password" 
                               id="new_password" 
                               name="new_password" 
                               placeholder="Nhập mật khẩu mới (tối thiểu 8 ký tự)"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Xác nhận mật khẩu <span style="color: #e74c3c;">*</span></label>
                        <input type="password" 
                               id="confirm_password" 
                               name="confirm_password" 
                               placeholder="Nhập lại mật khẩu mới"
                               required>
                    </div>

                    <div class="info-box" style="background: #fff3cd; border-left-color: #ffc107;">
                        <strong>Yêu cầu mật khẩu:</strong><br>
                        • Tối thiểu 8 ký tự<br>
                        • Có chữ thường (a-z)<br>
                        • Có chữ hoa (A-Z)<br>
                        • Có ký tự đặc biệt (!@#$%...)
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-check"></i> Đặt lại mật khẩu
                    </button>
                </form>

            <?php endif; ?>

            <div class="back-link">
                <a href="login.php">
                    <i class="fas fa-arrow-left"></i> Quay lại đăng nhập
                </a>
            </div>
        </div>
    </div>

    <script>
        // Form validation
        <?php if ($step == 2): ?>
        document.querySelector('form').addEventListener('submit', function(e) {
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
