<?php
require_once '../../models/mSession.php';
require_once '../../models/mAdmin.php';

Session::start();

// Kiểm tra đăng nhập admin từ bảng admin
$isAdminSession = Session::get('is_admin');
$userRole = Session::get('user_role');

// Chỉ cho phép truy cập nếu:
// 1. Đăng nhập qua hệ thống admin (is_admin = true) HOẶC
// 2. Đăng nhập qua bảng nguoidung với role = 'admin'
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
    header('Location: ../dangnhap/login.php');
    exit;
}

// Cập nhật thời gian hoạt động
Session::set('admin_last_activity', time());

$adminId = Session::get('admin_id');
$adminName = Session::get('admin_name');
$adminRole = Session::get('admin_role');

$adminModel = new Admin();
$stats = $adminModel->getDashboardStats();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Panel</title>
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
                    <a href="index.php" class="active">
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
                        <?php if ($stats['pendingReports'] > 0): ?>
                            <span style="margin-left: auto; background: #dc3545; padding: 2px 8px; border-radius: 10px; font-size: 12px;">
                                <?php echo $stats['pendingReports']; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="doimatkhau.php">
                        <i class="fas fa-key"></i>
                        <span>Đổi mật khẩu</span>
                    </a>
                </li>
                <li style="margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 20px;">
                    <a href="../../index.php" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                        <span>Xem trang chủ</span>
                    </a>
                </li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="top-bar">
                <div>
                    <h1 class="page-title">Dashboard</h1>
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
            
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-value"><?php echo number_format($stats['totalUsers']); ?></div>
                            <div class="stat-label">Tổng người dùng</div>
                            <div class="stat-change">
                                <i class="fas fa-arrow-up"></i> +<?php echo $stats['newUsersToday']; ?> hôm nay
                            </div>
                        </div>
                        <div class="stat-card-icon blue">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-value"><?php echo number_format($stats['totalMatches']); ?></div>
                            <div class="stat-label">Tổng ghép đôi</div>
                            <div class="stat-change">
                                <i class="fas fa-arrow-up"></i> +<?php echo $stats['newMatchesToday']; ?> hôm nay
                            </div>
                        </div>
                        <div class="stat-card-icon green">
                            <i class="fas fa-heart"></i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-value"><?php echo number_format($stats['pendingReports']); ?></div>
                            <div class="stat-label">Báo cáo chờ xử lý</div>
                            <div class="stat-change" style="color: <?php echo $stats['pendingReports'] > 0 ? '#dc3545' : '#28a745'; ?>">
                                <i class="fas fa-exclamation-circle"></i> Cần xử lý
                            </div>
                        </div>
                        <div class="stat-card-icon red">
                            <i class="fas fa-flag"></i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <div class="stat-value"><?php echo number_format($stats['totalMessages']); ?></div>
                            <div class="stat-label">Tổng tin nhắn</div>
                            <div class="stat-change">
                                <i class="fas fa-comment"></i> Đang hoạt động
                            </div>
                        </div>
                        <div class="stat-card-icon orange">
                            <i class="fas fa-comments"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <h2 style="margin-bottom: 20px; color: #333;">
                    <i class="fas fa-bolt"></i> Thao tác nhanh
                </h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <a href="quanlynguoidung.php" style="
                        padding: 20px;
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        text-decoration: none;
                        border-radius: 12px;
                        text-align: center;
                        transition: all 0.3s;
                    " onmouseover="this.style.transform='translateY(-5px)'" 
                       onmouseout="this.style.transform='translateY(0)'">
                        <i class="fas fa-users" style="font-size: 32px; margin-bottom: 10px; display: block;"></i>
                        <strong>Quản lý người dùng</strong>
                    </a>
                    
                    <a href="quanlybaocao.php" style="
                        padding: 20px;
                        background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
                        color: white;
                        text-decoration: none;
                        border-radius: 12px;
                        text-align: center;
                        transition: all 0.3s;
                    " onmouseover="this.style.transform='translateY(-5px)'" 
                       onmouseout="this.style.transform='translateY(0)'">
                        <i class="fas fa-flag" style="font-size: 32px; margin-bottom: 10px; display: block;"></i>
                        <strong>Xử lý báo cáo</strong>
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
