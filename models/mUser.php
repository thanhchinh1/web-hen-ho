<?php
require_once 'mDbconnect.php';

class User {
    private $conn;
    
    public function __construct() {
        $db = new clsConnect();
        $this->conn = $db->connect();
    }
    
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
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
        $stmt = $this->conn->prepare("SELECT maNguoiDung, matKhau FROM nguoidung WHERE tenDangNhap = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['matKhau'])) {
                return $user['maNguoiDung']; // Trả về ID của user
            }
        }
        return false;
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
        return $user && $user['trangThaiNguoiDung'] === 'hoat_dong';
    }
}
?>