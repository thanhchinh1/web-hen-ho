<?php
require_once 'mDbconnect.php';

class VIP {
    private $conn;
    
    public function __construct() {
        $db = clsConnect::getInstance();
        $this->conn = $db->connect();
    }
    
    /**
     * Kiểm tra người dùng có VIP không
     */
    public function isVIP($userId) {
        $stmt = $this->conn->prepare("
            SELECT * FROM goidangky 
            WHERE maNguoiDung = ? 
            AND loaiGoi = 'VIP' 
            AND trangThaiGoi = 'Active'
            AND (ngayHetHan IS NULL OR ngayHetHan > CURDATE())
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
    
    /**
     * Lấy thông tin gói VIP hiện tại
     */
    public function getCurrentVIPPackage($userId) {
        $stmt = $this->conn->prepare("
            SELECT * FROM goidangky 
            WHERE maNguoiDung = ? 
            AND loaiGoi = 'VIP' 
            AND trangThaiGoi = 'Active'
            ORDER BY maGoiDangKy DESC
            LIMIT 1
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Tạo gói VIP mới cho người dùng
     */
    public function createVIPPackage($userId, $months = 1) {
        // Kiểm tra xem có gói Active không
        $currentPackage = $this->getCurrentVIPPackage($userId);
        
        if ($currentPackage) {
            // Nếu đã có gói active, gia hạn thêm
            return $this->extendVIPPackage($userId, $months);
        }
        
        // Tạo gói mới
        $ngayHetHan = date('Y-m-d', strtotime("+$months months"));
        
        $stmt = $this->conn->prepare("
            INSERT INTO goidangky (maNguoiDung, loaiGoi, trangThaiGoi, ngayHetHan, thoiDiemTao)
            VALUES (?, 'VIP', 'Active', ?, NOW())
        ");
        $stmt->bind_param("is", $userId, $ngayHetHan);
        
        return $stmt->execute();
    }
    
    /**
     * Gia hạn gói VIP
     */
    public function extendVIPPackage($userId, $months = 1) {
        $currentPackage = $this->getCurrentVIPPackage($userId);
        
        if (!$currentPackage) {
            return $this->createVIPPackage($userId, $months);
        }
        
        // Tính ngày hết hạn mới
        $currentExpiry = $currentPackage['ngayHetHan'];
        
        // Nếu ngày hết hạn còn trong tương lai, cộng thêm từ ngày đó
        if (strtotime($currentExpiry) > time()) {
            $newExpiry = date('Y-m-d', strtotime($currentExpiry . " +$months months"));
        } else {
            // Nếu đã hết hạn, tính từ hôm nay
            $newExpiry = date('Y-m-d', strtotime("+$months months"));
        }
        
        $stmt = $this->conn->prepare("
            UPDATE goidangky 
            SET ngayHetHan = ?, trangThaiGoi = 'Active'
            WHERE maGoiDangKy = ?
        ");
        $stmt->bind_param("si", $newExpiry, $currentPackage['maGoiDangKy']);
        
        return $stmt->execute();
    }
    
    /**
     * Tạo bản ghi thanh toán
     */
    public function createPayment($userId, $amount) {
        $stmt = $this->conn->prepare("
            INSERT INTO thanhtoan (maNguoiThanhToan, soTien, thoiDiemThanhToan)
            VALUES (?, ?, NOW())
        ");
        $stmt->bind_param("id", $userId, $amount);
        
        return $stmt->execute();
    }
    
    /**
     * Lấy lịch sử thanh toán
     */
    public function getPaymentHistory($userId, $limit = 10) {
        $stmt = $this->conn->prepare("
            SELECT * FROM thanhtoan 
            WHERE maNguoiThanhToan = ?
            ORDER BY thoiDiemThanhToan DESC
            LIMIT ?
        ");
        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        
        return $history;
    }
    
    /**
     * Kiểm tra và cập nhật các gói hết hạn
     */
    public function updateExpiredPackages() {
        $stmt = $this->conn->prepare("
            UPDATE goidangky 
            SET trangThaiGoi = 'Expired'
            WHERE trangThaiGoi = 'Active' 
            AND loaiGoi = 'VIP'
            AND ngayHetHan < CURDATE()
        ");
        
        return $stmt->execute();
    }
    
    /**
     * Lấy giá gói VIP theo số tháng
     */
    public function getVIPPrice($months = 1) {
        $prices = [
            1 => 99000,   // 1 tháng
            3 => 249000,  // 3 tháng (giảm 16%)
            6 => 449000,  // 6 tháng (giảm 24%)
            12 => 799000  // 12 tháng (giảm 33%)
        ];
        
        return $prices[$months] ?? $prices[1];
    }
    
    /**
     * Lấy số ngày còn lại của gói VIP
     */
    public function getDaysRemaining($userId) {
        $package = $this->getCurrentVIPPackage($userId);
        
        if (!$package || !$package['ngayHetHan']) {
            return 0;
        }
        
        $today = new DateTime();
        $expiry = new DateTime($package['ngayHetHan']);
        $diff = $today->diff($expiry);
        
        return $diff->days;
    }
}
?>
