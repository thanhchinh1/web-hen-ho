<?php
require_once '../../models/mSession.php';
require_once '../../models/mAdmin.php';
require_once '../../models/mReport.php';

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

// Phân trang
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

$reportModel = new Report();
$reports = $reportModel->getAllReports($limit, $offset, $status);
$totalReports = $reportModel->getTotalReports($status);
$totalPages = ceil($totalReports / $limit);

$successMessage = Session::getFlash('admin_success');
$errorMessage = Session::getFlash('admin_error');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý báo cáo - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }
        
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 20px;
        }
        
        .sidebar-header h2 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.2);
            border-left: 4px solid white;
        }
        
        .sidebar-menu i {
            margin-right: 12px;
            width: 20px;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 30px;
        }
        
        .top-bar {
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-title {
            font-size: 28px;
            color: #333;
        }
        
        .admin-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .admin-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            font-weight: 600;
        }
        
        .btn-logout {
            padding: 10px 20px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
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
        
        /* Filter Tabs */
        .filter-tabs {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        
        .filter-tab {
            padding: 12px 24px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            background: white;
            color: #666;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .filter-tab:hover {
            border-color: #667eea;
            color: #667eea;
        }
        
        .filter-tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: transparent;
        }
        
        /* Reports Table */
        .reports-table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .reports-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .reports-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .reports-table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .reports-table tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-resolved {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .report-type {
            font-size: 12px;
            color: #666;
            margin-top: 3px;
        }
        
        .btn-action {
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.3s;
            margin-right: 5px;
        }
        
        .btn-resolve {
            background: #28a745;
            color: white;
        }
        
        .btn-reject {
            background: #dc3545;
            color: white;
        }
        
        .btn-view {
            background: #17a2b8;
            color: white;
        }
        
        .no-reports {
            padding: 60px 20px;
            text-align: center;
            color: #999;
        }
        
        .no-reports i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            padding: 20px;
        }
        
        .pagination a,
        .pagination span {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
        }
        
        .pagination a:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .pagination .active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
    </style>
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
                    <a href="quanlybaocao.php" class="active">
                        <i class="fas fa-flag"></i>
                        <span>Quản lý báo cáo</span>
                    </a>
                </li>
                <li>
                    <a href="doimatkhau.php">
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
                    <h1 class="page-title">Quản lý báo cáo vi phạm</h1>
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
            
            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <a href="?status=all" class="filter-tab <?php echo $status === 'all' ? 'active' : ''; ?>">
                    <i class="fas fa-list"></i> Tất cả (<?php echo $reportModel->getTotalReports('all'); ?>)
                </a>
                <a href="?status=ChuaXuLy" class="filter-tab <?php echo $status === 'ChuaXuLy' ? 'active' : ''; ?>">
                    <i class="fas fa-clock"></i> Chờ xử lý (<?php echo $reportModel->getTotalReports('ChuaXuLy'); ?>)
                </a>
                <a href="?status=DaXuLy" class="filter-tab <?php echo $status === 'DaXuLy' ? 'active' : ''; ?>">
                    <i class="fas fa-check"></i> Đã xử lý (<?php echo $reportModel->getTotalReports('DaXuLy'); ?>)
                </a>
            </div>
            
            <!-- Reports Table -->
            <div class="reports-table-container">
                <?php if (count($reports) > 0): ?>
                    <table class="reports-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Người báo cáo</th>
                                <th>Người bị báo cáo</th>
                                <th>Loại vi phạm</th>
                                <th>Lý do</th>
                                <th>Ngày báo cáo</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $report): ?>
                                <tr>
                                    <td>#<?php echo $report['maBaoCao']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($report['reporter_name'] ?? 'N/A'); ?></strong><br>
                                        <small>@<?php echo htmlspecialchars($report['reporter_username']); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($report['reported_name'] ?? 'N/A'); ?></strong><br>
                                        <small>@<?php echo htmlspecialchars($report['reported_username']); ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        // Loại báo cáo - sẽ được thêm trong tương lai
                                        echo isset($report['loaiBaoCao']) ? htmlspecialchars($report['loaiBaoCao']) : 'N/A';
                                        ?>
                                    </td>
                                    <td>
                                        <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            <?php echo htmlspecialchars($report['lyDoBaoCao']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($report['thoiDiemBaoCao'])); ?></td>
                                    <td>
                                        <?php
                                        $statusClass = 'badge-pending';
                                        $statusText = 'Chờ xử lý';
                                        
                                        if ($report['trangThai'] === 'DaXuLy') {
                                            $statusClass = 'badge-resolved';
                                            $statusText = 'Đã xử lý';
                                        }
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>">
                                            <?php echo $statusText; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($adminRole !== 'support' && $report['trangThai'] === 'ChuaXuLy'): ?>
                                            <button onclick="updateReportStatus(<?php echo $report['maBaoCao']; ?>, 'DaXuLy')" 
                                                    class="btn-action btn-resolve"
                                                    title="Đánh dấu đã xử lý">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                        <a href="xemnguoidung.php?id=<?php echo $report['maNguoiBiBaoCao']; ?>" 
                                           class="btn-action btn-view"
                                           title="Xem hồ sơ người bị báo cáo">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $status; ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="active"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $i; ?>&status=<?php echo $status; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $status; ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="no-reports">
                        <i class="fas fa-flag"></i>
                        <h3>Không có báo cáo</h3>
                        <p>
                            <?php if ($status === 'pending'): ?>
                                Không có báo cáo nào đang chờ xử lý
                            <?php elseif ($status === 'resolved'): ?>
                                Chưa có báo cáo nào được xử lý
                            <?php elseif ($status === 'rejected'): ?>
                                Chưa có báo cáo nào bị từ chối
                            <?php else: ?>
                                Chưa có báo cáo vi phạm nào trong hệ thống
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script>
        function updateReportStatus(reportId, newStatus) {
            const statusText = newStatus === 'resolved' ? 'đã xử lý' : 'từ chối';
            
            if (!confirm(`Bạn có chắc muốn đánh dấu báo cáo này là ${statusText}?`)) {
                return;
            }
            
            fetch('../../controller/cAdminUpdateReport.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `report_id=${reportId}&status=${newStatus}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể cập nhật'));
                }
            })
            .catch(error => {
                alert('Lỗi kết nối');
                console.error(error);
            });
        }
    </script>
</body>
</html>