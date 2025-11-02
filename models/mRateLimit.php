<?php
/**
 * Rate Limiting Class
 * Ngăn spam và abuse bằng cách giới hạn số request trong 1 khoảng thời gian
 */
class RateLimit {
    private $conn;
    
    public function __construct() {
        $db = clsConnect::getInstance();
        $this->conn = $db->connect();
    }
    
    /**
     * Kiểm tra rate limit cho một action
     * 
     * @param int $userId User ID
     * @param string $action Tên action (like, unlike, send_message, etc.)
     * @param int $maxAttempts Số lần tối đa
     * @param int $timeWindow Thời gian window (giây)
     * @return bool true nếu còn trong giới hạn, false nếu vượt quá
     */
    public function checkRateLimit($userId, $action, $maxAttempts = 10, $timeWindow = 60) {
        // Tính thời gian window
        $windowStart = date('Y-m-d H:i:s', time() - $timeWindow);
        
        // Đếm số lần thực hiện action trong window
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as attempt_count
            FROM RateLimitLog
            WHERE maNguoiDung = ? 
            AND action = ? 
            AND thoiDiem >= ?
        ");
        $stmt->bind_param("iss", $userId, $action, $windowStart);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $attemptCount = $row['attempt_count'] ?? 0;
        
        // Kiểm tra có vượt quá không
        if ($attemptCount >= $maxAttempts) {
            return false; // Vượt quá giới hạn
        }
        
        return true; // Còn trong giới hạn
    }
    
    /**
     * Log một action vào rate limit table
     */
    public function logAction($userId, $action, $ipAddress = null) {
        if ($ipAddress === null) {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }
        
        $stmt = $this->conn->prepare("
            INSERT INTO RateLimitLog (maNguoiDung, action, ipAddress)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iss", $userId, $action, $ipAddress);
        return $stmt->execute();
    }
    
    /**
     * Lấy số attempt còn lại
     */
    public function getRemainingAttempts($userId, $action, $maxAttempts = 10, $timeWindow = 60) {
        $windowStart = date('Y-m-d H:i:s', time() - $timeWindow);
        
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as attempt_count
            FROM RateLimitLog
            WHERE maNguoiDung = ? 
            AND action = ? 
            AND thoiDiem >= ?
        ");
        $stmt->bind_param("iss", $userId, $action, $windowStart);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $attemptCount = $row['attempt_count'] ?? 0;
        return max(0, $maxAttempts - $attemptCount);
    }
    
    /**
     * Cleanup old logs (chạy định kỳ)
     */
    public function cleanupOldLogs($daysOld = 7) {
        $cutoffDate = date('Y-m-d H:i:s', time() - ($daysOld * 24 * 60 * 60));
        
        $stmt = $this->conn->prepare("
            DELETE FROM RateLimitLog
            WHERE thoiDiem < ?
        ");
        $stmt->bind_param("s", $cutoffDate);
        return $stmt->execute();
    }
}
?>
