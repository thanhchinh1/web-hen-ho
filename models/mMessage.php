<?php
require_once 'mDbconnect.php';

class Message {
    private $conn;
    
    public function __construct() {
        $db = clsConnect::getInstance();
        $this->conn = $db->connect();
    }
    
    /**
     * Gửi tin nhắn
     */
    public function sendMessage($matchId, $senderId, $content) {
        $stmt = $this->conn->prepare("
            INSERT INTO tinnhan (maGhepDoi, maNguoiGui, noiDung, trangThai) 
            VALUES (?, ?, ?, 'sent')
        ");
        $stmt->bind_param("iis", $matchId, $senderId, $content);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }
    
    /**
     * Lấy danh sách tin nhắn của một cuộc trò chuyện với pagination
     */
    public function getMessages($matchId, $limit = 50, $offset = 0) {
        $stmt = $this->conn->prepare("
            SELECT 
                t.maTinNhan,
                t.maNguoiGui,
                t.noiDung,
                t.thoiDiemGui,
                t.trangThai,
                t.thoiDiemNhan,
                t.thoiDiemXem,
                h.ten as tenNguoiGui,
                h.avt as avtNguoiGui
            FROM tinnhan t
            JOIN hoso h ON t.maNguoiGui = h.maNguoiDung
            WHERE t.maGhepDoi = ?
            ORDER BY t.thoiDiemGui DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("iii", $matchId, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        
        // Reverse để hiển thị từ cũ đến mới
        return array_reverse($messages);
    }
    
    /**
     * Đếm tổng số tin nhắn trong cuộc trò chuyện
     */
    public function countMessages($matchId) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as total
            FROM tinnhan
            WHERE maGhepDoi = ?
        ");
        $stmt->bind_param("i", $matchId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }
    
    /**
     * Lấy tin nhắn mới sau một thời điểm
     */
    public function getNewMessages($matchId, $lastMessageId) {
        $stmt = $this->conn->prepare("
            SELECT 
                t.maTinNhan,
                t.maNguoiGui,
                t.noiDung,
                t.thoiDiemGui,
                t.trangThai,
                t.thoiDiemNhan,
                t.thoiDiemXem,
                h.ten as tenNguoiGui,
                h.avt as avtNguoiGui
            FROM tinnhan t
            JOIN hoso h ON t.maNguoiGui = h.maNguoiDung
            WHERE t.maGhepDoi = ? AND t.maTinNhan > ?
            ORDER BY t.thoiDiemGui ASC
        ");
        $stmt->bind_param("ii", $matchId, $lastMessageId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        return $messages;
    }
    
    /**
     * Đếm số tin nhắn chưa đọc
     */
    public function getUnreadCount($matchId, $userId, $lastSeenMessageId = 0) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as unread_count
            FROM tinnhan
            WHERE maGhepDoi = ? 
            AND maNguoiGui != ? 
            AND maTinNhan > ?
        ");
        $stmt->bind_param("iii", $matchId, $userId, $lastSeenMessageId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['unread_count'] ?? 0;
    }
    
    /**
     * Lấy tin nhắn cuối cùng của một cuộc trò chuyện
     */
    public function getLastMessage($matchId) {
        $stmt = $this->conn->prepare("
            SELECT 
                t.maTinNhan,
                t.maNguoiGui,
                t.noiDung,
                t.thoiDiemGui,
                h.ten as tenNguoiGui
            FROM tinnhan t
            JOIN hoso h ON t.maNguoiGui = h.maNguoiDung
            WHERE t.maGhepDoi = ?
            ORDER BY t.thoiDiemGui DESC
            LIMIT 1
        ");
        $stmt->bind_param("i", $matchId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Cập nhật trạng thái tin nhắn thành 'delivered' (đã nhận)
     */
    public function markAsDelivered($matchId, $userId) {
        $stmt = $this->conn->prepare("
            UPDATE tinnhan 
            SET trangThai = 'delivered', thoiDiemNhan = NOW() 
            WHERE maGhepDoi = ? 
            AND maNguoiGui != ? 
            AND trangThai = 'sent'
        ");
        $stmt->bind_param("ii", $matchId, $userId);
        return $stmt->execute();
    }
    
    /**
     * Cập nhật trạng thái tin nhắn thành 'seen' (đã xem)
     */
    public function markAsSeen($matchId, $userId) {
        $stmt = $this->conn->prepare("
            UPDATE tinnhan 
            SET trangThai = 'seen', thoiDiemXem = NOW() 
            WHERE maGhepDoi = ? 
            AND maNguoiGui != ? 
            AND (trangThai = 'sent' OR trangThai = 'delivered')
        ");
        $stmt->bind_param("ii", $matchId, $userId);
        return $stmt->execute();
    }
    
    /**
     * Lấy trạng thái tin nhắn cuối cùng của người gửi
     */
    public function getLastMessageStatus($matchId, $senderId) {
        $stmt = $this->conn->prepare("
            SELECT trangThai, thoiDiemGui, thoiDiemNhan, thoiDiemXem
            FROM tinnhan
            WHERE maGhepDoi = ? AND maNguoiGui = ?
            ORDER BY thoiDiemGui DESC
            LIMIT 1
        ");
        $stmt->bind_param("ii", $matchId, $senderId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Lấy trạng thái của nhiều tin nhắn (cho realtime update)
     */
    public function getMessagesStatus($matchId, $senderId, $messageIds) {
        if (empty($messageIds)) {
            return [];
        }
        
        $placeholders = implode(',', array_fill(0, count($messageIds), '?'));
        $query = "
            SELECT maTinNhan, trangThai
            FROM tinnhan
            WHERE maGhepDoi = ? 
            AND maNguoiGui = ? 
            AND maTinNhan IN ($placeholders)
        ";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $types = 'ii' . str_repeat('i', count($messageIds));
        $params = array_merge([$matchId, $senderId], $messageIds);
        $stmt->bind_param($types, ...$params);
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $statuses = [];
        while ($row = $result->fetch_assoc()) {
            $statuses[$row['maTinNhan']] = $row['trangThai'];
        }
        
        return $statuses;
    }
}
?>
