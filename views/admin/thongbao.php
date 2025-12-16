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
    
    if ($action === 'send') {
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        $type = $_POST['type'];
        $priority = $_POST['priority'];
        $sendTo = $_POST['send_to'];
        
        $stmt = $db->prepare("INSERT INTO thongbaoheothong (tieuDe, noiDung, loai, doUuTien, guiToi, maAdminTao, trangThai, thoiDiemGui) VALUES (?, ?, ?, ?, ?, ?, 'sent', NOW())");
        $stmt->bind_param("sssssi", $title, $content, $type, $priority, $sendTo, $adminId);
        
        if ($stmt->execute()) {
            Session::setFlash('success', 'Đã gửi thông báo thành công!');
        } else {
            Session::setFlash('error', 'Có lỗi xảy ra!');
        }
    } elseif ($action === 'delete') {
        $notifId = intval($_POST['notifId']);
        $stmt = $db->prepare("DELETE FROM thongbaoheothong WHERE maThongBao = ?");
        $stmt->bind_param("i", $notifId);
        $stmt->execute();
        Session::setFlash('success', 'Đã xóa thông báo!');
    }
    
    header('Location: thongbao.php');
    exit;
}

// Lấy danh sách thông báo
$filter = $_GET['filter'] ?? 'sent';
$sql = "SELECT * FROM thongbaoheothong";

if ($filter === 'sent') {
    $sql .= " WHERE trangThai = 'sent'";
} elseif ($filter === 'draft') {
    $sql .= " WHERE trangThai = 'draft'";
}

$sql .= " ORDER BY thoiDiemTao DESC LIMIT 100";

$result = $db->query($sql);
$notifications = $result->fetch_all(MYSQLI_ASSOC);

$success = Session::getFlash('success');
$error = Session::getFlash('error');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo hệ thống - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/admin.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="top-bar">
                <h1>Thông báo hệ thống</h1>
                <button onclick="showSendModal()" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Gửi thông báo mới
                </button>
            </div>
            
            <div class="content-area">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <div class="filter-tabs">
                    <a href="?filter=sent" class="<?php echo $filter === 'sent' ? 'active' : ''; ?>">Đã gửi</a>
                    <a href="?filter=draft" class="<?php echo $filter === 'draft' ? 'active' : ''; ?>">Bản nháp</a>
                    <a href="?filter=all" class="<?php echo $filter === 'all' ? 'active' : ''; ?>">Tất cả</a>
                </div>
                
                <div class="notification-list">
                    <?php foreach ($notifications as $notif): ?>
                    <div class="notification-card priority-<?php echo $notif['doUuTien']; ?>">
                        <div class="notification-header">
                            <div class="notification-type">
                                <?php 
                                $types = [
                                    'info' => '<i class="fas fa-info-circle"></i> Thông tin',
                                    'warning' => '<i class="fas fa-exclamation-triangle"></i> Cảnh báo',
                                    'promotion' => '<i class="fas fa-gift"></i> Khuyến mãi',
                                    'maintenance' => '<i class="fas fa-tools"></i> Bảo trì'
                                ];
                                echo $types[$notif['loai']] ?? $notif['loai'];
                                ?>
                            </div>
                            <div class="notification-priority">
                                <?php 
                                $priorities = [
                                    'low' => 'Thấp',
                                    'medium' => 'Trung bình',
                                    'high' => 'Cao',
                                    'urgent' => 'Khẩn cấp'
                                ];
                                echo $priorities[$notif['doUuTien']] ?? $notif['doUuTien'];
                                ?>
                            </div>
                            <div class="notification-time">
                                <?php echo date('d/m/Y H:i', strtotime($notif['thoiDiemGui'] ?? $notif['thoiDiemTao'])); ?>
                            </div>
                        </div>
                        <div class="notification-content">
                            <h4><?php echo htmlspecialchars($notif['tieuDe']); ?></h4>
                            <p><?php echo nl2br(htmlspecialchars($notif['noiDung'])); ?></p>
                        </div>
                        <div class="notification-footer">
                            <div class="notification-target">
                                <i class="fas fa-users"></i>
                                <?php 
                                $sendTo = [
                                    'all' => 'Tất cả người dùng',
                                    'vip' => 'Thành viên VIP',
                                    'specific' => 'Người dùng cụ thể'
                                ];
                                echo $sendTo[$notif['guiToi']] ?? $notif['guiToi'];
                                ?>
                            </div>
                            <div class="notification-actions">
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc muốn xóa thông báo này?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="notifId" value="<?php echo $notif['maThongBao']; ?>">
                                    <button type="submit" class="btn-action btn-danger" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($notifications)): ?>
                    <div class="empty-state">
                        <i class="fas fa-bell-slash"></i>
                        <p>Không có thông báo nào</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Modal gửi thông báo -->
    <div id="sendModal" class="modal" style="display: none;">
        <div class="modal-content modal-large">
            <h2>Gửi thông báo mới</h2>
            <form method="POST">
                <input type="hidden" name="action" value="send">
                
                <div class="form-group">
                    <label>Tiêu đề:<span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" required maxlength="255">
                </div>
                
                <div class="form-group">
                    <label>Nội dung:<span class="text-danger">*</span></label>
                    <textarea name="content" rows="6" class="form-control" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Loại thông báo:</label>
                        <select name="type" class="form-control" required>
                            <option value="info">Thông tin</option>
                            <option value="warning">Cảnh báo</option>
                            <option value="promotion">Khuyến mãi</option>
                            <option value="maintenance">Bảo trì</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Độ ưu tiên:</label>
                        <select name="priority" class="form-control" required>
                            <option value="low">Thấp</option>
                            <option value="medium" selected>Trung bình</option>
                            <option value="high">Cao</option>
                            <option value="urgent">Khẩn cấp</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Gửi tới:</label>
                    <select name="send_to" class="form-control" required>
                        <option value="all">Tất cả người dùng</option>
                        <option value="vip">Chỉ thành viên VIP</option>
                    </select>
                </div>
                
                <div class="modal-buttons">
                    <button type="submit" class="btn btn-primary">Gửi ngay</button>
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">Hủy</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    function showSendModal() {
        document.getElementById('sendModal').style.display = 'flex';
    }
    
    function closeModal() {
        document.getElementById('sendModal').style.display = 'none';
    }
    </script>
</body>
</html>
