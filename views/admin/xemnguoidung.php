<?php
require_once '../../models/mSession.php';
require_once '../../models/mAdmin.php';
require_once '../../models/mProfile.php';
require_once '../../models/mDbconnect.php';

Session::start();

// Kiểm tra đăng nhập admin
if (!Session::get('is_admin')) {
    header('Location: dangnhap.php');
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
        
        .sidebar-header p {
            font-size: 13px;
            opacity: 0.8;
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
        
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .back-button:hover {
            background: #5a6268;
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
        
        /* Profile Card */
        .profile-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .profile-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            color: white;
            text-align: center;
        }
        
        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            margin: 0 auto 20px;
            display: block;
            background: white;
        }
        
        .profile-name {
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .profile-username {
            font-size: 18px;
            opacity: 0.9;
        }
        
        .profile-body {
            padding: 30px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-item {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .info-label {
            font-size: 13px;
            color: #6c757d;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-value {
            font-size: 16px;
            color: #333;
            font-weight: 600;
        }
        
        .profile-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 20px;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .badge-active {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-banned {
            background: #f8d7da;
            color: #721c24;
        }
        
        .badge-vip {
            background: #ffc107;
            color: #000;
        }
        
        .badge-free {
            background: #e9ecef;
            color: #6c757d;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .btn-ban {
            background: #dc3545;
            color: white;
        }
        
        .btn-ban:hover {
            background: #c82333;
        }
        
        .btn-unban {
            background: #28a745;
            color: white;
        }
        
        .btn-unban:hover {
            background: #218838;
        }
        
        .profile-description {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        
        .profile-description p {
            color: #333;
            line-height: 1.6;
        }
        
        .photo-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .photo-item {
            aspect-ratio: 1;
            border-radius: 10px;
            overflow: hidden;
            background: #f0f0f0;
        }
        
        .photo-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        
        .photo-item img:hover {
            transform: scale(1.05);
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
                    <div class="admin-avatar">
                        <?php echo strtoupper(substr($adminName, 0, 1)); ?>
                    </div>
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
