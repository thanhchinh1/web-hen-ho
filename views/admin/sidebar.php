<?php
$db = clsConnect::getInstance()->connect();
$result = $db->query("SELECT COUNT(*) as total FROM baocao WHERE trangThai = 'ChuaXuLy'");
$pendingReports = $result->fetch_assoc()['total'];
$result = $db->query("SELECT COUNT(*) as total FROM hotro WHERE trangThai IN ('pending', 'in_progress')");
$pendingSupport = $result->fetch_assoc()['total'];
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <i class="fas fa-heart"></i>
            <h2>DuyenHub Admin</h2>
        </div>
        <p class="admin-info">
            <i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($adminName); ?>
        </p>
    </div>
    
    <ul class="sidebar-menu">
        <li>
            <a href="index.php" class="<?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="quanlynguoidung.php" class="<?php echo $currentPage === 'quanlynguoidung.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>Quản lý người dùng</span>
            </a>
        </li>
        <li>
            <a href="quanlybaocao.php" class="<?php echo $currentPage === 'quanlybaocao.php' ? 'active' : ''; ?>">
                <i class="fas fa-flag"></i>
                <span>Báo cáo vi phạm</span>
                <?php if ($pendingReports > 0): ?>
                    <span class="badge"><?php echo $pendingReports; ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li>
            <a href="hotro.php" class="<?php echo $currentPage === 'hotro.php' ? 'active' : ''; ?>">
                <i class="fas fa-headset"></i>
                <span>Hỗ trợ khách hàng</span>
                <?php if ($pendingSupport > 0): ?>
                    <span class="badge"><?php echo $pendingSupport; ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li>
            <a href="magiamgia.php" class="<?php echo $currentPage === 'magiamgia.php' ? 'active' : ''; ?>">
                <i class="fas fa-ticket-alt"></i>
                <span>Mã giảm giá</span>
            </a>
        </li>
        <li>
            <a href="thongbao.php" class="<?php echo $currentPage === 'thongbao.php' ? 'active' : ''; ?>">
                <i class="fas fa-bell"></i>
                <span>Thông báo hệ thống</span>
            </a>
        </li>
        <li>
            <a href="thongke.php" class="<?php echo $currentPage === 'thongke.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i>
                <span>Thống kê</span>
            </a>
        </li>
        <li class="menu-separator"></li>
        <li>
            <a href="doimatkhau.php" class="<?php echo $currentPage === 'doimatkhau.php' ? 'active' : ''; ?>">
                <i class="fas fa-key"></i>
                <span>Đổi mật khẩu</span>
            </a>
        </li>
        <li>
            <a href="../../controller/cAdminLogout.php" class="logout-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Đăng xuất</span>
            </a>
        </li>
    </ul>
</aside>
