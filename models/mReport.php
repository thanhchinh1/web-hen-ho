<?php
require_once 'mDbconnect.php';

class Report {
    private $conn;
    
    public function __construct() {
        $db = clsConnect::getInstance();
        $this->conn = $db->connect();
    }
    
    /**
     * Báo cáo vi phạm
     * @param int $reporterId - Người báo cáo
     * @param int $reportedId - Người bị báo cáo
     * @param string $reason - Lý do báo cáo
     * @param string $type - Loại báo cáo: 'spam', 'fake', 'harassment', 'inappropriate', 'other'
     * @return array - ['success' => bool, 'locked' => bool, 'count' => int]
     */
    public function reportUser($reporterId, $reportedId, $reason, $type = 'other') {
        // Kiểm tra đã báo cáo chưa (trong vòng 30 ngày)
        if ($this->hasReportedRecently($reporterId, $reportedId)) {
            return ['success' => false, 'message' => 'Đã báo cáo gần đây'];
        }
        
        // Bắt đầu transaction
        $this->conn->begin_transaction();
        
        try {
            // Thêm báo cáo
            $stmt = $this->conn->prepare("
                INSERT INTO baocao (maNguoiBaoCao, maNguoiBiBaoCao, lyDoBaoCao, loaiBaoCao)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("iiss", $reporterId, $reportedId, $reason, $type);
            
            if (!$stmt->execute()) {
                throw new Exception("Không thể tạo báo cáo");
            }
            
            // Đếm số lượng báo cáo về user này
            $reportCount = $this->countReportsAgainstUser($reportedId);
            
            // Nếu >= 5 báo cáo, tự động khóa tài khoản
            $accountLocked = false;
            if ($reportCount >= 5) {
                $stmt = $this->conn->prepare("
                    UPDATE nguoidung 
                    SET trangThai = 'locked'
                    WHERE maNguoiDung = ?
                ");
                $stmt->bind_param("i", $reportedId);
                
                if ($stmt->execute()) {
                    $accountLocked = true;
                }
            }
            
            $this->conn->commit();
            
            return [
                'success' => true,
                'locked' => $accountLocked,
                'count' => $reportCount
            ];
            
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Kiểm tra đã báo cáo gần đây chưa (tránh spam)
     */
    public function hasReportedRecently($reporterId, $reportedId, $days = 30) {
        $stmt = $this->conn->prepare("
            SELECT maBaoCao FROM baocao
            WHERE maNguoiBaoCao = ? 
            AND maNguoiBiBaoCao = ?
            AND thoiDiemBaoCao >= DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        $stmt->bind_param("iii", $reporterId, $reportedId, $days);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
    
    /**
     * Lấy danh sách báo cáo của user (để admin xem)
     */
    public function getReportsByUser($reporterId) {
        $stmt = $this->conn->prepare("
            SELECT b.*, h.ten as tenNguoiBiBaoCao, h.avt
            FROM baocao b
            JOIN hoso h ON b.maNguoiBiBaoCao = h.maNguoiDung
            WHERE b.maNguoiBaoCao = ?
            ORDER BY b.thoiDiemBaoCao DESC
        ");
        $stmt->bind_param("i", $reporterId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $reports = [];
        while ($row = $result->fetch_assoc()) {
            $reports[] = $row;
        }
        return $reports;
    }
    
    /**
     * Lấy danh sách báo cáo về một user (để admin xem)
     */
    public function getReportsAgainstUser($userId) {
        $stmt = $this->conn->prepare("
            SELECT b.*, h.ten as tenNguoiBaoCao
            FROM baocao b
            JOIN hoso h ON b.maNguoiBaoCao = h.maNguoiDung
            WHERE b.maNguoiBiBaoCao = ?
            ORDER BY b.thoiDiemBaoCao DESC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $reports = [];
        while ($row = $result->fetch_assoc()) {
            $reports[] = $row;
        }
        return $reports;
    }
    
    /**
     * Đếm số lượng báo cáo về một user
     */
    public function countReportsAgainstUser($userId) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as total FROM baocao
            WHERE maNguoiBiBaoCao = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    
    /**
     * Lấy tất cả báo cáo (cho admin)
     */
    public function getAllReports($limit = 50, $offset = 0, $status = 'all') {
        $sql = "
            SELECT b.*, 
                   h1.ten as reporter_name,
                   nd1.tenDangNhap as reporter_username,
                   h2.ten as reported_name,
                   nd2.tenDangNhap as reported_username,
                   h2.avt as reported_avatar
            FROM baocao b
            LEFT JOIN hoso h1 ON b.maNguoiBaoCao = h1.maNguoiDung
            LEFT JOIN nguoidung nd1 ON b.maNguoiBaoCao = nd1.maNguoiDung
            LEFT JOIN hoso h2 ON b.maNguoiBiBaoCao = h2.maNguoiDung
            LEFT JOIN nguoidung nd2 ON b.maNguoiBiBaoCao = nd2.maNguoiDung
        ";
        
        if ($status !== 'all') {
            $sql .= " WHERE b.trangThai = ? ";
            $sql .= " ORDER BY b.thoiDiemBaoCao DESC LIMIT ? OFFSET ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("sii", $status, $limit, $offset);
        } else {
            $sql .= " ORDER BY b.thoiDiemBaoCao DESC LIMIT ? OFFSET ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $limit, $offset);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $reports = [];
        while ($row = $result->fetch_assoc()) {
            $reports[] = $row;
        }
        return $reports;
    }
    
    /**
     * Đếm tổng số báo cáo
     */
    public function getTotalReports($status = 'all') {
        if ($status !== 'all') {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) as total FROM baocao
                WHERE trangThai = ?
            ");
            $stmt->bind_param("s", $status);
        } else {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM baocao");
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }
    
    /**
     * Cập nhật trạng thái báo cáo
     */
    public function updateReportStatus($reportId, $status, $adminId = null) {
        if ($adminId) {
            $stmt = $this->conn->prepare("
                UPDATE baocao 
                SET trangThai = ?, maAdminXuLy = ?
                WHERE maBaoCao = ?
            ");
            $stmt->bind_param("sii", $status, $adminId, $reportId);
        } else {
            $stmt = $this->conn->prepare("
                UPDATE baocao 
                SET trangThai = ?
                WHERE maBaoCao = ?
            ");
            $stmt->bind_param("si", $status, $reportId);
        }
        
        return $stmt->execute();
    }
}
?>
