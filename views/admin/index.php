<?php
require_once '../../models/mSession.php';
require_once '../../models/mDbconnect.php';

Session::start();

// Kiểm tra đăng nhập admin
$isAdminSession = Session::get('is_admin');
$userRole = Session::get('user_role');

if (!$isAdminSession && $userRole !== 'admin') {
    Session::destroy();
    header('Location: ../dangnhap/login.php');
    exit;
}

// Kiểm tra timeout (30 phút)
$timeout = 1800;
if (Session::get('admin_last_activity') && (time() - Session::get('admin_last_activity') > $timeout)) {
    Session::setFlash('admin_error', 'Session đã hết hạn. Vui lòng đăng nhập lại!');
    Session::destroy();
    header('Location: ../dangnhap/login.php');
    exit;
}

Session::set('admin_last_activity', time());

$adminId = Session::get('admin_id');
$adminName = Session::get('admin_name');

// Lấy thống kê từ database
$db = clsConnect::getInstance()->connect();

// Tổng số người dùng
$result = $db->query("SELECT COUNT(*) as total FROM nguoidung");
$totalUsers = $result->fetch_assoc()['total'];

// Người dùng mới hôm nay
$result = $db->query("SELECT COUNT(*) as total FROM nguoidung WHERE DATE(lanHoatDongCuoi) = CURDATE()");
$newUsersToday = $result->fetch_assoc()['total'];

// Người dùng VIP
$result = $db->query("SELECT COUNT(*) as total FROM goidangky WHERE loaiGoi = 'VIP' AND trangThaiGoi = 'Active'");
$vipUsers = $result->fetch_assoc()['total'];

// Báo cáo chưa xử lý
$result = $db->query("SELECT COUNT(*) as total FROM baocao WHERE trangThai = 'ChuaXuLy'");
$pendingReports = $result->fetch_assoc()['total'];

// Ghép đôi hôm nay
$result = $db->query("SELECT COUNT(*) as total FROM ghepdoi WHERE DATE(thoiDiemGhepDoi) = CURDATE()");
$matchesToday = $result->fetch_assoc()['total'];

// Tin nhắn hôm nay
$result = $db->query("SELECT COUNT(*) as total FROM tinnhan WHERE DATE(thoiDiemGui) = CURDATE()");
$messagesToday = $result->fetch_assoc()['total'];

// Hỗ trợ đang chờ
$result = $db->query("SELECT COUNT(*) as total FROM hotro WHERE trangThai IN ('pending', 'in_progress')");
$pendingSupport = $result->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/admin.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <img src="/public/img/logo.jpg" alt="Logo" style="height:40px;width:auto;vertical-align:middle;">
                    <h2>DuyenHub</h2>
                </div>
                <p class="admin-info">
                    <i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($adminName); ?>
                </p>
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
                        <span>Báo cáo vi phạm</span>
                        <?php if ($pendingReports > 0): ?>
                            <span class="badge"><?php echo $pendingReports; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="magiamgia.php">
                        <i class="fas fa-ticket-alt"></i>
                        <span>Mã giảm giá</span>
                    </a>
                </li>
                <li>
                    <a href="thongbao.php">
                        <i class="fas fa-bell"></i>
                        <span>Thông báo hệ thống</span>
                    </a>
                </li>
                <li>
                    <a href="thongke.php">
                        <i class="fas fa-chart-line"></i>
                        <span>Thống kê</span>
                    </a>
                </li>
                <li class="menu-separator"></li>
                <li>
                    <a href="../../controller/cAdminLogout.php" class="logout-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Đăng xuất</span>
                    </a>
                </li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="top-bar">
                <h1>Dashboard</h1>
                <div class="top-actions">
                    <a href="../../index.php" target="_blank" class="btn-view-site">
                        <i class="fas fa-external-link-alt"></i>
                        Xem trang chủ
                    </a>
                </div>
            </div>
            
            <div class="dashboard-content">
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card blue">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo number_format($totalUsers); ?></h3>
                            <p>Tổng người dùng</p>
                            <span class="stat-change positive">
                                <i class="fas fa-arrow-up"></i> +<?php echo $newUsersToday; ?> hôm nay
                            </span>
                        </div>
                    </div>
                    
                    <div class="stat-card gold">
                        <div class="stat-icon">
                            <i class="fas fa-crown"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo number_format($vipUsers); ?></h3>
                            <p>Thành viên VIP</p>
                            <span class="stat-label">Đang hoạt động</span>
                        </div>
                    </div>
                    
                    <div class="stat-card green">
                        <div class="stat-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo number_format($matchesToday); ?></h3>
                            <p>Ghép đôi hôm nay</p>
                            <span class="stat-label">Kết nối thành công</span>
                        </div>
                    </div>
                    
                    <div class="stat-card red">
                        <div class="stat-icon">
                            <i class="fas fa-flag"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo number_format($pendingReports); ?></h3>
                            <p>Báo cáo chưa xử lý</p>
                            <?php if ($pendingReports > 0): ?>
                                <span class="stat-change negative">
                                    <i class="fas fa-exclamation-circle"></i> Cần xử lý
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="stat-card purple">
                        <div class="stat-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo number_format($messagesToday); ?></h3>
                            <p>Tin nhắn hôm nay</p>
                            <span class="stat-label">Hoạt động tích cực</span>
                        </div>
                    </div>
                    
                </div>
                
                <!-- Quick Actions -->
                <div class="section-title">
                    <h2>Thao tác nhanh</h2>
                </div>
                
                <div class="actions-grid">
                    <a href="quanlynguoidung.php" class="action-card">
                        <i class="fas fa-user-plus"></i>
                        <h3>Quản lý người dùng</h3>
                        <p>Xem và quản lý tài khoản người dùng</p>
                    </a>
                    
                    <a href="quanlybaocao.php" class="action-card urgent">
                        <i class="fas fa-flag"></i>
                        <h3>Xử lý báo cáo</h3>
                        <p><?php echo $pendingReports; ?> báo cáo đang chờ xử lý</p>
                    </a>
                    
                    <a href="magiamgia.php" class="action-card">
                        <i class="fas fa-ticket-alt"></i>
                        <h3>Mã giảm giá</h3>
                        <p>Tạo và quản lý mã khuyến mãi</p>
                    </a>
                    
                    <a href="thongbao.php" class="action-card">
                        <i class="fas fa-bell"></i>
                        <h3>Gửi thông báo</h3>
                        <p>Thông báo cho người dùng</p>
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
