<?php
require_once '../models/mSession.php';
require_once '../models/mUser.php';
require_once '../models/mProfile.php';

Session::start();

// Chỉ chấp nhận POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/dangnhap/login.php');
    exit;
}

// Lấy dữ liệu từ form
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Mảng lưu lỗi
$errors = [];

// Validate
if (empty($email)) {
    $errors[] = 'Vui lòng nhập email hoặc số điện thoại!';
}

if (empty($password)) {
    $errors[] = 'Vui lòng nhập mật khẩu!';
}

// Nếu có lỗi, quay lại trang đăng nhập
if (!empty($errors)) {
    Session::set('login_errors', $errors);
    Session::set('login_data', ['email' => $email]);
    
    // Giữ lại redirect params nếu có
    $redirectUrl = '../views/dangnhap/login.php';
    if (isset($_GET['redirect']) && isset($_GET['id'])) {
        $redirectUrl .= '?redirect=' . urlencode($_GET['redirect']) . '&id=' . urlencode($_GET['id']);
    }
    
    header('Location: ' . $redirectUrl);
    exit;
}

// Xử lý đăng nhập
$userModel = new User();
$userId = $userModel->login($email, $password);

if ($userId) {
    // Đăng nhập thành công
    Session::set('user_id', $userId);
    
    // Kiểm tra xem user đã có hồ sơ chưa
    $profileModel = new Profile();
    $hasProfile = $profileModel->hasProfile($userId);
    
    if (!$hasProfile) {
        // Chưa có hồ sơ -> chuyển đến trang thiết lập hồ sơ
        header('Location: ../views/hoso/thietlaphoso.php');
        exit;
    } else {
        // Đã có hồ sơ -> kiểm tra có redirect không
        if (isset($_GET['redirect']) && $_GET['redirect'] === 'profile' && isset($_GET['id'])) {
            // Redirect đến xem hồ sơ người khác
            header('Location: ../views/hoso/xemnguoikhac.php?id=' . urlencode($_GET['id']));
            exit;
        } else {
            // Chuyển đến trang chủ đã đăng nhập
            header('Location: ../views/trangchu/index.php');
            exit;
        }
    }
} else {
    // Đăng nhập thất bại
    $errors[] = 'Email/Số điện thoại hoặc mật khẩu không đúng!';
    Session::set('login_errors', $errors);
    Session::set('login_data', ['email' => $email]);
    
    // Giữ lại redirect params nếu có
    $redirectUrl = '../views/dangnhap/login.php';
    if (isset($_GET['redirect']) && isset($_GET['id'])) {
        $redirectUrl .= '?redirect=' . urlencode($_GET['redirect']) . '&id=' . urlencode($_GET['id']);
    }
    
    header('Location: ' . $redirectUrl);
    exit;
}
?>
