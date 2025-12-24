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
     * CHỈ TÌM NGƯỜI ĐANG SEARCHING VÀ CHƯA BỊ KHÓA (cùng bấm ghép đôi nhanh)
     */
    private function tryFindMatch($userId) {
        error_log("=== TRY FIND MATCH FOR USER $userId ===");
        
        // KIỂM TRA THỜI GIAN CHỞ: Phải chờ ít nhất 5 giây để tích lũy người trong hàng đợi
        $checkTimeStmt = $this->conn->prepare("
            SELECT TIMESTAMPDIFF(SECOND, thoiDiemBatDau, NOW()) as waitTime
            FROM timkiemghepdoi
            WHERE maNguoiDung = ? AND trangThai = 'searching'
        ");
        $checkTimeStmt->bind_param("i", $userId);
        $checkTimeStmt->execute();
        $timeResult = $checkTimeStmt->get_result();
        $timeRow = $timeResult->fetch_assoc();
        
        if ($timeRow && $timeRow['waitTime'] < 5) {
            error_log("⏳ Chưa đủ 5 giây chờ tích lũy (hiện tại: {$timeRow['waitTime']}s) - tiếp tục chờ...");
            return false; // Chưa đủ thời gian chờ
        }
        
        error_log("✅ Đã chờ đủ 5 giây - bắt đầu tính toán ghép đôi...");
        
        // Bắt đầu transaction để đảm bảo atomic operation
        $this->conn->begin_transaction();
        
        try {
            // KHÓA user hiện tại ngay lập tức để tránh bị ghép trùng
            $lockStmt = $this->conn->prepare("
                UPDATE timkiemghepdoi 
                SET isLocked = 1, lockedAt = NOW() 
                WHERE maNguoiDung = ? AND trangThai = 'searching' AND isLocked = 0
            ");
            $lockStmt->bind_param("i", $userId);
            $lockStmt->execute();
            
            if ($lockStmt->affected_rows === 0) {
                // Không thể khóa (có thể đã bị khóa bởi thread khác)
                $this->conn->rollback();
                error_log("❌ Không thể khóa user $userId - có thể đang được xử lý");
                return false;
            }
            
            // Lấy giới tính của user hiện tại
            $genderStmt = $this->conn->prepare("
                SELECT gioiTinh FROM hoso WHERE maNguoiDung = ?
            ");
            $genderStmt->bind_param("i", $userId);
            $genderStmt->execute();
            $genderResult = $genderStmt->get_result();
            $genderRow = $genderResult->fetch_assoc();
            $userGender = $genderRow['gioiTinh'] ?? null;
            
            error_log("👤 Giới tính của user $userId: $userGender");
            
            // Xác định giới tính đối lập để tìm kiếm
            $targetGender = null;
            if ($userGender === 'Nam') {
                $targetGender = 'Nữ';
            } elseif ($userGender === 'Nữ') {
                $targetGender = 'Nam';
            }
            
            if (!$targetGender) {
                // Không xác định được giới tính hoặc giới tính "Khác" - MỞ KHÓA và dừng
                $unlockStmt = $this->conn->prepare("
                    UPDATE timkiemghepdoi 
                    SET isLocked = 0, lockedAt = NULL 
                    WHERE maNguoiDung = ? AND trangThai = 'searching'
                ");
                $unlockStmt->bind_param("i", $userId);
                $unlockStmt->execute();
                
                $this->conn->commit();
                error_log("❌ Không thể xác định giới tính đối lập cho user $userId");
                return false;
            }
            
            error_log("🎯 Tìm người giới tính: $targetGender");
            
            // CHỈ tìm người ĐANG TÌM KIẾM, CHƯA BỊ KHÓA và có GIỚI TÍNH ĐỐI LẬP
            $stmt = $this->conn->prepare("
                SELECT DISTINCT tk.maNguoiDung 
                FROM timkiemghepdoi tk
                INNER JOIN hoso h ON tk.maNguoiDung = h.maNguoiDung
                INNER JOIN nguoidung n ON tk.maNguoiDung = n.maNguoiDung
                WHERE tk.trangThai = 'searching'
                AND tk.isLocked = 0
                AND n.trangThaiNguoiDung = 'active'
                AND tk.maNguoiDung != ?
                AND h.gioiTinh = ?
                AND tk.thoiDiemBatDau >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                FOR UPDATE
            ");
            $stmt->bind_param("is", $userId, $targetGender);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $searchingUsers = [];
            while ($row = $result->fetch_assoc()) {
                $searchingUsers[] = $row['maNguoiDung'];
            }
            
            $queueSize = count($searchingUsers);
            error_log("📋 Số người trong hàng đợi (chưa khóa): $queueSize");
            error_log("Người đang tìm kiếm: " . print_r($searchingUsers, true));
            
            if (empty($searchingUsers)) {
                // Không có ai trong hàng đợi - MỞ KHÓA user hiện tại
                $unlockStmt = $this->conn->prepare("
                    UPDATE timkiemghepdoi 
                    SET isLocked = 0, lockedAt = NULL 
                    WHERE maNguoiDung = ? AND trangThai = 'searching'
                ");
                $unlockStmt->bind_param("i", $userId);
                $unlockStmt->execute();
                
                $this->conn->commit();
                error_log("❌ KHÔNG CÓ AI KHÁC TRONG HÀNG ĐỢI - tiếp tục chờ...");
                return false; // Không có ai đang searching
            }
            
            error_log("Danh sách ứng viên: " . print_r($searchingUsers, true));
            
            // Lọc bỏ người đã match và bị chặn
            $excludedUsers = $this->getExcludedUsers($userId);
            error_log("Người bị loại trừ: " . print_r($excludedUsers, true));
            
            $candidateUsers = array_diff($searchingUsers, $excludedUsers);
            
            if (empty($candidateUsers)) {
                // Không còn ai sau khi lọc - MỞ KHÓA user hiện tại
                $unlockStmt = $this->conn->prepare("
                    UPDATE timkiemghepdoi 
                    SET isLocked = 0, lockedAt = NULL 
                    WHERE maNguoiDung = ? AND trangThai = 'searching'
                ");
                $unlockStmt->bind_param("i", $userId);
                $unlockStmt->execute();
                
                $this->conn->commit();
                error_log("❌ SAU KHI LỌC - KHÔNG CÒN AI PHÙ HỢP!");
                return false; // Không còn ai phù hợp
            }
            
            $candidateCount = count($candidateUsers);
            error_log("🎯 Số ứng viên sau khi lọc: $candidateCount");
            error_log("Danh sách ứng viên: " . print_r($candidateUsers, true));
            
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
            
            // Nếu tìm thấy người phù hợp, KHÓA partner và tạo match
            if ($bestMatch) {
                // KHÓA partner trước khi tạo match
                $lockPartnerStmt = $this->conn->prepare("
                    UPDATE timkiemghepdoi 
                    SET isLocked = 1, lockedAt = NOW() 
                    WHERE maNguoiDung = ? AND trangThai = 'searching' AND isLocked = 0
                ");
                $lockPartnerStmt->bind_param("i", $bestMatch);
                $lockPartnerStmt->execute();
                
                if ($lockPartnerStmt->affected_rows === 0) {
                    // Partner đã bị khóa bởi thread khác - MỞ KHÓA user hiện tại và thử lại
                    $unlockStmt = $this->conn->prepare("
                        UPDATE timkiemghepdoi 
                        SET isLocked = 0, lockedAt = NULL 
                        WHERE maNguoiDung = ? AND trangThai = 'searching'
                    ");
                    $unlockStmt->bind_param("i", $userId);
                    $unlockStmt->execute();
                    
                    $this->conn->commit();
                    error_log("⚠️  Partner $bestMatch đã bị khóa - sẽ thử lại");
                    return false;
                }
                
                error_log("✅ TÌM THẤY MATCH! User $bestMatch với điểm $highestScore%");
                error_log("🔒 Đã khóa cả 2 user: $userId và $bestMatch");
                
                // Tạo match (transaction sẽ được commit trong createMatch)
                return $this->createMatch($userId, $bestMatch, $highestScore);
            }
            
            // Không tìm thấy ai đủ điều kiện - MỞ KHÓA user hiện tại
            $unlockStmt = $this->conn->prepare("
                UPDATE timkiemghepdoi 
                SET isLocked = 0, lockedAt = NULL 
                WHERE maNguoiDung = ? AND trangThai = 'searching'
            ");
            $unlockStmt->bind_param("i", $userId);
            $unlockStmt->execute();
            
            $this->conn->commit();
            error_log("❌ KHÔNG TÌM THẤY AI ĐỦ ĐIỀU KIỆN (điểm cao nhất: $highestScore%)");
            return false;
            
        } catch (Exception $e) {
            // Rollback nếu có lỗi
            $this->conn->rollback();
            error_log("❌ Exception trong tryFindMatch: " . $e->getMessage());
            return false;
        }
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
        
        // Loại trừ những người đã TỪNG ghép đôi với mình (để tránh ghép lại)
        $stmt = $this->conn->prepare("
            SELECT DISTINCT maNguoiB FROM ghepdoi WHERE maNguoiA = ?
            UNION
            SELECT DISTINCT maNguoiA FROM ghepdoi WHERE maNguoiB = ?
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
     * Tạo ghép đôi giữa 2 người (trong transaction)
     */
    private function createMatch($userId1, $userId2, $compatibilityScore) {
        error_log("🔄 createMatch: User $userId1 <-> User $userId2");
        
        try {
            // CHO PHÉP GHÉP ĐÔI NHIỀU LẦN - không check match đã tồn tại
            error_log("✨ Tạo match mới...");
            
            // Tạo ghép đôi mới
            $stmt = $this->conn->prepare("
                INSERT INTO ghepdoi (maNguoiA, maNguoiB, thoiDiemGhepDoi, trangThaiGhepDoi) 
                VALUES (?, ?, NOW(), 'matched')
            ");
            $stmt->bind_param("ii", $userId1, $userId2);
            
            if (!$stmt->execute()) {
                throw new Exception("Không thể tạo match: " . $stmt->error);
            }
            
            $matchId = $this->conn->insert_id;
            
            error_log("✅ Match created! ID: $matchId");
            
            // XÓA record tìm kiếm của cả 2 người (đã bị khóa)
            $deleteStmt = $this->conn->prepare("
                DELETE FROM timkiemghepdoi 
                WHERE maNguoiDung IN (?, ?) AND trangThai = 'searching'
            ");
            $deleteStmt->bind_param("ii", $userId1, $userId2);
            $deleteStmt->execute();
            
            error_log("🗑️  Đã xóa record tìm kiếm của cả 2 user khỏi hàng đợi");
            
            // Tạo tin nhắn chào mừng
            $this->createWelcomeMessage($matchId, $userId1, $userId2, $compatibilityScore);
            
            // COMMIT transaction
            $this->conn->commit();
            error_log("✅ Transaction committed - Match hoàn tất!");
            
            return [
                'success' => true,
                'matchId' => $matchId,
                'partnerId' => $userId2,
                'score' => $compatibilityScore
            ];
            
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("❌ Error trong createMatch: " . $e->getMessage());
            return false;
        }
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
        
        // BƯỚC 1: Kiểm tra xem có match MỚI nào được tạo gần đây không (trong 10 giây vừa qua)
        // Điều này đảm bảo khi user A tạo match với user B, thì B sẽ nhận được match đó khi polling
        $stmt = $this->conn->prepare("
            SELECT maGhepDoi, maNguoiA, maNguoiB, thoiDiemGhepDoi
            FROM ghepdoi 
            WHERE (maNguoiA = ? OR maNguoiB = ?)
            AND trangThaiGhepDoi = 'matched'
            AND thoiDiemGhepDoi >= DATE_SUB(NOW(), INTERVAL 10 SECOND)
            ORDER BY thoiDiemGhepDoi DESC
            LIMIT 1
        ");
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Tìm thấy match mới được tạo!
            $matchData = $result->fetch_assoc();
            $partnerId = ($matchData['maNguoiA'] == $userId) ? $matchData['maNguoiB'] : $matchData['maNguoiA'];
            
            error_log("✅ Tìm thấy match mới! Match ID: {$matchData['maGhepDoi']}, Partner: $partnerId");
            
            // Xóa record tìm kiếm nếu còn
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
     * Dọn dẹp các tìm kiếm cũ (>5 phút) và mở khóa các record bị kẹt
     */
    public function cleanupOldSearches() {
        // Mở khóa các record bị khóa quá lâu (>30 giây) - có thể do lỗi
        $unlockStmt = $this->conn->prepare("
            UPDATE timkiemghepdoi 
            SET isLocked = 0, lockedAt = NULL
            WHERE isLocked = 1 
            AND lockedAt < DATE_SUB(NOW(), INTERVAL 30 SECOND)
        ");
        $unlockStmt->execute();
        
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
