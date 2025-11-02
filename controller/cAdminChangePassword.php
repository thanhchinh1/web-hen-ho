<?php
require_once '../models/mSession.php';
require_once '../models/mAdmin.php';

Session::start();

// Kiểm tra đăng nhập admin
if (!Session::get('is_admin')) {
    Session::setFlash('admin_error', 'Vui lòng đăng nhập');
    header('Location: ../views/admin/dangnhap.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/admin/doimatkhau.php');
    exit;
}

$adminId = Session::get('admin_id');

// Get form data
$oldPassword = $_POST['old_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Validation
$errors = [];

if (empty($oldPassword)) {
    $errors[] = 'Vui lòng nhập mật khẩu hiện tại';
}

if (empty($newPassword)) {
    $errors[] = 'Vui lòng nhập mật khẩu mới';
} elseif (strlen($newPassword) < 6) {
    $errors[] = 'Mật khẩu mới phải có ít nhất 6 ký tự';
}

if ($newPassword !== $confirmPassword) {
    $errors[] = 'Mật khẩu xác nhận không khớp';
}

if ($oldPassword === $newPassword) {
    $errors[] = 'Mật khẩu mới phải khác với mật khẩu cũ';
}

if (!empty($errors)) {
    Session::setFlash('admin_error', implode('<br>', $errors));
    header('Location: ../views/admin/doimatkhau.php');
    exit;
}

// Change password
try {
    $adminModel = new Admin();
    $result = $adminModel->changePassword($adminId, $oldPassword, $newPassword);
    
    if ($result === true) {
        Session::setFlash('admin_success', 'Đổi mật khẩu thành công!');
        header('Location: ../views/admin/doimatkhau.php');
        exit;
    } elseif ($result === 'wrong_password') {
        Session::setFlash('admin_error', 'Mật khẩu hiện tại không đúng');
        header('Location: ../views/admin/doimatkhau.php');
        exit;
    } else {
        Session::setFlash('admin_error', 'Không thể đổi mật khẩu. Vui lòng thử lại');
        header('Location: ../views/admin/doimatkhau.php');
        exit;
    }
} catch (Exception $e) {
    Session::setFlash('admin_error', 'Lỗi: ' . $e->getMessage());
    header('Location: ../views/admin/doimatkhau.php');
    exit;
}
?>