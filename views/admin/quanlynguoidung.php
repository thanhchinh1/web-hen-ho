<?php
require_once '../../models/mSession.php';
require_once '../../models/mDbconnect.php';

Session::start();

// Kiểm tra đăng nhập admin
if (!Session::get('is_admin') && Session::get('user_role') !== 'admin') {
    Session::destroy();
    header('Location: ../dangnhap/login.php');
    exit;
}

Session::set('admin_last_activity', time());
$adminName = Session::get('admin_name');

$db = clsConnect::getInstance()->connect();

// Xử lý action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $userId = intval($_POST['userId'] ?? 0);
    
    if ($action === 'ban' && $userId > 0) {
        $stmt = $db->prepare("UPDATE nguoidung SET trangThaiNguoiDung = 'banned' WHERE maNguoiDung = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        Session::setFlash('success', 'Đã khóa tài khoản người dùng');
    } elseif ($action === 'unban' && $userId > 0) {
        $stmt = $db->prepare("UPDATE nguoidung SET trangThaiNguoiDung = 'active' WHERE maNguoiDung = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        Session::setFlash('success', 'Đã mở khóa tài khoản người dùng');
    }
    header('Location: quanlynguoidung.php');
    exit;
}

// Lấy danh sách người dùng
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'all';

$sql = "SELECT n.*, h.ten, h.avt, h.gioiTinh, g.loaiGoi, g.trangThaiGoi 
        FROM nguoidung n
        LEFT JOIN hoso h ON n.maNguoiDung = h.maNguoiDung
        LEFT JOIN goidangky g ON n.maNguoiDung = g.maNguoiDung AND g.trangThaiGoi = 'Active'
        WHERE n.role = 'user'";

if (!empty($search)) {
    $sql .= " AND (n.tenDangNhap LIKE ? OR h.ten LIKE ?)";
}

if ($filter === 'vip') {
    $sql .= " AND g.loaiGoi = 'VIP'";
} elseif ($filter === 'banned') {
    $sql .= " AND n.trangThaiNguoiDung = 'banned'";
} elseif ($filter === 'active') {
    $sql .= " AND n.trangThaiNguoiDung = 'active'";
}

$sql .= " ORDER BY n.maNguoiDung DESC LIMIT 100";

$stmt = $db->prepare($sql);
if (!empty($search)) {
    $searchTerm = "%$search%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
}
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$success = Session::getFlash('success');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý người dùng - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/admin.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="top-bar">
                <h1>Quản lý người dùng</h1>
            </div>
            
            <div class="content-area">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <div class="filter-bar">
                    <form method="GET" class="search-form">
                        <input type="text" name="search" placeholder="Tìm kiếm theo tên hoặc email..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit"><i class="fas fa-search"></i> Tìm kiếm</button>
                    </form>
                    
                    <div class="filter-tabs">
                        <a href="?filter=all" class="<?php echo $filter === 'all' ? 'active' : ''; ?>">Tất cả</a>
                        <a href="?filter=active" class="<?php echo $filter === 'active' ? 'active' : ''; ?>">Hoạt động</a>
                        <a href="?filter=vip" class="<?php echo $filter === 'vip' ? 'active' : ''; ?>">VIP</a>
                        <a href="?filter=banned" class="<?php echo $filter === 'banned' ? 'active' : ''; ?>">Đã khóa</a>
                    </div>
                </div>
                
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ảnh đại diện</th>
                                <th>Tên</th>
                                <th>Email</th>
                                <th>Loại tài khoản</th>
                                <th>Trạng thái</th>
                                <th>Hoạt động cuối</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['maNguoiDung']; ?></td>
                                <td>
                                    <?php if (!empty($user['avt'])): ?>
                                        <img src="../../<?php echo htmlspecialchars($user['avt']); ?>" alt="Avatar" class="user-avatar">
                                    <?php else: ?>
                                        <div class="user-avatar-placeholder">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($user['ten'] ?? 'Chưa cập nhật'); ?></strong>
                                    <?php if ($user['gioiTinh']): ?>
                                        <br><span class="text-muted"><?php echo $user['gioiTinh']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['tenDangNhap']); ?></td>
                                <td>
                                    <?php if ($user['loaiGoi'] === 'VIP'): ?>
                                        <span class="badge badge-vip"><i class="fas fa-crown"></i> VIP</span>
                                    <?php else: ?>
                                        <span class="badge badge-free">Free</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user['trangThaiNguoiDung'] === 'active'): ?>
                                        <span class="badge badge-success">Hoạt động</span>
                                    <?php elseif ($user['trangThaiNguoiDung'] === 'banned'): ?>
                                        <span class="badge badge-danger">Đã khóa</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Không hoạt động</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $user['lanHoatDongCuoi'] ? date('d/m/Y H:i', strtotime($user['lanHoatDongCuoi'])) : 'Chưa xác định'; ?></td>
                                <td class="action-buttons">
                                    <a href="xemnguoidung.php?id=<?php echo $user['maNguoiDung']; ?>" class="btn-action btn-view" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($user['trangThaiNguoiDung'] === 'active'): ?>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc muốn khóa tài khoản này?');">
                                            <input type="hidden" name="action" value="ban">
                                            <input type="hidden" name="userId" value="<?php echo $user['maNguoiDung']; ?>">
                                            <button type="submit" class="btn-action btn-danger" title="Khóa tài khoản">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="unban">
                                            <input type="hidden" name="userId" value="<?php echo $user['maNguoiDung']; ?>">
                                            <button type="submit" class="btn-action btn-success" title="Mở khóa">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="8" class="text-center">Không tìm thấy người dùng nào</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
