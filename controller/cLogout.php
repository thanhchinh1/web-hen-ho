<?php
require_once '../models/mSession.php';

// Hủy session và đăng xuất
Session::destroy();

// Chuyển về trang chủ
header('Location: ../index.php');
exit;
?>
