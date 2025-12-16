<?php
require_once 'mDbconnect.php';

class Support {
    private $conn;
    
    public function __construct() {
        $db = clsConnect::getInstance();
        $this->conn = $db->connect();
    }
    
    /**
     * Tạo yêu cầu hỗ trợ mới
     */
    public function createSupportRequest($userId, $title, $content, $type = 'general') {
        $stmt = $this->conn->prepare("
            INSERT INTO hotro (maNguoiDung, tieuDe, noiDung, loai, trangThai)
            VALUES (?, ?, ?, ?, 'pending')
        ");
        
        $stmt->bind_param("isss", $userId, $title, $content, $type);
        return $stmt->execute();
    }
    
    /**
     * Lấy danh sách yêu cầu hỗ trợ của người dùng
     */
    public function getUserSupportRequests($userId, $limit = 10) {
        $stmt = $this->conn->prepare("
            SELECT 
                h.*,
                n.tenDangNhap as tenAdmin
            FROM hotro h
            LEFT JOIN nguoidung n ON h.maAdminPhuTrach = n.maNguoiDung
            WHERE h.maNguoiDung = ?
            ORDER BY h.thoiDiemTao DESC
            LIMIT ?
        ");
        
        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $requests = [];
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
        
        return $requests;
    }
    
    /**
     * Lấy chi tiết yêu cầu hỗ trợ
     */
    public function getSupportRequestDetail($requestId, $userId) {
        $stmt = $this->conn->prepare("
            SELECT 
                h.*,
                n.tenDangNhap as tenAdmin
            FROM hotro h
            LEFT JOIN nguoidung n ON h.maAdminPhuTrach = n.maNguoiDung
            WHERE h.maHoTro = ? AND h.maNguoiDung = ?
        ");
        
        $stmt->bind_param("ii", $requestId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    /**
     * Đếm số yêu cầu hỗ trợ đang chờ xử lý
     */
    public function countPendingRequests($userId) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as count
            FROM hotro
            WHERE maNguoiDung = ? AND trangThai IN ('pending', 'in_progress')
        ");
        
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'] ?? 0;
    }
    
    /**
     * Đếm số phản hồi mới từ admin (đã được trả lời nhưng chưa xem)
     */
    public function countNewReplies($userId) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as count
            FROM hotro
            WHERE maNguoiDung = ? 
            AND phanHoi IS NOT NULL 
            AND phanHoi != ''
            AND trangThai = 'resolved'
            AND thoiDiemCapNhat > DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'] ?? 0;
    }
    
    /**
     * Lấy phản hồi mới nhất từ admin
     */
    public function getLatestReplies($userId, $limit = 3) {
        $stmt = $this->conn->prepare("
            SELECT 
                h.*,
                n.tenDangNhap as tenAdmin
            FROM hotro h
            LEFT JOIN nguoidung n ON h.maAdminPhuTrach = n.maNguoiDung
            WHERE h.maNguoiDung = ?
            AND h.phanHoi IS NOT NULL
            AND h.phanHoi != ''
            ORDER BY h.thoiDiemCapNhat DESC
            LIMIT ?
        ");
        
        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $replies = [];
        while ($row = $result->fetch_assoc()) {
            $replies[] = $row;
        }
        
        return $replies;
    }
}
?>
