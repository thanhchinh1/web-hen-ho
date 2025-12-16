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
    
    if ($action === 'create') {
        $code = strtoupper(trim($_POST['code']));
        $name = trim($_POST['name']);
        $type = $_POST['type'];
        $value = floatval($_POST['value']);
        $maxValue = floatval($_POST['max_value'] ?? 0);
        $maxUsage = intval($_POST['max_usage'] ?? 0);
        $startDate = $_POST['start_date'];
        $endDate = $_POST['end_date'];
        $applyFor = $_POST['apply_for'];
        
        $stmt = $db->prepare("INSERT INTO magiamgia (maCoupon, tenChuongTrinh, loaiGiam, giaTriGiam, giaTriToiDa, soLuongToiDa, ngayBatDau, ngayKetThuc, apDungCho) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssddisss", $code, $name, $type, $value, $maxValue, $maxUsage, $startDate, $endDate, $applyFor);
        
        if ($stmt->execute()) {
            Session::setFlash('success', 'Tạo mã giảm giá thành công!');
        } else {
            Session::setFlash('error', 'Mã giảm giá đã tồn tại!');
        }
    } elseif ($action === 'deactivate') {
        $couponId = intval($_POST['couponId']);
        $stmt = $db->prepare("UPDATE magiamgia SET trangThai = 'inactive' WHERE maMaGiamGia = ?");
        $stmt->bind_param("i", $couponId);
        $stmt->execute();
        Session::setFlash('success', 'Đã vô hiệu hóa mã giảm giá!');
    }
    
    header('Location: magiamgia.php');
    exit;
}

// Lấy danh sách mã giảm giá
$filter = $_GET['filter'] ?? 'active';
$sql = "SELECT * FROM magiamgia";

if ($filter === 'active') {
    $sql .= " WHERE trangThai = 'active' AND ngayKetThuc >= NOW()";
} elseif ($filter === 'expired') {
    $sql .= " WHERE trangThai = 'expired' OR ngayKetThuc < NOW()";
} elseif ($filter === 'inactive') {
    $sql .= " WHERE trangThai = 'inactive'";
}

$sql .= " ORDER BY thoiDiemTao DESC";

$result = $db->query($sql);
$coupons = $result->fetch_all(MYSQLI_ASSOC);

