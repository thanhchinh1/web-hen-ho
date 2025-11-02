<?php
// Header layout cho các trang phụ (likes, search, etc.)
if (!isset($currentUserId)) {
    $currentUserId = Session::getUserId();
}
?>
<header class="main-header" style="background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 15px 0; position: sticky; top: 0; z-index: 1000;">
    <div class="header-container" style="max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; padding: 0 20px;">
        <a href="/views/trangchu/index.php" class="logo" style="display: flex; align-items: center; gap: 10px; text-decoration: none;">
            <img src="/public/img/logo.jpg" alt="DuyenHub" style="width: 40px; height: 40px; border-radius: 50%;">
            <span class="logo-text" style="font-size: 24px; font-weight: 700; color: #e94057;">DuyenHub</span>
        </a>
        
        <nav style="display: flex; align-items: center; gap: 20px;">
            <a href="/views/trangchu/index.php" style="color: #333; text-decoration: none; font-weight: 500;">
                <i class="fas fa-home"></i> Trang chủ
            </a>
            <a href="/views/thich/nguoithichban.php" style="color: #333; text-decoration: none; font-weight: 500;">
                <i class="fas fa-heart"></i> Người thích bạn
            </a>
            <a href="/views/thich/nguoibanthich.php" style="color: #333; text-decoration: none; font-weight: 500;">
                <i class="fas fa-heart"></i> Bạn đã thích
            </a>
            <a href="/views/hoso/index.php" style="color: #333; text-decoration: none; font-weight: 500;">
                <i class="fas fa-user"></i> Hồ sơ
            </a>
            <a href="/controller/cLogout.php" class="btn-logout" style="background: #e94057; color: white; padding: 8px 20px; border-radius: 20px; text-decoration: none; font-weight: 600;">
                <i class="fas fa-sign-out-alt"></i> Đăng xuất
            </a>
        </nav>
    </div>
</header>