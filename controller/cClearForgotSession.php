<?php
require_once '../models/mSession.php';

Session::start();

// Xóa các session liên quan đến quên mật khẩu
Session::delete('forgot_password_step');
Session::delete('forgot_user_email');
Session::delete('forgot_errors');
Session::delete('forgot_success');

// Lấy redirect URL từ tham số hoặc về trang login
$redirect = $_GET['redirect'] ?? '../views/dangnhap/login.php';

// Redirect về trang được chỉ định
header('Location: ' . $redirect);
exit;
