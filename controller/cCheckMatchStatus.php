<?php
require_once '../models/mSession.php';
require_once '../models/mMatch.php';
require_once '../models/mDbconnect.php';

Session::start();

header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!Session::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

$currentUserId = Session::getUserId();
$matchId = isset($_GET['matchId']) ? intval($_GET['matchId']) : 0;

if ($matchId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Match ID không hợp lệ']);
    exit;
}

$matchModel = new MatchModel();

// Kiểm tra user có phải thành viên của match này không
if (!$matchModel->isMatchMember($matchId, $currentUserId)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

// Kiểm tra match còn tồn tại không
$db = clsConnect::getInstance();
$conn = $db->connect();

$stmt = $conn->prepare("
    SELECT 
        maGhepDoi,
        maNguoiA,
        maNguoiB,
        trangThaiGhepDoi
    FROM ghepdoi 
    WHERE maGhepDoi = ?
");
$stmt->bind_param("i", $matchId);
$stmt->execute();
$result = $stmt->get_result();
$match = $result->fetch_assoc();

if (!$match) {
    // Match đã bị xóa (unmatched)
    echo json_encode([
        'success' => false,
        'unmatched' => true,
        'message' => 'Người này đã hủy ghép đôi với bạn. Tất cả tin nhắn đã bị xóa.'
    ]);
    exit;
}

// Match vẫn tồn tại
echo json_encode([
    'success' => true,
    'matched' => true,
    'status' => $match['trangThaiGhepDoi']
]);
?>