$success = Session::getFlash('success');
$error = Session::getFlash('error');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã giảm giá - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/admin.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="top-bar">
                <h1>Quản lý mã giảm giá</h1>
                <button onclick="showCreateModal()" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tạo mã mới
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
                    <a href="?filter=active" class="<?php echo $filter === 'active' ? 'active' : ''; ?>">Đang hoạt động</a>
                    <a href="?filter=expired" class="<?php echo $filter === 'expired' ? 'active' : ''; ?>">Hết hạn</a>
                    <a href="?filter=inactive" class="<?php echo $filter === 'inactive' ? 'active' : ''; ?>">Đã vô hiệu</a>
                    <a href="?filter=all" class="<?php echo $filter === 'all' ? 'active' : ''; ?>">Tất cả</a>
                </div>
                
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Mã</th>
                                <th>Tên chương trình</th>
                                <th>Giảm giá</th>
                                <th>Sử dụng</th>
                                <th>Áp dụng cho</th>
                                <th>Thời gian</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($coupons as $coupon): ?>
                            <tr>
                                <td><strong class="coupon-code"><?php echo htmlspecialchars($coupon['maCoupon']); ?></strong></td>
                                <td><?php echo htmlspecialchars($coupon['tenChuongTrinh']); ?></td>
                                <td>
                                    <?php if ($coupon['loaiGiam'] === 'percent'): ?>
                                        <span class="discount-value"><?php echo $coupon['giaTriGiam']; ?>%</span>
                                    <?php else: ?>
                                        <span class="discount-value"><?php echo number_format($coupon['giaTriGiam']); ?>đ</span>
                                    <?php endif; ?>
                                    <?php if ($coupon['giaTriToiDa'] > 0): ?>
                                        <br><small>Tối đa: <?php echo number_format($coupon['giaTriToiDa']); ?>đ</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo $coupon['soLuongDaSuDung']; ?> / 
                                    <?php echo $coupon['soLuongToiDa'] > 0 ? $coupon['soLuongToiDa'] : '∞'; ?>
                                </td>
                                <td>
                                    <?php 
                                    $applyFor = [
                                        'all' => 'Tất cả',
                                        'new_user' => 'Người dùng mới',
                                        'vip_only' => 'Chỉ VIP'
                                    ];
                                    echo $applyFor[$coupon['apDungCho']] ?? $coupon['apDungCho'];
                                    ?>
                                </td>
                                <td>
                                    <small>
                                        <?php echo date('d/m/Y', strtotime($coupon['ngayBatDau'])); ?><br>
                                        đến<br>
                                        <?php echo date('d/m/Y', strtotime($coupon['ngayKetThuc'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <?php if ($coupon['trangThai'] === 'active' && strtotime($coupon['ngayKetThuc']) >= time()): ?>
                                        <span class="badge badge-success">Hoạt động</span>
                                    <?php elseif ($coupon['trangThai'] === 'expired' || strtotime($coupon['ngayKetThuc']) < time()): ?>
                                        <span class="badge badge-danger">Hết hạn</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Vô hiệu</span>
                                    <?php endif; ?>
                                </td>
                                <td class="action-buttons">
                                    <?php if ($coupon['trangThai'] === 'active'): ?>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc muốn vô hiệu hóa mã này?');">
                                            <input type="hidden" name="action" value="deactivate">
                                            <input type="hidden" name="couponId" value="<?php echo $coupon['maMaGiamGia']; ?>">
                                            <button type="submit" class="btn-action btn-danger" title="Vô hiệu hóa">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($coupons)): ?>
                            <tr>
                                <td colspan="8" class="text-center">Không có mã giảm giá nào</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Modal tạo mã -->
    <div id="createModal" class="modal" style="display: none;">
        <div class="modal-content modal-large">
            <h2>Tạo mã giảm giá mới</h2>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <div class="form-row">
                    <div class="form-group">
                        <label>Mã coupon:<span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" required maxlength="50">
                    </div>
                    <div class="form-group">
                        <label>Tên chương trình:<span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required maxlength="255">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Loại giảm:<span class="text-danger">*</span></label>
                        <select name="type" class="form-control" required>
                            <option value="percent">Phần trăm (%)</option>
                            <option value="fixed">Số tiền cố định (đ)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Giá trị giảm:<span class="text-danger">*</span></label>
                        <input type="number" name="value" class="form-control" required step="0.01" min="0">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Giá trị giảm tối đa (đ):</label>
                        <input type="number" name="max_value" class="form-control" step="1000" min="0">
                    </div>
                    <div class="form-group">
                        <label>Số lượng tối đa:</label>
                        <input type="number" name="max_usage" class="form-control" min="0" placeholder="0 = không giới hạn">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Ngày bắt đầu:<span class="text-danger">*</span></label>
                        <input type="datetime-local" name="start_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Ngày kết thúc:<span class="text-danger">*</span></label>
                        <input type="datetime-local" name="end_date" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Áp dụng cho:</label>
                    <select name="apply_for" class="form-control" required>
                        <option value="all">Tất cả người dùng</option>
                        <option value="new_user">Người dùng mới</option>
                        <option value="vip_only">Chỉ thành viên VIP</option>
                    </select>
                </div>
                
                <div class="modal-buttons">
                    <button type="submit" class="btn btn-primary">Tạo mã</button>
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">Hủy</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    function showCreateModal() {
        document.getElementById('createModal').style.display = 'flex';
    }
    
    function closeModal() {
        document.getElementById('createModal').style.display = 'none';
    }
    </script>
</body>
</html>
