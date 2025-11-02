<?php
require_once 'mDbconnect.php';
require_once 'mMatching.php';

class QuickMatch {
    private $conn;
    private $matching;
    
    public function __construct() {
        $db = clsConnect::getInstance();
        $this->conn = $db->connect();
        $this->matching = new Matching();
    }
    
    /**
     * Bắt đầu tìm kiếm ghép đôi
     */
    public function startSearching($userId) {
        // Hủy các tìm kiếm cũ của user này
        $this->cancelSearching($userId);
        
        // Tạo yêu cầu tìm kiếm mới
        $stmt = $this->conn->prepare("
            INSERT INTO TimKiemGhepDoi (maNguoiDung, trangThai, thoiDiemBatDau) 
            VALUES (?, 'searching', NOW())
        ");
        $stmt->bind_param("i", $userId);
        $result = $stmt->execute();
        
        if ($result) {
            // Thử tìm match ngay lập tức
            return $this->tryFindMatch($userId);
        }
        
        return false;
    }
    
    /**
     * Hủy tìm kiếm
     */
    public function cancelSearching($userId) {
        $stmt = $this->conn->prepare("
            UPDATE TimKiemGhepDoi 
            SET trangThai = 'cancelled', thoiDiemKetThuc = NOW() 
            WHERE maNguoiDung = ? AND trangThai = 'searching'
        ");
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }
    
    /**
     * Kiểm tra trạng thái tìm kiếm
     */
    public function getSearchStatus($userId) {
        $stmt = $this->conn->prepare("
            SELECT maTimKiem, trangThai, thoiDiemBatDau 
            FROM TimKiemGhepDoi 
            WHERE maNguoiDung = ? AND trangThai = 'searching'
            ORDER BY thoiDiemBatDau DESC 
            LIMIT 1
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Thử tìm người phù hợp trong hàng đợi
     */
    private function tryFindMatch($userId) {
        // Lấy danh sách người đang tìm kiếm (không bao gồm chính mình)
        $stmt = $this->conn->prepare("
            SELECT DISTINCT t.maNguoiDung 
            FROM TimKiemGhepDoi t
            INNER JOIN HoSo h ON t.maNguoiDung = h.maNguoiDung
            WHERE t.trangThai = 'searching' 
            AND t.maNguoiDung != ?
            AND t.thoiDiemBatDau >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
            ORDER BY t.thoiDiemBatDau ASC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $searchingUsers = [];
        while ($row = $result->fetch_assoc()) {
            $searchingUsers[] = $row['maNguoiDung'];
        }
        
        if (empty($searchingUsers)) {
            return false; // Không có ai đang tìm kiếm
        }
        
        // Tính độ phù hợp với từng người
        $bestMatch = null;
        $highestScore = 30; // Ngưỡng tối thiểu để ghép đôi (30%)
        
        foreach ($searchingUsers as $candidateId) {
            $score = $this->matching->calculateCompatibility($userId, $candidateId);
            
            if ($score > $highestScore) {
                $highestScore = $score;
                $bestMatch = $candidateId;
            }
        }
        
        // Nếu tìm thấy người phù hợp
        if ($bestMatch) {
            return $this->createMatch($userId, $bestMatch, $highestScore);
        }
        
        return false;
    }
    
    /**
     * Tạo ghép đôi giữa 2 người
     */
    private function createMatch($userId1, $userId2, $compatibilityScore) {
        // Kiểm tra xem đã có ghép đôi chưa
        $stmt = $this->conn->prepare("
            SELECT maGhepDoi FROM GhepDoi 
            WHERE ((maNguoiA = ? AND maNguoiB = ?) OR (maNguoiA = ? AND maNguoiB = ?))
            AND trangThaiGhepDoi = 'matched'
            LIMIT 1
        ");
        $stmt->bind_param("iiii", $userId1, $userId2, $userId2, $userId1);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Đã có ghép đôi, chỉ cập nhật trạng thái tìm kiếm
            $this->updateSearchStatus($userId1, 'matched');
            $this->updateSearchStatus($userId2, 'matched');
            
            $row = $result->fetch_assoc();
            return [
                'success' => true,
                'matchId' => $row['maGhepDoi'],
                'partnerId' => $userId2,
                'score' => $compatibilityScore
            ];
        }
        
        // Tạo ghép đôi mới
        $stmt = $this->conn->prepare("
            INSERT INTO GhepDoi (maNguoiA, maNguoiB, thoiDiemGhepDoi, trangThaiGhepDoi) 
            VALUES (?, ?, NOW(), 'matched')
        ");
        $stmt->bind_param("ii", $userId1, $userId2);
        
        if ($stmt->execute()) {
            $matchId = $this->conn->insert_id;
            
            // Cập nhật trạng thái tìm kiếm của cả 2 người
            $this->updateSearchStatus($userId1, 'matched');
            $this->updateSearchStatus($userId2, 'matched');
            
            // Tạo tin nhắn chào mừng
            $this->createWelcomeMessage($matchId, $userId1, $userId2, $compatibilityScore);
            
            return [
                'success' => true,
                'matchId' => $matchId,
                'partnerId' => $userId2,
                'score' => $compatibilityScore
            ];
        }
        
        return false;
    }
    
    /**
     * Cập nhật trạng thái tìm kiếm
     */
    private function updateSearchStatus($userId, $status) {
        $stmt = $this->conn->prepare("
            UPDATE TimKiemGhepDoi 
            SET trangThai = ?, thoiDiemKetThuc = NOW() 
            WHERE maNguoiDung = ? AND trangThai = 'searching'
        ");
        $stmt->bind_param("si", $status, $userId);
        return $stmt->execute();
    }
    
    /**
     * Tạo tin nhắn chào mừng khi ghép đôi thành công
     */
    private function createWelcomeMessage($matchId, $userId1, $userId2, $score) {
        $message = "🎉 Chúc mừng! Bạn đã được ghép đôi với độ phù hợp {$score}%! Hãy bắt đầu cuộc trò chuyện nhé! 💕";
        
        $stmt = $this->conn->prepare("
            INSERT INTO TinNhan (maGhepDoi, maNguoiGui, noiDung, thoiDiemGui) 
            VALUES (?, NULL, ?, NOW())
        ");
        $stmt->bind_param("is", $matchId, $message);
        return $stmt->execute();
    }
    
    /**
     * Kiểm tra xem có match mới không (dùng cho polling)
     */
    public function checkForMatch($userId) {
        // Kiểm tra trạng thái tìm kiếm hiện tại
        $status = $this->getSearchStatus($userId);
        
        if (!$status) {
            return ['searching' => false];
        }
        
        // Thử tìm match
        $match = $this->tryFindMatch($userId);
        
        if ($match) {
            return array_merge(['searching' => false], $match);
        }
        
        // Vẫn đang tìm kiếm
        return [
            'searching' => true,
            'duration' => time() - strtotime($status['thoiDiemBatDau'])
        ];
    }
    
    /**
     * Lấy thông tin partner sau khi match
     */
    public function getPartnerInfo($userId, $partnerId) {
        $stmt = $this->conn->prepare("
            SELECT h.*, n.ten, n.email,
                   TIMESTAMPDIFF(YEAR, h.ngaySinh, CURDATE()) as tuoi
            FROM HoSo h
            INNER JOIN NguoiDung n ON h.maNguoiDung = n.maNguoiDung
            WHERE h.maNguoiDung = ?
        ");
        $stmt->bind_param("i", $partnerId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Dọn dẹp các tìm kiếm cũ (>5 phút)
     */
    public function cleanupOldSearches() {
        $stmt = $this->conn->prepare("
            UPDATE TimKiemGhepDoi 
            SET trangThai = 'cancelled', thoiDiemKetThuc = NOW() 
            WHERE trangThai = 'searching' 
            AND thoiDiemBatDau < DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        ");
        return $stmt->execute();
    }
}
?>
