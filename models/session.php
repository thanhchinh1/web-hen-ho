<?php
// Bắt đầu session nếu chưa được bắt đầu
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Hàm kiểm tra người dùng đã đăng nhập chưa
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;
}

// Hàm yêu cầu đăng nhập - chuyển hướng nếu chưa đăng nhập
function requireLogin($redirectTo = '../../views/dangnhap/login.php') {
    if (!isLoggedIn()) {
        header('Location: ' . $redirectTo);
        exit();
    }
}

// Hàm đăng xuất
function logout($redirectTo = '../../views/dangnhap/login.php') {
    // Xóa tất cả session variables
    $_SESSION = array();
    
    // Xóa session cookie nếu có
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Hủy session
    session_destroy();
    
    // Chuyển hướng
    header('Location: ' . $redirectTo);
    exit();
}

// Hàm lấy ID người dùng hiện tại
function getCurrentUserId() {
    return isLoggedIn() ? $_SESSION['user_id'] : null;
}

// Hàm lấy email người dùng hiện tại
function getCurrentUserEmail() {
    return isLoggedIn() ? $_SESSION['user_email'] : null;
}

// Hàm chuyển hướng user đã đăng nhập về trang chủ
function redirectIfLoggedIn($redirectTo = '../../views/trangchu/index.php') {
    if (isLoggedIn()) {
        header('Location: ' . $redirectTo);
        exit();
    }
}
?>