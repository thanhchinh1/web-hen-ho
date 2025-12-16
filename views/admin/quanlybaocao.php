<?php
require_once '../../models/mSession.php';
require_once '../../models/mDbconnect.php';

Session::start();

if (!Session::get('is_admin') && Session::get('user_role') !== 'admin') {
    Session::destroy();
    header('Location: ../dangnhap/login.php');
    exit;
}

Session::set('admin_last_activity', time());
$adminId = Session::get('admin_id');
$adminName = Session::get('admin_name');

$db = clsConnect::getInstance()->connect();

// Xử lý action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $reportId = intval($_POST['reportId'] ?? 0);
    
    if ($action === 'resolve' && $reportId > 0) {
        $note = $_POST['note'] ?? '';
        $stmt = $db->prepare("UPDATE baocao SET trangThai = 'DaXuLy', maAdminXuLy = ?, ghiChuAdmin = ?, thoiDiemXuLy = NOW() WHERE maBaoCao = ?");
        $stmt->bind_param("isi", $adminId, $note, $reportId);
        $stmt->execute();
        Session::setFlash('success', 'Đã xử lý báo cáo');
    } elseif ($action === 'reject' && $reportId > 0) {
        $stmt = $db->prepare("UPDATE baocao SET trangThaiAD = 'rejected', maAdminXuLy = ?, thoiDiemXuLy = NOW() WHERE maBaoCao = ?");
        $stmt->bind_param("ii", $adminId, $reportId);
        $stmt->execute();
        Session::setFlash('success', 'Đã từ chối báo cáo');
    }
    header('Location: quanlybaocao.php');
    exit;
}

// Lấy danh sách báo cáo
$filter = $_GET['filter'] ?? 'pending';
$sql = "SELECT b.*, 
        nb.tenDangNhap as nguoiBaoCao,
        nbb.tenDangNhap as nguoiBiBaoCao,
        hb.ten as tenNguoiBiBaoCao
        FROM baocao b
        LEFT JOIN nguoidung nb ON b.maNguoiBaoCao = nb.maNguoiDung
        LEFT JOIN nguoidung nbb ON b.maNguoiBiBaoCao = nbb.maNguoiDung
        LEFT JOIN hoso hb ON b.maNguoiBiBaoCao = hb.maNguoiDung";

if ($filter === 'pending') {
    $sql .= " WHERE b.trangThai = 'ChuaXuLy'";
} elseif ($filter === 'resolved') {
    $sql .= " WHERE b.trangThai = 'DaXuLy'";
}

$sql .= " ORDER BY b.thoiDiemBaoCao DESC LIMIT 100";

$result = $db->query($sql);
$reports = $result->fetch_all(MYSQLI_ASSOC);

$success = Session::getFlash('success');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý báo cáo - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/admin.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="top-bar">
                <h1>Quản lý báo cáo vi phạm</h1>
            </div>
            
            <div class="content-area">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <div class="filter-tabs">
                    <a href="?filter=pending" class="<?php echo $filter === 'pending' ? 'active' : ''; ?>">Chưa xử lý</a>
                    <a href="?filter=resolved" class="<?php echo $filter === 'resolved' ? 'active' : ''; ?>">Đã xử lý</a>
                    <a href="?filter=all" class="<?php echo $filter === 'all' ? 'active' : ''; ?>">Tất cả</a>
                </div>
                
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Người báo cáo</th>
                                <th>Người bị báo cáo</th>
                                <th>Loại vi phạm</th>
                                <th>Lý do</th>
                                <th>Thời gian</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $report): ?>
                            <tr>
                                <td><?php echo $report['maBaoCao']; ?></td>
                                <td><?php echo htmlspecialchars($report['nguoiBaoCao'] ?? 'N/A'); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($report['tenNguoiBiBaoCao'] ?? $report['nguoiBiBaoCao'] ?? 'N/A'); ?></strong>
                                </td>
                                <td>
                                    <span class="badge badge-warning"><?php echo htmlspecialchars($report['loaiBaoCao']); ?></span>
                                </td>
                                <td>
                                    <div class="report-reason">
                                        <?php echo htmlspecialchars(substr($report['lyDoBaoCao'] ?? '', 0, 50)); ?>
                                        <?php if (strlen($report['lyDoBaoCao'] ?? '') > 50): ?>...<?php endif; ?>
                                    </div>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($report['thoiDiemBaoCao'])); ?></td>
                                <td>
                                    <?php if ($report['trangThai'] === 'ChuaXuLy'): ?>
                                        <span class="badge badge-danger">Chưa xử lý</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Đã xử lý</span>
                                    <?php endif; ?>
                                </td>
                                <td class="action-buttons">
                                    <?php if ($report['trangThai'] === 'ChuaXuLy'): ?>
                                        <button onclick="showResolveModal(<?php echo $report['maBaoCao']; ?>)" class="btn-action btn-success" title="Xử lý">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="reject">
                                            <input type="hidden" name="reportId" value="<?php echo $report['maBaoCao']; ?>">
                                            <button type="submit" class="btn-action btn-danger" title="Từ chối">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="xemnguoidung.php?id=<?php echo $report['maNguoiBiBaoCao']; ?>" class="btn-action btn-view" title="Xem người bị báo cáo">
                                        <i class="fas fa-user"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($reports)): ?>
                            <tr>
                                <td colspan="8" class="text-center">Không có báo cáo nào</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Modal xử lý báo cáo -->
    <div id="resolveModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h2>Xử lý báo cáo</h2>
            <form method="POST">
                <input type="hidden" name="action" value="resolve">
                <input type="hidden" name="reportId" id="resolveReportId">
                <div class="form-group">
                    <label>Ghi chú xử lý:</label>
                    <textarea name="note" rows="4" class="form-control" required></textarea>
                </div>
                <div class="modal-buttons">
                    <button type="submit" class="btn btn-primary">Xác nhận</button>
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">Hủy</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    function showResolveModal(reportId) {
        document.getElementById('resolveReportId').value = reportId;
        document.getElementById('resolveModal').style.display = 'flex';
    }
    
    function closeModal() {
        document.getElementById('resolveModal').style.display = 'none';
    }
    </script>
</body>
</html>
