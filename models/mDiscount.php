<?php
require_once 'mDbconnect.php';

class Discount {
    private $conn;
    
    public function __construct() {
        $db = clsConnect::getInstance();
        $this->conn = $db->connect();
    }
    
    /**
     * Kiểm tra và áp dụng mã giảm giá
     */
    public function validateAndApplyDiscount($couponCode, $originalPrice, $userId = null) {
        // Lấy thông tin mã giảm giá
        $stmt = $this->conn->prepare("
            SELECT 
                maMaGiamGia,
                maCoupon,
                tenChuongTrinh,
                loaiGiam,
                giaTriGiam,
                giaTriToiDa,
                soLuongToiDa,
                soLuongDaSuDung,
                ngayBatDau,
                ngayKetThuc,
                trangThai,
                apDungCho
            FROM magiamgia
            WHERE maCoupon = ?
            AND trangThai = 'active'
            AND ngayBatDau <= NOW()
            AND ngayKetThuc >= NOW()
        ");
        
        $stmt->bind_param("s", $couponCode);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return [
                'success' => false,
                'message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn'
            ];
        }
        
        $coupon = $result->fetch_assoc();
        
        // Kiểm tra số lượng
        if ($coupon['soLuongToiDa'] && $coupon['soLuongDaSuDung'] >= $coupon['soLuongToiDa']) {
            return [
                'success' => false,
                'message' => 'Mã giảm giá đã hết lượt sử dụng'
            ];
        }
        
        // Kiểm tra điều kiện áp dụng
        if ($coupon['apDungCho'] === 'new_user' && $userId) {
            // Kiểm tra user đã từng nâng cấp VIP chưa
            $checkStmt = $this->conn->prepare("
                SELECT COUNT(*) as count 
                FROM thanhtoan 
                WHERE maNguoiThanhToan = ?
            ");
            $checkStmt->bind_param("i", $userId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $checkData = $checkResult->fetch_assoc();
            
            if ($checkData['count'] > 0) {
                return [
                    'success' => false,
                    'message' => 'Mã giảm giá chỉ dành cho người dùng mới'
                ];
            }
        }
        
        // Tính toán giảm giá
        $discountAmount = 0;
        if ($coupon['loaiGiam'] === 'percent') {
            $discountAmount = ($originalPrice * $coupon['giaTriGiam']) / 100;
            
            // Áp dụng giá trị tối đa nếu có
            if ($coupon['giaTriToiDa'] && $discountAmount > $coupon['giaTriToiDa']) {
                $discountAmount = $coupon['giaTriToiDa'];
            }
        } else {
            // Giảm giá cố định
            $discountAmount = $coupon['giaTriGiam'];
        }
        
        // Đảm bảo không giảm quá giá gốc
        if ($discountAmount > $originalPrice) {
            $discountAmount = $originalPrice;
        }
        
        $finalPrice = $originalPrice - $discountAmount;
        
        return [
            'success' => true,
            'message' => 'Áp dụng mã giảm giá thành công',
            'coupon' => $coupon,
            'discountAmount' => $discountAmount,
            'finalPrice' => $finalPrice
        ];
    }
    
    /**
     * Tăng số lượng đã sử dụng của mã giảm giá
     */
    public function incrementUsage($couponCode) {
        $stmt = $this->conn->prepare("
            UPDATE magiamgia 
            SET soLuongDaSuDung = soLuongDaSuDung + 1
            WHERE maCoupon = ?
        ");
        $stmt->bind_param("s", $couponCode);
        return $stmt->execute();
    }
    
    /**
     * Lấy thông tin mã giảm giá theo code
     */
    public function getCouponByCode($couponCode) {
        $stmt = $this->conn->prepare("
            SELECT * FROM magiamgia WHERE maCoupon = ?
        ");
        $stmt->bind_param("s", $couponCode);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
?>
