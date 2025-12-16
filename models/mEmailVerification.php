<?php
require_once __DIR__ . '/mDbconnect.php';
require_once __DIR__ . '/mEmailConfig.php';

/**
 * Email Verification Model
 * Quản lý OTP xác thực email
 */
class EmailVerification {
    private $conn;
    private $table = 'email_verifications';
    
    public function __construct() {
        $db = clsConnect::getInstance();
        $this->conn = $db->connect();
    }
    
    /**
     * Tạo mã OTP mới
     * 
     * @param string $email Email hoặc SĐT
     * @param string $passwordHash Mật khẩu đã hash
     * @return array ['success' => bool, 'otp' => string, 'message' => string]
     */
    public function createOTP($email, $passwordHash) {
        try {
            // Xóa các OTP cũ chưa xác thực của email này
            $this->deleteUnverifiedOTP($email);
            
            // Tạo mã OTP ngẫu nhiên
            $otpCode = $this->generateOTP();
            
            // Tính thời gian hết hạn
            $expiresAt = date('Y-m-d H:i:s', strtotime('+' . EmailConfig::OTP_EXPIRE_MINUTES . ' minutes'));
            
            // Lưu vào database
            $sql = "INSERT INTO {$this->table} (email, otp_code, password_hash, expires_at) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('ssss', $email, $otpCode, $passwordHash, $expiresAt);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'otp' => $otpCode,
                    'expires_minutes' => EmailConfig::OTP_EXPIRE_MINUTES,
                    'message' => 'Mã OTP đã được tạo thành công'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Không thể tạo mã OTP'
            ];
            
        } catch (Exception $e) {
            error_log("Create OTP Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Xác thực mã OTP
     * 
     * @param string $email Email hoặc SĐT
     * @param string $otpCode Mã OTP người dùng nhập
     * @return array ['success' => bool, 'message' => string, 'password_hash' => string]
     */
    public function verifyOTP($email, $otpCode) {
        try {
            // Lấy OTP mới nhất chưa xác thực
            $sql = "SELECT * FROM {$this->table} 
                    WHERE email = ? AND is_verified = 0 
                    ORDER BY created_at DESC LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return [
                    'success' => false,
                    'message' => 'Không tìm thấy mã OTP. Vui lòng đăng ký lại!'
                ];
            }
            
            $record = $result->fetch_assoc();
            
            // Kiểm tra hết hạn
            if (strtotime($record['expires_at']) < time()) {
                return [
                    'success' => false,
                    'message' => 'Mã OTP đã hết hạn. Vui lòng đăng ký lại!'
                ];
            }
            
            // Kiểm tra số lần nhập sai
            if ($record['attempts'] >= EmailConfig::OTP_MAX_ATTEMPTS) {
                return [
                    'success' => false,
                    'message' => 'Bạn đã nhập sai quá nhiều lần. Vui lòng nhập lại email!'
                ];
            }
            
            // Kiểm tra mã OTP
            if ($record['otp_code'] !== $otpCode) {
                // Tăng số lần nhập sai
                $this->incrementAttempts($record['id']);
                
                $remainingAttempts = EmailConfig::OTP_MAX_ATTEMPTS - ($record['attempts'] + 1);
                return [
                    'success' => false,
                    'message' => "Mã OTP không chính xác! Còn lại {$remainingAttempts} lần thử."
                ];
            }
            
            // OTP chính xác - đánh dấu đã xác thực
            $this->markAsVerified($record['id']);
            
            return [
                'success' => true,
                'message' => 'Xác thực thành công!',
                'password_hash' => $record['password_hash']
            ];
            
        } catch (Exception $e) {
            error_log("Verify OTP Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Tạo mã OTP ngẫu nhiên
     */
    private function generateOTP() {
        $length = EmailConfig::OTP_LENGTH;
        $otp = '';
        for ($i = 0; $i < $length; $i++) {
            $otp .= rand(0, 9);
        }
        return $otp;
    }
    
    /**
     * Xóa các OTP chưa xác thực của email
     */
    private function deleteUnverifiedOTP($email) {
        $sql = "DELETE FROM {$this->table} WHERE email = ? AND is_verified = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
    }
    
    /**
     * Tăng số lần nhập sai
     */
    private function incrementAttempts($id) {
        $sql = "UPDATE {$this->table} SET attempts = attempts + 1 WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }
    
    /**
     * Đánh dấu OTP đã được xác thực
     */
    private function markAsVerified($id) {
        $sql = "UPDATE {$this->table} 
                SET is_verified = 1, verified_at = NOW() 
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }
    
    /**
     * Gửi lại mã OTP (resend)
     * 
     * @param string $email Email hoặc SĐT
     * @return array ['success' => bool, 'otp' => string, 'message' => string]
     */
    public function resendOTP($email) {
        try {
            // Kiểm tra có OTP chưa xác thực không
            $sql = "SELECT * FROM {$this->table} 
                    WHERE email = ? AND is_verified = 0 
                    ORDER BY created_at DESC LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return [
                    'success' => false,
                    'message' => 'Không tìm thấy yêu cầu đăng ký. Vui lòng đăng ký lại!'
                ];
            }
            
            $record = $result->fetch_assoc();
            
            // Tạo OTP mới với cùng password hash
            return $this->createOTP($email, $record['password_hash']);
            
        } catch (Exception $e) {
            error_log("Resend OTP Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Xóa các OTP đã hết hạn (chạy định kỳ bằng cron job)
     */
    public function cleanupExpiredOTP() {
        $sql = "DELETE FROM {$this->table} 
                WHERE expires_at < NOW() AND is_verified = 0";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute();
    }
}
