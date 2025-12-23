<?php
// Set timezone to Vietnam
date_default_timezone_set('Asia/Ho_Chi_Minh');

require_once 'mDbconnect.php';

class User {
    private $conn;
    private $onlineStatusCache = []; // Cache trạng thái online
    private $cacheTimeout = 30; // Cache 30 giây
    
    public function __construct() {
        $db = clsConnect::getInstance();
        $this->conn = $db->connect();
    }
    
    // Kiểm tra email/SĐT đã tồn tại chưa
    public function checkEmailExists($email) {
        $stmt = $this->conn->prepare("SELECT maNguoiDung FROM nguoidung WHERE tenDangNhap = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
    
    // Đăng ký người dùng mới
    public function register($email, $password) {
        // Kiểm tra email đã tồn tại
        if ($this->checkEmailExists($email)) {
            return false;
        }
        
        // Mã hóa mật khẩu bằng MD5
        $hashedPassword = md5($password);
        
        // Thêm người dùng mới
        $stmt = $this->conn->prepare("INSERT INTO nguoidung (tenDangNhap, matKhau) VALUES (?, ?)");
        $stmt->bind_param("ss", $email, $hashedPassword);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id; // Trả về ID của user vừa tạo
        }
        return false;
    }
    
    // Đăng nhập
    public function login($email, $password) {
        $stmt = $this->conn->prepare("SELECT maNguoiDung, matKhau, trangThaiNguoiDung, role, email_verified FROM nguoidung WHERE tenDangNhap = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Kiểm tra mật khẩu bằng MD5
            if (md5($password) === $user['matKhau']) {
                // Kiểm tra email đã xác thực chưa
                if ($user['email_verified'] == 0) {
                    return ['status' => 'not_verified', 'message' => 'Email chưa được xác thực. Vui lòng kiểm tra email và hoàn tất xác thực!'];
                }
                
                // Kiểm tra trạng thái tài khoản
                if ($user['trangThaiNguoiDung'] === 'banned' || $user['trangThaiNguoiDung'] === 'locked') {
                    return ['status' => 'banned', 'message' => 'Tài khoản của bạn đã bị khóa do vi phạm chính sách. Vui lòng liên hệ admin để biết thêm chi tiết.'];
                }
                
                // Cập nhật thời gian hoạt động khi đăng nhập thành công
                $this->updateOnlineStatus($user['maNguoiDung']);
                
                return [
                    'status' => 'success', 
                    'userId' => $user['maNguoiDung'],
                    'role' => $user['role']
                ]; // Trả về ID và role của user
            }
        }
        return ['status' => 'error', 'message' => 'Email/Số điện thoại hoặc mật khẩu không đúng!'];
    }
    
    // Lấy thông tin người dùng theo ID
    public function getUserById($userId) {
        $stmt = $this->conn->prepare("SELECT * FROM nguoidung WHERE maNguoiDung = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    // Kiểm tra xem user đã thiết lập hồ sơ chưa
    public function hasProfile($userId) {
        $stmt = $this->conn->prepare("SELECT trangThaiNguoiDung FROM nguoidung WHERE maNguoiDung = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        // Kiểm tra trạng thái hoặc các trường quan trọng khác
        return $user && $user['trangThaiNguoiDung'] === 'active';
    }
    
    /**
     * Xác minh mật khẩu hiện tại của user
     */
    public function verifyPassword($userId, $password) {
        $stmt = $this->conn->prepare("SELECT matKhau FROM nguoidung WHERE maNguoiDung = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            return md5($password) === $user['matKhau'];
        }
        return false;
    }
    
    /**
     * Cập nhật mật khẩu mới
     */
    public function updatePassword($userId, $newPassword) {
        $hashedPassword = md5($newPassword);
        
        $stmt = $this->conn->prepare("UPDATE nguoidung SET matKhau = ? WHERE maNguoiDung = ?");
        $stmt->bind_param("si", $hashedPassword, $userId);
        
        return $stmt->execute();
    }
    
    /**
     * Kiểm tra user có online không (hoạt động trong 5 phút gần đây)
     * Có cache để tối ưu performance
     */
    public function isUserOnline($userId) {
        // Kiểm tra cache
        $cacheKey = 'online_' . $userId;
        if (isset($this->onlineStatusCache[$cacheKey])) {
            $cached = $this->onlineStatusCache[$cacheKey];
            // Nếu cache chưa hết hạn (30 giây)
            if ((time() - $cached['time']) < $this->cacheTimeout) {
                return $cached['value'];
            }
        }
        
        $stmt = $this->conn->prepare("
            SELECT lanHoatDongCuoi,
                   TIMESTAMPDIFF(MINUTE, lanHoatDongCuoi, NOW()) as minutesAgo
            FROM nguoidung 
            WHERE maNguoiDung = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $isOnline = false;
        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
            // Nếu lanHoatDongCuoi là NULL (đã đăng xuất), return false
            if ($data['lanHoatDongCuoi'] === null) {
                $isOnline = false;
            } else {
                // Online nếu hoạt động trong vòng 5 phút
                $isOnline = $data['minutesAgo'] !== null && $data['minutesAgo'] <= 5;
            }
        }
        
        // Lưu vào cache
        $this->onlineStatusCache[$cacheKey] = [
            'value' => $isOnline,
            'time' => time()
        ];
        
        return $isOnline;
    }
    
    /**
     * Lấy thời gian hoạt động cuối của user
     */
    public function getLastActivity($userId) {
        $stmt = $this->conn->prepare("
            SELECT lanHoatDongCuoi,
                   TIMESTAMPDIFF(MINUTE, lanHoatDongCuoi, NOW()) as minutesAgo
            FROM nguoidung 
            WHERE maNguoiDung = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }
    
    /**
     * Kiểm tra user có offline lâu không (> 2 giờ)
     */
    public function isUserInactive($userId) {
        $lastActivity = $this->getLastActivity($userId);
        
        if (!$lastActivity || $lastActivity['lanHoatDongCuoi'] === null) {
            return true; // Đã đăng xuất hoặc chưa từng hoạt động
        }
        
        // Offline nếu không hoạt động quá 2 giờ (120 phút)
        return $lastActivity['minutesAgo'] !== null && $lastActivity['minutesAgo'] > 120;
    }
    
    /**
     * Cập nhật trạng thái online khi đăng nhập
     */
    public function updateOnlineStatus($userId) {
        $stmt = $this->conn->prepare("
            UPDATE nguoidung 
            SET lanHoatDongCuoi = NOW() 
            WHERE maNguoiDung = ?
        ");
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }
    
    /**
     * Cập nhật trạng thái offline khi đăng xuất
     */
    public function updateOfflineStatus($userId) {
        $stmt = $this->conn->prepare("
            UPDATE nguoidung 
            SET lanHoatDongCuoi = NULL
            WHERE maNguoiDung = ?
        ");
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }
}
?>