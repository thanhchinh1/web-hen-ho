<?php
require_once 'mDbconnect.php';

class Profile {
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
    
    /**
     * Kiểm tra xem người dùng đã có hồ sơ chưa
     */
    public function hasProfile($userId) {
        $stmt = $this->conn->prepare("SELECT maHoSo FROM HoSo WHERE maNguoiDung = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
    
    /**
     * Lấy thông tin hồ sơ của người dùng
     */
    public function getProfile($userId) {
        $stmt = $this->conn->prepare("SELECT * FROM HoSo WHERE maNguoiDung = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Tạo hồ sơ mới cho người dùng
     */
    public function createProfile($userId, $data, $avatarPath) {
        // Kiểm tra xem đã có hồ sơ chưa
        if ($this->hasProfile($userId)) {
            return false;
        }
        
        $stmt = $this->conn->prepare("
            INSERT INTO HoSo (
                maNguoiDung, ten, ngaySinh, gioiTinh, tinhTrangHonNhan,
                canNang, chieuCao, mucTieuPhatTrien, hocVan, noiSong,
                soThich, moTa, avt
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param(
            "issssddssssss",
            $userId,
            $data['ten'],
            $data['ngaySinh'],
            $data['gioiTinh'],
            $data['tinhTrangHonNhan'],
            $data['canNang'],
            $data['chieuCao'],
            $data['mucTieuPhatTrien'],
            $data['hocVan'],
            $data['noiSong'],
            $data['soThich'],
            $data['moTa'],
            $avatarPath
        );
        
        return $stmt->execute();
    }
    
    /**
     * Cập nhật hồ sơ của người dùng
     */
    public function updateProfile($userId, $data, $avatarPath = null) {
        // Nếu có avatar mới thì cập nhật, không thì giữ nguyên
        if ($avatarPath) {
            $stmt = $this->conn->prepare("
                UPDATE HoSo SET
                    ten = ?,
                    ngaySinh = ?,
                    gioiTinh = ?,
                    tinhTrangHonNhan = ?,
                    canNang = ?,
                    chieuCao = ?,
                    mucTieuPhatTrien = ?,
                    hocVan = ?,
                    noiSong = ?,
                    soThich = ?,
                    moTa = ?,
                    avt = ?
                WHERE maNguoiDung = ?
            ");
            
            $stmt->bind_param(
                "ssssddssssssi",
                $data['ten'],
                $data['ngaySinh'],
                $data['gioiTinh'],
                $data['tinhTrangHonNhan'],
                $data['canNang'],
                $data['chieuCao'],
                $data['mucTieuPhatTrien'],
                $data['hocVan'],
                $data['noiSong'],
                $data['soThich'],
                $data['moTa'],
                $avatarPath,
                $userId
            );
        } else {
            $stmt = $this->conn->prepare("
                UPDATE HoSo SET
                    ten = ?,
                    ngaySinh = ?,
                    gioiTinh = ?,
                    tinhTrangHonNhan = ?,
                    canNang = ?,
                    chieuCao = ?,
                    mucTieuPhatTrien = ?,
                    hocVan = ?,
                    noiSong = ?,
                    soThich = ?,
                    moTa = ?
                WHERE maNguoiDung = ?
            ");
            
            $stmt->bind_param(
                "ssssddsssssi",
                $data['ten'],
                $data['ngaySinh'],
                $data['gioiTinh'],
                $data['tinhTrangHonNhan'],
                $data['canNang'],
                $data['chieuCao'],
                $data['mucTieuPhatTrien'],
                $data['hocVan'],
                $data['noiSong'],
                $data['soThich'],
                $data['moTa'],
                $userId
            );
        }
        
        return $stmt->execute();
    }
    
    /**
     * Lấy danh sách hồ sơ để hiển thị trên trang chủ
     */
    public function getAllProfiles($limit = 12, $offset = 0) {
        $stmt = $this->conn->prepare("
            SELECT h.*, n.maNguoiDung 
            FROM HoSo h
            INNER JOIN NguoiDung n ON h.maNguoiDung = n.maNguoiDung
            WHERE n.trangThaiNguoiDung = 'active'
            ORDER BY RAND()
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $profiles = [];
        while ($row = $result->fetch_assoc()) {
            $profiles[] = $row;
        }
        
        return $profiles;
    }
    
    /**
     * Tính tuổi từ ngày sinh
     */
    public function calculateAge($birthDate) {
        $birth = new DateTime($birthDate);
        $now = new DateTime();
        $age = $now->diff($birth)->y;
        return $age;
    }
}
?>
