<?php
require_once '../models/mSession.php';
require_once '../models/mUser.php';
require_once '../models/mProfile.php';

Session::start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/dangnhap/login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$errors = [];

if (empty($email)) {
    $errors[] = 'Vui lòng nhập email hoặc số điện thoại!';
}

if (empty($password)) {
    $errors[] = 'Vui lòng nhập mật khẩu!';
}

if (!empty($errors)) {
    Session::setFlash('login_errors', $errors);
    Session::setFlash('login_data', ['email' => $email]);
    $redirectUrl = '../views/dangnhap/login.php';
    $params = [];
    if (isset($_GET['action']) && isset($_GET['targetUser'])) {
        $params[] = 'action=' . urlencode($_GET['action']);
        $params[] = 'targetUser=' . urlencode($_GET['targetUser']);
    } elseif (isset($_GET['redirect']) && isset($_GET['id'])) {
        $params[] = 'redirect=' . urlencode($_GET['redirect']);
        $params[] = 'id=' . urlencode($_GET['id']);
    }
    if (!empty($params)) {
        $redirectUrl .= '?' . implode('&', $params);
    }
    header('Location: ' . $redirectUrl);
    exit;
}

$userModel = new User();
$loginResult = $userModel->login($email, $password);

if (is_array($loginResult) && $loginResult['status'] === 'success') {
    $userId = $loginResult['userId'];
    $userRole = $loginResult['role'] ?? 'user';
    
    Session::set('user_id', $userId);
    Session::set('user_role', $userRole);
    
    // Kiểm tra role và chuyển hướng phù hợp
    if ($userRole === 'admin') {
        // Lấy thông tin admin từ bảng nguoidung
        $adminInfo = $userModel->getUserById($userId);
        
        // Set các session cần thiết cho admin
        Session::set('is_admin', true);
        Session::set('admin_id', $userId);
        Session::set('admin_name', $adminInfo['tenDangNhap'] ?? 'Admin');
        Session::set('admin_role', 'admin');
        Session::set('admin_username', $adminInfo['tenDangNhap'] ?? '');
        Session::set('admin_last_activity', time());
        
        // Chuyển về trang admin
        header('Location: ../views/admin/index.php');
        exit;
    }
    
    // Nếu là user, kiểm tra profile và xử lý như cũ
    $profileModel = new Profile();
    $hasProfile = $profileModel->hasProfile($userId);

    if (!$hasProfile) {
        if (isset($_GET['action']) && $_GET['action'] === 'like' && isset($_GET['targetUser'])) {
            Session::set('pending_like_user_id', intval($_GET['targetUser']));
        }
        header('Location: ../views/hoso/thietlaphoso.php');
        exit;
    } else {
        if (isset($_GET['action']) && $_GET['action'] === 'like' && isset($_GET['targetUser'])) {
            $targetUserId = intval($_GET['targetUser']);
            if ($targetUserId === $userId) {
                Session::setFlash('error_message', 'Bạn không thể thích chính mình!');
                header('Location: ../views/trangchu/index.php');
                exit;
            }
            require_once '../models/mLike.php';
            require_once '../models/mMatch.php';
            $likeModel = new Like();
            $matchModel = new MatchModel();
            if (!$profileModel->hasProfile($targetUserId)) {
                Session::setFlash('error_message', 'Người dùng không tồn tại!');
                header('Location: ../views/trangchu/index.php');
                exit;
            }
            if (!$likeModel->hasLiked($userId, $targetUserId)) {
                $likeModel->likeUser($userId, $targetUserId);
                if ($matchModel->canCreateMatch($userId, $targetUserId)) {
                    $matchId = $matchModel->createMatch($userId, $targetUserId);
                    if ($matchId) {
                        Session::setFlash('success_message', 'Ghép đôi thành công!  Bạn và người này đã thích nhau!');
                        Session::setFlash('match_id', $matchId);
                        Session::setFlash('matched_user_id', $targetUserId);
                        header('Location: ../views/nhantin/message.php?match=' . $matchId);
                        exit;
                    }
                }
                Session::setFlash('success_message', 'Đã thích hồ sơ thành công! ');
            } else {
                Session::setFlash('info_message', 'Bạn đã thích hồ sơ này trước đó rồi!');
            }
            header('Location: ../views/trangchu/index.php');
            exit;
        }
        if (isset($_GET['redirect']) && $_GET['redirect'] === 'profile' && isset($_GET['id'])) {
            // Thêm flag 'from_login' để xemnguoikhac.php biết user vừa đăng nhập
            header('Location: ../views/hoso/xemnguoikhac.php?id=' . urlencode($_GET['id']) . '&from_login=1');
            exit;
        } else {
            header('Location: ../views/trangchu/index.php');
            exit;
        }
    }
} elseif (is_array($loginResult) && $loginResult['status'] === 'banned') {
    $errors[] = $loginResult['message'];
    Session::setFlash('login_errors', $errors);
    Session::setFlash('login_data', ['email' => $email]);
    $redirectUrl = '../views/dangnhap/login.php';
    $params = [];
    if (isset($_GET['action']) && isset($_GET['targetUser'])) {
        $params[] = 'action=' . urlencode($_GET['action']);
        $params[] = 'targetUser=' . urlencode($_GET['targetUser']);
    } elseif (isset($_GET['redirect']) && isset($_GET['id'])) {
        $params[] = 'redirect=' . urlencode($_GET['redirect']);
        $params[] = 'id=' . urlencode($_GET['id']);
    }
    if (!empty($params)) {
        $redirectUrl .= '?' . implode('&', $params);
    }
    header('Location: ' . $redirectUrl);
    exit;
} else {
    $message = (is_array($loginResult) && isset($loginResult['message'])) 
                ? $loginResult['message'] 
                : 'Email/Số điện thoại hoặc mật khẩu không đúng!';
    $errors[] = $message;
    Session::setFlash('login_errors', $errors);
    Session::setFlash('login_data', ['email' => $email]);
    $redirectUrl = '../views/dangnhap/login.php';
    $params = [];
    if (isset($_GET['action']) && isset($_GET['targetUser'])) {
        $params[] = 'action=' . urlencode($_GET['action']);
        $params[] = 'targetUser=' . urlencode($_GET['targetUser']);
    } elseif (isset($_GET['redirect']) && isset($_GET['id'])) {
        $params[] = 'redirect=' . urlencode($_GET['redirect']);
        $params[] = 'id=' . urlencode($_GET['id']);
    }
    if (!empty($params)) {
        $redirectUrl .= '?' . implode('&', $params);
    }
    header('Location: ' . $redirectUrl);
    exit;
}
?>