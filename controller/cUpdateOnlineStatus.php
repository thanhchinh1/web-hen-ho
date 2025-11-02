<?php
/**
 * Controller cập nhật trạng thái online
 * Endpoint này được gọi định kỳ từ JavaScript để cập nhật thời gian hoạt động cuối
 */

session_start();
require_once '../models/mDbconnect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['maNguoiDung'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

$maNguoiDung = $_SESSION['maNguoiDung'];

// Cập nhật thời gian hoạt động cuối
try {
    $db = clsConnect::getInstance()->connect();
    
    $sql = "UPDATE NguoiDung 
            SET lanHoatDongCuoi = NOW() 
            WHERE maNguoiDung = ?";
    
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $maNguoiDung);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        throw new Exception('Không thể cập nhật trạng thái');
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi server: ' . $e->getMessage()
    ]);
}
