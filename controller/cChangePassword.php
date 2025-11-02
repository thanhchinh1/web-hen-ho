<?php
require_once '../models/mSession.php';
require_once '../models/mUser.php';

Session::start();

// Kiểm tra đăng nhập
if (!Session::isLoggedIn()) {
    Session::setFlash('error_message', 'Vui lòng đăng nhập!');
    header('Location: ../views/dangnhap/login.php');
    exit;
}

// Kiểm tra POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/taikhoan/doimatkhau.php');
    exit;
}

// CSRF Protection
$csrfToken = $_POST['csrf_token'] ?? '';
if (!Session::verifyCSRFToken($csrfToken)) {
    Session::setFlash('error_message', 'Invalid CSRF token. Vui lòng thử lại!');
    header('Location: ../views/taikhoan/doimatkhau.php');
    exit;
}

$userId = Session::getUserId();
$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Validation
$errors = [];

if (empty($currentPassword)) {
    $errors[] = 'Vui lòng nhập mật khẩu hiện tại';
}

if (empty($newPassword)) {
    $errors[] = 'Vui lòng nhập mật khẩu mới';
}

if (empty($confirmPassword)) {
    $errors[] = 'Vui lòng xác nhận mật khẩu mới';
}

// Kiểm tra độ mạnh mật khẩu
if (!empty($newPassword)) {
    if (strlen($newPassword) < 8) {
        $errors[] = 'Mật khẩu phải có ít nhất 8 ký tự';
    }
    
    if (!preg_match('/[a-z]/', $newPassword)) {
        $errors[] = 'Mật khẩu phải có ít nhất 1 chữ thường (a-z)';
    }
    
    if (!preg_match('/[A-Z]/', $newPassword)) {
        $errors[] = 'Mật khẩu phải có ít nhất 1 chữ hoa (A-Z)';
    }
    
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $newPassword)) {
        $errors[] = 'Mật khẩu phải có ít nhất 1 ký tự đặc biệt (!@#$%^&*...)';
    }
}

// Kiểm tra mật khẩu mới và xác nhận khớp
if ($newPassword !== $confirmPassword) {
    $errors[] = 'Mật khẩu mới và xác nhận mật khẩu không khớp';
}

// Kiểm tra mật khẩu mới không giống mật khẩu cũ
if ($currentPassword === $newPassword) {
    $errors[] = 'Mật khẩu mới phải khác mật khẩu hiện tại';
}

if (!empty($errors)) {
    Session::setFlash('error_message', implode('<br>', $errors));
    header('Location: ../views/taikhoan/doimatkhau.php');
    exit;
}

// Xử lý đổi mật khẩu
$userModel = new User();

// Kiểm tra mật khẩu hiện tại
if (!$userModel->verifyPassword($userId, $currentPassword)) {
    Session::setFlash('error_message', 'Mật khẩu hiện tại không đúng!');
    header('Location: ../views/taikhoan/doimatkhau.php');
    exit;
}

// Cập nhật mật khẩu mới
if ($userModel->updatePassword($userId, $newPassword)) {
    Session::setFlash('success_message', 'Đổi mật khẩu thành công!');
    header('Location: ../views/taikhoan/doimatkhau.php');
    exit;
} else {
    Session::setFlash('error_message', 'Có lỗi xảy ra khi đổi mật khẩu. Vui lòng thử lại!');
    header('Location: ../views/taikhoan/doimatkhau.php');
    exit;
}
?>
