<?php
require_once '../models/mSession.php';
require_once '../models/mAdmin.php';

Session::start();

// Kiểm tra đăng nhập admin (hỗ trợ cả admin từ bảng Admin và bảng NguoiDung)
$isAdminSession = Session::get('is_admin'); // Admin từ bảng Admin
$userRole = Session::get('user_role'); // Admin từ bảng NguoiDung

if (!$isAdminSession && $userRole !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Lấy thông tin admin
if ($isAdminSession) {
    // Admin từ bảng Admin
    $adminId = Session::get('admin_id');
    $adminRole = Session::get('admin_role');
} else {
    // Admin từ bảng NguoiDung (có toàn quyền)
    $adminId = Session::get('user_id');
    $adminRole = 'super_admin'; // Gán quyền super_admin cho admin từ bảng NguoiDung
}

// Chỉ super_admin và moderator mới được phép
if ($adminRole === 'support') {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này']);
    exit;
}

// Get parameters
$userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$newStatus = isset($_POST['status']) ? $_POST['status'] : '';

if ($userId <= 0 || !in_array($newStatus, ['active', 'banned', 'inactive'])) {
    echo json_encode(['success' => false, 'message' => 'Tham số không hợp lệ']);
    exit;
}

try {
    $adminModel = new Admin();
    $result = $adminModel->toggleUserStatus($adminId, $userId, $newStatus);
    
    if ($result) {
        $action = $newStatus === 'banned' ? 'Khóa' : 'Mở khóa';
        echo json_encode([
            'success' => true, 
            'message' => $action . ' tài khoản thành công'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không thể cập nhật trạng thái']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>