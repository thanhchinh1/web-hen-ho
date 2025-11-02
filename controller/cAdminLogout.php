<?php
require_once __DIR__ . '/../models/mSession.php';
require_once __DIR__ . '/../models/mAdmin.php';

Session::start();

// Kiểm tra đăng nhập admin
if (Session::get('is_admin')) {
    $adminId = Session::get('admin_id');
    
    // Log đăng xuất
    $adminModel = new Admin();
    $adminModel->logAction($adminId, 'logout', 'Đăng xuất hệ thống');
    
    // Xóa session admin
    Session::delete('admin_id');
    Session::delete('admin_username');
    Session::delete('admin_name');
    Session::delete('admin_role');
    Session::delete('is_admin');
    
    Session::setFlash('admin_info', 'Đã đăng xuất thành công!');
}

header('Location: ../views/admin/dangnhap.php');
exit;
?>
