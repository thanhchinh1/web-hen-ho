<?php
require_once '../models/session.php';
require_once '../models/mUser.php';

// Kiểm tra nếu đã đăng nhập thì chuyển về trang chủ
redirectIfLoggedIn();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validate input
    $errors = [];
    
    // Validate email/phone
    if (empty($email)) {
        $errors[] = "Email/SĐT không được để trống";
    } else {
        // Kiểm tra format email hoặc số điện thoại
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) && !preg_match('/^[0-9]{10,11}$/', $email)) {
            $errors[] = "Email hoặc số điện thoại không hợp lệ";
        }
    }
    
    // Validate password
    if (empty($password)) {
        $errors[] = "Mật khẩu không được để trống";
    } elseif (strlen($password) < 6) {
        $errors[] = "Mật khẩu phải có ít nhất 6 ký tự";
    }
    
    // Validate confirm password
    if ($password !== $confirmPassword) {
        $errors[] = "Mật khẩu xác nhận không khớp";
    }
    
    // Nếu không có lỗi thì tiến hành đăng ký
    if (empty($errors)) {
        $userModel = new User();
        
        // Kiểm tra email đã tồn tại
        if ($userModel->checkEmailExists($email)) {
            $errors[] = "Email/SĐT này đã được sử dụng";
        } else {
            // Đăng ký user
            $userId = $userModel->register($email, $password);
            
            if ($userId) {
                // Đăng ký thành công, tự động đăng nhập
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_email'] = $email;
                $_SESSION['is_logged_in'] = true;
                
                // Chuyển hướng đến trang thiết lập hồ sơ
                header('Location: ../views/hoso/thietlaphoso.php?success=register');
                exit();
            } else {
                $errors[] = "Có lỗi xảy ra khi đăng ký. Vui lòng thử lại";
            }
        }
    }
    
    // Nếu có lỗi, lưu vào session để hiển thị
    if (!empty($errors)) {
        $_SESSION['register_errors'] = $errors;
        $_SESSION['register_data'] = [
            'email' => $email
        ];
        header('Location: ../views/dangky/register.php');
        exit();
    }
} else {
    // Nếu không phải POST request, chuyển về trang đăng ký
    header('Location: ../views/dangky/register.php');
    exit();
}
?>