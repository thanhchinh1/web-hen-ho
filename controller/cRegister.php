<?php
// Bật hiển thị lỗi cho debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../models/mSession.php';
require_once '../models/mUser.php';
require_once '../models/mPasswordValidator.php';
require_once '../models/mEmailVerification.php';
require_once '../models/mEmailService.php';

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
$acceptTerms = isset($_POST['accept_terms']) ? true : false;

// Mảng lưu lỗi
$errors = [];

// Validate chấp nhận điều khoản
if (!$acceptTerms) {
    $errors[] = 'Bạn phải đồng ý với Điều khoản dịch vụ và Chính sách bảo mật để đăng ký!';
}

// Validate email
if (empty($email)) {
    $errors[] = 'Vui lòng nhập email!';
} else {
    // Kiểm tra định dạng email
    $emailPattern = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
    
    if (!preg_match($emailPattern, $email)) {
        $errors[] = 'Email không hợp lệ!';
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
    $errors[] = 'Email này đã được đăng ký!';
    Session::set('register_errors', $errors);
    Session::set('register_data', ['email' => $email]);
    header('Location: ../views/dangky/register.php');
    exit;
}

// ============================================
// TẠO OTP VÀ GỬI EMAIL XÁC THỰC
// ============================================

// Hash mật khẩu
$passwordHash = md5($password);

// Tạo OTP
$emailVerification = new EmailVerification();
$otpResult = $emailVerification->createOTP($email, $passwordHash);

if (!$otpResult['success']) {
    $errors[] = $otpResult['message'];
    Session::set('register_errors', $errors);
    Session::set('register_data', ['email' => $email]);
    header('Location: ../views/dangky/register.php');
    exit;
}

// Gửi email OTP
$emailService = new EmailService();
$emailSent = $emailService->sendOTPEmail($email, $otpResult['otp'], $otpResult['expires_minutes']);

if (!$emailSent) {
    $errors[] = 'Không thể gửi email xác thực. Vui lòng kiểm tra email và thử lại!';
    Session::set('register_errors', $errors);
    Session::set('register_data', ['email' => $email]);
    header('Location: ../views/dangky/register.php');
    exit;
}

// Lưu email vào session để dùng ở trang verify
Session::set('verify_email', $email);
Session::setFlash('otp_sent', 'Mã xác thực đã được gửi đến ' . $email);

// Chuyển đến trang nhập OTP
$redirectUrl = '../views/dangky/verify-email.php';
if (isset($_GET['action']) && $_GET['action'] === 'like' && isset($_GET['targetUser'])) {
    $redirectUrl .= '?action=like&targetUser=' . urlencode($_GET['targetUser']);
}

header('Location: ' . $redirectUrl);
exit;
?>
