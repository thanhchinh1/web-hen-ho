<?php
require_once '../models/mSession.php';
require_once '../models/mUser.php';

Session::start();

// Lấy userId trước khi hủy session
$userId = Session::get('user_id');

// Cập nhật trạng thái offline trước khi đăng xuất
if ($userId) {
    $userModel = new User();
    $userModel->updateOfflineStatus($userId);
}

// Thiết lập thông báo đăng xuất
Session::setFlash('success', 'Đã đăng xuất thành công!');

// Hủy session và đăng xuất
Session::destroy();

// Chuyển về trang chủ
header('Location: ../index.php');
exit;
?>
