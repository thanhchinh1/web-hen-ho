<?php
require_once '../models/session.php';
require_once '../models/mLike.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng đăng nhập để sử dụng tính năng này.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Phương thức không hợp lệ.'
    ]);
    exit;
}

$likedId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$currentUserId = getCurrentUserId();

if ($likedId <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Người dùng không hợp lệ.'
    ]);
    exit;
}

if ($likedId === $currentUserId) {
    echo json_encode([
        'success' => false,
        'message' => 'Bạn không thể thích chính mình.'
    ]);
    exit;
}

try {
    $likeModel = new LikeModel();
    $liked = $likeModel->toggleLike($currentUserId, $likedId);

    echo json_encode([
        'success' => true,
        'liked' => $liked,
        'message' => $liked ? 'Đã thêm vào danh sách thích.' : 'Đã bỏ thích người dùng này.'
    ]);
} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Không thể cập nhật lượt thích. Vui lòng thử lại sau.'
    ]);
}
