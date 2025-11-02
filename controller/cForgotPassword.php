<?php
require_once '../models/mSession.php';
require_once '../models/mUser.php';
require_once '../models/mDbconnect.php';

Session::start();

// Chỉ chấp nhận POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/dangnhap/quenmatkhau.php');
    exit;
}

$step = intval($_POST['step'] ?? 1);
$errors = [];

if ($step == 1) {
    // Bước 1: Xác minh email/SĐT
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $errors[] = 'Vui lòng nhập email hoặc số điện thoại!';
        Session::setFlash('forgot_errors', $errors);
        header('Location: ../views/dangnhap/quenmatkhau.php');
        exit;
    }
    
    // Kiểm tra tài khoản có tồn tại không
    $db = clsConnect::getInstance();
    $conn = $db->connect();
    
    $stmt = $conn->prepare("SELECT maNguoiDung, tenDangNhap, trangThaiNguoiDung FROM NguoiDung WHERE tenDangNhap = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $errors[] = 'Email/Số điện thoại không tồn tại trong hệ thống!';
        Session::setFlash('forgot_errors', $errors);
        header('Location: ../views/dangnhap/quenmatkhau.php');
        exit;
    }
    
    $user = $result->fetch_assoc();
    
    // Kiểm tra tài khoản có bị khóa không
    if ($user['trangThaiNguoiDung'] === 'banned' || $user['trangThaiNguoiDung'] === 'locked') {
        $errors[] = 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ admin!';
        Session::setFlash('forgot_errors', $errors);
        header('Location: ../views/dangnhap/quenmatkhau.php');
        exit;
    }
    
    // Lưu thông tin vào session
    Session::set('forgot_user_id', $user['maNguoiDung']);
    Session::set('forgot_user_email', $user['tenDangNhap']);
    Session::set('forgot_password_step', 2);
    
    header('Location: ../views/dangnhap/quenmatkhau.php');
    exit;
    
} elseif ($step == 2) {
    // Bước 2: Đặt lại mật khẩu
    $userId = Session::get('forgot_user_id');
    
    if (!$userId) {
        $errors[] = 'Phiên làm việc đã hết hạn. Vui lòng thử lại!';
        Session::setFlash('forgot_errors', $errors);
        Session::set('forgot_password_step', 1);
        header('Location: ../views/dangnhap/quenmatkhau.php');
        exit;
    }
    
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($newPassword) || empty($confirmPassword)) {
        $errors[] = 'Vui lòng nhập đầy đủ thông tin!';
    }
    
    if ($newPassword !== $confirmPassword) {
        $errors[] = 'Mật khẩu xác nhận không khớp!';
    }
    
    if (strlen($newPassword) < 8) {
        $errors[] = 'Mật khẩu phải có ít nhất 8 ký tự!';
    }
    
    if (!preg_match('/[a-z]/', $newPassword)) {
        $errors[] = 'Mật khẩu phải có ít nhất 1 chữ thường (a-z)!';
    }
    
    if (!preg_match('/[A-Z]/', $newPassword)) {
        $errors[] = 'Mật khẩu phải có ít nhất 1 chữ hoa (A-Z)!';
    }
    
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $newPassword)) {
        $errors[] = 'Mật khẩu phải có ít nhất 1 ký tự đặc biệt (!@#$%^&*...)!';
    }
    
    if (!empty($errors)) {
        Session::setFlash('forgot_errors', $errors);
        header('Location: ../views/dangnhap/quenmatkhau.php');
        exit;
    }
    
    // Cập nhật mật khẩu mới
    $userModel = new User();
    
    if ($userModel->updatePassword($userId, $newPassword)) {
        // Xóa session quên mật khẩu
        Session::delete('forgot_user_id');
        Session::delete('forgot_user_email');
        Session::delete('forgot_password_step');
        
        // Thông báo thành công
        Session::setFlash('login_errors', ['Đặt lại mật khẩu thành công! Vui lòng đăng nhập với mật khẩu mới.']);
        header('Location: ../views/dangnhap/login.php');
        exit;
    } else {
        $errors[] = 'Có lỗi xảy ra khi đặt lại mật khẩu. Vui lòng thử lại!';
        Session::setFlash('forgot_errors', $errors);
        header('Location: ../views/dangnhap/quenmatkhau.php');
        exit;
    }
}

// Fallback
header('Location: ../views/dangnhap/quenmatkhau.php');
exit;
?>
