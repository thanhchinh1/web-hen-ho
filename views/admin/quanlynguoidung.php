<?php
require_once '../../models/mSession.php';
require_once '../../models/mAdmin.php';

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
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$adminModel = new Admin();
$users = $adminModel->getAllUsers($limit, $offset, $search);
$totalUsers = $adminModel->getTotalUsers($search);
$totalPages = ceil($totalUsers / $limit);

$successMessage = Session::getFlash('admin_success');
$errorMessage = Session::getFlash('admin_error');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý người dùng - Admin Panel</title>
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
                <div>
                    <h1 class="page-title">Quản lý người dùng</h1>
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
            
            <!-- Search Bar -->
            <div class="search-bar">
                <form method="GET" class="search-form">
                    <input type="text" 
                           name="search" 
                           class="search-input" 
                           placeholder="Tìm kiếm theo tên, username..."
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn-search">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                    <?php if ($search): ?>
                        <a href="quanlynguoidung.php" class="btn-search" style="background: #6c757d;">
                            <i class="fas fa-times"></i> Xóa
                        </a>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Users Table -->
            <div class="users-table-container">
                <?php if (count($users) > 0): ?>
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Người dùng</th>
                                <th>Ngày sinh</th>
                                <th>Giới tính</th>
                                <th>Trạng thái</th>
                                <th>Gói VIP</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['maNguoiDung']; ?></td>
                                    <td>
                                        <div class="user-info">
                                            <?php 
                                            // Xử lý đường dẫn avatar
                                            if (!empty($user['avt'])) {
                                                // Nếu đã có 'public/' trong đường dẫn
                                                if (strpos($user['avt'], 'public/') === 0) {
                                                    $avatarPath = '/' . htmlspecialchars($user['avt']);
                                                } else {
                                                    $avatarPath = '/public/uploads/avatars/' . htmlspecialchars($user['avt']);
                                                }
                                            } else {
                                                $avatarPath = '/public/img/default-avatar.jpg';
                                            }
                                            ?>
                                            <img src="<?php echo $avatarPath; ?>" 
                                                 alt="Avatar" 
                                                 class="user-avatar"
                                                 onerror="this.src='/public/img/default-avatar.jpg'">
                                            <div>
                                                <div class="user-name">
                                                    <?php echo htmlspecialchars($user['ten'] ?? 'Chưa cập nhật'); ?>
                                                </div>
                                                <div class="user-username">
                                                    @<?php echo htmlspecialchars($user['tenDangNhap']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo $user['ngaySinh'] ? date('d/m/Y', strtotime($user['ngaySinh'])) : 'N/A'; ?></td>
                                    <td><?php echo htmlspecialchars($user['gioiTinh'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php
                                        $statusClass = 'badge-inactive';
                                        $statusText = $user['trangThaiNguoiDung'];
                                        
                                        if ($user['trangThaiNguoiDung'] === 'active') {
                                            $statusClass = 'badge-active';
                                            $statusText = 'Hoạt động';
                                        } elseif ($user['trangThaiNguoiDung'] === 'banned') {
                                            $statusClass = 'badge-banned';
                                            $statusText = 'Bị khóa';
                                        }
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>">
                                            <?php echo $statusText; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($user['loaiGoi'] === 'VIP'): ?>
                                            <span class="badge" style="background: #ffc107; color: #000;">
                                                <i class="fas fa-crown"></i> VIP
                                            </span>
                                        <?php else: ?>
                                            <span class="badge" style="background: #e9ecef; color: #6c757d;">
                                                Free
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="xemnguoidung.php?id=<?php echo $user['maNguoiDung']; ?>" 
                                           class="btn-action btn-view">
                                            <i class="fas fa-eye"></i> Xem
                                        </a>
                                        
                                        <?php if ($adminRole !== 'support'): ?>
                                            <?php if ($user['trangThaiNguoiDung'] === 'banned'): ?>
                                                <button onclick="toggleUserStatus(<?php echo $user['maNguoiDung']; ?>, 'active')" 
                                                        class="btn-action btn-unban">
                                                    <i class="fas fa-unlock"></i> Mở khóa
                                                </button>
                                            <?php else: ?>
                                                <button onclick="toggleUserStatus(<?php echo $user['maNguoiDung']; ?>, 'banned')" 
                                                        class="btn-action btn-ban">
                                                    <i class="fas fa-ban"></i> Khóa
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="active"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="no-users">
                        <i class="fas fa-users-slash"></i>
                        <h3>Không tìm thấy người dùng</h3>
                        <p>
                            <?php if ($search): ?>
                                Không có kết quả cho "<?php echo htmlspecialchars($search); ?>"
                            <?php else: ?>
                                Chưa có người dùng nào trong hệ thống
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script>
        function toggleUserStatus(userId, newStatus) {
            const action = newStatus === 'banned' ? 'khóa' : 'mở khóa';
            
            if (!confirm(`Bạn có chắc muốn ${action} người dùng này?`)) {
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