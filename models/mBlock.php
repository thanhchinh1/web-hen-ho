<?php
require_once 'mDbconnect.php';

class Block {
    private $conn;
    
    public function __construct() {
        $db = clsConnect::getInstance();
        $this->conn = $db->connect();
    }
    
    /**
     * Block một user
     */
    public function blockUser($blockerId, $blockedId) {
        // Kiểm tra đã block chưa
        if ($this->isBlocked($blockerId, $blockedId)) {
            return false;
        }
        
        $stmt = $this->conn->prepare("
            INSERT INTO channguoidung (maNguoiChan, maNguoiBiChan)
            VALUES (?, ?)
        ");
        $stmt->bind_param("ii", $blockerId, $blockedId);
        return $stmt->execute();
    }
    
    /**
     * Block user với transaction (xóa match và like)
     */
    public function blockUserWithCleanup($blockerId, $blockedId) {
        $this->conn->begin_transaction();
        
        try {
            // 1. Block user
            $stmt = $this->conn->prepare("
                INSERT INTO channguoidung (maNguoiChan, maNguoiBiChan)
                VALUES (?, ?)
            ");
            $stmt->bind_param("ii", $blockerId, $blockedId);
            $stmt->execute();
            $stmt->close();
            
            // 2. Unmatch nếu đang ghép đôi và XÓA TIN NHẮN
            $userA = min($blockerId, $blockedId);
            $userB = max($blockerId, $blockedId);
            
            // Lấy ID ghép đôi để xóa tin nhắn
            $stmt = $this->conn->prepare("
                SELECT maGhepDoi FROM ghepdoi 
                WHERE maNguoiA = ? AND maNguoiB = ?
                AND trangThaiGhepDoi = 'matched'
            ");
            $stmt->bind_param("ii", $userA, $userB);
            $stmt->execute();
            $result = $stmt->get_result();
            $match = $result->fetch_assoc();
            $matchId = $match ? $match['maGhepDoi'] : null;
            $stmt->close();
            
            // Xóa tất cả tin nhắn nếu có ghép đôi
            if ($matchId) {
                $stmt = $this->conn->prepare("
                    DELETE FROM tinnhan 
                    WHERE maGhepDoi = ?
                ");
                $stmt->bind_param("i", $matchId);
                $stmt->execute();
                $stmt->close();
            }
            
            // Cập nhật trạng thái ghép đôi thành 'blocked' hoặc xóa
            $stmt = $this->conn->prepare("
                DELETE FROM ghepdoi 
                WHERE maNguoiA = ? AND maNguoiB = ?
                AND trangThaiGhepDoi = 'matched'
            ");
            $stmt->bind_param("ii", $userA, $userB);
            $stmt->execute();
            $stmt->close();
            
            // 3. Xóa tất cả lượt thích (2 chiều)
            $stmt = $this->conn->prepare("
                DELETE FROM thich 
                WHERE (maNguoiThich = ? AND maNguoiDuocThich = ?)
                OR (maNguoiThich = ? AND maNguoiDuocThich = ?)
            ");
            $stmt->bind_param("iiii", $blockerId, $blockedId, $blockedId, $blockerId);
            $stmt->execute();
            $stmt->close();
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Block error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Unblock một user
     */
    public function unblockUser($blockerId, $blockedId) {
        $stmt = $this->conn->prepare("
            DELETE FROM channguoidung
            WHERE maNguoiChan = ? AND maNguoiBiChan = ?
        ");
        $stmt->bind_param("ii", $blockerId, $blockedId);
        return $stmt->execute();
    }
    
    /**
     * Kiểm tra đã block chưa
     */
    public function isBlocked($blockerId, $blockedId) {
        $stmt = $this->conn->prepare("
            SELECT id FROM channguoidung
            WHERE maNguoiChan = ? AND maNguoiBiChan = ?
        ");
        $stmt->bind_param("ii", $blockerId, $blockedId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
    
    /**
     * Kiểm tra 2 chiều: A block B hoặc B block A
     */
    public function isBlockedEitherWay($userId1, $userId2) {
        $stmt = $this->conn->prepare("
            SELECT id FROM channguoidung
            WHERE (maNguoiChan = ? AND maNguoiBiChan = ?)
            OR (maNguoiChan = ? AND maNguoiBiChan = ?)
        ");
        $stmt->bind_param("iiii", $userId1, $userId2, $userId2, $userId1);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
    
    /**
     * Lấy danh sách người đã block
     */
    public function getBlockedUsers($userId) {
        $stmt = $this->conn->prepare("
            SELECT h.*, c.thoiDiemChan
            FROM channguoidung c
            JOIN hoso h ON c.maNguoiBiChan = h.maNguoiDung
            WHERE c.maNguoiChan = ?
            ORDER BY c.thoiDiemChan DESC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $blocked = [];
        while ($row = $result->fetch_assoc()) {
            $blocked[] = $row;
        }
        return $blocked;
    }
    
    /**
     * Lấy danh sách ID người đã block (để filter)
     */
    public function getBlockedUserIds($userId) {
        $stmt = $this->conn->prepare("
            SELECT maNguoiBiChan FROM channguoidung
            WHERE maNguoiChan = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $ids = [];
        while ($row = $result->fetch_assoc()) {
            $ids[] = $row['maNguoiBiChan'];
        }
        return $ids;
    }
    
    /**
     * Lấy danh sách ID người đã chặn mình (để filter)
     */
    public function getUserIdsWhoBlockedMe($userId) {
        $stmt = $this->conn->prepare("
            SELECT maNguoiChan FROM channguoidung
            WHERE maNguoiBiChan = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $ids = [];
        while ($row = $result->fetch_assoc()) {
            $ids[] = $row['maNguoiChan'];
        }
        return $ids;
    }
}
?>
