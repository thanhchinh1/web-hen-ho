<?php
require_once 'mDbconnect.php';

class MatchModel {
    private $conn;
    
    public function __construct() {
        $db = clsConnect::getInstance();
        $this->conn = $db->connect();
    }
    
    /**
     * Kiểm tra xem 2 người đã ghép đôi chưa
     */
    public function isMatched($userId1, $userId2) {
        $userA = min($userId1, $userId2);
        $userB = max($userId1, $userId2);
        
        $stmt = $this->conn->prepare("
            SELECT maGhepDoi FROM ghepdoi 
            WHERE maNguoiA = ? AND maNguoiB = ?
            AND trangThaiGhepDoi = 'matched'
        ");
        $stmt->bind_param("ii", $userA, $userB);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
    
    /**
     * Kiểm tra có thể tạo match không (cả 2 đều đã like nhau)
     */
    public function canCreateMatch($userId1, $userId2) {
        require_once 'mLike.php';
        $likeModel = new Like();
        
        // Kiểm tra người 1 đã like người 2 và ngược lại
        $user1LikesUser2 = $likeModel->hasLiked($userId1, $userId2);
        $user2LikesUser1 = $likeModel->hasLiked($userId2, $userId1);
        
        return $user1LikesUser2 && $user2LikesUser1;
    }
    
    /**
     * Tạo ghép đôi mới với transaction để tránh race condition
     */
    public function createMatch($userId1, $userId2) {
        // Đảm bảo userId1 luôn nhỏ hơn userId2 để tránh duplicate
        $userA = min($userId1, $userId2);
        $userB = max($userId1, $userId2);
        
        // Bắt đầu transaction
        $this->conn->begin_transaction();
        
        try {
            // Kiểm tra đã match chưa (với lock để tránh race condition)
            $checkStmt = $this->conn->prepare("
                SELECT maGhepDoi FROM ghepdoi 
                WHERE maNguoiA = ? AND maNguoiB = ?
                AND trangThaiGhepDoi = 'matched'
                FOR UPDATE
            ");
            $checkStmt->bind_param("ii", $userA, $userB);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            
            if ($result->num_rows > 0) {
                // Đã tồn tại match
                $this->conn->rollback();
                return false;
            }
            
            // Kiểm tra điều kiện match (cả 2 đều đã like nhau)
            if (!$this->canCreateMatch($userId1, $userId2)) {
                $this->conn->rollback();
                return false;
            }
            
            // Insert record ghép đôi (luôn đảm bảo maNguoiA < maNguoiB)
            $stmt = $this->conn->prepare("
                INSERT INTO ghepdoi (maNguoiA, maNguoiB, trangThaiGhepDoi) 
                VALUES (?, ?, 'matched')
            ");
            $stmt->bind_param("ii", $userA, $userB);
            
            if ($stmt->execute()) {
                $matchId = $this->conn->insert_id;
                $this->conn->commit();
                return $matchId;
            } else {
                $this->conn->rollback();
                return false;
            }
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Error creating match: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lấy ID ghép đôi của 2 người
     */
    public function getMatchId($userId1, $userId2) {
        $userA = min($userId1, $userId2);
        $userB = max($userId1, $userId2);
        
        $stmt = $this->conn->prepare("
            SELECT maGhepDoi FROM ghepdoi 
            WHERE maNguoiA = ? AND maNguoiB = ?
            AND trangThaiGhepDoi = 'matched'
        ");
        $stmt->bind_param("ii", $userA, $userB);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $row['maGhepDoi'];
        }
        return null;
    }
    
    /**
     * Lấy danh sách tất cả người đã ghép đôi với user
     */
    public function getMyMatches($userId) {
        $stmt = $this->conn->prepare("
            SELECT 
                g.maGhepDoi,
                g.thoiDiemGhepDoi,
                CASE 
                    WHEN g.maNguoiA = ? THEN g.maNguoiB 
                    ELSE g.maNguoiA 
                END as maNguoiDung,
                h.ten,
                h.avt,
                h.ngaySinh,
                h.noiSong
            FROM ghepdoi g
            JOIN hoso h ON (
                CASE 
                    WHEN g.maNguoiA = ? THEN g.maNguoiB 
                    ELSE g.maNguoiA 
                END = h.maNguoiDung
            )
            WHERE (g.maNguoiA = ? OR g.maNguoiB = ?)
            AND g.trangThaiGhepDoi = 'matched'
            ORDER BY g.thoiDiemGhepDoi DESC
        ");
        $stmt->bind_param("iiii", $userId, $userId, $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $matches = [];
        while ($row = $result->fetch_assoc()) {
            $matches[] = $row;
        }
        return $matches;
    }
    
    /**
     * Hủy ghép đôi HOÀN TOÀN
     * - Xóa tất cả TIN NHẮN giữa 2 người trong cuộc trò chuyện
     * - Xóa tất cả lượt thích giữa 2 người (cả 2 chiều)
     * - Cập nhật trạng thái ghép đôi thành 'unmatched'
     * - Hồ sơ sẽ tự động xuất hiện lại trên trang chủ cho cả 2 người
     * 
     * CẢNH BÁO: Chỉ dùng khi muốn xóa hoàn toàn mối quan hệ (ví dụ: Block user)
     * Nếu chỉ muốn bỏ thích, dùng updateMatchStatus() thay vì unmatch()
     */
    public function unmatch($userId1, $userId2) {
        $userA = min($userId1, $userId2);
        $userB = max($userId1, $userId2);
        
        // Bắt đầu transaction
        $this->conn->begin_transaction();
        
        try {
            // Bước 1: Lấy ID ghép đôi để xóa tin nhắn
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
            
            // Bước 2: XÓA TẤT CẢ TIN NHẮN của cuộc trò chuyện này
            if ($matchId) {
                $stmt = $this->conn->prepare("
                    DELETE FROM tinnhan 
                    WHERE maGhepDoi = ?
                ");
                $stmt->bind_param("i", $matchId);
                $stmt->execute();
                $deletedMessages = $stmt->affected_rows;
                $stmt->close();
                
                error_log("Unmatch: Deleted $deletedMessages messages for match ID $matchId");
            }
            
            // Bước 3: Xóa tất cả lượt thích giữa 2 người (cả 2 chiều)
            // Điều này làm cho hồ sơ biến mất khỏi danh sách "Người thích bạn"
            $stmt = $this->conn->prepare("
                DELETE FROM thich 
                WHERE (maNguoiThich = ? AND maNguoiDuocThich = ?)
                OR (maNguoiThich = ? AND maNguoiDuocThich = ?)
            ");
            $stmt->bind_param("iiii", $userId1, $userId2, $userId2, $userId1);
            $stmt->execute();
            $stmt->close();
            
            // Bước 4: XÓA RECORD ghép đôi thay vì UPDATE
            // (Do có UNIQUE constraint trên (maNguoiA, maNguoiB, trangThaiGhepDoi))
            $stmt = $this->conn->prepare("
                DELETE FROM ghepdoi 
                WHERE maNguoiA = ? AND maNguoiB = ?
                AND trangThaiGhepDoi = 'matched'
            ");
            $stmt->bind_param("ii", $userA, $userB);
            $stmt->execute();
            $deletedRows = $stmt->affected_rows;
            $stmt->close();
            
            error_log("Unmatch: Deleted $deletedRows match record(s)");
            
            // Commit transaction
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            // Rollback nếu có lỗi
            $this->conn->rollback();
            error_log("Unmatch error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cập nhật trạng thái ghép đôi (không xóa lượt thích)
     * Dùng khi 1 người bỏ thích nhưng người kia vẫn giữ lượt thích
     */
    public function updateMatchStatus($userId1, $userId2, $status) {
        $userA = min($userId1, $userId2);
        $userB = max($userId1, $userId2);
        
        $stmt = $this->conn->prepare("
            UPDATE ghepdoi 
            SET trangThaiGhepDoi = ?
            WHERE maNguoiA = ? AND maNguoiB = ?
            AND trangThaiGhepDoi = 'matched'
        ");
        $stmt->bind_param("sii", $status, $userA, $userB);
        return $stmt->execute();
    }
    
    /**
     * Kiểm tra user có phải thành viên của match không (hiệu quả)
     * Dùng thay cho việc load toàn bộ matches rồi loop
     */
    public function isMatchMember($matchId, $userId) {
        $stmt = $this->conn->prepare("
            SELECT maGhepDoi FROM ghepdoi
            WHERE maGhepDoi = ?
            AND (maNguoiA = ? OR maNguoiB = ?)
            AND trangThaiGhepDoi = 'matched'
        ");
        $stmt->bind_param("iii", $matchId, $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
}
?>
