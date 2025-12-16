<?php
require_once 'mDbconnect.php';

class Notification {
    private $conn;
    
    public function __construct() {
        $db = clsConnect::getInstance();
        $this->conn = $db->connect();
    }
    
    /**
     * Đếm số ghép đôi MỚI (chưa có tin nhắn nào)
     * Dùng để hiển thị badge thông báo trên icon "Tin nhắn"
     */
    public function getNewMatchesCount($userId) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(DISTINCT g.maGhepDoi) as newMatchCount
            FROM GhepDoi g
            LEFT JOIN TinNhan t ON g.maGhepDoi = t.maGhepDoi
            WHERE (g.maNguoiA = ? OR g.maNguoiB = ?)
            AND g.trangThaiGhepDoi = 'matched'
            AND t.maTinNhan IS NULL
        ");
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['newMatchCount'] ?? 0;
    }
    
    /**
     * Đếm tổng số tin nhắn chưa đọc (tùy chọn cho tương lai)
     */
    public function getUnreadMessagesCount($userId) {
        // TODO: Implement khi có trường 'daDoc' trong bảng TinNhan
        return 0;
    }
    
    /**
     * Lấy danh sách ghép đôi mới (chưa nhắn tin)
     */
    public function getNewMatches($userId) {
        $stmt = $this->conn->prepare("
            SELECT 
                g.maGhepDoi,
                g.thoiDiemGhepDoi,
                CASE 
                    WHEN g.maNguoiA = ? THEN g.maNguoiB 
                    ELSE g.maNguoiA 
                END as maNguoiDung,
                h.ten,
                h.avt
            FROM GhepDoi g
            LEFT JOIN TinNhan t ON g.maGhepDoi = t.maGhepDoi
            JOIN HoSo h ON (
                CASE 
                    WHEN g.maNguoiA = ? THEN g.maNguoiB 
                    ELSE g.maNguoiA 
                END = h.maNguoiDung
            )
            WHERE (g.maNguoiA = ? OR g.maNguoiB = ?)
            AND g.trangThaiGhepDoi = 'matched'
            AND t.maTinNhan IS NULL
            ORDER BY g.thoiDiemGhepDoi DESC
        ");
        $stmt->bind_param("iiii", $userId, $userId, $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $newMatches = [];
        while ($row = $result->fetch_assoc()) {
            $newMatches[] = $row;
        }
        return $newMatches;
    }
    
    /**
     * Lấy thông báo hệ thống từ admin
     */
    public function getSystemNotifications($limit = 3) {
        $stmt = $this->conn->prepare("
            SELECT 
                maThongBao,
                tieuDe,
                noiDung,
                loai,
                doUuTien,
                thoiDiemGui,
                thoiDiemTao
            FROM thongbaoheothong
            WHERE trangThai = 'sent'
            AND (thoiDiemGui IS NULL OR thoiDiemGui <= NOW())
            ORDER BY doUuTien DESC, thoiDiemGui DESC
            LIMIT ?
        ");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
        return $notifications;
    }
}
?>
