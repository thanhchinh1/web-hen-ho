<?php
require_once 'mDbconnect.php';

class Matching {
    private $conn;
    
    public function __construct() {
        $db = clsConnect::getInstance();
        $this->conn = $db->connect();
    }
    
    /**
     * Tính độ tương thích giữa 2 người dùng (0-100%)
     */
    public function calculateCompatibility($userId1, $userId2) {
        // Lấy thông tin 2 người
        $user1 = $this->getUserProfile($userId1);
        $user2 = $this->getUserProfile($userId2);
        
        if (!$user1 || !$user2) {
            return 0;
        }
        
        $score = 0;
        $totalFactors = 0;
        
        // 1. Độ tuổi phù hợp (20 điểm)
        $totalFactors += 20;
        $age1 = $this->calculateAge($user1['ngaySinh']);
        $age2 = $this->calculateAge($user2['ngaySinh']);
        $ageDiff = abs($age1 - $age2);
        
        if ($ageDiff <= 2) {
            $score += 20;
        } elseif ($ageDiff <= 5) {
            $score += 15;
        } elseif ($ageDiff <= 10) {
            $score += 10;
        } elseif ($ageDiff <= 15) {
            $score += 5;
        }
        
        // 2. Học vấn (15 điểm)
        $totalFactors += 15;
        if ($user1['hocVan'] == $user2['hocVan']) {
            $score += 15;
        }
        
        // 3. Mục tiêu (20 điểm)
        $totalFactors += 20;
        if ($user1['mucTieuPhatTrien'] == $user2['mucTieuPhatTrien']) {
            $score += 20;
        }
        
        // 4. Nơi sống (15 điểm)
        $totalFactors += 15;
        if ($user1['noiSong'] == $user2['noiSong']) {
            $score += 15;
        }
        
        // 5. Sở thích chung (30 điểm)
        $totalFactors += 30;
        $interests1 = array_map('trim', explode(',', $user1['soThich'] ?? ''));
        $interests2 = array_map('trim', explode(',', $user2['soThich'] ?? ''));
        $commonInterests = count(array_intersect($interests1, $interests2));
        
        if ($commonInterests > 0) {
            $score += min(30, $commonInterests * 10);
        }
        
        // Tính phần trăm
        $percentage = ($score / $totalFactors) * 100;
        
        return round($percentage);
    }
    
    /**
     * Tìm người phù hợp nhất cho user (dành cho VIP)
     */
    public function findBestMatches($userId, $limit = 10) {
        // Lấy danh sách người dùng đã thích, đã match, đã chặn
        $excludedUsers = $this->getExcludedUsers($userId);
        $excludedUsers[] = $userId; // Loại chính mình
        
        // Tạo placeholder cho NOT IN
        $placeholders = str_repeat('?,', count($excludedUsers) - 1) . '?';
        
        // Lấy hồ sơ người dùng hiện tại
        $currentUser = $this->getUserProfile($userId);
        if (!$currentUser) {
            return [];
        }
        
        // Xác định giới tính tìm kiếm (ngược lại với giới tính của user)
        // CHỈ ghép Nam với Nữ và ngược lại
        $targetGender = '';
        if ($currentUser['gioiTinh'] == 'Nam') {
            $targetGender = 'Nữ';
        } elseif ($currentUser['gioiTinh'] == 'Nữ') {
            $targetGender = 'Nam';
        } else {
            // Nếu là giới tính khác hoặc không xác định, không tìm kiếm
            return [];
        }
        
        // Query lấy danh sách người dùng phù hợp
        // LUÔN LUÔN lọc theo giới tính đối lập
        $sql = "
            SELECT h.*, n.maNguoiDung, n.tenDangNhap
            FROM hoso h
            INNER JOIN nguoidung n ON h.maNguoiDung = n.maNguoiDung
            WHERE n.trangThaiNguoiDung = 'active'
            AND n.maNguoiDung NOT IN ($placeholders)
            AND h.gioiTinh = '$targetGender'
            ORDER BY h.maHoSo DESC 
            LIMIT 50
        ";
        
        $stmt = $this->conn->prepare($sql);
        $types = str_repeat('i', count($excludedUsers));
        $stmt->bind_param($types, ...$excludedUsers);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $candidates = [];
        while ($row = $result->fetch_assoc()) {
            $compatibility = $this->calculateCompatibility($userId, $row['maNguoiDung']);
            $row['compatibility'] = $compatibility;
            $candidates[] = $row;
        }
        
        // Sắp xếp theo độ tương thích
        usort($candidates, function($a, $b) {
            return $b['compatibility'] - $a['compatibility'];
        });
        
        // Lấy top matches
        return array_slice($candidates, 0, $limit);
    }
    
    /**
     * Lấy danh sách user đã thích, đã bị thích, đã chặn
     */
    private function getExcludedUsers($userId) {
        $excluded = [];
        
        // Người đã thích
        $stmt = $this->conn->prepare("
            SELECT maNguoiDuocThich FROM thich WHERE maNguoiThich = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $excluded[] = $row['maNguoiDuocThich'];
        }
        
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
        
        return array_unique($excluded);
    }
    
    /**
     * Lấy thông tin hồ sơ user
     */
    private function getUserProfile($userId) {
        $stmt = $this->conn->prepare("
            SELECT * FROM hoso WHERE maNguoiDung = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Tính tuổi từ ngày sinh
     */
    private function calculateAge($birthDate) {
        if (!$birthDate) return 0;
        $birth = new DateTime($birthDate);
        $now = new DateTime();
        $age = $now->diff($birth)->y;
        return $age;
    }
    
    /**
     * Lấy lý do tương thích
     */
    public function getCompatibilityReasons($userId1, $userId2) {
        $user1 = $this->getUserProfile($userId1);
        $user2 = $this->getUserProfile($userId2);
        
        if (!$user1 || !$user2) {
            return [];
        }
        
        $reasons = [];
        
        // Kiểm tra độ tuổi
        $age1 = $this->calculateAge($user1['ngaySinh']);
        $age2 = $this->calculateAge($user2['ngaySinh']);
        $ageDiff = abs($age1 - $age2);
        
        if ($ageDiff <= 5) {
            $reasons[] = "Độ tuổi phù hợp ({$age1} và {$age2} tuổi)";
        }
        
        // Kiểm tra học vấn
        if ($user1['hocVan'] == $user2['hocVan']) {
            $reasons[] = "Cùng trình độ học vấn: {$user1['hocVan']}";
        }
        
        // Kiểm tra mục tiêu
        if ($user1['mucTieuPhatTrien'] == $user2['mucTieuPhatTrien']) {
            $reasons[] = "Cùng mục tiêu: {$user1['mucTieuPhatTrien']}";
        }
        
        // Kiểm tra nơi sống
        if ($user1['noiSong'] == $user2['noiSong']) {
            $reasons[] = "Cùng sống ở: {$user1['noiSong']}";
        }
        
        // Kiểm tra sở thích
        $interests1 = array_map('trim', explode(',', $user1['soThich'] ?? ''));
        $interests2 = array_map('trim', explode(',', $user2['soThich'] ?? ''));
        $commonInterests = array_intersect($interests1, $interests2);
        
        if (count($commonInterests) > 0) {
            $reasons[] = "Sở thích chung: " . implode(', ', array_slice($commonInterests, 0, 3));
        }
        
        return $reasons;
    }
}
?>
