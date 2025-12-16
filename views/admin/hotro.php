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
    
    if ($action === 'reply') {
        $supportId = intval($_POST['supportId'] ?? 0);
        $reply = $_POST['reply'] ?? '';
        
        $stmt = $db->prepare("UPDATE hotro SET trangThai = 'resolved', maAdminPhuTrach = ?, phanHoi = ?, thoiDiemCapNhat = NOW() WHERE maHoTro = ?");
        $stmt->bind_param("isi", $adminId, $reply, $supportId);
        $stmt->execute();
        Session::setFlash('success', 'Đã trả lời yêu cầu hỗ trợ');
    } elseif ($action === 'close') {
        $supportId = intval($_POST['supportId'] ?? 0);
        $stmt = $db->prepare("UPDATE hotro SET trangThai = 'closed', thoiDiemCapNhat = NOW() WHERE maHoTro = ?");
        $stmt->bind_param("i", $supportId);
        $stmt->execute();
        Session::setFlash('success', 'Đã đóng yêu cầu hỗ trợ');
    }
    header('Location: hotro.php');
    exit;
}

// Lấy danh sách yêu cầu hỗ trợ
$filter = $_GET['filter'] ?? 'pending';
$sql = "SELECT h.*, n.tenDangNhap, hs.ten 
        FROM hotro h
        LEFT JOIN nguoidung n ON h.maNguoiDung = n.maNguoiDung
        LEFT JOIN hoso hs ON h.maNguoiDung = hs.maNguoiDung";

if ($filter === 'pending') {
    $sql .= " WHERE h.trangThai IN ('pending', 'in_progress')";
} elseif ($filter === 'resolved') {
    $sql .= " WHERE h.trangThai = 'resolved'";
} elseif ($filter === 'closed') {
    $sql .= " WHERE h.trangThai = 'closed'";
}

$sql .= " ORDER BY h.thoiDiemTao DESC LIMIT 100";

$result = $db->query($sql);
$supports = $result->fetch_all(MYSQLI_ASSOC);

$success = Session::getFlash('success');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hỗ trợ khách hàng - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/admin.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="top-bar">
                <h1>Hỗ trợ khách hàng</h1>
            </div>
            
            <div class="content-area">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <div class="filter-tabs">
                    <a href="?filter=pending" class="<?php echo $filter === 'pending' ? 'active' : ''; ?>">Đang xử lý</a>
                    <a href="?filter=resolved" class="<?php echo $filter === 'resolved' ? 'active' : ''; ?>">Đã trả lời</a>
                    <a href="?filter=closed" class="<?php echo $filter === 'closed' ? 'active' : ''; ?>">Đã đóng</a>
                    <a href="?filter=all" class="<?php echo $filter === 'all' ? 'active' : ''; ?>">Tất cả</a>
                </div>
                
                <div class="support-list">
                    <?php foreach ($supports as $support): ?>
                    <div class="support-card">
                        <div class="support-header">
                            <div class="support-user">
                                <i class="fas fa-user-circle"></i>
                                <strong><?php echo htmlspecialchars($support['ten'] ?? $support['tenDangNhap'] ?? 'N/A'); ?></strong>
                                <span class="support-time"><?php echo date('d/m/Y H:i', strtotime($support['thoiDiemTao'])); ?></span>
                            </div>
                            <div class="support-type">
                                <?php 
                                $types = [
                                    'general' => 'Thắc mắc chung',
                                    'payment' => 'Thanh toán',
                                    'technical' => 'Kỹ thuật',
                                    'report' => 'Báo cáo',
                                    'other' => 'Khác'
                                ];
                                echo $types[$support['loai']] ?? $support['loai'];
                                ?>
                            </div>
                            <div class="support-status">
                                <?php if ($support['trangThai'] === 'pending'): ?>
                                    <span class="badge badge-warning">Chờ xử lý</span>
                                <?php elseif ($support['trangThai'] === 'in_progress'): ?>
                                    <span class="badge badge-info">Đang xử lý</span>
                                <?php elseif ($support['trangThai'] === 'resolved'): ?>
                                    <span class="badge badge-success">Đã trả lời</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Đã đóng</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="support-content">
                            <h4><?php echo htmlspecialchars($support['tieuDe']); ?></h4>
                            <p><?php echo nl2br(htmlspecialchars($support['noiDung'])); ?></p>
                            
                            <?php if (!empty($support['phanHoi'])): ?>
                            <div class="support-reply">
                                <strong><i class="fas fa-reply"></i> Phản hồi:</strong>
                                <p><?php echo nl2br(htmlspecialchars($support['phanHoi'])); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="support-actions">
                            <?php if ($support['trangThai'] === 'pending' || $support['trangThai'] === 'in_progress'): ?>
                                <button onclick="showReplyModal(<?php echo $support['maHoTro']; ?>)" class="btn btn-primary">
                                    <i class="fas fa-reply"></i> Trả lời
                                </button>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="close">
                                    <input type="hidden" name="supportId" value="<?php echo $support['maHoTro']; ?>">
                                    <button type="submit" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Đóng
                                    </button>
                                </form>
                            <?php endif; ?>
                            <a href="xemnguoidung.php?id=<?php echo $support['maNguoiDung']; ?>" class="btn btn-outline">
                                <i class="fas fa-user"></i> Xem người dùng
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($supports)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Không có yêu cầu hỗ trợ nào</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Modal trả lời -->
    <div id="replyModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h2>Trả lời yêu cầu hỗ trợ</h2>
            <form method="POST">
                <input type="hidden" name="action" value="reply">
                <input type="hidden" name="supportId" id="replySupportId">
                <div class="form-group">
                    <label>Nội dung phản hồi:</label>
                    <textarea name="reply" rows="6" class="form-control" required></textarea>
                </div>
                <div class="modal-buttons">
                    <button type="submit" class="btn btn-primary">Gửi phản hồi</button>
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">Hủy</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    function showReplyModal(supportId) {
        document.getElementById('replySupportId').value = supportId;
        document.getElementById('replyModal').style.display = 'flex';
    }
    
    function closeModal() {
        document.getElementById('replyModal').style.display = 'none';
    }
    </script>
</body>
</html>
