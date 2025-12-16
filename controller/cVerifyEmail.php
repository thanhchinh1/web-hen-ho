<?php
require_once '../models/mSession.php';
require_once '../models/mEmailVerification.php';
require_once '../models/mUser.php';
require_once '../models/mEmailService.php';
require_once '../models/mDbconnect.php';

Session::start();

// Chỉ chấp nhận POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/dangky/verify-email.php');
    exit;
}

// Lấy email từ session
$email = Session::get('verify_email');
if (empty($email)) {
    header('Location: ../views/dangky/register.php');
    exit;
}

// Lấy mã OTP từ form
$otpCode = trim($_POST['otp_code'] ?? '');

// Validate OTP
$errors = [];
if (empty($otpCode)) {
    $errors[] = 'Vui lòng nhập mã OTP!';
} elseif (!preg_match('/^\d{6}$/', $otpCode)) {
    $errors[] = 'Mã OTP phải là 6 chữ số!';
}

if (!empty($errors)) {
    Session::set('verify_errors', $errors);
    
    // Giữ lại action và targetUser nếu có
    $redirectUrl = '../views/dangky/verify-email.php';
    if (isset($_GET['action']) && isset($_GET['targetUser'])) {
        $redirectUrl .= '?action=' . urlencode($_GET['action']) . '&targetUser=' . urlencode($_GET['targetUser']);
    }
    
    header('Location: ' . $redirectUrl);
    exit;
}

// Xác thực OTP
$emailVerification = new EmailVerification();
$verifyResult = $emailVerification->verifyOTP($email, $otpCode);

if (!$verifyResult['success']) {
    // Xác thực thất bại
    $errors[] = $verifyResult['message'];
    Session::set('verify_errors', $errors);
    
    $redirectUrl = '../views/dangky/verify-email.php';
    if (isset($_GET['action']) && isset($_GET['targetUser'])) {
        $redirectUrl .= '?action=' . urlencode($_GET['action']) . '&targetUser=' . urlencode($_GET['targetUser']);
    }
    
    header('Location: ' . $redirectUrl);
    exit;
}

// ============================================
// XÁC THỰC THÀNH CÔNG - TẠO TÀI KHOẢN
// ============================================

$userModel = new User();

// Kiểm tra lại email có tồn tại chưa (phòng trường hợp đã đăng ký trong lúc chờ OTP)
if ($userModel->checkEmailExists($email)) {
    $errors[] = 'Email/Số điện thoại này đã được đăng ký!';
    Session::set('verify_errors', $errors);
    Session::delete('verify_email');
    header('Location: ../views/dangky/register.php');
    exit;
}

// Tạo tài khoản với password hash từ OTP
$passwordHash = $verifyResult['password_hash'];

// Insert user vào database
try {
    $db = clsConnect::getInstance();
    $conn = $db->connect();
    
    $stmt = $conn->prepare("INSERT INTO nguoidung (tenDangNhap, matKhau, email_verified) VALUES (?, ?, 1)");
    $stmt->bind_param("ss", $email, $passwordHash);
    
    if ($stmt->execute()) {
        $userId = $conn->insert_id;
        
        // Gửi email chào mừng (không quan trọng nên không kiểm tra kết quả)
        try {
            $emailService = new EmailService();
            $emailService->sendWelcomeEmail($email);
        } catch (Exception $e) {
            error_log("Welcome email failed: " . $e->getMessage());
        }
        
        // Xóa session verify
        Session::delete('verify_email');
        
        // Đăng ký thành công
        Session::setFlash('register_success', 'Đăng ký tài khoản thành công! Vui lòng đăng nhập.');
        Session::set('registered_email', $email);
        
        // Kiểm tra có pending action không
        $redirectUrl = '../views/dangnhap/login.php';
        if (isset($_GET['action']) && $_GET['action'] === 'like' && isset($_GET['targetUser'])) {
            $redirectUrl .= '?action=like&targetUser=' . urlencode($_GET['targetUser']);
        }
        
        header('Location: ' . $redirectUrl);
        exit;
    } else {
        throw new Exception('Không thể tạo tài khoản');
    }
    
} catch (Exception $e) {
    error_log("Create account error: " . $e->getMessage());
    $errors[] = 'Có lỗi xảy ra khi tạo tài khoản. Vui lòng thử lại!';
    Session::set('verify_errors', $errors);
    
    $redirectUrl = '../views/dangky/verify-email.php';
    if (isset($_GET['action']) && isset($_GET['targetUser'])) {
        $redirectUrl .= '?action=' . urlencode($_GET['action']) . '&targetUser=' . urlencode($_GET['targetUser']);
    }
    
    header('Location: ' . $redirectUrl);
    exit;
}
