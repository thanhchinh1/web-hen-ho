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