<?php
require_once __DIR__ . '/../models/mSession.php';
require_once __DIR__ . '/../models/mSupport.php';

header('Content-Type: application/json');

Session::start();

// Kiểm tra đăng nhập
if (!Session::get('user_id')) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng đăng nhập'
    ]);
    exit;
}

// Kiểm tra method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

$userId = Session::get('user_id');
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$content = isset($_POST['content']) ? trim($_POST['content']) : '';
$type = isset($_POST['type']) ? trim($_POST['type']) : 'general';

// Validate input
if (empty($title)) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng nhập tiêu đề'
    ]);
    exit;
}

if (empty($content)) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng nhập nội dung'
    ]);
    exit;
}

// Validate type
$validTypes = ['general', 'payment', 'technical', 'report', 'other'];
if (!in_array($type, $validTypes)) {
    $type = 'general';
}

try {
    $supportModel = new Support();
    
    if ($supportModel->createSupportRequest($userId, $title, $content, $type)) {
        echo json_encode([
            'success' => true,
            'message' => 'Gửi yêu cầu hỗ trợ thành công! Admin sẽ phản hồi trong thời gian sớm nhất.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Không thể gửi yêu cầu. Vui lòng thử lại sau.'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
    ]);
}
?>
