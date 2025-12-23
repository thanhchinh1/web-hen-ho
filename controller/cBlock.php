<?php
require_once '../models/mSession.php';
require_once '../models/mBlock.php';
require_once '../models/mMatch.php';
require_once '../models/mLike.php';

Session::start();

// Set header JSON
header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!Session::isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
    exit;
}

$currentUserId = Session::getUserId();

// Chỉ chấp nhận POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// CSRF Protection
$csrfToken = $_POST['csrf_token'] ?? '';
if (!Session::verifyCSRFToken($csrfToken)) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token. Vui lòng refresh trang!']);
    exit;
}

// Lấy action và targetUserId
$action = $_POST['action'] ?? '';
$targetUserId = intval($_POST['targetUserId'] ?? 0);

if (empty($action) || $targetUserId === 0) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin!']);
    exit;
}

// Validate không thể block chính mình
if ($targetUserId === $currentUserId) {
    echo json_encode(['success' => false, 'message' => 'Không thể chặn chính mình!']);
    exit;
}

$blockModel = new Block();
$matchModel = new MatchModel();
$likeModel = new Like();

if ($action === 'block') {
    // Chặn người dùng
    error_log("Block action initiated by user $currentUserId against user $targetUserId");
    
    // Kiểm tra đã block chưa
    if ($blockModel->isBlocked($currentUserId, $targetUserId)) {
        error_log("Already blocked");
        echo json_encode(['success' => false, 'message' => 'Bạn đã chặn người này rồi!']);
        exit;
    }
    
    // Block với cleanup (xóa match, like)
    $result = $blockModel->blockUserWithCleanup($currentUserId, $targetUserId);
    error_log("Block result: " . ($result ? "SUCCESS" : "FAILED"));
    
    if ($result) {
        echo json_encode([
            'success' => true, 
            'message' => 'Đã chặn người dùng này! ⛔'
        ]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi chặn! Vui lòng thử lại.']);
        exit;
    }
    
} elseif ($action === 'unblock') {
    // Bỏ chặn người dùng
    
    if (!$blockModel->isBlocked($currentUserId, $targetUserId)) {
        echo json_encode(['success' => false, 'message' => 'Bạn chưa chặn người này!']);
        exit;
    }
    
    if ($blockModel->unblockUser($currentUserId, $targetUserId)) {
        echo json_encode([
            'success' => true, 
            'message' => 'Đã bỏ chặn! ✅'
        ]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra!']);
        exit;
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Action không hợp lệ!']);
    exit;
}
?>
