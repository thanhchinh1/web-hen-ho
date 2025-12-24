<?php
require_once '../../models/mSession.php';
require_once '../../models/mVIP.php';

Session::start();
if (!Session::get('is_admin') && Session::get('user_role') !== 'admin') {
    Session::destroy();
    header('Location: ../dangnhap/login.php');
    exit;
}

$db = clsConnect::getInstance()->connect();
$vipModel = new VIP();

// Xác nhận giao dịch
if (isset($_POST['action'], $_POST['id'])) {
    $id = intval($_POST['id']);
    if ($_POST['action'] === 'confirm') {
        // Lấy thông tin giao dịch
        $stmt = $db->prepare("SELECT * FROM giaodich WHERE id = ? AND trang_thai = 'cho_xac_nhan'");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $giaoDich = $stmt->get_result()->fetch_assoc();
        if ($giaoDich) {
            // Nâng VIP cho user
            $months = 1;
            if (preg_match('/VIP (\\d+)T/i', $giaoDich['ma_chuyen_khoan'], $m)) {
                $months = intval($m[1]);
            }
            $vipModel->createVIPPackage($giaoDich['user_id'], $months);
            // Cập nhật trạng thái giao dịch
            $stmt2 = $db->prepare("UPDATE giaodich SET trang_thai = 'da_xac_nhan', thoi_gian_xac_nhan = NOW() WHERE id = ?");
            $stmt2->bind_param("i", $id);
            $stmt2->execute();
        }
    } elseif ($_POST['action'] === 'delete') {
        $stmt = $db->prepare("UPDATE giaodich SET trang_thai = 'da_xoa', thoi_gian_xoa = NOW() WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    header('Location: giaodich.php');
    exit;
}

// Lấy danh sách giao dịch chờ xác nhận
$result = $db->query("SELECT * FROM giaodich WHERE trang_thai = 'cho_xac_nhan' ORDER BY thoi_gian_tao DESC");
$giaodichs = $result->fetch_all(MYSQLI_ASSOC);

// Lấy danh sách giao dịch đã thanh toán thành công
$result_success = $db->query("SELECT * FROM giaodich WHERE trang_thai = 'da_xac_nhan' ORDER BY thoi_gian_xac_nhan DESC");
$giaodichs_success = $result_success->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý giao dịch - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/admin.css?v=<?php echo time(); ?>">
</head>
<body>
<?php $adminName = Session::get('admin_name'); ?>
<div class="admin-wrapper">
    <?php include 'sidebar.php'; ?>
    <main class="main-content">
        <div class="top-bar">
            <h1>Quản lý giao dịch nâng cấp VIP</h1>
        </div>
        <div class="content-area">
            <div class="table-container">
                <h2 style="margin-bottom:8px;">Giao dịch chờ xác nhận</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tài khoản</th>
                            <th>Mã chuyển khoản</th>
                            <th>Ngày giao dịch</th>
                            <th>Thời gian tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($giaodichs as $gd): ?>
                        <tr>
                            <td><?php echo $gd['id']; ?></td>
                            <td><?php echo htmlspecialchars($gd['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($gd['ma_chuyen_khoan']); ?></td>
                            <td><?php echo $gd['ngay_giao_dich'] ? $gd['ngay_giao_dich'] : '-'; ?></td>
                            <td><?php echo $gd['thoi_gian_tao']; ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo $gd['id']; ?>">
                                    <button type="submit" name="action" value="confirm" class="btn-action btn-success" onclick="return confirm('Xác nhận giao dịch này và nâng VIP cho tài khoản?');">
                                        <i class="fas fa-check"></i> Xác nhận
                                    </button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo $gd['id']; ?>">
                                    <button type="submit" name="action" value="delete" class="btn-action btn-danger" onclick="return confirm('Xoá giao dịch này?');">
                                        <i class="fas fa-trash"></i> Xoá
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($giaodichs)): ?>
                        <tr><td colspan="6" class="text-center">Không có giao dịch chờ xác nhận</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="table-container" style="margin-top:32px;">
                <h2 style="margin-bottom:8px;">Lịch sử giao dịch thành công</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tài khoản</th>
                            <th>Mã chuyển khoản</th>
                            <th>Ngày giao dịch</th>
                            <th>Thời gian tạo</th>
                            <th>Thời gian xác nhận</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($giaodichs_success as $gd): ?>
                        <tr>
                            <td><?php echo $gd['id']; ?></td>
                            <td><?php echo htmlspecialchars($gd['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($gd['ma_chuyen_khoan']); ?></td>
                            <td><?php echo $gd['ngay_giao_dich'] ? $gd['ngay_giao_dich'] : '-'; ?></td>
                            <td><?php echo $gd['thoi_gian_tao']; ?></td>
                            <td><?php echo $gd['thoi_gian_xac_nhan'] ? $gd['thoi_gian_xac_nhan'] : '-'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($giaodichs_success)): ?>
                        <tr><td colspan="6" class="text-center">Chưa có giao dịch thành công</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>
