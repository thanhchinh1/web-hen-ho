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

if (!$matchId) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
    exit;
}

// Lấy trạng thái typing của người khác
$typingUser = $messageModel->getTypingStatus($matchId, $currentUserId);

if ($typingUser) {
    echo json_encode([
        'success' => true,
        'isTyping' => true,
        'userName' => $typingUser['ten']
    ]);
} else {
    echo json_encode([
        'success' => true,
        'isTyping' => false
    ]);
}
?>
