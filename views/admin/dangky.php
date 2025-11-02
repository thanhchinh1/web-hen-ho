<?php
session_start();

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get flash messages
$error = $_SESSION['flash_error'] ?? '';
$success = $_SESSION['flash_success'] ?? '';
$formData = $_SESSION['form_data'] ?? [];

// Clear flash messages
unset($_SESSION['flash_error']);
unset($_SESSION['flash_success']);
unset($_SESSION['form_data']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký Admin - WebHenHo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
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

        .register-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .register-header i {
            font-size: 50px;
            margin-bottom: 15px;
        }

        .register-header h1 {
            font-size: 28px;
            margin-bottom: 8px;
        }

        .register-header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .register-body {
            padding: 30px;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background-color: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .alert-success {
            background-color: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        label .required {
            color: #e74c3c;
        }

        input, select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .input-icon {
            position: relative;
        }

        .input-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .input-icon input {
            padding-left: 45px;
        }

        .btn-register {
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

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-register:active {
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

        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }

        .info-box h4 {
            color: #667eea;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .info-box p {
            color: #666;
            font-size: 13px;
            line-height: 1.6;
        }

        .role-info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .role-info strong {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <i class="fas fa-user-shield"></i>
            <h1>Đăng ký Admin</h1>
            <p>Tạo tài khoản quản trị viên mới</p>
        </div>

        <div class="register-body">
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <div class="info-box">
                <h4><i class="fas fa-info-circle"></i> Lưu ý quan trọng</h4>
                <p>Bạn cần có <strong>Mã bảo mật</strong> để tạo tài khoản admin. Liên hệ quản trị viên hệ thống để lấy mã.</p>
            </div>

            <form action="../../controller/cAdminRegister.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <div class="form-group">
                    <label>
                        Mã bảo mật <span class="required">*</span>
                    </label>
                    <div class="input-icon">
                        <i class="fas fa-key"></i>
                        <input type="password" 
                               name="secret_key" 
                               placeholder="Nhập mã bảo mật"
                               required>
                    </div>
                </div>

                <div class="form-group">
                    <label>
                        Tên đăng nhập <span class="required">*</span>
                    </label>
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" 
                               name="username" 
                               placeholder="Tối thiểu 4 ký tự"
                               value="<?php echo htmlspecialchars($formData['username'] ?? ''); ?>"
                               required>
                    </div>
                </div>

                <div class="form-group">
                    <label>
                        Họ tên <span class="required">*</span>
                    </label>
                    <div class="input-icon">
                        <i class="fas fa-id-card"></i>
                        <input type="text" 
                               name="full_name" 
                               placeholder="Nhập họ tên đầy đủ"
                               value="<?php echo htmlspecialchars($formData['full_name'] ?? ''); ?>"
                               required>
                    </div>
                </div>

                <div class="form-group">
                    <label>
                        Email <span class="required">*</span>
                    </label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" 
                               name="email" 
                               placeholder="admin@webhenho.com"
                               value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>"
                               required>
                    </div>
                </div>

                <div class="form-group">
                    <label>
                        Mật khẩu <span class="required">*</span>
                    </label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" 
                               name="password" 
                               placeholder="Tối thiểu 6 ký tự"
                               required>
                    </div>
                </div>

                <div class="form-group">
                    <label>
                        Xác nhận mật khẩu <span class="required">*</span>
                    </label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" 
                               name="confirm_password" 
                               placeholder="Nhập lại mật khẩu"
                               required>
                    </div>
                </div>

                <div class="form-group">
                    <label>
                        Vai trò <span class="required">*</span>
                    </label>
                    <select name="role" required>
                        <option value="moderator" <?php echo ($formData['role'] ?? 'moderator') === 'moderator' ? 'selected' : ''; ?>>
                            Moderator (Quản lý)
                        </option>
                        <option value="support" <?php echo ($formData['role'] ?? '') === 'support' ? 'selected' : ''; ?>>
                            Support (Hỗ trợ)
                        </option>
                        <option value="super_admin" <?php echo ($formData['role'] ?? '') === 'super_admin' ? 'selected' : ''; ?>>
                            Super Admin (Toàn quyền)
                        </option>
                    </select>
                    <div class="role-info">
                        <strong>Super Admin:</strong> Toàn quyền<br>
                        <strong>Moderator:</strong> Quản lý người dùng và báo cáo<br>
                        <strong>Support:</strong> Chỉ xem thông tin
                    </div>
                </div>

                <button type="submit" class="btn-register">
                    <i class="fas fa-user-plus"></i> Đăng ký
                </button>
            </form>

            <div class="form-footer">
                <p>Đã có tài khoản? <a href="dangnhap.php"><i class="fas fa-sign-in-alt"></i> Đăng nhập ngay</a></p>
            </div>
        </div>
    </div>
</body>
</html>
