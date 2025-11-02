<?php
require_once 'mDbconnect.php';

class Like {
    private $conn;
    
    public function __construct() {
        $db = clsConnect::getInstance();
        $this->conn = $db->connect();
    }
    
    /**
     * Thích một người dùng
     */
    public function likeUser($fromUserId, $toUserId) {
        // Kiểm tra xem đã thích chưa
        if ($this->hasLiked($fromUserId, $toUserId)) {
            return false; // Đã thích rồi
        }
        
        $stmt = $this->conn->prepare("INSERT INTO Thich (maNguoiThich, maNguoiDuocThich) VALUES (?, ?)");
        $stmt->bind_param("ii", $fromUserId, $toUserId);
        return $stmt->execute();
    }
    
    /**
     * Bỏ thích một người dùng
     */
    public function unlikeUser($fromUserId, $toUserId) {
        $stmt = $this->conn->prepare("DELETE FROM Thich WHERE maNguoiThich = ? AND maNguoiDuocThich = ?");
        $stmt->bind_param("ii", $fromUserId, $toUserId);
        return $stmt->execute();
    }
    
    /**
     * Kiểm tra xem đã thích chưa
     */
    public function hasLiked($fromUserId, $toUserId) {
        $stmt = $this->conn->prepare("SELECT maThich FROM Thich WHERE maNguoiThich = ? AND maNguoiDuocThich = ?");
        $stmt->bind_param("ii", $fromUserId, $toUserId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
    
    /**
     * Lấy danh sách người mà user đã thích
     */
    public function getPeopleLikedByUser($userId) {
        $stmt = $this->conn->prepare("
            SELECT h.*, n.maNguoiDung, t.thoiDiemThich
            FROM Thich t
            INNER JOIN HoSo h ON t.maNguoiDuocThich = h.maNguoiDung
            INNER JOIN NguoiDung n ON h.maNguoiDung = n.maNguoiDung
            WHERE t.maNguoiThich = ?
            ORDER BY t.thoiDiemThich DESC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $people = [];
        while ($row = $result->fetch_assoc()) {
            $people[] = $row;
        }
        
        return $people;
    }
    
    /**
     * Lấy danh sách người đã thích user
     */
    public function getPeopleWhoLikedUser($userId) {
        $stmt = $this->conn->prepare("
            SELECT h.*, n.maNguoiDung, t.thoiDiemThich
            FROM Thich t
            INNER JOIN HoSo h ON t.maNguoiThich = h.maNguoiDung
            INNER JOIN NguoiDung n ON h.maNguoiDung = n.maNguoiDung
            WHERE t.maNguoiDuocThich = ?
            ORDER BY t.thoiDiemThich DESC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $people = [];
        while ($row = $result->fetch_assoc()) {
            $people[] = $row;
        }
        
        return $people;
    }
    
    /**
     * Lấy danh sách ID người mà user đã thích (để lọc khỏi trang chủ)
     */
    public function getLikedUserIds($userId) {
        $stmt = $this->conn->prepare("SELECT maNguoiDuocThich FROM Thich WHERE maNguoiThich = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $ids = [];
        while ($row = $result->fetch_assoc()) {
            $ids[] = $row['maNguoiDuocThich'];
        }
        
        return $ids;
    }
    
    /**
     * Lấy danh sách ID người đã thích user (để lọc khỏi trang chủ)
     */
    public function getUserIdsWhoLikedMe($userId) {
        $stmt = $this->conn->prepare("SELECT maNguoiThich FROM Thich WHERE maNguoiDuocThich = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $ids = [];
        while ($row = $result->fetch_assoc()) {
            $ids[] = $row['maNguoiThich'];
        }
        
        return $ids;
    }
    
    /**
     * Đếm số người đã thích user
     */
    public function countPeopleWhoLikedUser($userId) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM Thich WHERE maNguoiDuocThich = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    
    /**
     * Đếm số người mà user đã thích
     */
    public function countPeopleLikedByUser($userId) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM Thich WHERE maNguoiThich = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'];
    }
}
?>
