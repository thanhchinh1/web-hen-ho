<?php
// Bật hiển thị lỗi cho debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../models/mSession.php';
require_once '../models/mLike.php';
require_once '../models/mMatch.php';

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

// Validate không thể like chính mình
if ($targetUserId === $currentUserId) {
    echo json_encode(['success' => false, 'message' => 'Không thể thực hiện với chính mình!']);
    exit;
}

$likeModel = new Like();
$matchModel = new MatchModel();

if ($action === 'like_back') {
    // Thích lại (người này đã like mình, giờ mình like lại)
    
    // Kiểm tra người kia đã like mình chưa
    if (!$likeModel->hasLiked($targetUserId, $currentUserId)) {
        echo json_encode(['success' => false, 'message' => 'Người này chưa thích bạn!']);
        exit;
    }
    
    // Thực hiện like
    if ($likeModel->likeUser($currentUserId, $targetUserId)) {
        // Kiểm tra xem có tạo được match không
        if ($matchModel->canCreateMatch($currentUserId, $targetUserId)) {
            // Tạo match
            $matchId = $matchModel->createMatch($currentUserId, $targetUserId);
            
            if ($matchId) {
                // Ghép đôi thành công! Chuyển đến trang chat
                echo json_encode([
                    'success' => true,
                    'matched' => true,
                    'message' => 'Ghép đôi thành công! 💕',
                    'matchId' => $matchId,
                    'redirect' => '/views/nhantin/message.php?match=' . $matchId
                ]);
                exit;
            }
        }
        
        // Like thành công nhưng chưa match (có thể do lỗi)
        echo json_encode([
            'success' => true,
            'matched' => false,
            'message' => 'Đã thích lại! ❤️'
        ]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra!']);
        exit;
    }
    
} elseif ($action === 'unmatch') {
    // Hủy ghép đôi
    
    // Nhận matchId nếu có (cho trường hợp nhiều match giữa 2 người)
    $matchId = intval($_POST['matchId'] ?? 0);
    
    // Log để debug
    error_log("Unmatch request - Current User: $currentUserId, Target User: $targetUserId, Match ID: $matchId");
    
    if ($matchId > 0) {
        // Xóa match cụ thể theo ID
        $result = $matchModel->unmatchById($matchId, $currentUserId);
        error_log("UnmatchById result: " . ($result ? 'SUCCESS' : 'FAILED'));
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => '✅ Đã hủy ghép đôi!\n🗑️ Tất cả tin nhắn đã bị xóa vĩnh viễn!',
                'redirect' => '/views/nhantin/message.php'
            ]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể xóa match này!']);
            exit;
        }
    } else {
        // Xóa tất cả match giữa 2 người (fallback)
        // Kiểm tra xem có đang matched không
        $isMatched = $matchModel->isMatched($currentUserId, $targetUserId);
        error_log("Is matched: " . ($isMatched ? 'YES' : 'NO'));
        
        if (!$isMatched) {
            echo json_encode(['success' => false, 'message' => 'Bạn và người này chưa ghép đôi!']);
            exit;
        }
        
        // Thực hiện unmatch
        $result = $matchModel->unmatch($currentUserId, $targetUserId);
        error_log("Unmatch result: " . ($result ? 'SUCCESS' : 'FAILED'));
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => '✅ Đã hủy ghép đôi!\n🗑️ Tất cả tin nhắn đã bị xóa vĩnh viễn!',
                'redirect' => '/views/nhantin/message.php'
            ]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi khi hủy ghép đôi! Chi tiết trong error log.']);
            exit;
        }
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Action không hợp lệ!']);
    exit;
}
?>
