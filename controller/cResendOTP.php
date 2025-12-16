<?php
require_once '../models/mSession.php';
require_once '../models/mEmailVerification.php';
require_once '../models/mEmailService.php';

Session::start();

header('Content-Type: application/json');

// Chỉ chấp nhận POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Lấy email từ session
$email = Session::get('verify_email');
if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Phiên làm việc đã hết hạn. Vui lòng đăng ký lại!']);
    exit;
}

try {
    // Gửi lại OTP
    $emailVerification = new EmailVerification();
    $otpResult = $emailVerification->resendOTP($email);
    
    if (!$otpResult['success']) {
        echo json_encode(['success' => false, 'message' => $otpResult['message']]);
        exit;
    }
    
    // Gửi email
    $emailService = new EmailService();
    $emailSent = $emailService->sendOTPEmail($email, $otpResult['otp'], $otpResult['expires_minutes']);
    
    if (!$emailSent) {
        echo json_encode(['success' => false, 'message' => 'Không thể gửi email. Vui lòng thử lại!']);
        exit;
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Mã OTP mới đã được gửi đến email của bạn!'
    ]);
    
} catch (Exception $e) {
    error_log("Resend OTP error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra. Vui lòng thử lại!']);
}
