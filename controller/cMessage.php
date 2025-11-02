<?php
// Bật hiển thị lỗi cho debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../models/mSession.php';
require_once '../models/mMatch.php';
require_once '../models/mMessage.php';
require_once '../models/mRateLimit.php';

Session::start();

// Set header JSON
header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!Session::isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
    exit;
}

$currentUserId = Session::getUserId();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

$matchModel = new MatchModel();
$messageModel = new Message();
$rateLimit = new RateLimit();

if ($action === 'send') {
    // Rate limiting: 30 messages per minute
    if (!$rateLimit->checkRateLimit($currentUserId, 'send_message', 30, 60)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Bạn đang gửi tin nhắn quá nhanh! Vui lòng chờ.',
            'rateLimit' => true
        ]);
        exit;
    }
    
    // Gửi tin nhắn
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }
    
    $matchId = intval($_POST['matchId'] ?? 0);
    $content = trim($_POST['content'] ?? '');
    
    if ($matchId <= 0 || empty($content)) {
        echo json_encode(['success' => false, 'message' => 'Thiếu thông tin!']);
        exit;
    }
    
    // Kiểm tra quyền gửi tin (phải là thành viên của match) - HIỆU QUẢ
    if (!$matchModel->isMatchMember($matchId, $currentUserId)) {
        echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập!']);
        exit;
    }
    
    // Gửi tin nhắn
    $messageId = $messageModel->sendMessage($matchId, $currentUserId, $content);
    
    if ($messageId) {
        // Log action cho rate limiting
        $rateLimit->logAction($currentUserId, 'send_message');
        
        echo json_encode([
            'success' => true,
            'message' => 'Đã gửi tin nhắn!',
            'messageId' => $messageId,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không thể gửi tin nhắn!']);
    }
    
} elseif ($action === 'get_messages') {
    // Lấy danh sách tin nhắn
    $matchId = intval($_GET['matchId'] ?? 0);
    $limit = intval($_GET['limit'] ?? 50);
    $offset = intval($_GET['offset'] ?? 0);
    
    if ($matchId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Thiếu matchId!']);
        exit;
    }
    
    // Validate limit và offset
    $limit = min(max($limit, 10), 100); // Min 10, max 100
    $offset = max($offset, 0);
    
    // Kiểm tra quyền xem tin - HIỆU QUẢ
    if (!$matchModel->isMatchMember($matchId, $currentUserId)) {
        echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập!']);
        exit;
    }
    
    // Lấy tin nhắn
    $messages = $messageModel->getMessages($matchId, $limit, $offset);
    $totalMessages = $messageModel->countMessages($matchId);
    
    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'total' => $totalMessages,
        'hasMore' => ($offset + $limit) < $totalMessages
    ]);
    
} elseif ($action === 'get_new_messages') {
    // Lấy tin nhắn mới (polling)
    $matchId = intval($_GET['matchId'] ?? 0);
    $lastMessageId = intval($_GET['lastMessageId'] ?? 0);
    
    if ($matchId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Thiếu matchId!']);
        exit;
    }
    
    // Kiểm tra quyền xem tin - HIỆU QUẢ
    if (!$matchModel->isMatchMember($matchId, $currentUserId)) {
        echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập!']);
        exit;
    }
    
    // Lấy tin nhắn mới
    $newMessages = $messageModel->getNewMessages($matchId, $lastMessageId);
    
    echo json_encode([
        'success' => true,
        'messages' => $newMessages,
        'count' => count($newMessages)
    ]);
    
} else {
    echo json_encode(['success' => false, 'message' => 'Action không hợp lệ!']);
}
?>
