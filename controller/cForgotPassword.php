<?php
require_once '../models/mSession.php';
require_once '../models/mUser.php';
require_once '../models/mDbconnect.php';
require_once '../models/mEmailVerification.php';
require_once '../models/mEmailService.php';

Session::start();

// Chỉ chấp nhận POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/dangnhap/quenmatkhau.php');
    exit;
}

$step = intval($_POST['step'] ?? 1);
$errors = [];

if ($step == 1) {
    // ============================================
    // BƯỚC 1: XÁC MINH EMAIL VÀ GỬI OTP
    // ============================================
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $errors[] = 'Vui lòng nhập email!';
        Session::setFlash('forgot_errors', $errors);
        header('Location: ../views/dangnhap/quenmatkhau.php');
        exit;
    }
    
    // Kiểm tra định dạng email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email không đúng định dạng!';
        Session::setFlash('forgot_errors', $errors);
        header('Location: ../views/dangnhap/quenmatkhau.php');
        exit;
    }
    
    // Kiểm tra tài khoản có tồn tại không
    $db = clsConnect::getInstance();
    $conn = $db->connect();
    
    $stmt = $conn->prepare("SELECT maNguoiDung, tenDangNhap, trangThaiNguoiDung FROM nguoidung WHERE tenDangNhap = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $errors[] = 'Email không tồn tại trong hệ thống!';
        Session::setFlash('forgot_errors', $errors);
        header('Location: ../views/dangnhap/quenmatkhau.php');
        exit;
    }
    
    $user = $result->fetch_assoc();
    
    // Kiểm tra tài khoản có bị khóa không
    if ($user['trangThaiNguoiDung'] === 'banned' || $user['trangThaiNguoiDung'] === 'locked') {
        $errors[] = 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ admin!';
        Session::setFlash('forgot_errors', $errors);
        header('Location: ../views/dangnhap/quenmatkhau.php');
        exit;
    }
    
    // Tạo OTP cho quên mật khẩu
    $tempHash = 'forgot_password_' . $user['maNguoiDung'];
    
    $emailVerification = new EmailVerification();
    $otpResult = $emailVerification->createOTP($email, $tempHash);
    
    if (!$otpResult['success']) {
        $errors[] = $otpResult['message'];
        Session::setFlash('forgot_errors', $errors);
        header('Location: ../views/dangnhap/quenmatkhau.php');
        exit;
    }
    
    // Gửi email OTP
    $emailService = new EmailService();
    $emailSent = $emailService->sendForgotPasswordOTP($email, $otpResult['otp'], $otpResult['expires_minutes']);
    
    if (!$emailSent) {
        $errors[] = 'Không thể gửi email xác thực. Vui lòng kiểm tra email và thử lại!';
        Session::setFlash('forgot_errors', $errors);
        header('Location: ../views/dangnhap/quenmatkhau.php');
        exit;
    }
    
    // Lưu thông tin vào session
    Session::set('forgot_user_id', $user['maNguoiDung']);
    Session::set('forgot_user_email', $user['tenDangNhap']);
    Session::set('forgot_password_step', 2);
    Session::setFlash('otp_sent', 'Mã xác thực đã được gửi đến ' . $email);
    
    header('Location: ../views/dangnhap/quenmatkhau.php');
    exit;
    
} elseif ($step == 2) {
    // ============================================
    // BƯỚC 2: XÁC THỰC OTP
    // ============================================
    $email = Session::get('forgot_user_email');
    $userId = Session::get('forgot_user_id');
    
    if (!$email || !$userId) {
        $errors[] = 'Phiên làm việc đã hết hạn. Vui lòng thử lại!';
        Session::setFlash('forgot_errors', $errors);
        Session::set('forgot_password_step', 1);
        header('Location: ../views/dangnhap/quenmatkhau.php');
        exit;
    }
    
    $otpCode = trim($_POST['otp_code'] ?? '');
    
    if (empty($otpCode)) {
        $errors[] = 'Vui lòng nhập mã OTP!';
    } elseif (!preg_match('/^\d{6}$/', $otpCode)) {
        $errors[] = 'Mã OTP phải là 6 chữ số!';
    }
    
    if (!empty($errors)) {
        Session::setFlash('forgot_errors', $errors);
        header('Location: ../views/dangnhap/quenmatkhau.php');
        exit;
    }
    
    // Xác thực OTP
    $emailVerification = new EmailVerification();
    $verifyResult = $emailVerification->verifyOTP($email, $otpCode);
    
    if (!$verifyResult['success']) {
        $errors[] = $verifyResult['message'];
        Session::setFlash('forgot_errors', $errors);
        header('Location: ../views/dangnhap/quenmatkhau.php');
        exit;
    }
    
    // OTP hợp lệ - chuyển sang bước 3
    Session::set('forgot_password_step', 3);
    Session::set('otp_verified', true);
    Session::setFlash('forgot_success', 'Xác thực thành công! Vui lòng nhập mật khẩu mới.');
    
    header('Location: ../views/dangnhap/quenmatkhau.php');
    exit;
    
} elseif ($step == 3) {
    // ============================================
    // BƯỚC 3: ĐẶT MẬT KHẨU MỚI
    // ============================================
    $userId = Session::get('forgot_user_id');
    $otpVerified = Session::get('otp_verified');
    
    if (!$userId || !$otpVerified) {
        $errors[] = 'Phiên làm việc không hợp lệ. Vui lòng thử lại!';
        Session::setFlash('forgot_errors', $errors);
        Session::set('forgot_password_step', 1);
        header('Location: ../views/dangnhap/quenmatkhau.php');
        exit;
    }
    
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($newPassword) || empty($confirmPassword)) {
        $errors[] = 'Vui lòng nhập đầy đủ thông tin!';
    }
    
    if ($newPassword !== $confirmPassword) {
        $errors[] = 'Mật khẩu xác nhận không khớp!';
    }
    
    if (strlen($newPassword) < 8) {
        $errors[] = 'Mật khẩu phải có ít nhất 8 ký tự!';
    }
    
    if (!preg_match('/[a-z]/', $newPassword)) {
        $errors[] = 'Mật khẩu phải có ít nhất 1 chữ thường (a-z)!';
    }
    
    if (!preg_match('/[A-Z]/', $newPassword)) {
        $errors[] = 'Mật khẩu phải có ít nhất 1 chữ hoa (A-Z)!';
    }
    
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $newPassword)) {
        $errors[] = 'Mật khẩu phải có ít nhất 1 ký tự đặc biệt (!@#$%^&*...)!';
    }
    
    if (!empty($errors)) {
        Session::setFlash('forgot_errors', $errors);
        header('Location: ../views/dangnhap/quenmatkhau.php');
        exit;
    }
    
    // Cập nhật mật khẩu mới
    $userModel = new User();
    
    if ($userModel->updatePassword($userId, $newPassword)) {
        // Xóa session quên mật khẩu
        Session::delete('forgot_user_id');
        Session::delete('forgot_user_email');
        Session::delete('forgot_password_step');
        Session::delete('otp_verified');
        
        // Thông báo thành công
        Session::setFlash('login_success', 'Đặt lại mật khẩu thành công! Vui lòng đăng nhập với mật khẩu mới.');
        header('Location: ../views/dangnhap/login.php');
        exit;
    } else {
        $errors[] = 'Có lỗi xảy ra khi đặt lại mật khẩu. Vui lòng thử lại!';
        Session::setFlash('forgot_errors', $errors);
        header('Location: ../views/dangnhap/quenmatkhau.php');
        exit;
    }
}

// Fallback
header('Location: ../views/dangnhap/quenmatkhau.php');
exit;
?>
