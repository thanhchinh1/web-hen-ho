<?php
require_once 'mDbconnect.php';

class User {
    private $conn;
    
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
        
        // Mã hóa mật khẩu
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
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
        $stmt = $this->conn->prepare("SELECT maNguoiDung, matKhau, trangThaiNguoiDung FROM nguoidung WHERE tenDangNhap = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Kiểm tra mật khẩu
            if (password_verify($password, $user['matKhau'])) {
                // Kiểm tra trạng thái tài khoản
                if ($user['trangThaiNguoiDung'] === 'banned' || $user['trangThaiNguoiDung'] === 'locked') {
                    return ['status' => 'banned', 'message' => 'Tài khoản của bạn đã bị khóa do vi phạm chính sách. Vui lòng liên hệ admin để biết thêm chi tiết.'];
                }
                
                return ['status' => 'success', 'userId' => $user['maNguoiDung']]; // Trả về ID của user
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
            return password_verify($password, $user['matKhau']);
        }
        return false;
    }
    
    /**
     * Cập nhật mật khẩu mới
     */
    public function updatePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $this->conn->prepare("UPDATE nguoidung SET matKhau = ? WHERE maNguoiDung = ?");
        $stmt->bind_param("si", $hashedPassword, $userId);
        
        return $stmt->execute();
    }
    
    /**
     * Kiểm tra user có online không (hoạt động trong 5 phút gần đây)
     */
    public function isUserOnline($userId) {
        $stmt = $this->conn->prepare("
            SELECT TIMESTAMPDIFF(MINUTE, lanHoatDongCuoi, NOW()) as minutesAgo
            FROM nguoidung 
            WHERE maNguoiDung = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
            // Online nếu hoạt động trong vòng 5 phút
            return $data['minutesAgo'] !== null && $data['minutesAgo'] <= 5;
        }
        return false;
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
}
?>