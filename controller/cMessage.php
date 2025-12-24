<?php
// Bật hiển thị lỗi cho debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../models/mSession.php';
require_once '../models/mMatch.php';
require_once '../models/mMessage.php';

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

if ($action === 'send') {
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
    
    // Đánh dấu tin nhắn là đã nhận (delivered)
    if (!empty($newMessages)) {
        $messageModel->markAsDelivered($matchId, $currentUserId);
        
        // Tắt typing status của tất cả người dùng trong match này khi có tin nhắn mới
        // (vì người gửi đã gửi tin rồi, không còn typing nữa)
        foreach ($newMessages as $msg) {
            if ($msg['maNguoiGui'] != $currentUserId) {
                $messageModel->setTypingStatus($matchId, $msg['maNguoiGui'], 0);
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'messages' => $newMessages,
        'count' => count($newMessages)
    ]);
    
} elseif ($action === 'mark_seen') {
    // Đánh dấu tin nhắn đã xem
    $matchId = intval($_POST['matchId'] ?? 0);
    
    if ($matchId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Thiếu matchId!']);
        exit;
    }
    
    // Kiểm tra quyền
    if (!$matchModel->isMatchMember($matchId, $currentUserId)) {
        echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập!']);
        exit;
    }
    
    // Đánh dấu đã xem
    $result = $messageModel->markAsSeen($matchId, $currentUserId);
    
    echo json_encode([
        'success' => $result,
        'message' => $result ? 'Đã đánh dấu đã xem' : 'Có lỗi xảy ra'
    ]);
    
} elseif ($action === 'get_status_updates') {
    // Lấy status updates của tin nhắn đã gửi (cho người gửi)
    $matchId = intval($_GET['matchId'] ?? 0);
    $messageIds = $_GET['messageIds'] ?? '';
    
    if ($matchId <= 0 || empty($messageIds)) {
        echo json_encode(['success' => false, 'message' => 'Thiếu thông tin!']);
        exit;
    }
    
    // Kiểm tra quyền
    if (!$matchModel->isMatchMember($matchId, $currentUserId)) {
        echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập!']);
        exit;
    }
    
    // Parse message IDs
    $ids = explode(',', $messageIds);
    $ids = array_map('intval', $ids);
    $ids = array_filter($ids, function($id) { return $id > 0; });
    
    if (empty($ids)) {
        echo json_encode(['success' => false, 'message' => 'Message IDs không hợp lệ!']);
        exit;
    }
    
    // Lấy status của các tin nhắn
    $statuses = $messageModel->getMessagesStatus($matchId, $currentUserId, $ids);
    
    echo json_encode([
        'success' => true,
        'statuses' => $statuses
    ]);
    
} elseif ($action === 'get_all_unread_counts') {
    // Lấy số tin nhắn chưa đọc cho tất cả các match của user
    $myMatches = $matchModel->getMyMatches($currentUserId);
    $counts = [];
    
    foreach ($myMatches as $match) {
        $matchId = $match['maGhepDoi'];
        $unreadCount = $messageModel->getUnreadCount($matchId, $currentUserId);
        if ($unreadCount > 0) {
            $counts[$matchId] = $unreadCount;
        }
    }
    
    echo json_encode([
        'success' => true,
        'counts' => $counts
    ]);
    
} else {
    echo json_encode(['success' => false, 'message' => 'Action không hợp lệ!']);
}
?>
