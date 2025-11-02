<?php
session_start();
require_once '../models/mDbconnect.php';
require_once '../models/mAdmin.php';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// CSRF Protection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['flash_error'] = 'Invalid CSRF token. Vui lòng thử lại.';
        header('Location: ../views/admin/dangky.php');
        exit();
    }
    
    // Regenerate CSRF token after use
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    
    $db = clsConnect::getInstance();
    $conn = $db->connect();
    $adminModel = new Admin($conn);
    
    // Get form data
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'moderator';
    $secretKey = $_POST['secret_key'] ?? '';
    
    // Validation
    $errors = [];
    
    // Check secret key (bảo mật để không ai cũng tạo được admin)
    // Bạn có thể đổi secret key này
    if ($secretKey !== 'FG') {
        $errors[] = 'Mã bảo mật không đúng';
    }
    
    if (empty($username)) {
        $errors[] = 'Vui lòng nhập tên đăng nhập';
    } elseif (strlen($username) < 4) {
        $errors[] = 'Tên đăng nhập phải có ít nhất 4 ký tự';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Tên đăng nhập chỉ được chứa chữ cái, số và dấu gạch dưới';
    }
    
    if (empty($password)) {
        $errors[] = 'Vui lòng nhập mật khẩu';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự';
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = 'Mật khẩu xác nhận không khớp';
    }
    
    if (empty($fullName)) {
        $errors[] = 'Vui lòng nhập họ tên';
    }
    
    if (empty($email)) {
        $errors[] = 'Vui lòng nhập email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email không hợp lệ';
    }
    
    if (!in_array($role, ['super_admin', 'moderator', 'support'])) {
        $errors[] = 'Vai trò không hợp lệ';
    }
    
    // Check if username exists
    if (empty($errors)) {
        $checkQuery = "SELECT maAdmin FROM Admin WHERE tenDangNhap = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'Tên đăng nhập đã tồn tại';
        }
    }
    
    // Check if email exists
    if (empty($errors)) {
        $checkQuery = "SELECT maAdmin FROM Admin WHERE email = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'Email đã được sử dụng';
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['flash_error'] = implode('<br>', $errors);
        $_SESSION['form_data'] = $_POST;
        header('Location: ../views/admin/dangky.php');
        exit();
    }
    
    // Create admin account
    try {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO Admin (tenDangNhap, matKhau, hoTen, email, vaiTro, trangThai) 
                  VALUES (?, ?, ?, ?, ?, 'active')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssss", $username, $hashedPassword, $fullName, $email, $role);
        
        if ($stmt->execute()) {
            $_SESSION['flash_success'] = 'Tạo tài khoản admin thành công! Bạn có thể đăng nhập ngay.';
            header('Location: ../views/admin/dangnhap.php');
            exit();
        } else {
            throw new Exception('Không thể tạo tài khoản');
        }
    } catch (Exception $e) {
        $_SESSION['flash_error'] = 'Lỗi: ' . $e->getMessage();
        $_SESSION['form_data'] = $_POST;
        header('Location: ../views/admin/dangky.php');
        exit();
    }
} else {
    // Redirect to register page
    header('Location: ../views/admin/dangky.php');
    exit();
}
?>
