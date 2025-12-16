<?php
require_once '../../models/mSession.php';
require_once '../../models/mUser.php';

Session::start();

if (!Session::get('is_admin') && Session::get('user_role') !== 'admin') {
    Session::destroy();
    header('Location: ../dangnhap/login.php');
    exit;
}

$adminId = Session::get('admin_id');
$adminName = Session::get('admin_name');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'Vui lòng điền đầy đủ thông tin!';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Mật khẩu mới không khớp!';
    } elseif (strlen($newPassword) < 6) {
        $error = 'Mật khẩu mới phải có ít nhất 6 ký tự!';
    } else {
        $userModel = new User();
        $admin = $userModel->getUserById($adminId);
        
        if (md5($oldPassword) !== $admin['matKhau']) {
            $error = 'Mật khẩu cũ không đúng!';
        } else {
            $hashedPassword = md5($newPassword);
            $db = clsConnect::getInstance()->connect();
            $stmt = $db->prepare("UPDATE nguoidung SET matKhau = ? WHERE maNguoiDung = ?");
            $stmt->bind_param("si", $hashedPassword, $adminId);
            
            if ($stmt->execute()) {
                Session::setFlash('success', 'Đổi mật khẩu thành công!');
                header('Location: doimatkhau.php');
                exit;
            } else {
                $error = 'Có lỗi xảy ra, vui lòng thử lại!';
            }
        }
    }
}

$success = Session::getFlash('success');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi mật khẩu - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/admin.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="top-bar">
                <h1>Đổi mật khẩu</h1>
            </div>
            
            <div class="content-area">
                <div class="form-container">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="admin-form">
                        <div class="form-group">
                            <label><i class="fas fa-lock"></i> Mật khẩu cũ:</label>
                            <input type="password" name="old_password" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-key"></i> Mật khẩu mới:</label>
                            <input type="password" name="new_password" class="form-control" required minlength="6">
                            <small class="form-text">Mật khẩu phải có ít nhất 6 ký tự</small>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-check-double"></i> Xác nhận mật khẩu mới:</label>
                            <input type="password" name="confirm_password" class="form-control" required minlength="6">
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Đổi mật khẩu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
