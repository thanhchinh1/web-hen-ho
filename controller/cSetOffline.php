<?php
/**
 * Controller đặt trạng thái offline khi user rời khỏi trang
 * Được gọi khi user tắt tab/browser hoặc chuyển trang
 */

// Set timezone to Vietnam
date_default_timezone_set('Asia/Ho_Chi_Minh');

require_once '../models/mSession.php';
require_once '../models/mDbconnect.php';

Session::start();

// Kiểm tra đăng nhập
if (!Session::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

$maNguoiDung = Session::getUserId();

// Đặt lanHoatDongCuoi về NULL để đánh dấu offline
try {
    $db = clsConnect::getInstance()->connect();
    
    $sql = "UPDATE NguoiDung 
            SET lanHoatDongCuoi = NULL 
            WHERE maNguoiDung = ?";
    
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $maNguoiDung);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Đã set offline',
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
?>
