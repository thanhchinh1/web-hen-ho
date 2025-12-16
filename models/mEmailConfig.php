<?php
/**
 * Email Configuration
 * Cấu hình SMTP cho PHPMailer
 */
class EmailConfig {
    // ========================================
    // CẤU HÌNH QUAN TRỌNG - VUI LÒNG CẬP NHẬT
    // ========================================
    
    // Email gửi đi (Gmail của bạn)
    const SMTP_USERNAME = 'tranthanhchinhhmt@gmail.com';  // ⚠️ THAY ĐỔI NÀY
    
    // App Password (Mật khẩu ứng dụng của Gmail - KHÔNG phải mật khẩu đăng nhập)
    // Hướng dẫn tạo: https://support.google.com/accounts/answer/185833
    const SMTP_PASSWORD = 'sydogfzduydahytm';  // ⚠️ THAY ĐỔI NÀY
    
    // Tên hiển thị khi gửi email
    const FROM_NAME = 'wehhenho';
    
    // ========================================
    // CẤU HÌNH SMTP GMAIL (KHÔNG CẦN SỬA)
    // ========================================
    const SMTP_HOST = 'smtp.gmail.com';
    const SMTP_PORT = 587;
    const SMTP_SECURE = 'tls';  // tls hoặc ssl
    const SMTP_AUTH = true;
    
    // ========================================
    // CẤU HÌNH OTP
    // ========================================
    const OTP_LENGTH = 6;           // Độ dài mã OTP
    const OTP_EXPIRE_MINUTES = 10;  // Thời gian hết hạn OTP (phút)
    const OTP_MAX_ATTEMPTS = 5;     // Số lần nhập sai tối đa
    
    /**
     * Kiểm tra cấu hình đã được thiết lập chưa
     */
    public static function isConfigured() {
        // Kiểm tra email và password không phải giá trị mặc định
        return self::SMTP_USERNAME !== 'your-email@gmail.com' 
            && self::SMTP_PASSWORD !== 'your-app-password-here'
            && !empty(self::SMTP_USERNAME) 
            && !empty(self::SMTP_PASSWORD);
    }
    
    /**
     * Lấy tất cả cấu hình SMTP
     */
    public static function getSMTPConfig() {
        // Bỏ qua kiểm tra, cho phép sử dụng luôn
        // if (!self::isConfigured()) {
        //     throw new Exception('Vui lòng cấu hình email trong file models/mEmailConfig.php');
        // }
        
        return [
            'host' => self::SMTP_HOST,
            'port' => self::SMTP_PORT,
            'secure' => self::SMTP_SECURE,
            'auth' => self::SMTP_AUTH,
            'username' => self::SMTP_USERNAME,
            'password' => self::SMTP_PASSWORD,
            'from_email' => self::SMTP_USERNAME,
            'from_name' => self::FROM_NAME
        ];
    }
}
