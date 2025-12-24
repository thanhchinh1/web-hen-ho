<?php
require_once '../models/mSession.php';
require_once '../models/mMessage.php';

Session::start();

header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!Session::isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

$currentUserId = Session::getUserId();
$messageModel = new Message();

// Lấy dữ liệu từ request
$data = json_decode(file_get_contents('php://input'), true);
$messageId = $data['messageId'] ?? 0;

if (!$messageId) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
    exit;
}

// Thu hồi tin nhắn
$result = $messageModel->recallMessage($messageId, $currentUserId);

if ($result) {
    echo json_encode([
        'success' => true,
        'message' => 'Đã thu hồi tin nhắn',
        'status' => 'recalled'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Không thể thu hồi tin nhắn'
    ]);
}
?>
