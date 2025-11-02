<?php
// Bật hiển thị lỗi cho debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../models/mSession.php';
require_once '../models/mUser.php';
require_once '../models/mPasswordValidator.php';

Session::start();

// Chỉ chấp nhận POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/dangky/register.php');
    exit;
}

// Lấy dữ liệu từ form
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Mảng lưu lỗi
$errors = [];

// Validate email/SĐT
if (empty($email)) {
    $errors[] = 'Vui lòng nhập email hoặc số điện thoại!';
} else {
    // Kiểm tra định dạng email hoặc SĐT Việt Nam
    $emailPattern = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
    $phonePattern = '/^(032|033|034|035|036|037|038|039|096|097|098|081|082|083|084|085|091|094|070|076|077|078|079|090|093)\d{7}$/';
    
    if (!preg_match($emailPattern, $email) && !preg_match($phonePattern, $email)) {
        $errors[] = 'Email hoặc số điện thoại không hợp lệ!';
    }
}

// Validate mật khẩu với PasswordValidator
if (empty($password)) {
    $errors[] = 'Vui lòng nhập mật khẩu!';
} else {
    $passwordValidation = PasswordValidator::validate($password);
    if (!$passwordValidation['valid']) {
        $errors = array_merge($errors, $passwordValidation['errors']);
    }
}

// Validate xác nhận mật khẩu
if (empty($confirmPassword)) {
    $errors[] = 'Vui lòng xác nhận mật khẩu!';
} elseif ($password !== $confirmPassword) {
    $errors[] = 'Mật khẩu xác nhận không khớp!';
}

// Nếu có lỗi, quay lại trang đăng ký
if (!empty($errors)) {
    Session::set('register_errors', $errors);
    Session::set('register_data', ['email' => $email]);
    header('Location: ../views/dangky/register.php');
    exit;
}

// Xử lý đăng ký
$userModel = new User();

// Kiểm tra email đã tồn tại
if ($userModel->checkEmailExists($email)) {
    $errors[] = 'Email/Số điện thoại này đã được đăng ký!';
    Session::set('register_errors', $errors);
    Session::set('register_data', ['email' => $email]);
    header('Location: ../views/dangky/register.php');
    exit;
}

// Đăng ký người dùng mới
$userId = $userModel->register($email, $password);

if ($userId) {
    // Đăng ký thành công
    // Lưu thông báo và email vào session
    Session::set('register_success', 'Đăng ký tài khoản thành công! Vui lòng đăng nhập.');
    Session::set('registered_email', $email);
    
    // Kiểm tra có pending action không
    $redirectUrl = '../views/dangnhap/login.php';
    if (isset($_GET['action']) && $_GET['action'] === 'like' && isset($_GET['targetUser'])) {
        $redirectUrl .= '?action=like&targetUser=' . urlencode($_GET['targetUser']);
    }
    
    // Chuyển đến trang đăng nhập
    header('Location: ' . $redirectUrl);
    exit;
} else {
    // Đăng ký thất bại
    $errors[] = 'Có lỗi xảy ra khi đăng ký. Vui lòng thử lại!';
    Session::set('register_errors', $errors);
    Session::set('register_data', ['email' => $email]);
    header('Location: ../views/dangky/register.php');
    exit;
}
?>
