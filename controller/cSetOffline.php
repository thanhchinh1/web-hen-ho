<?php
/**
 * Controller để set trạng thái offline khi user tắt tab/đóng browser
 * Sử dụng với navigator.sendBeacon()
 */

require_once '../models/mSession.php';
require_once '../models/mUser.php';

Session::start();

// Cho phép cả GET và POST
$userId = Session::getUserId();

if ($userId) {
    $userModel = new User();
    $userModel->updateOfflineStatus($userId);
    
    // Return simple response
    http_response_code(200);
    echo json_encode(['success' => true]);
} else {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
}
?>
