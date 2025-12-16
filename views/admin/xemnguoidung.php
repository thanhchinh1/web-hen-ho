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

// Lấy thông tin người dùng
$userId = intval($_GET['id'] ?? 0);
if ($userId <= 0) {
    header('Location: quanlynguoidung.php');
    exit;
}

$stmt = $db->prepare("
    SELECT n.*, h.*, g.loaiGoi, g.trangThaiGoi, g.ngayHetHan
    FROM nguoidung n
    LEFT JOIN hoso h ON n.maNguoiDung = h.maNguoiDung
    LEFT JOIN goidangky g ON n.maNguoiDung = g.maNguoiDung AND g.trangThaiGoi = 'Active'
    WHERE n.maNguoiDung = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header('Location: quanlynguoidung.php');
    exit;
}

// Thống kê hoạt động
$stmt = $db->prepare("SELECT COUNT(*) as total FROM thich WHERE maNguoiThich = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$totalLikes = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM ghepdoi WHERE (maNguoiA = ? OR maNguoiB = ?) AND trangThaiGhepDoi = 'matched'");
$stmt->bind_param("ii", $userId, $userId);
$stmt->execute();
$totalMatches = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM tinnhan WHERE maNguoiGui = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$totalMessages = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM baocao WHERE maNguoiBiBaoCao = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$totalReports = $stmt->get_result()->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết người dùng - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/admin.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="top-bar">
                <h1>Chi tiết người dùng</h1>
                <a href="quanlynguoidung.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
            
            <div class="content-area">
                <div class="user-detail-grid">
                    <!-- Thông tin cơ bản -->
                    <div class="detail-card">
                        <h3><i class="fas fa-user"></i> Thông tin cơ bản</h3>
                        <div class="user-avatar-large">
                            <?php if (!empty($user['avt'])): ?>
                                <img src="../../<?php echo htmlspecialchars($user['avt']); ?>" alt="Avatar">
                            <?php else: ?>
                                <i class="fas fa-user-circle"></i>
                            <?php endif; ?>
                        </div>
                        <table class="info-table">
                            <tr>
                                <td><strong>ID:</strong></td>
                                <td><?php echo $user['maNguoiDung']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Tên:</strong></td>
                                <td><?php echo htmlspecialchars($user['ten'] ?? 'Chưa cập nhật'); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td><?php echo htmlspecialchars($user['tenDangNhap']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Giới tính:</strong></td>
                                <td><?php echo htmlspecialchars($user['gioiTinh'] ?? 'Chưa cập nhật'); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Ngày sinh:</strong></td>
                                <td><?php echo $user['ngaySinh'] ? date('d/m/Y', strtotime($user['ngaySinh'])) : 'Chưa cập nhật'; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Loại tài khoản:</strong></td>
                                <td>
                                    <?php if ($user['loaiGoi'] === 'VIP'): ?>
                                        <span class="badge badge-vip"><i class="fas fa-crown"></i> VIP</span>
                                    <?php else: ?>
                                        <span class="badge badge-free">Free</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Trạng thái:</strong></td>
                                <td>
                                    <?php if ($user['trangThaiNguoiDung'] === 'active'): ?>
                                        <span class="badge badge-success">Hoạt động</span>
                                    <?php elseif ($user['trangThaiNguoiDung'] === 'banned'): ?>
                                        <span class="badge badge-danger">Đã khóa</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Không hoạt động</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Hoạt động cuối:</strong></td>
                                <td><?php echo $user['lanHoatDongCuoi'] ? date('d/m/Y H:i', strtotime($user['lanHoatDongCuoi'])) : 'Chưa xác định'; ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Hồ sơ chi tiết -->
                    <div class="detail-card">
                        <h3><i class="fas fa-id-card"></i> Hồ sơ chi tiết</h3>
                        <table class="info-table">
                            <tr>
                                <td><strong>Tình trạng hôn nhân:</strong></td>
                                <td><?php echo htmlspecialchars($user['tinhTrangHonNhan'] ?? 'Chưa cập nhật'); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Chiều cao:</strong></td>
                                <td><?php echo $user['chieuCao'] ? $user['chieuCao'] . ' cm' : 'Chưa cập nhật'; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Cân nặng:</strong></td>
                                <td><?php echo $user['canNang'] ? $user['canNang'] . ' kg' : 'Chưa cập nhật'; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Học vấn:</strong></td>
                                <td><?php echo htmlspecialchars($user['hocVan'] ?? 'Chưa cập nhật'); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Nơi sống:</strong></td>
                                <td><?php echo htmlspecialchars($user['noiSong'] ?? 'Chưa cập nhật'); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Mục tiêu:</strong></td>
                                <td><?php echo htmlspecialchars($user['mucTieuPhatTrien'] ?? 'Chưa cập nhật'); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Sở thích:</strong></td>
                                <td><?php echo htmlspecialchars($user['soThich'] ?? 'Chưa cập nhật'); ?></td>
                            </tr>
                            <tr>
                                <td colspan="2"><strong>Mô tả bản thân:</strong><br>
                                    <?php echo nl2br(htmlspecialchars($user['moTa'] ?? 'Chưa cập nhật')); ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Thống kê hoạt động -->
                    <div class="detail-card">
                        <h3><i class="fas fa-chart-bar"></i> Thống kê hoạt động</h3>
                        <div class="stats-mini-grid">
                            <div class="stat-mini">
                                <i class="fas fa-heart"></i>
                                <div>
                                    <h4><?php echo $totalLikes; ?></h4>
                                    <p>Lượt thích</p>
                                </div>
                            </div>
                            <div class="stat-mini">
                                <i class="fas fa-users"></i>
                                <div>
                                    <h4><?php echo $totalMatches; ?></h4>
                                    <p>Ghép đôi</p>
                                </div>
                            </div>
                            <div class="stat-mini">
                                <i class="fas fa-comments"></i>
                                <div>
                                    <h4><?php echo $totalMessages; ?></h4>
                                    <p>Tin nhắn</p>
                                </div>
                            </div>
                            <div class="stat-mini">
                                <i class="fas fa-flag"></i>
                                <div>
                                    <h4><?php echo $totalReports; ?></h4>
                                    <p>Báo cáo</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
