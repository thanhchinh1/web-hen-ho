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
     * BATCH MATCHING: Chờ 5 giây rồi ghép tất cả cùng lúc
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
        
        // Tạo yêu cầu tìm kiếm mới với timestamp batch
        // Batch ID = timestamp làm tròn đến 5 giây
        // VD: 10:00:00-10:00:04 → batch 10:00:00
        //     10:00:05-10:00:09 → batch 10:00:05
        $stmt = $this->conn->prepare("
            INSERT INTO timkiemghepdoi (maNguoiDung, trangThai, thoiDiemBatDau) 
            VALUES (?, 'searching', NOW())
        ");
        $stmt->bind_param("i", $userId);
        $result = $stmt->execute();
        
        if ($result) {
            // KHÔNG TÌM NGAY - Chờ batch matching sau 5 giây
            return ['status' => 'waiting', 'message' => 'Đang chờ thu thập người dùng...'];
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
     * SỬ DỤNG THUẬT TOÁN GREEDY TOÀN CỤC - Ưu tiên cặp có điểm cao nhất
     */
    private function tryFindMatch($userId) {
        error_log("=== TRY FIND MATCH FOR USER $userId ===");
        
        // BẮT ĐẦU TRANSACTION
        $this->conn->begin_transaction();
        
        try {
            // BƯỚC 1: LOCK và lấy TẤT CẢ người đang tìm kiếm
            $stmt = $this->conn->prepare("
                SELECT tk.maNguoiDung, tk.maTimKiem
                FROM timkiemghepdoi tk
                INNER JOIN hoso h ON tk.maNguoiDung = h.maNguoiDung
                INNER JOIN nguoidung n ON tk.maNguoiDung = n.maNguoiDung
                WHERE tk.trangThai = 'searching'
                AND n.trangThaiNguoiDung = 'active'
                AND tk.thoiDiemBatDau >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                FOR UPDATE  -- LOCK các record này
            ");
            $stmt->execute();
            $result = $stmt->get_result();
            
            $allSearchingUsers = [];
            while ($row = $result->fetch_assoc()) {
                $allSearchingUsers[] = $row['maNguoiDung'];
            }
            
            error_log("Tất cả người đang tìm kiếm: " . print_r($allSearchingUsers, true));
            
            if (count($allSearchingUsers) < 2) {
                $this->conn->rollback();
                error_log("❌ Ít hơn 2 người - không thể ghép đôi!");
                return false;
            }
            
            // BƯỚC 2: Tính điểm TẤT CẢ các cặp có thể
            error_log("🔢 Tính điểm tất cả các cặp...");
            $allPairs = [];
            $processedUsers = [];
            
            for ($i = 0; $i < count($allSearchingUsers); $i++) {
                $user1 = $allSearchingUsers[$i];
                
                // Kiểm tra user1 đã bị loại trừ chưa
                $excluded1 = $this->getExcludedUsers($user1);
                
                for ($j = $i + 1; $j < count($allSearchingUsers); $j++) {
                    $user2 = $allSearchingUsers[$j];
                    
                    // Kiểm tra 2 user có loại trừ nhau không
                    $excluded2 = $this->getExcludedUsers($user2);
                    
                    if (in_array($user2, $excluded1) || in_array($user1, $excluded2)) {
                        error_log("  ⛔ Skip: User $user1 <-> $user2 (bị loại trừ)");
                        continue;
                    }
                    
                    // Tính điểm 2 chiều và lấy trung bình
                    $score1to2 = $this->matching->calculateCompatibility($user1, $user2);
                    $score2to1 = $this->matching->calculateCompatibility($user2, $user1);
                    $avgScore = ($score1to2 + $score2to1) / 2;
                    
                    error_log("  📊 User $user1 <-> $user2: {$score1to2}% / {$score2to1}% = Avg {$avgScore}%");
                    
                    if ($avgScore >= 30) { // Ngưỡng tối thiểu
                        $allPairs[] = [
                            'user1' => $user1,
                            'user2' => $user2,
                            'score' => $avgScore
                        ];
                    }
                }
            }
            
            if (empty($allPairs)) {
                $this->conn->rollback();
                error_log("❌ Không có cặp nào đủ điều kiện!");
                return false;
            }
            
            // BƯỚC 3: SẮP XẾP các cặp theo điểm GIẢM DẦN
            usort($allPairs, function($a, $b) {
                return $b['score'] <=> $a['score']; // Điểm cao nhất lên đầu
            });
            
            error_log("📋 Danh sách cặp (đã sắp xếp):");
            foreach ($allPairs as $idx => $pair) {
                error_log("  " . ($idx+1) . ". User {$pair['user1']} <-> {$pair['user2']}: {$pair['score']}%");
            }
            
            // BƯỚC 4: CHỌN CẶP TỐT NHẤT có chứa user hiện tại
            $bestPairForCurrentUser = null;
            
            foreach ($allPairs as $pair) {
                // Tìm cặp tốt nhất có chứa user hiện tại
                if ($pair['user1'] == $userId || $pair['user2'] == $userId) {
                    $bestPairForCurrentUser = $pair;
                    break; // Đã tìm thấy cặp tốt nhất
                }
            }
            
            if (!$bestPairForCurrentUser) {
                $this->conn->rollback();
                error_log("❌ Không tìm thấy cặp phù hợp cho user $userId");
                return false;
            }
            
            // BƯỚC 5: Tạo match cho cặp tốt nhất
            $partnerId = ($bestPairForCurrentUser['user1'] == $userId) 
                ? $bestPairForCurrentUser['user2'] 
                : $bestPairForCurrentUser['user1'];
            
            error_log("✅ CẶP TỐT NHẤT: User $userId <-> $partnerId ({$bestPairForCurrentUser['score']}%)");
            
            $result = $this->createMatchInTransaction($userId, $partnerId, $bestPairForCurrentUser['score']);
            
            if ($result) {
                // COMMIT transaction
                $this->conn->commit();
                return $result;
            } else {
                $this->conn->rollback();
                return false;
            }
            
        } catch (Exception $e) {
            // Rollback nếu có lỗi
            $this->conn->rollback();
            error_log("❌ ERROR in tryFindMatch: " . $e->getMessage());
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
     * Tạo ghép đôi giữa 2 người (SỬ DỤNG TRONG TRANSACTION)
     */
    private function createMatchInTransaction($userId1, $userId2, $compatibilityScore) {
        error_log("🔄 createMatchInTransaction: User $userId1 <-> User $userId2");
        
        // Kiểm tra xem đã có ghép đôi chưa (với LOCK)
        $stmt = $this->conn->prepare("
            SELECT maGhepDoi FROM ghepdoi 
            WHERE ((maNguoiA = ? AND maNguoiB = ?) OR (maNguoiA = ? AND maNguoiB = ?))
            AND trangThaiGhepDoi = 'matched'
            LIMIT 1
            FOR UPDATE
        ");
        $stmt->bind_param("iiii", $userId1, $userId2, $userId2, $userId1);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            error_log("⚠️ Match đã tồn tại!");
            $row = $result->fetch_assoc();
            
            // XÓA record tìm kiếm của cả 2
            $this->deleteSearchRecordInTransaction($userId1);
            $this->deleteSearchRecordInTransaction($userId2);
            
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
            
            // XÓA record tìm kiếm của cả 2 người
            $this->deleteSearchRecordInTransaction($userId1);
            $this->deleteSearchRecordInTransaction($userId2);
            
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
     * Xóa record tìm kiếm TRONG TRANSACTION
     */
    private function deleteSearchRecordInTransaction($userId) {
        $stmt = $this->conn->prepare("
            DELETE FROM timkiemghepdoi 
            WHERE maNguoiDung = ? AND trangThai = 'searching'
        ");
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }
    
    /**
     * Tạo ghép đôi giữa 2 người (LEGACY - không dùng transaction)
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
     * BATCH MATCHING: Sau 5 giây, ghép tất cả người dùng cùng batch
     */
    public function checkForMatch($userId) {
        error_log("🔄 checkForMatch for user $userId");
        
        // BƯỚC 1: Kiểm tra xem đã có match nào được tạo chưa
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
            // ĐÃ CÓ MATCH!
            $matchData = $result->fetch_assoc();
            $partnerId = ($matchData['maNguoiA'] == $userId) ? $matchData['maNguoiB'] : $matchData['maNguoiA'];
            
            error_log("✅ Tìm thấy match! Match ID: {$matchData['maGhepDoi']}, Partner: $partnerId");
            
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
        
        // BƯỚC 2: Kiểm tra trạng thái tìm kiếm
        $status = $this->getSearchStatus($userId);
        
        if (!$status) {
            error_log("❌ Không có trạng thái tìm kiếm");
            return ['searching' => false];
        }
        
        error_log("📊 Search status: " . print_r($status, true));
        
        // BƯỚC 3: Kiểm tra đã đủ 5 giây chưa
        // SỬ DỤNG MySQL để tính thời gian tránh lỗi timezone
        $stmt = $this->conn->prepare("
            SELECT TIMESTAMPDIFF(SECOND, ?, NOW()) as duration
        ");
        $stmt->bind_param("s", $status['thoiDiemBatDau']);
        $stmt->execute();
        $durationResult = $stmt->get_result()->fetch_assoc();
        $searchDuration = $durationResult['duration'];
        
        error_log("⏱️ Thời gian tìm kiếm: {$searchDuration}s (MySQL)");
        
        if ($searchDuration < 5) {
            // CHƯA ĐỦ 5 GIÂY - Tiếp tục chờ
            error_log("⏳ Chờ batch matching... ({$searchDuration}/5s)");
            return [
                'searching' => true,
                'waiting' => true,
                'duration' => $searchDuration,
                'message' => 'Đang thu thập người dùng... (' . (5 - $searchDuration) . 's)'
            ];
        }
        
        // BƯỚC 4: ĐỦ 5 GIÂY - Thực hiện BATCH MATCHING
        error_log("🎯 ĐỦ 5 GIÂY - Bắt đầu batch matching!");
        
        $match = $this->performBatchMatching($userId);
        
        if ($match) {
            error_log("✅ Batch matching thành công!");
            return array_merge(['searching' => false], $match);
        }
        
        // BƯỚC 5: Không tìm thấy sau 5 giây
        error_log("❌ Không tìm thấy ai phù hợp sau 5 giây");
        $this->cancelSearching($userId);
        
        return [
            'searching' => false,
            'success' => false,
            'message' => 'Không tìm thấy người phù hợp'
        ];
    }
    
    /**
     * Thực hiện batch matching - Ghép TẤT CẢ người dùng đã chờ đủ 5 giây
     */
    private function performBatchMatching($userId) {
        error_log("=== BATCH MATCHING ===");
        
        // BẮT ĐẦU TRANSACTION
        $this->conn->begin_transaction();
        
        try {
            // Lấy TẤT CẢ người đã tìm kiếm >= 5 giây
            $stmt = $this->conn->prepare("
                SELECT tk.maNguoiDung
                FROM timkiemghepdoi tk
                INNER JOIN nguoidung n ON tk.maNguoiDung = n.maNguoiDung
                WHERE tk.trangThai = 'searching'
                AND n.trangThaiNguoiDung = 'active'
                AND tk.thoiDiemBatDau <= DATE_SUB(NOW(), INTERVAL 5 SECOND)
                FOR UPDATE
            ");
            $stmt->execute();
            $result = $stmt->get_result();
            
            $batchUsers = [];
            while ($row = $result->fetch_assoc()) {
                $batchUsers[] = $row['maNguoiDung'];
            }
            
            error_log("👥 Batch users (>= 5s): " . print_r($batchUsers, true));
            
            if (count($batchUsers) < 2) {
                $this->conn->rollback();
                error_log("❌ Ít hơn 2 người trong batch");
                return false;
            }
            
            // Tính điểm TẤT CẢ các cặp
            $allPairs = [];
            
            for ($i = 0; $i < count($batchUsers); $i++) {
                $user1 = $batchUsers[$i];
                $excluded1 = $this->getExcludedUsers($user1);
                
                for ($j = $i + 1; $j < count($batchUsers); $j++) {
                    $user2 = $batchUsers[$j];
                    $excluded2 = $this->getExcludedUsers($user2);
                    
                    // Kiểm tra loại trừ
                    if (in_array($user2, $excluded1) || in_array($user1, $excluded2)) {
                        continue;
                    }
                    
                    // Tính điểm trung bình 2 chiều
                    $score1to2 = $this->matching->calculateCompatibility($user1, $user2);
                    $score2to1 = $this->matching->calculateCompatibility($user2, $user1);
                    $avgScore = ($score1to2 + $score2to1) / 2;
                    
                    if ($avgScore >= 30) {
                        $allPairs[] = [
                            'user1' => $user1,
                            'user2' => $user2,
                            'score' => $avgScore
                        ];
                    }
                }
            }
            
            if (empty($allPairs)) {
                $this->conn->rollback();
                error_log("❌ Không có cặp nào đủ điều kiện");
                return false;
            }
            
            // Sắp xếp theo điểm giảm dần
            usort($allPairs, function($a, $b) {
                return $b['score'] <=> $a['score'];
            });
            
            error_log("📊 Tất cả các cặp (sorted):");
            foreach ($allPairs as $idx => $pair) {
                error_log("  " . ($idx+1) . ". User {$pair['user1']} <-> {$pair['user2']}: {$pair['score']}%");
            }
            
            // Tìm cặp tốt nhất có chứa userId
            $bestPairForCurrentUser = null;
            
            foreach ($allPairs as $pair) {
                if ($pair['user1'] == $userId || $pair['user2'] == $userId) {
                    $bestPairForCurrentUser = $pair;
                    break;
                }
            }
            
            if (!$bestPairForCurrentUser) {
                $this->conn->rollback();
                error_log("❌ Không tìm thấy cặp cho user $userId");
                return false;
            }
            
            // Tạo match
            $partnerId = ($bestPairForCurrentUser['user1'] == $userId) 
                ? $bestPairForCurrentUser['user2'] 
                : $bestPairForCurrentUser['user1'];
            
            error_log("✅ CẶP TỐT NHẤT: User $userId <-> $partnerId ({$bestPairForCurrentUser['score']}%)");
            
            $result = $this->createMatchInTransaction($userId, $partnerId, $bestPairForCurrentUser['score']);
            
            if ($result) {
                $this->conn->commit();
                return $result;
            } else {
                $this->conn->rollback();
                return false;
            }
            
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("❌ ERROR in performBatchMatching: " . $e->getMessage());
            return false;
        }
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
