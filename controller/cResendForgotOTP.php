<?php
require_once '../models/mSession.php';
require_once '../models/mEmailVerification.php';
require_once '../models/mEmailService.php';

Session::start();

// Lấy email từ session
$email = Session::get('forgot_user_email');
$userId = Session::get('forgot_user_id');

if (empty($email) || empty($userId)) {
    Session::setFlash('forgot_errors', ['Phiên làm việc đã hết hạn. Vui lòng thử lại!']);
    Session::set('forgot_password_step', 1);
    header('Location: ../views/dangnhap/quenmatkhau.php');
    exit;
}

try {
    // Gửi lại OTP
    $emailVerification = new EmailVerification();
    $otpResult = $emailVerification->resendOTP($email);
    
    if (!$otpResult['success']) {
        Session::setFlash('forgot_errors', [$otpResult['message']]);
        header('Location: ../views/dangnhap/quenmatkhau.php');
        exit;
    }
    
    // Gửi email
    $emailService = new EmailService();
    $emailSent = $emailService->sendForgotPasswordOTP($email, $otpResult['otp'], $otpResult['expires_minutes']);
    
    if (!$emailSent) {
        Session::setFlash('forgot_errors', ['Không thể gửi email. Vui lòng thử lại!']);
        header('Location: ../views/dangnhap/quenmatkhau.php');
        exit;
    }
    
    Session::setFlash('otp_sent', 'Mã OTP mới đã được gửi đến email của bạn!');
    header('Location: ../views/dangnhap/quenmatkhau.php');
    exit;
    
} catch (Exception $e) {
    error_log("Resend forgot OTP error: " . $e->getMessage());
    Session::setFlash('forgot_errors', ['Có lỗi xảy ra. Vui lòng thử lại!']);
    header('Location: ../views/dangnhap/quenmatkhau.php');
    exit;
}
