<?php
require_once 'mDbconnect.php';

class Message {
    private $conn;
    
    public function __construct() {
        $db = clsConnect::getInstance();
        $this->conn = $db->connect();
    }
    
    /**
     * Gửi tin nhắn với trạng thái 'sending' ban đầu
     */
    public function sendMessage($matchId, $senderId, $content) {
        $stmt = $this->conn->prepare("
            INSERT INTO tinnhan (maGhepDoi, maNguoiGui, noiDung, trangThai) 
            VALUES (?, ?, ?, 'sending')
        ");
        $stmt->bind_param("iis", $matchId, $senderId, $content);
        
        if ($stmt->execute()) {
            $messageId = $this->conn->insert_id;
            // Cập nhật ngay sang 'sent' nếu gửi thành công
            $this->updateMessageStatus($messageId, 'sent');
            
            // Tắt typing status của người gửi ngay khi gửi tin
            $this->setTypingStatus($matchId, $senderId, 0);
            
            return $messageId;
        }
        return false;
    }
    
    /**
     * Cập nhật trạng thái của một tin nhắn cụ thể
     */
    public function updateMessageStatus($messageId, $status) {
        $validStatuses = ['sending', 'sent', 'delivered', 'seen', 'failed', 'recalled'];
        if (!in_array($status, $validStatuses)) {
            return false;
        }
        
        $timeColumn = '';
        switch($status) {
            case 'delivered':
                $timeColumn = ', thoiDiemNhan = NOW()';
                break;
            case 'seen':
                $timeColumn = ', thoiDiemXem = NOW()';
                break;
            case 'recalled':
                $timeColumn = ', thuHoiLuc = NOW()';
                break;
        }
        
        $stmt = $this->conn->prepare("
            UPDATE tinnhan 
            SET trangThai = ? $timeColumn
            WHERE maTinNhan = ?
        ");
        $stmt->bind_param("si", $status, $messageId);
        return $stmt->execute();
    }
    
    /**
     * Đánh dấu tin nhắn gửi thất bại
     */
    public function markAsFailed($messageId, $errorMessage = '') {
        $stmt = $this->conn->prepare("
            UPDATE tinnhan 
            SET trangThai = 'failed', loiGanNhat = ?
            WHERE maTinNhan = ?
        ");
        $stmt->bind_param("si", $errorMessage, $messageId);
        return $stmt->execute();
    }
    
    /**
     * Gửi lại tin nhắn thất bại
     */
    public function retryFailedMessage($messageId) {
        $stmt = $this->conn->prepare("
            UPDATE tinnhan 
            SET trangThai = 'sending', loiGanNhat = NULL
            WHERE maTinNhan = ? AND trangThai = 'failed'
        ");
        $stmt->bind_param("i", $messageId);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            // Cập nhật sang 'sent'
            $this->updateMessageStatus($messageId, 'sent');
            return true;
        }
        return false;
    }
    
    /**
     * Thu hồi tin nhắn
     */
    public function recallMessage($messageId, $senderId) {
        // Kiểm tra tin nhắn có thuộc về người gửi không
        $stmt = $this->conn->prepare("
            SELECT maNguoiGui FROM tinnhan WHERE maTinNhan = ?
        ");
        $stmt->bind_param("i", $messageId);
        $stmt->execute();
        $result = $stmt->get_result();
        $message = $result->fetch_assoc();
        
        if (!$message || $message['maNguoiGui'] != $senderId) {
            return false;
        }
        
        // Cập nhật trạng thái sang 'recalled'
        return $this->updateMessageStatus($messageId, 'recalled');
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
        // Đếm tin nhắn chưa xem (status != 'seen')
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as unread_count
            FROM tinnhan
            WHERE maGhepDoi = ? 
            AND maNguoiGui != ? 
            AND trangThai IN ('sending', 'sent', 'delivered')
        ");
        $stmt->bind_param("ii", $matchId, $userId);
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
            AND trangThai IN ('sent', 'delivered')
        ");
        $stmt->bind_param("ii", $matchId, $userId);
        return $stmt->execute();
    }
    
    /**
     * Cập nhật trạng thái typing (đang soạn)
     */
    public function setTypingStatus($matchId, $userId, $isTyping) {
        $stmt = $this->conn->prepare("
            INSERT INTO typing_status (maGhepDoi, maNguoiDung, isTyping) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE isTyping = ?, lastUpdate = NOW()
        ");
        $stmt->bind_param("iiii", $matchId, $userId, $isTyping, $isTyping);
        return $stmt->execute();
    }
    
    /**
     * Lấy trạng thái typing của người dùng khác trong cuộc trò chuyện
     */
    public function getTypingStatus($matchId, $excludeUserId) {
        $stmt = $this->conn->prepare("
            SELECT ts.maNguoiDung, ts.isTyping, h.ten
            FROM typing_status ts
            JOIN hoso h ON ts.maNguoiDung = h.maNguoiDung
            WHERE ts.maGhepDoi = ? 
            AND ts.maNguoiDung != ?
            AND ts.isTyping = 1
            AND ts.lastUpdate >= DATE_SUB(NOW(), INTERVAL 3 SECOND)
        ");
        $stmt->bind_param("ii", $matchId, $excludeUserId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
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
    
    /**
     * Đếm tổng số tin nhắn chưa đọc từ tất cả các cuộc trò chuyện của user
     */
    public function getTotalUnreadCount($userId) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(DISTINCT t.maTinNhan) as total_unread
            FROM tinnhan t
            INNER JOIN ghepdoi g ON t.maGhepDoi = g.maGhepDoi
            WHERE (g.maNguoiA = ? OR g.maNguoiB = ?)
            AND t.maNguoiGui != ?
            AND t.trangThai != 'seen'
        ");
        $stmt->bind_param("iii", $userId, $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total_unread'] ?? 0;
    }
}
?>
