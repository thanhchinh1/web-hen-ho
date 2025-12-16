<?php
require_once '../../models/mSession.php';
require_once '../../models/mAdmin.php';

Session::start();

// Kiểm tra đăng nhập admin từ bảng admin hoặc role admin từ bảng nguoidung
$isAdminSession = Session::get('is_admin');
$userRole = Session::get('user_role');

if (!$isAdminSession && $userRole !== 'admin') {
    header('Location: ../dangnhap/login.php');
    exit;
}

// Kiểm tra timeout (30 phút không hoạt động)
$timeout = 1800; // 30 phút
if (Session::get('admin_last_activity') && (time() - Session::get('admin_last_activity') > $timeout)) {
    Session::setFlash('admin_error', 'Session đã hết hạn. Vui lòng đăng nhập lại!');
    Session::delete('is_admin');
    Session::delete('admin_id');
    Session::delete('admin_name');
    Session::delete('admin_role');
    Session::delete('admin_username');
    header('Location: dangnhap.php');
    exit;
}

// Cập nhật thời gian hoạt động
Session::set('admin_last_activity', time());

$adminId = Session::get('admin_id');
$adminName = Session::get('admin_name');
$adminRole = Session::get('admin_role');

$successMessage = Session::getFlash('admin_success');
$errorMessage = Session::getFlash('admin_error');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi mật khẩu - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-user-shield"></i> Admin Panel</h2>
                <p>Hệ thống quản trị</p>
            </div>
            
            <ul class="sidebar-menu">
                <li>
                    <a href="index.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="quanlynguoidung.php">
                        <i class="fas fa-users"></i>
                        <span>Quản lý người dùng</span>
                    </a>
                </li>
                <li>
                    <a href="quanlybaocao.php">
                        <i class="fas fa-flag"></i>
                        <span>Quản lý báo cáo</span>
                    </a>
                </li>
                <li>
                    <a href="doimatkhau.php" class="active">
                        <i class="fas fa-key"></i>
                        <span>Đổi mật khẩu</span>
                    </a>
                </li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="top-bar">
                <div>
                    <h1 class="page-title">Đổi mật khẩu</h1>
                </div>
                <div class="admin-info">
                    <div>
                        <div style="font-weight: 600; color: #333;">
                            <?php echo htmlspecialchars($adminName); ?>
                        </div>
                        <div style="font-size: 13px; color: #999;">
                            <?php 
                            $roles = [
                                'super_admin' => 'Super Admin',
                                'moderator' => 'Moderator',
                                'support' => 'Support'
                            ];
                            echo $roles[$adminRole] ?? $adminRole; 
                            ?>
                        </div>
                    </div>
                    <a href="../../controller/cAdminLogout.php" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i>
                        Đăng xuất
                    </a>
                </div>
            </div>
            
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
            
            <div class="form-container">
                <div class="form-header">
                    <i class="fas fa-lock"></i>
                    <h2>Đổi mật khẩu</h2>
                    <p>Bảo mật tài khoản admin của bạn</p>
                </div>
                
                <form action="../../controller/cAdminChangePassword.php" method="POST" id="changePasswordForm">
                    <div class="form-group">
                        <label for="old_password">
                            <i class="fas fa-lock"></i> Mật khẩu hiện tại
                        </label>
                        <div class="input-with-icon">
                            <i class="fas fa-key"></i>
                            <input type="password" 
                                   id="old_password" 
                                   name="old_password" 
                                   placeholder="Nhập mật khẩu hiện tại"
                                   required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">
                            <i class="fas fa-lock"></i> Mật khẩu mới
                        </label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" 
                                   id="new_password" 
                                   name="new_password" 
                                   placeholder="Nhập mật khẩu mới"
                                   required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="fas fa-lock"></i> Xác nhận mật khẩu mới
                        </label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   placeholder="Nhập lại mật khẩu mới"
                                   required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Đổi mật khẩu
                    </button>
                    
                    <a href="index.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Quay lại Dashboard
                    </a>
                </form>
            </div>
        </main>
    </div>
    
    <script>
        document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Mật khẩu xác nhận không khớp!');
                return false;
            }
        });
    </script>
</body>
</html>