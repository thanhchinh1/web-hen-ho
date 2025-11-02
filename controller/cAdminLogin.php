<?php
require_once __DIR__ . '/../models/mSession.php';
require_once __DIR__ . '/../models/mAdmin.php';

Session::start();

// Chỉ chấp nhận POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/admin/dangnhap.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

$errors = [];

// Validate
if (empty($username)) {
    $errors[] = 'Vui lòng nhập tên đăng nhập!';
}

if (empty($password)) {
    $errors[] = 'Vui lòng nhập mật khẩu!';
}

if (!empty($errors)) {
    Session::setFlash('admin_login_errors', $errors);
    Session::setFlash('admin_login_data', ['username' => $username]);
    header('Location: ../views/admin/dangnhap.php');
    exit;
}

// Xử lý đăng nhập
$adminModel = new Admin();
$admin = $adminModel->login($username, $password);

if ($admin) {
    // Đăng nhập thành công
    Session::set('admin_id', $admin['maAdmin']);
    Session::set('admin_username', $admin['tenDangNhap']);
    Session::set('admin_name', $admin['hoTen']);
    Session::set('admin_role', $admin['vaiTro']);
    Session::set('is_admin', true);
    Session::set('admin_last_activity', time()); // Lưu thời gian đăng nhập
    
    Session::setFlash('admin_success', 'Đăng nhập thành công!');
    header('Location: ../views/admin/index.php');
    exit;
} else {
    // Đăng nhập thất bại
    $errors[] = 'Tên đăng nhập hoặc mật khẩu không đúng!';
    Session::setFlash('admin_login_errors', $errors);
    Session::setFlash('admin_login_data', ['username' => $username]);
    header('Location: ../views/admin/dangnhap.php');
    exit;
}
?>
