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
        
        // CẬP NHẬT thời gian hoạt động cuối để đánh dấu user đang online
        $updateStmt = $this->conn->prepare("
            UPDATE nguoidung 
            SET lanHoatDongCuoi = NOW() 
            WHERE maNguoiDung = ?
        ");
        $updateStmt->bind_param("i", $userId);
        $updateStmt->execute();
        
        // Tạo yêu cầu tìm kiếm mới
        $stmt = $this->conn->prepare("
            INSERT INTO timkiemghepdoi (maNguoiDung, trangThai, thoiDiemBatDau) 
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
        // XÓA HOÀN TOÀN bản ghi thay vì chỉ update trạng thái
        $stmt = $this->conn->prepare("
            DELETE FROM timkiemghepdoi
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
            FROM timkiemghepdoi 
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
     * CHỈ TÌM NGƯỜI ĐANG SEARCHING (cùng bấm ghép đôi nhanh)
     */
    private function tryFindMatch($userId) {
        error_log("=== TRY FIND MATCH FOR USER $userId ===");
        
        // CHỈ tìm người ĐANG TÌM KIẾM trong bảng TimKiemGhepDoi
        // KHÔNG tìm người online bình thường
        $stmt = $this->conn->prepare("
            SELECT DISTINCT tk.maNguoiDung 
            FROM timkiemghepdoi tk
            INNER JOIN hoso h ON tk.maNguoiDung = h.maNguoiDung
            INNER JOIN nguoidung n ON tk.maNguoiDung = n.maNguoiDung
            WHERE tk.trangThai = 'searching'
            AND n.trangThaiNguoiDung = 'active'
            AND tk.maNguoiDung != ?
            AND tk.thoiDiemBatDau >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $searchingUsers = [];
        while ($row = $result->fetch_assoc()) {
            $searchingUsers[] = $row['maNguoiDung'];
        }
        
        error_log("Người đang tìm kiếm (bấm ghép đôi nhanh): " . print_r($searchingUsers, true));
        
        if (empty($searchingUsers)) {
            error_log("❌ KHÔNG CÓ AI ĐANG BẤM GHÉP ĐÔI NHANH!");
            return false; // Không có ai đang searching
        }
        
        error_log("Danh sách ứng viên: " . print_r($searchingUsers, true));
        
        // Lọc bỏ người đã match và bị chặn
        $excludedUsers = $this->getExcludedUsers($userId);
        error_log("Người bị loại trừ: " . print_r($excludedUsers, true));
        
        $candidateUsers = array_diff($searchingUsers, $excludedUsers);
        
        if (empty($candidateUsers)) {
            error_log("❌ SAU KHI LỌC - KHÔNG CÒN AI!");
            return false; // Không còn ai phù hợp
        }
        
        error_log("Danh sách sau khi lọc: " . print_r($candidateUsers, true));
        
        // Tính độ phù hợp với từng người
        $bestMatch = null;
        $highestScore = 30; // Ngưỡng tối thiểu để ghép đôi (30%)
        
        foreach ($candidateUsers as $candidateId) {
            $score = $this->matching->calculateCompatibility($userId, $candidateId);
            error_log("Độ phù hợp với user $candidateId: $score%");
            
            if ($score > $highestScore) {
                $highestScore = $score;
                $bestMatch = $candidateId;
            }
        }
        
        // Nếu tìm thấy người phù hợp, tạo match
        if ($bestMatch) {
            error_log("✅ TÌM THẤY MATCH! User $bestMatch với điểm $highestScore%");
            return $this->createMatch($userId, $bestMatch, $highestScore);
        }
        
        error_log("❌ KHÔNG TÌM THẤY AI ĐỦ ĐIỀU KIỆN (điểm cao nhất: $highestScore%)");
        return false;
    }
    
    /**
     * Lấy danh sách user đã chặn và đã match (CHỈ loại những người này)
     */
    private function getExcludedUsers($userId) {
        $excluded = [];
        
        // Người đã chặn
        $stmt = $this->conn->prepare("
            SELECT maNguoiBiChan FROM channguoidung WHERE maNguoiChan = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $excluded[] = $row['maNguoiBiChan'];
        }
        
        // Người đã bị chặn mình
        $stmt = $this->conn->prepare("
            SELECT maNguoiChan FROM channguoidung WHERE maNguoiBiChan = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $excluded[] = $row['maNguoiChan'];
        }
        
        // Người đã match (chỉ loại người đã match thành công)
        $stmt = $this->conn->prepare("
            SELECT maNguoiB FROM ghepdoi WHERE maNguoiA = ? AND trangThaiGhepDoi = 'matched'
            UNION
            SELECT maNguoiA FROM ghepdoi WHERE maNguoiB = ? AND trangThaiGhepDoi = 'matched'
        ");
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $excluded[] = $row['maNguoiB'] ?? $row['maNguoiA'];
        }
        
        return array_unique($excluded);
    }
    
    /**
     * Tạo ghép đôi giữa 2 người
     */
    private function createMatch($userId1, $userId2, $compatibilityScore) {
        error_log("🔄 createMatch: User $userId1 <-> User $userId2");
        
        // Kiểm tra xem đã có ghép đôi chưa
        $stmt = $this->conn->prepare("
            SELECT maGhepDoi FROM ghepdoi 
            WHERE ((maNguoiA = ? AND maNguoiB = ?) OR (maNguoiA = ? AND maNguoiB = ?))
            AND trangThaiGhepDoi = 'matched'
            LIMIT 1
        ");
        $stmt->bind_param("iiii", $userId1, $userId2, $userId2, $userId1);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            error_log("⚠️  Match đã tồn tại!");
            // Đã có ghép đôi - XÓA record tìm kiếm của cả 2
            $this->cancelSearching($userId1);
            $this->cancelSearching($userId2);
            
            $row = $result->fetch_assoc();
            return [
                'success' => true,
                'matchId' => $row['maGhepDoi'],
                'partnerId' => $userId2,
                'score' => $compatibilityScore
            ];
        }
        
        error_log("✨ Tạo match mới...");
        
        // Tạo ghép đôi mới
        $stmt = $this->conn->prepare("
            INSERT INTO ghepdoi (maNguoiA, maNguoiB, thoiDiemGhepDoi, trangThaiGhepDoi) 
            VALUES (?, ?, NOW(), 'matched')
        ");
        $stmt->bind_param("ii", $userId1, $userId2);
        
        if ($stmt->execute()) {
            $matchId = $this->conn->insert_id;
            
            error_log("✅ Match created! ID: $matchId");
            
            // XÓA record tìm kiếm của cả 2 người (thay vì update)
            $this->cancelSearching($userId1);
            $this->cancelSearching($userId2);
            
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
            UPDATE timkiemghepdoi 
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
            INSERT INTO tinnhan (maGhepDoi, maNguoiGui, noiDung, thoiDiemGui) 
            VALUES (?, NULL, ?, NOW())
        ");
        $stmt->bind_param("is", $matchId, $message);
        return $stmt->execute();
    }
    
    /**
     * Kiểm tra xem có match mới không (dùng cho polling)
     */
    public function checkForMatch($userId) {
        error_log("🔄 checkForMatch for user $userId");
        
        // BƯỚC 1: Kiểm tra xem đã có match nào được tạo chưa (do user khác tạo)
        $stmt = $this->conn->prepare("
            SELECT maGhepDoi, maNguoiA, maNguoiB, thoiDiemGhepDoi
            FROM ghepdoi 
            WHERE (maNguoiA = ? OR maNguoiB = ?)
            AND trangThaiGhepDoi = 'matched'
            ORDER BY thoiDiemGhepDoi DESC
            LIMIT 1
        ");
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // ĐÃ CÓ MATCH! (do user khác tạo trong lúc đang tìm kiếm)
            $matchData = $result->fetch_assoc();
            $partnerId = ($matchData['maNguoiA'] == $userId) ? $matchData['maNguoiB'] : $matchData['maNguoiA'];
            
            error_log("✅ Tìm thấy match đã tồn tại! Match ID: {$matchData['maGhepDoi']}, Partner: $partnerId");
            
            // Xóa record tìm kiếm
            $this->cancelSearching($userId);
            
            // Tính độ tương thích
            $score = $this->matching->calculateCompatibility($userId, $partnerId);
            
            return [
                'searching' => false,
                'success' => true,
                'matchId' => $matchData['maGhepDoi'],
                'partnerId' => $partnerId,
                'score' => $score
            ];
        }
        
        // BƯỚC 2: Kiểm tra trạng thái tìm kiếm hiện tại
        $status = $this->getSearchStatus($userId);
        
        if (!$status) {
            error_log("❌ Không có trạng thái tìm kiếm");
            return ['searching' => false];
        }
        
        // BƯỚC 3: Thử tìm match mới
        $match = $this->tryFindMatch($userId);
        
        if ($match) {
            error_log("✅ Tìm thấy match mới!");
            return array_merge(['searching' => false], $match);
        }
        
        // BƯỚC 4: Vẫn đang tìm kiếm
        error_log("⏳ Vẫn đang tìm...");
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
            SELECT h.*, n.tenDangNhap,
                   TIMESTAMPDIFF(YEAR, h.ngaySinh, CURDATE()) as tuoi
            FROM hoso h
            INNER JOIN nguoidung n ON h.maNguoiDung = n.maNguoiDung
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
        // XÓA các bản ghi quá cũ thay vì update
        $stmt = $this->conn->prepare("
            DELETE FROM timkiemghepdoi 
            WHERE trangThai = 'searching' 
            AND thoiDiemBatDau < DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        ");
        return $stmt->execute();
    }
}
?>
