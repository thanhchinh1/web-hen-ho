<?php
require_once 'mDbconnect.php';

class Profile {
    private $conn;
    
    public function __construct() {
        $db = clsConnect::getInstance();
        $this->conn = $db->connect();
    }
    
    /**
     * Kiểm tra xem người dùng đã có hồ sơ chưa
     */
    public function hasProfile($userId) {
        $stmt = $this->conn->prepare("SELECT maHoSo FROM hoso WHERE maNguoiDung = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
    
    /**
     * Lấy thông tin hồ sơ của người dùng
     */
    public function getProfile($userId) {
        $stmt = $this->conn->prepare("SELECT * FROM hoso WHERE maNguoiDung = ?");
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
            INSERT INTO hoso (
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
                UPDATE hoso SET
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
                UPDATE hoso SET
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
     * Loại trừ: chính mình, người đã thích, người đã thích mình
     * Sử dụng random offset thay vì ORDER BY RAND() để tăng hiệu năng
     */
    public function getAllProfiles($limit = 12, $offset = 0, $excludeUserIds = [], $currentUserGender = null) {
        // Tạo placeholder cho excludeUserIds
        $excludeCondition = '';
        if (!empty($excludeUserIds)) {
            $placeholders = str_repeat('?,', count($excludeUserIds) - 1) . '?';
            $excludeCondition = " AND n.maNguoiDung NOT IN ($placeholders)";
        }
        
        // Thêm điều kiện lọc giới tính đối lập
        $genderCondition = '';
        $targetGender = null;
        if ($currentUserGender === 'Nam') {
            $targetGender = 'Nữ';
            $genderCondition = " AND h.gioiTinh = 'Nữ'";
        } elseif ($currentUserGender === 'Nữ') {
            $targetGender = 'Nam';
            $genderCondition = " AND h.gioiTinh = 'Nam'";
        }
        
        // Đếm tổng số records để tính random offset
        $countQuery = "
            SELECT COUNT(*) as total
            FROM hoso h
            INNER JOIN nguoidung n ON h.maNguoiDung = n.maNguoiDung
            WHERE n.trangThaiNguoiDung = 'active'
            $excludeCondition
            $genderCondition
        ";
        
        $countStmt = $this->conn->prepare($countQuery);
        
        if (!empty($excludeUserIds)) {
            $types = str_repeat('i', count($excludeUserIds));
            $countStmt->bind_param($types, ...$excludeUserIds);
        }
        
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalRecords = $countResult->fetch_assoc()['total'];
        
        // Nếu không có đủ records, return empty
        if ($totalRecords == 0) {
            return [];
        }
        
        // Tính random offset (nhanh hơn ORDER BY RAND())
        // Random offset trong khoảng [0, max(0, total - limit)]
        $maxOffset = max(0, $totalRecords - $limit);
        $randomOffset = $maxOffset > 0 ? rand(0, $maxOffset) : 0;
        
        // Query với random offset thay vì ORDER BY RAND()
        $query = "
            SELECT h.*, n.maNguoiDung 
            FROM hoso h
            INNER JOIN nguoidung n ON h.maNguoiDung = n.maNguoiDung
            WHERE n.trangThaiNguoiDung = 'active'
            $excludeCondition
            $genderCondition
            ORDER BY h.maHoSo
            LIMIT ? OFFSET ?
        ";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        if (!empty($excludeUserIds)) {
            $types = str_repeat('i', count($excludeUserIds)) . 'ii';
            $params = array_merge($excludeUserIds, [$limit, $randomOffset]);
            $stmt->bind_param($types, ...$params);
        } else {
            $stmt->bind_param("ii", $limit, $randomOffset);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $profiles = [];
        while ($row = $result->fetch_assoc()) {
            $profiles[] = $row;
        }
        
        // Shuffle kết quả để thêm random (nhanh vì chỉ shuffle 12 items)
        shuffle($profiles);
        
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
    
    /**
     * Tìm kiếm hồ sơ với bộ lọc nâng cao
     */
    public function searchProfiles($filters, $excludeUserIds = [], $limit = 20, $currentUserGender = null) {
        // Base query
        $sql = "
            SELECT h.*, n.maNguoiDung, n.tenDangNhap
            FROM hoso h
            INNER JOIN nguoidung n ON h.maNguoiDung = n.maNguoiDung
            WHERE n.trangThaiNguoiDung = 'active' AND n.role != 'admin'
        ";
        
        $params = [];
        $types = '';
        
        // Loại trừ các user
        if (!empty($excludeUserIds)) {
            $placeholders = str_repeat('?,', count($excludeUserIds) - 1) . '?';
            $sql .= " AND n.maNguoiDung NOT IN ($placeholders)";
            $types .= str_repeat('i', count($excludeUserIds));
            $params = array_merge($params, $excludeUserIds);
        }
        
        // Filter theo giới tính (dùng giá trị database trực tiếp)
        // Nếu không chỉ định filter giới tính, sử dụng giới tính đối lập mặc định
        if (!empty($filters['gender']) && $filters['gender'] !== 'all') {
            $sql .= " AND h.gioiTinh = ?";
            $types .= 's';
            $params[] = $filters['gender'];
        } elseif ($currentUserGender) {
            // Nếu không chỉ định, dùng giới tính đối lập mặc định
            if ($currentUserGender === 'Nam') {
                $sql .= " AND h.gioiTinh = 'Nữ'";
            } elseif ($currentUserGender === 'Nữ') {
                $sql .= " AND h.gioiTinh = 'Nam'";
            }
        }
        
        // Filter theo tình trạng hôn nhân (dùng giá trị database trực tiếp)
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $sql .= " AND h.tinhTrangHonNhan = ?";
            $types .= 's';
            $params[] = $filters['status'];
        }
        
        // Filter theo mục tiêu (dùng giá trị database trực tiếp)
        if (!empty($filters['purpose']) && $filters['purpose'] !== 'all') {
            $sql .= " AND h.mucTieuPhatTrien = ?";
            $types .= 's';
            $params[] = $filters['purpose'];
        }
        
        // Filter theo thành phố (dùng giá trị database trực tiếp)
        if (!empty($filters['city']) && $filters['city'] !== 'all') {
            $sql .= " AND h.noiSong = ?";
            $types .= 's';
            $params[] = $filters['city'];
        }
        
        // Filter theo sở thích (có thể có nhiều sở thích)
        if (!empty($filters['interests']) && is_array($filters['interests'])) {
            $interestConditions = [];
            foreach ($filters['interests'] as $interest) {
                $interestConditions[] = "h.soThich LIKE ?";
                $types .= 's';
                $params[] = '%' . $interest . '%';
            }
            if (!empty($interestConditions)) {
                $sql .= " AND (" . implode(' OR ', $interestConditions) . ")";
            }
        }
        
        // Filter theo độ tuổi
        if (!empty($filters['ageRange'])) {
            $now = new DateTime();
            $minDate = null;
            $maxDate = null;
            
            switch ($filters['ageRange']) {
                case '18-25':
                    $maxDate = (clone $now)->modify('-18 years')->format('Y-m-d');
                    $minDate = (clone $now)->modify('-26 years')->format('Y-m-d');
                    break;
                case '26-30':
                    $maxDate = (clone $now)->modify('-26 years')->format('Y-m-d');
                    $minDate = (clone $now)->modify('-31 years')->format('Y-m-d');
                    break;
                case '31-35':
                    $maxDate = (clone $now)->modify('-31 years')->format('Y-m-d');
                    $minDate = (clone $now)->modify('-36 years')->format('Y-m-d');
                    break;
                case '36-40':
                    $maxDate = (clone $now)->modify('-36 years')->format('Y-m-d');
                    $minDate = (clone $now)->modify('-41 years')->format('Y-m-d');
                    break;
                case '41-50':
                    $maxDate = (clone $now)->modify('-41 years')->format('Y-m-d');
                    $minDate = (clone $now)->modify('-51 years')->format('Y-m-d');
                    break;
                case '51-100':
                    $maxDate = (clone $now)->modify('-51 years')->format('Y-m-d');
                    $minDate = '1900-01-01';
                    break;
            }
            
            if ($minDate && $maxDate) {
                $sql .= " AND h.ngaySinh BETWEEN ? AND ?";
                $types .= 'ss';
                $params[] = $minDate;
                $params[] = $maxDate;
            }
        }
        
        // Sắp xếp và limit
        $sql .= " ORDER BY h.maHoSo DESC LIMIT ?";
        $types .= 'i';
        $params[] = $limit;
        
        // Prepare và execute
        $stmt = $this->conn->prepare($sql);
        
        if (!empty($types)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $profiles = [];
        while ($row = $result->fetch_assoc()) {
            $profiles[] = $row;
        }
        
        return $profiles;
    }
}
?>
