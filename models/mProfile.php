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
     * Loại trừ: chính mình, người đã thích, người đã thích mình
     * Sử dụng random offset thay vì ORDER BY RAND() để tăng hiệu năng
     */
    public function getAllProfiles($limit = 12, $offset = 0, $excludeUserIds = []) {
        // Tạo placeholder cho excludeUserIds
        $excludeCondition = '';
        if (!empty($excludeUserIds)) {
            $placeholders = str_repeat('?,', count($excludeUserIds) - 1) . '?';
            $excludeCondition = " AND n.maNguoiDung NOT IN ($placeholders)";
        }
        
        // Đếm tổng số records để tính random offset
        $countQuery = "
            SELECT COUNT(*) as total
            FROM HoSo h
            INNER JOIN NguoiDung n ON h.maNguoiDung = n.maNguoiDung
            WHERE n.trangThaiNguoiDung = 'active'
            $excludeCondition
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
            FROM HoSo h
            INNER JOIN NguoiDung n ON h.maNguoiDung = n.maNguoiDung
            WHERE n.trangThaiNguoiDung = 'active'
            $excludeCondition
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
    public function searchProfiles($filters, $excludeUserIds = [], $limit = 20) {
        // Base query
        $sql = "
            SELECT h.*, n.maNguoiDung, n.tenDangNhap
            FROM HoSo h
            INNER JOIN NguoiDung n ON h.maNguoiDung = n.maNguoiDung
            WHERE n.trangThaiNguoiDung = 'active'
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
        
        // Filter theo giới tính
        if (!empty($filters['gender'])) {
            $genderMap = [
                'male' => 'Nam',
                'female' => 'Nu',
                'other' => 'Khac'
            ];
            if (isset($genderMap[$filters['gender']])) {
                $sql .= " AND h.gioiTinh = ?";
                $types .= 's';
                $params[] = $genderMap[$filters['gender']];
            }
        }
        
        // Filter theo tình trạng hôn nhân
        if (!empty($filters['status'])) {
            $statusMap = [
                'single' => 'Độc thân',
                'divorced' => 'Đã ly hôn',
                'widowed' => 'Góa',
                'separated' => 'Ly thân'
            ];
            if (isset($statusMap[$filters['status']])) {
                $sql .= " AND h.tinhTrangHonNhan = ?";
                $types .= 's';
                $params[] = $statusMap[$filters['status']];
            }
        }
        
        // Filter theo mục tiêu
        if (!empty($filters['purpose'])) {
            $purposeMap = [
                'relationship' => 'Hẹn hò',
                'friendship' => 'Kết bạn',
                'marriage' => 'Kết hôn',
                'casual' => 'Tìm hiểu'
            ];
            if (isset($purposeMap[$filters['purpose']])) {
                $sql .= " AND h.mucTieuPhatTrien = ?";
                $types .= 's';
                $params[] = $purposeMap[$filters['purpose']];
            }
        }
        
        // Filter theo thành phố
        if (!empty($filters['city'])) {
            $cityMap = [
                'hcm' => 'TP. Hồ Chí Minh',
                'hn' => 'Hà Nội',
                'dn' => 'Đà Nẵng',
                'hp' => 'Hải Phòng',
                'ct' => 'Cần Thơ'
            ];
            if (isset($cityMap[$filters['city']])) {
                $sql .= " AND h.noiSong LIKE ?";
                $types .= 's';
                $params[] = '%' . $cityMap[$filters['city']] . '%';
            }
        }
        
        // Filter theo sở thích
        if (!empty($filters['interest'])) {
            $interestMap = [
                'travel' => 'Du lịch',
                'music' => 'Nghe nhạc',
                'sport' => 'Thể thao',
                'cooking' => 'Nấu ăn',
                'reading' => 'Đọc sách',
                'movie' => 'Xem phim'
            ];
            if (isset($interestMap[$filters['interest']])) {
                $sql .= " AND h.soThich LIKE ?";
                $types .= 's';
                $params[] = '%' . $interestMap[$filters['interest']] . '%';
            }
        }
        
        // Filter theo độ tuổi
        if (!empty($filters['age'])) {
            $now = new DateTime();
            
            switch ($filters['age']) {
                case '18-25':
                    $maxDate = $now->modify('-18 years')->format('Y-m-d');
                    $minDate = (new DateTime())->modify('-25 years')->format('Y-m-d');
                    break;
                case '25-30':
                    $maxDate = (new DateTime())->modify('-25 years')->format('Y-m-d');
                    $minDate = (new DateTime())->modify('-30 years')->format('Y-m-d');
                    break;
                case '30-35':
                    $maxDate = (new DateTime())->modify('-30 years')->format('Y-m-d');
                    $minDate = (new DateTime())->modify('-35 years')->format('Y-m-d');
                    break;
                case '35-40':
                    $maxDate = (new DateTime())->modify('-35 years')->format('Y-m-d');
                    $minDate = (new DateTime())->modify('-40 years')->format('Y-m-d');
                    break;
                case '40+':
                    $maxDate = (new DateTime())->modify('-40 years')->format('Y-m-d');
                    $minDate = '1900-01-01';
                    break;
            }
            
            if (isset($minDate) && isset($maxDate)) {
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
