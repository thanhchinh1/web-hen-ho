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

try {
    switch ($action) {
        case 'start':
            // Bắt đầu tìm kiếm
            $result = $quickMatch->startSearching($userId);
            
            if ($result && isset($result['success'])) {
                // Tìm thấy match ngay
                $partner = $quickMatch->getPartnerInfo($userId, $result['partnerId']);
                echo json_encode([
                    'status' => 'matched',
                    'matchId' => $result['matchId'],
                    'partner' => $partner,
                    'score' => $result['score']
                ]);
            } else {
                // Đang tìm kiếm
                echo json_encode([
                    'status' => 'searching',
                    'message' => 'Đang tìm kiếm người phù hợp...'
                ]);
            }
            break;
            
        case 'check':
            // Kiểm tra trạng thái tìm kiếm (polling)
            $result = $quickMatch->checkForMatch($userId);
            
            if (isset($result['success']) && $result['success']) {
                // Tìm thấy match
                $partner = $quickMatch->getPartnerInfo($userId, $result['partnerId']);
                echo json_encode([
                    'status' => 'matched',
                    'matchId' => $result['matchId'],
                    'partner' => $partner,
                    'score' => $result['score']
                ]);
            } elseif (isset($result['searching']) && $result['searching']) {
                // Vẫn đang tìm
                echo json_encode([
                    'status' => 'searching',
                    'duration' => $result['duration']
                ]);
            } else {
                // Không tìm thấy
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
