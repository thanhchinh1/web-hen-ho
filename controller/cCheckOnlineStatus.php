<?php
/**
 * Controller kiểm tra trạng thái online của người dùng khác
 * Dùng để cập nhật real-time trong chat
 */

session_start();
require_once '../models/mDbconnect.php';
require_once '../models/mUser.php';

header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isset($_SESSION['maNguoiDung'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

// Kiểm tra userId cần check
if (!isset($_GET['userId'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu userId']);
    exit;
}

$userId = intval($_GET['userId']);

try {
    $userModel = new User();
    
    $isOnline = $userModel->isUserOnline($userId);
    $lastActivity = $userModel->getLastActivity($userId);
    
    $lastSeenText = '';
    if ($isOnline) {
        $lastSeenText = 'online';
    } elseif ($lastActivity && $lastActivity['minutesAgo'] !== null) {
        $minutes = $lastActivity['minutesAgo'];
        if ($minutes < 60) {
            $lastSeenText = $minutes . ' phút trước';
        } elseif ($minutes < 1440) {
            $lastSeenText = floor($minutes / 60) . ' giờ trước';
        } else {
            $lastSeenText = floor($minutes / 1440) . ' ngày trước';
        }
    }
    
    echo json_encode([
        'success' => true,
        'isOnline' => $isOnline,
        'lastSeen' => $lastSeenText,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi server: ' . $e->getMessage()
    ]);
}
