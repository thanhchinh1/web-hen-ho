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
$matchId = $data['matchId'] ?? 0;
$isTyping = $data['isTyping'] ?? false;

if (!$matchId) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
    exit;
}

// Cập nhật trạng thái typing
$result = $messageModel->setTypingStatus($matchId, $currentUserId, $isTyping ? 1 : 0);

if ($result) {
    echo json_encode([
        'success' => true,
        'message' => 'Cập nhật trạng thái typing thành công'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra'
    ]);
}
?>
