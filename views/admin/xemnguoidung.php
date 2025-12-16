<?php
require_once '../../models/mSession.php';
require_once '../../models/mAdmin.php';
require_once '../../models/mProfile.php';
require_once '../../models/mDbconnect.php';

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

// Lấy ID người dùng từ URL
$userId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($userId <= 0) {
    header('Location: quanlynguoidung.php');
    exit;
}

$adminModel = new Admin();

// Lấy thông tin người dùng từ admin model
$db = clsConnect::getInstance();
$conn = $db->connect();
$stmt = $conn->prepare("
    SELECT h.*, n.maNguoiDung, n.tenDangNhap, n.trangThaiNguoiDung, g.loaiGoi
    FROM HoSo h
    INNER JOIN NguoiDung n ON h.maNguoiDung = n.maNguoiDung
    LEFT JOIN GoiDangKy g ON n.maNguoiDung = g.maNguoiDung AND g.trangThaiGoi = 'Active'
    WHERE h.maNguoiDung = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userProfile = $result->fetch_assoc();

if (!$userProfile) {
    Session::setFlash('admin_error', 'Không tìm thấy người dùng');
    header('Location: quanlynguoidung.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xem hồ sơ - Admin Panel</title>
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
                    <a href="quanlynguoidung.php" class="active">
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
                <div style="display: flex; align-items: center; gap: 20px;">
                    <a href="quanlynguoidung.php" class="back-button">
                        <i class="fas fa-arrow-left"></i>
                        Quay lại
                    </a>
                    <h1 class="page-title">Xem hồ sơ người dùng</h1>
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
                </div>
            </div>
            
            <!-- Profile Container -->
            <div class="profile-container">
                <div class="profile-card">
                    <!-- Profile Header -->
                    <div class="profile-header">
                        <?php 
                        // Xử lý đường dẫn avatar
                        if (!empty($userProfile['avt'])) {
                            // Nếu đã có 'public/' trong đường dẫn
                            if (strpos($userProfile['avt'], 'public/') === 0) {
                                $avatarPath = '/' . htmlspecialchars($userProfile['avt']);
                            } else {
                                $avatarPath = '/public/uploads/avatars/' . htmlspecialchars($userProfile['avt']);
                            }
                        } else {
                            $avatarPath = '/public/img/default-avatar.svg';
                        }
                        ?>
                        <img src="<?php echo $avatarPath; ?>" 
                             alt="Avatar" 
                             class="profile-avatar"
                             onerror="this.src='/public/img/default-avatar.svg'">
                        <h2 class="profile-name"><?php echo htmlspecialchars($userProfile['ten'] ?? 'Chưa cập nhật'); ?></h2>
                        <p class="profile-username">@<?php echo htmlspecialchars($userProfile['tenDangNhap']); ?></p>
                    </div>
                    
                    <!-- Profile Body -->
                    <div class="profile-body">
                        <!-- Basic Info -->
                        <div class="profile-section">
                            <h3 class="section-title">
                                <i class="fas fa-info-circle"></i> Thông tin cơ bản
                            </h3>
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-hashtag"></i> ID
                                    </div>
                                    <div class="info-value">#<?php echo $userProfile['maNguoiDung']; ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-birthday-cake"></i> Ngày sinh
                                    </div>
                                    <div class="info-value">
                                        <?php 
                                        if ($userProfile['ngaySinh']) {
                                            echo date('d/m/Y', strtotime($userProfile['ngaySinh']));
                                            $age = date_diff(date_create($userProfile['ngaySinh']), date_create('today'))->y;
                                            echo " ($age tuổi)";
                                        } else {
                                            echo 'Chưa cập nhật';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-venus-mars"></i> Giới tính
                                    </div>
                                    <div class="info-value"><?php echo htmlspecialchars($userProfile['gioiTinh'] ?? 'Chưa cập nhật'); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-map-marker-alt"></i> Nơi sống
                                    </div>
                                    <div class="info-value"><?php echo htmlspecialchars($userProfile['noiSong'] ?? 'Chưa cập nhật'); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-graduation-cap"></i> Học vấn
                                    </div>
                                    <div class="info-value"><?php echo htmlspecialchars($userProfile['hocVan'] ?? 'Chưa cập nhật'); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-weight"></i> Cân nặng
                                    </div>
                                    <div class="info-value"><?php echo $userProfile['canNang'] ? $userProfile['canNang'] . ' kg' : 'Chưa cập nhật'; ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-ruler-vertical"></i> Chiều cao
                                    </div>
                                    <div class="info-value"><?php echo $userProfile['chieuCao'] ? $userProfile['chieuCao'] . ' cm' : 'Chưa cập nhật'; ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Account Status -->
                        <div class="profile-section">
                            <h3 class="section-title">
                                <i class="fas fa-user-cog"></i> Trạng thái tài khoản
                            </h3>
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-toggle-on"></i> Trạng thái
                                    </div>
                                    <div class="info-value">
                                        <?php
                                        $statusClass = 'badge-active';
                                        $statusText = 'Hoạt động';
                                        
                                        if ($userProfile['trangThaiNguoiDung'] === 'banned') {
                                            $statusClass = 'badge-banned';
                                            $statusText = 'Bị khóa';
                                        }
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>">
                                            <?php echo $statusText; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-crown"></i> Gói dịch vụ
                                    </div>
                                    <div class="info-value">
                                        <?php if ($userProfile['loaiGoi'] === 'VIP'): ?>
                                            <span class="badge badge-vip">
                                                <i class="fas fa-crown"></i> VIP
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-free">Free</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Profile Description -->
                        <?php if (!empty($userProfile['moTa'])): ?>
                        <div class="profile-section">
                            <h3 class="section-title">
                                <i class="fas fa-align-left"></i> Giới thiệu bản thân
                            </h3>
                            <div class="profile-description">
                                <p><?php echo nl2br(htmlspecialchars($userProfile['moTa'])); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Additional Info -->
                        <div class="profile-section">
                            <h3 class="section-title">
                                <i class="fas fa-heart"></i> Thông tin thêm
                            </h3>
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-ring"></i> Tình trạng hôn nhân
                                    </div>
                                    <div class="info-value"><?php echo htmlspecialchars($userProfile['tinhTrangHonNhan'] ?? 'Chưa cập nhật'); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-bullseye"></i> Mục tiêu phát triển
                                    </div>
                                    <div class="info-value"><?php echo htmlspecialchars($userProfile['mucTieuPhatTrien'] ?? 'Chưa cập nhật'); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-heart"></i> Sở thích
                                    </div>
                                    <div class="info-value"><?php echo htmlspecialchars($userProfile['soThich'] ?? 'Chưa cập nhật'); ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Admin Actions -->
                        <?php if ($adminRole !== 'support'): ?>
                        <div class="action-buttons">
                            <?php if ($userProfile['trangThaiNguoiDung'] === 'banned'): ?>
                                <button onclick="toggleUserStatus(<?php echo $userId; ?>, 'active')" class="btn btn-unban">
                                    <i class="fas fa-unlock"></i> Mở khóa tài khoản
                                </button>
                            <?php else: ?>
                                <button onclick="toggleUserStatus(<?php echo $userId; ?>, 'banned')" class="btn btn-ban">
                                    <i class="fas fa-ban"></i> Khóa tài khoản
                                </button>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        function toggleUserStatus(userId, newStatus) {
            const action = newStatus === 'banned' ? 'khóa' : 'mở khóa';
            
            if (!confirm(`Bạn có chắc muốn ${action} tài khoản này?`)) {
                return;
            }
            
            fetch('../../controller/cAdminToggleUser.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `user_id=${userId}&status=${newStatus}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể thực hiện'));
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
