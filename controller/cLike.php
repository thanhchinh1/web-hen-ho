<?php
require_once '../models/mSession.php';
require_once '../models/mLike.php';
require_once '../models/mProfile.php';
require_once '../models/mMatch.php';

Session::start();

// Kiểm tra đăng nhập
if (!Session::isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!', 'requireLogin' => true]);
    exit;
}

// Kiểm tra request method
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

$userId = Session::getUserId();

$targetUserId = intval($_POST['targetUserId'] ?? 0);

if ($targetUserId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ!']);
    exit;
}

// Không thể like chính mình
if ($userId == $targetUserId) {
    echo json_encode(['success' => false, 'message' => 'Bạn không thể thích chính mình!']);
    exit;
}

// Kiểm tra target user có tồn tại và có hồ sơ không
$profileModel = new Profile();
if (!$profileModel->hasProfile($targetUserId)) {
    echo json_encode(['success' => false, 'message' => 'Người dùng không tồn tại!']);
    exit;
}

// Kiểm tra block (2 chiều) - Không thể like người đã chặn hoặc bị chặn
require_once '../models/mBlock.php';
$blockModel = new Block();
if ($blockModel->isBlockedEitherWay($userId, $targetUserId)) {
    echo json_encode(['success' => false, 'message' => 'Không thể thực hiện với người dùng này!']);
    exit;
}

$likeModel = new Like();
$matchModel = new MatchModel();

// Toggle like/unlike - Tự động phát hiện trạng thái
if ($likeModel->hasLiked($userId, $targetUserId)) {
    // Đã like rồi, thực hiện unlike
    if ($likeModel->unlikeUser($userId, $targetUserId)) {
        // Kiểm tra có match không, nếu có thì unmatch
        // (Chỉ cập nhật trạng thái match, KHÔNG xóa lượt thích của người kia)
        if ($matchModel->isMatched($userId, $targetUserId)) {
            $matchModel->updateMatchStatus($userId, $targetUserId, 'unmatched');
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Đã bỏ thích!',
            'action' => 'unliked'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi bỏ thích!']);
    }
} else {
    // Chưa like, thực hiện like
    if ($likeModel->likeUser($userId, $targetUserId)) {
        // Kiểm tra xem có tạo match không (người kia đã like mình chưa)
        $matchModel = new MatchModel();
        
        if ($matchModel->canCreateMatch($userId, $targetUserId)) {
            // Có thể tạo match! (cả 2 đều đã like nhau)
            $matchId = $matchModel->createMatch($userId, $targetUserId);
            
            if ($matchId) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Ghép đôi thành công! 💕',
                    'action' => 'liked',
                    'matched' => true,
                    'matchId' => $matchId,
                    'redirect' => '/views/nhantin/message.php?match=' . $matchId
                ]);
                exit;
            }
        }
        
        // Like thành công nhưng chưa match
        echo json_encode([
            'success' => true, 
            'message' => 'Đã thích!',
            'action' => 'liked',
            'matched' => false
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi thích!']);
    }
}
?>
