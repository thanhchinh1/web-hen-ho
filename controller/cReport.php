<?php
require_once '../models/mSession.php';
require_once '../models/mReport.php';

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

// Lấy dữ liệu
$action = $_POST['action'] ?? '';
$targetUserId = intval($_POST['targetUserId'] ?? 0);
$reportType = $_POST['reportType'] ?? 'other';
$reportReason = trim($_POST['reportReason'] ?? '');

if (empty($action) || $targetUserId === 0) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin!']);
    exit;
}

// Validate không thể báo cáo chính mình
if ($targetUserId === $currentUserId) {
    echo json_encode(['success' => false, 'message' => 'Không thể báo cáo chính mình!']);
    exit;
}

$reportModel = new Report();

if ($action === 'report') {
    // Báo cáo vi phạm
    
    // Validate lý do
    if (empty($reportReason) || strlen($reportReason) < 10) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập lý do báo cáo (tối thiểu 10 ký tự)!']);
        exit;
    }
    
    // Kiểm tra đã báo cáo gần đây chưa
    if ($reportModel->hasReportedRecently($currentUserId, $targetUserId)) {
        echo json_encode(['success' => false, 'message' => 'Bạn đã báo cáo người này trong vòng 30 ngày qua!']);
        exit;
    }
    
    // Thực hiện báo cáo
    $result = $reportModel->reportUser($currentUserId, $targetUserId, $reportReason, $reportType);
    
    if ($result['success']) {
        $message = 'Báo cáo đã được gửi thành công! Cảm ơn bạn đã giúp cộng đồng an toàn hơn. 🛡️';
        
        // Thêm thông báo nếu tài khoản bị khóa
        if ($result['locked']) {
            $message .= "\n\n⚠️ Tài khoản này đã bị khóa tự động do vi phạm nhiều lần (≥5 báo cáo).";
        }
        
        echo json_encode([
            'success' => true, 
            'message' => $message,
            'locked' => $result['locked'],
            'reportCount' => $result['count']
        ]);
        exit;
    } else {
        echo json_encode([
            'success' => false, 
            'message' => $result['message'] ?? 'Có lỗi xảy ra khi gửi báo cáo!'
        ]);
        exit;
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Action không hợp lệ!']);
    exit;
}
?>
