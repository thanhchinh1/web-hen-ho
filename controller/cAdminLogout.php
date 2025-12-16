<?php
require_once __DIR__ . '/../models/mSession.php';
require_once __DIR__ . '/../models/mAdmin.php';

Session::start();

// Kiểm tra đăng nhập admin (cả 2 loại: admin table và user với role admin)
$isAdminSession = Session::get('is_admin');
$userRole = Session::get('user_role');

if ($isAdminSession || $userRole === 'admin') {
    // Xóa tất cả session liên quan đến admin và user
    Session::delete('admin_id');
    Session::delete('admin_username');
    Session::delete('admin_name');
    Session::delete('admin_role');
    Session::delete('is_admin');
    Session::delete('admin_last_activity');
    
    // Xóa session user
    Session::delete('user_id');
    Session::delete('user_role');
    Session::delete('user_email');
    
    Session::setFlash('admin_info', 'Đã đăng xuất thành công!');
}

header('Location: ../index.php');
exit;
?>