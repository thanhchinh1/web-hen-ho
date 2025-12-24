<?php
require_once '../models/mSession.php';
require_once '../models/mMessage.php';
require_once '../models/mNotification.php';

Session::start();

header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!Session::isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$currentUserId = Session::getUserId();

try {
    $messageModel = new Message();
    $notificationModel = new Notification();
    
    // Đếm số tin nhắn chưa đọc
    $unreadMessagesCount = $messageModel->getTotalUnreadCount($currentUserId);
    
    // Đếm số ghép đôi mới
    $newMatchesCount = $notificationModel->getNewMatchesCount($currentUserId);
    
    // Tổng số thông báo
    $totalNotifications = $unreadMessagesCount + $newMatchesCount;
    
    echo json_encode([
        'success' => true,
        'unreadMessages' => $unreadMessagesCount,
        'newMatches' => $newMatchesCount,
        'total' => $totalNotifications
    ]);
    
} catch (Exception $e) {
    error_log("Check notifications error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error checking notifications'
    ]);
}
?>
