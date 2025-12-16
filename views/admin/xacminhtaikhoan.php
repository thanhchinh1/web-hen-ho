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
    $verifyId = intval($_POST['verifyId'] ?? 0);
    
    if ($action === 'approve' && $verifyId > 0) {
        $stmt = $db->prepare("UPDATE xacminhtaikhoan SET trangThai = 'verified', thoiDiemXacMinh = NOW() WHERE maXacMinh = ?");
        $stmt->bind_param("i", $verifyId);
        $stmt->execute();
        Session::setFlash('success', 'Đã duyệt xác minh');
    } elseif ($action === 'reject' && $verifyId > 0) {
        $note = $_POST['note'] ?? '';
        $stmt = $db->prepare("UPDATE xacminhtaikhoan SET trangThai = 'rejected', ghiChu = ?, thoiDiemXacMinh = NOW() WHERE maXacMinh = ?");
        $stmt->bind_param("si", $note, $verifyId);
        $stmt->execute();
        Session::setFlash('success', 'Đã từ chối xác minh');
    }
    header('Location: xacminhtaikhoan.php');
    exit;
}

// Lấy danh sách xác minh
$filter = $_GET['filter'] ?? 'pending';
$sql = "SELECT x.*, n.tenDangNhap, h.ten 
        FROM xacminhtaikhoan x
        LEFT JOIN nguoidung n ON x.maNguoiDung = n.maNguoiDung
        LEFT JOIN hoso h ON x.maNguoiDung = h.maNguoiDung";

if ($filter === 'pending') {
    $sql .= " WHERE x.trangThai = 'pending'";
} elseif ($filter === 'verified') {
    $sql .= " WHERE x.trangThai = 'verified'";
} elseif ($filter === 'rejected') {
    $sql .= " WHERE x.trangThai = 'rejected'";
}

$sql .= " ORDER BY x.thoiDiemTao DESC LIMIT 100";

$result = $db->query($sql);
$verifications = $result->fetch_all(MYSQLI_ASSOC);

$success = Session::getFlash('success');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác minh tài khoản - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/admin.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="top-bar">
                <h1>Xác minh tài khoản</h1>
            </div>
            
            <div class="content-area">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <div class="filter-tabs">
                    <a href="?filter=pending" class="<?php echo $filter === 'pending' ? 'active' : ''; ?>">Chờ duyệt</a>
                    <a href="?filter=verified" class="<?php echo $filter === 'verified' ? 'active' : ''; ?>">Đã duyệt</a>
                    <a href="?filter=rejected" class="<?php echo $filter === 'rejected' ? 'active' : ''; ?>">Đã từ chối</a>
                    <a href="?filter=all" class="<?php echo $filter === 'all' ? 'active' : ''; ?>">Tất cả</a>
                </div>
                
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Người dùng</th>
                                <th>Loại xác minh</th>
                                <th>Thời gian yêu cầu</th>
                                <th>Trạng thái</th>
                                <th>Ghi chú</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($verifications as $verify): ?>
                            <tr>
                                <td><?php echo $verify['maXacMinh']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($verify['ten'] ?? $verify['tenDangNhap'] ?? 'N/A'); ?></strong>
                                </td>
                                <td>
                                    <?php 
                                    $verifyTypes = [
                                        'email' => '<i class="fas fa-envelope"></i> Email',
                                        'phone' => '<i class="fas fa-phone"></i> Số điện thoại',
                                        'profile' => '<i class="fas fa-id-card"></i> Hồ sơ',
                                        'photo' => '<i class="fas fa-camera"></i> Ảnh'
                                    ];
                                    echo $verifyTypes[$verify['loaiXacMinh']] ?? $verify['loaiXacMinh'];
                                    ?>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($verify['thoiDiemTao'])); ?></td>
                                <td>
                                    <?php if ($verify['trangThai'] === 'pending'): ?>
                                        <span class="badge badge-warning">Chờ duyệt</span>
                                    <?php elseif ($verify['trangThai'] === 'verified'): ?>
                                        <span class="badge badge-success">Đã duyệt</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Từ chối</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($verify['ghiChu'] ?? '-'); ?></td>
                                <td class="action-buttons">
                                    <?php if ($verify['trangThai'] === 'pending'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="approve">
                                            <input type="hidden" name="verifyId" value="<?php echo $verify['maXacMinh']; ?>">
                                            <button type="submit" class="btn-action btn-success" title="Duyệt">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <button onclick="showRejectModal(<?php echo $verify['maXacMinh']; ?>)" class="btn-action btn-danger" title="Từ chối">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php endif; ?>
                                    <a href="xemnguoidung.php?id=<?php echo $verify['maNguoiDung']; ?>" class="btn-action btn-view" title="Xem người dùng">
                                        <i class="fas fa-user"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($verifications)): ?>
                            <tr>
                                <td colspan="7" class="text-center">Không có yêu cầu xác minh nào</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Modal từ chối -->
    <div id="rejectModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h2>Từ chối xác minh</h2>
            <form method="POST">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="verifyId" id="rejectVerifyId">
                <div class="form-group">
                    <label>Lý do từ chối:</label>
                    <textarea name="note" rows="4" class="form-control" required></textarea>
                </div>
                <div class="modal-buttons">
                    <button type="submit" class="btn btn-danger">Xác nhận</button>
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">Hủy</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    function showRejectModal(verifyId) {
        document.getElementById('rejectVerifyId').value = verifyId;
        document.getElementById('rejectModal').style.display = 'flex';
    }
    
    function closeModal() {
        document.getElementById('rejectModal').style.display = 'none';
    }
    </script>
</body>
</html>
