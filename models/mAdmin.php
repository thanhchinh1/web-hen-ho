<?php
require_once 'mDbconnect.php';

class Admin {
    private $conn;
    
    public function __construct() {
        $db = clsConnect::getInstance();
        $this->conn = $db->connect();
    }
    
    /**
     * Đăng nhập admin
     */
    public function login($username, $password) {
        $stmt = $this->conn->prepare("
            SELECT maAdmin, matKhau, hoTen, vaiTro, trangThai
            FROM Admin
            WHERE tenDangNhap = ?
        ");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return false;
        }
        
        $admin = $result->fetch_assoc();
        
        // Kiểm tra trạng thái
        if ($admin['trangThai'] !== 'active') {
            return false;
        }
        
        // Verify password
        if (!password_verify($password, $admin['matKhau'])) {
            return false;
        }
        
        // Cập nhật lần đăng nhập cuối
        $stmt = $this->conn->prepare("
            UPDATE Admin 
            SET lanDangNhapCuoi = NOW()
            WHERE maAdmin = ?
        ");
        $stmt->bind_param("i", $admin['maAdmin']);
        $stmt->execute();
        
        // Log đăng nhập
        $this->logAction($admin['maAdmin'], 'login', 'Đăng nhập hệ thống');
        
        return $admin;
    }
    
    /**
     * Lấy thông tin admin
     */
    public function getAdminInfo($adminId) {
        $stmt = $this->conn->prepare("
            SELECT maAdmin, tenDangNhap, hoTen, email, soDienThoai, vaiTro, lanDangNhapCuoi
            FROM Admin
            WHERE maAdmin = ?
        ");
        $stmt->bind_param("i", $adminId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Đổi mật khẩu
     */
    public function changePassword($adminId, $oldPassword, $newPassword) {
        // Lấy mật khẩu hiện tại
        $stmt = $this->conn->prepare("SELECT matKhau FROM Admin WHERE maAdmin = ?");
        $stmt->bind_param("i", $adminId);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        
        // Verify old password
        if (!password_verify($oldPassword, $admin['matKhau'])) {
            return false;
        }
        
        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update
        $stmt = $this->conn->prepare("
            UPDATE Admin 
            SET matKhau = ?
            WHERE maAdmin = ?
        ");
        $stmt->bind_param("si", $hashedPassword, $adminId);
        $success = $stmt->execute();
        
        if ($success) {
            $this->logAction($adminId, 'change_password', 'Đổi mật khẩu');
        }
        
        return $success;
    }
    
    /**
     * Log hành động admin
     */
    public function logAction($adminId, $action, $details = null) {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        $stmt = $this->conn->prepare("
            INSERT INTO AdminLog (maAdmin, hanhDong, chiTiet, ipAddress)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isss", $adminId, $action, $details, $ipAddress);
        return $stmt->execute();
    }
    
    /**
     * Lấy danh sách tất cả người dùng
     */
    public function getAllUsers($limit = 50, $offset = 0, $search = '') {
        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $stmt = $this->conn->prepare("
                SELECT nd.*, h.ten, h.gioiTinh, h.ngaySinh, h.avt, g.loaiGoi
                FROM NguoiDung nd
                LEFT JOIN HoSo h ON nd.maNguoiDung = h.maNguoiDung
                LEFT JOIN GoiDangKy g ON nd.maNguoiDung = g.maNguoiDung AND g.trangThaiGoi = 'Active'
                WHERE nd.tenDangNhap LIKE ? 
                   OR h.ten LIKE ?
                ORDER BY nd.maNguoiDung DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->bind_param("ssii", $searchTerm, $searchTerm, $limit, $offset);
        } else {
            $stmt = $this->conn->prepare("
                SELECT nd.*, h.ten, h.gioiTinh, h.ngaySinh, h.avt, g.loaiGoi
                FROM NguoiDung nd
                LEFT JOIN HoSo h ON nd.maNguoiDung = h.maNguoiDung
                LEFT JOIN GoiDangKy g ON nd.maNguoiDung = g.maNguoiDung AND g.trangThaiGoi = 'Active'
                ORDER BY nd.maNguoiDung DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->bind_param("ii", $limit, $offset);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        return $users;
    }
    
    /**
     * Đếm tổng số người dùng
     */
    public function getTotalUsers($search = '') {
        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $stmt = $this->conn->prepare("
                SELECT COUNT(DISTINCT nd.maNguoiDung) as total
                FROM NguoiDung nd
                LEFT JOIN HoSo h ON nd.maNguoiDung = h.maNguoiDung
                WHERE nd.tenDangNhap LIKE ? 
                   OR h.ten LIKE ?
            ");
            $stmt->bind_param("ss", $searchTerm, $searchTerm);
        } else {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM NguoiDung");
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    
    /**
     * Khóa/Mở khóa tài khoản người dùng
     */
    public function toggleUserStatus($adminId, $userId, $newStatus = null) {
        if ($newStatus) {
            // Set trạng thái cụ thể
            $stmt = $this->conn->prepare("
                UPDATE NguoiDung 
                SET trangThaiNguoiDung = ?
                WHERE maNguoiDung = ?
            ");
            $stmt->bind_param("si", $newStatus, $userId);
        } else {
            // Toggle giữa active và banned
            $stmt = $this->conn->prepare("
                UPDATE NguoiDung 
                SET trangThaiNguoiDung = IF(trangThaiNguoiDung = 'active', 'banned', 'active')
                WHERE maNguoiDung = ?
            ");
            $stmt->bind_param("i", $userId);
        }
        
        $success = $stmt->execute();
        
        if ($success) {
            $action = $newStatus === 'banned' ? 'ban_user' : 'unban_user';
            $this->logAction($adminId, $action, "User ID: $userId");
        }
        
        return $success;
    }
    
    /**
     * Thống kê tổng quan
     */
    public function getDashboardStats() {
        $stats = [];
        
        // Tổng người dùng
        $result = $this->conn->query("SELECT COUNT(*) as total FROM NguoiDung");
        $stats['totalUsers'] = $result->fetch_assoc()['total'];
        
        // Người dùng mới hôm nay (không có cột ngayTao, tạm set 0)
        // TODO: Thêm cột ngayTao vào bảng NguoiDung nếu cần
        $stats['newUsersToday'] = 0;
        
        // Tổng ghép đôi
        $result = $this->conn->query("
            SELECT COUNT(*) as total FROM GhepDoi 
            WHERE trangThaiGhepDoi = 'matched'
        ");
        $stats['totalMatches'] = $result->fetch_assoc()['total'] ?? 0;
        
        // Ghép đôi mới hôm nay
        $result = $this->conn->query("
            SELECT COUNT(*) as total FROM GhepDoi 
            WHERE trangThaiGhepDoi = 'matched' 
            AND DATE(thoiDiemGhepDoi) = CURDATE()
        ");
        $stats['newMatchesToday'] = $result->fetch_assoc()['total'] ?? 0;
        
        // Báo cáo chờ xử lý
        $result = $this->conn->query("
            SELECT COUNT(*) as total FROM BaoCao 
            WHERE trangThai = 'pending'
        ");
        $stats['pendingReports'] = $result->fetch_assoc()['total'] ?? 0;
        
        // Tổng tin nhắn
        $result = $this->conn->query("SELECT COUNT(*) as total FROM TinNhan");
        $stats['totalMessages'] = $result->fetch_assoc()['total'] ?? 0;
        
        return $stats;
    }
}
?>
