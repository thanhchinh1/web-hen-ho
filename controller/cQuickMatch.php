<?php
require_once '../models/mSession.php';
require_once '../models/mQuickMatch.php';
require_once '../models/mVIP.php';

Session::start();

// Kiểm tra AJAX request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['error' => 'Method not allowed']));
}

// Kiểm tra đăng nhập
if (!Session::isLoggedIn()) {
    http_response_code(401);
    exit(json_encode(['error' => 'Unauthorized']));
}

$userId = Session::getUserId();
$action = $_POST['action'] ?? '';

// Kiểm tra VIP
$vipModel = new VIP();
if (!$vipModel->isVIP($userId)) {
    http_response_code(403);
    exit(json_encode([
        'error' => 'VIP required',
        'message' => 'Bạn cần nâng cấp tài khoản VIP để sử dụng tính năng này!'
    ]));
}

$quickMatch = new QuickMatch();

// Debug logging
error_log("=== QUICK MATCH DEBUG ===");
error_log("User ID: " . $userId);
error_log("Action: " . $action);

try {
    switch ($action) {
        case 'start':
            error_log("Starting search for user " . $userId);
            // Bắt đầu tìm kiếm
            $result = $quickMatch->startSearching($userId);
            
            error_log("Start result: " . print_r($result, true));
            
            if ($result && isset($result['success'])) {
                // Tìm thấy match ngay
                $partner = $quickMatch->getPartnerInfo($userId, $result['partnerId']);
                error_log("Found match immediately! Partner: " . $result['partnerId']);
                echo json_encode([
                    'status' => 'matched',
                    'matchId' => $result['matchId'],
                    'partner' => $partner,
                    'score' => $result['score']
                ]);
            } else {
                // Đang tìm kiếm
                error_log("No immediate match, searching...");
                echo json_encode([
                    'status' => 'searching',
                    'message' => 'Đang tìm kiếm người phù hợp...'
                ]);
            }
            break;
            
        case 'check':
            error_log("Checking match status for user " . $userId);
            // Kiểm tra trạng thái tìm kiếm (polling)
            $result = $quickMatch->checkForMatch($userId);
            
            error_log("Check result: " . print_r($result, true));
            
            if (isset($result['success']) && $result['success']) {
                // Tìm thấy match
                $partner = $quickMatch->getPartnerInfo($userId, $result['partnerId']);
                error_log("Match found! Partner: " . $result['partnerId']);
                echo json_encode([
                    'status' => 'matched',
                    'matchId' => $result['matchId'],
                    'partner' => $partner,
                    'score' => $result['score']
                ]);
            } elseif (isset($result['searching']) && $result['searching']) {
                // Vẫn đang tìm
                error_log("Still searching... Duration: " . $result['duration']);
                echo json_encode([
                    'status' => 'searching',
                    'duration' => $result['duration']
                ]);
            } else {
                // Không tìm thấy
                error_log("Not found");
                echo json_encode([
                    'status' => 'not_found',
                    'message' => 'Không tìm thấy người phù hợp'
                ]);
            }
            break;
            
        case 'cancel':
            // Hủy tìm kiếm
            $quickMatch->cancelSearching($userId);
            echo json_encode([
                'status' => 'cancelled',
                'message' => 'Đã hủy tìm kiếm'
            ]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error',
        'message' => $e->getMessage()
    ]);
}
?>
