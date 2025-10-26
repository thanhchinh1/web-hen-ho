<?php
require_once '../models/session.php';
require_once '../models/mUser.php';

// Kiểm tra nếu đã đăng nhập thì chuyển về trang chủ
redirectIfLoggedIn();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validate input
    $errors = [];
    
    if (empty($email)) {
        $errors[] = "Email/SĐT không được để trống";
    }
    
    if (empty($password)) {
        $errors[] = "Mật khẩu không được để trống";
    }
    
    // Nếu không có lỗi thì tiến hành đăng nhập
    if (empty($errors)) {
        $userModel = new User();
        $userId = $userModel->login($email, $password);
        
        if ($userId) {
            // Đăng nhập thành công
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_email'] = $email;
            $_SESSION['is_logged_in'] = true;
            
            // Chuyển hướng đến trang chủ
            header('Location: ../views/trangchu/index.php');
            exit();
        } else {
            $errors[] = "Email/SĐT hoặc mật khẩu không chính xác";
        }
    }
    
    // Nếu có lỗi, lưu vào session để hiển thị
    if (!empty($errors)) {
        $_SESSION['login_errors'] = $errors;
        $_SESSION['login_data'] = [
            'email' => $email
        ];
        header('Location: ../views/dangnhap/login.php');
        exit();
    }
} else {
    // Nếu không phải POST request, chuyển về trang đăng nhập
    header('Location: ../views/dangnhap/login.php');
    exit();
}
?>