<?php
/**
 * Controller kiểm tra trạng thái online của người dùng khác
 * Dùng để cập nhật real-time trong chat
 */

require_once '../models/mSession.php';
require_once '../models/mDbconnect.php';
require_once '../models/mUser.php';

Session::start();
header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!Session::isLoggedIn()) {
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
    
    // Tối ưu: Gộp 2 query thành 1
    $lastActivity = $userModel->getLastActivity($userId);
    
    $isOnline = false;
    $lastSeenText = '';
    
    if ($lastActivity) {
        // Kiểm tra online
        if ($lastActivity['lanHoatDongCuoi'] === null) {
            // Đã logout
            $isOnline = false;
            $lastSeenText = 'Không hoạt động';
        } elseif ($lastActivity['minutesAgo'] !== null) {
            if ($lastActivity['minutesAgo'] <= 5) {
                // Online
                $isOnline = true;
                $lastSeenText = 'online';
            } else {
                // Offline - hiển thị thời gian
                $minutes = $lastActivity['minutesAgo'];
                if ($minutes < 60) {
                    $lastSeenText = $minutes . ' phút trước';
                } elseif ($minutes < 1440) {
                    $lastSeenText = floor($minutes / 60) . ' giờ trước';
                } else {
                    $lastSeenText = floor($minutes / 1440) . ' ngày trước';
                }
            }
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
