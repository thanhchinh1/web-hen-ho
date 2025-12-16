<?php
require_once __DIR__ . '/../../models/mSession.php';
require_once __DIR__ . '/../../models/mBlock.php';
require_once __DIR__ . '/../../models/mProfile.php';

Session::start();

if (!Session::isLoggedIn()) {
    header("Location: /views/dangnhap/login.php");
    exit();
}

// Kiểm tra role - nếu là admin thì chuyển về trang admin
$userRole = Session::get('user_role');
if ($userRole === 'admin') {
    header('Location: /views/admin/index.php');
    exit;
}

$currentUserId = Session::getUserId();
$blockModel = new Block();
$profileModel = new Profile();

$blockedUsers = $blockModel->getBlockedUsers($currentUserId);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách chặn - DuyenHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/danhsachchan.css?v=<?php echo time(); ?>">
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="header-container">
            <div class="header-left">
                <a href="../trangchu/index.php" class="logo">
                    <img src="../../public/img/logo.jpg" alt="DuyenHub Logo">
                    <span class="logo-text">DuyenHub</span>
                </a>
                <nav class="header-menu">
                    <a href="../trangchu/index.php" class="menu-item active">
                        <i class="fas fa-home"></i>
                        <span>Trang chủ</span>
                    </a>
                    <a href="../nhantin/chat.php" class="menu-item">
                        <i class="fas fa-comments"></i>
                        <span>Tin nhắn</span>
                    </a>
                    <a href="../timkiem/ghepdoinhanh.php" class="menu-item">
                        <i class="fas fa-search"></i>
                        <span>Tìm kiếm</span>
                    </a>
                    <a href="#" class="menu-item">
                        <i class="fas fa-question-circle"></i>
                        <span>Trợ giúp</span>
                    </a>
                </nav>
            </div>
            <div class="header-actions">
                <!-- Đã xóa nút đăng xuất để đồng bộ với yêu cầu -->
            </div>
        </div>
    </header>

    <div class="blocked-wrapper">
        <button class="back-btn" onclick="window.history.back()" title="Quay lại">
            <i class="fas fa-arrow-left"></i>
        </button>
        
        <div class="blocked-container">
            <div class="blocked-header">
                <h1>Danh sách người đã chặn</h1>
                <p>Quản lý những người bạn đã chặn</p>
            </div>
            
        <?php if (empty($blockedUsers)): ?>
            <div class="empty-state">
                <i class="fas fa-user-slash"></i>
                <h2>Chưa chặn ai</h2>
                <p>Bạn chưa chặn người dùng nào</p>
            </div>
        <?php else: ?>
            <div class="blocked-list">
                <?php foreach ($blockedUsers as $user): 
                    $avatarSrc = '/public/img/default-avatar.jpg';
                    if (!empty($user['avt'])) {
                        if (strpos($user['avt'], 'public/') === 0) {
                            $avatarSrc = '/' . htmlspecialchars($user['avt']);
                        } else {
                            $avatarSrc = '/public/uploads/avatars/' . htmlspecialchars($user['avt']);
                        }
                    }
                    
                    $age = $profileModel->calculateAge($user['ngaySinh']);
                ?>
                    <div class="blocked-item" id="blocked-<?php echo $user['maNguoiDung']; ?>">
                        <div class="blocked-avatar">
                            <img src="<?php echo $avatarSrc; ?>" 
                                 alt="<?php echo htmlspecialchars($user['ten']); ?>">
                        </div>
                        <div class="blocked-info">
                            <h3 class="blocked-name">
                                <?php echo htmlspecialchars($user['ten']); ?>
                            </h3>
                            <p class="blocked-age"><?php echo $age; ?> tuổi</p>
                            <p class="blocked-date">
                                <i class="fas fa-clock"></i>
                                Chặn lúc: <?php echo date('d/m/Y H:i', strtotime($user['thoiDiemChan'])); ?>
                            </p>
                        </div>
                        <button class="btn-unblock" onclick="unblockUser(<?php echo $user['maNguoiDung']; ?>)">
                            <i class="fas fa-unlock"></i> Bỏ chặn
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        </div>
    </div>
    
    <script>
        function unblockUser(userId) {
            if (confirm('Bạn có chắc muốn bỏ chặn người này?')) {
                fetch('/controller/cBlock.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=unblock&targetUserId=' + userId + '&csrf_token=<?php echo Session::getCSRFToken(); ?>'
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Xóa item khỏi danh sách
                        const item = document.getElementById('blocked-' + userId);
                        item.style.transition = 'all 0.3s';
                        item.style.opacity = '0';
                        item.style.transform = 'translateX(100px)';
                        
                        setTimeout(() => {
                            item.remove();
                            
                            // Kiểm tra nếu không còn ai
                            const list = document.querySelector('.blocked-list');
                            if (list && list.children.length === 0) {
                                location.reload();
                            }
                        }, 300);
                        
                        showNotification(data.message);
                    } else {
                        showNotification(data.message || 'Có lỗi xảy ra!', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Có lỗi xảy ra!', 'error');
                });
            }
        }
        
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.textContent = message;
            const bgColor = type === 'error' ? '#dc3545' : '#28a745';
            notification.style.cssText = `
                position: fixed;
                top: 100px;
                left: 50%;
                transform: translateX(-50%);
                background: ${bgColor};
                color: white;
                padding: 15px 30px;
                border-radius: 25px;
                font-size: 16px;
                font-weight: 600;
                box-shadow: 0 5px 20px rgba(0,0,0,0.3);
                z-index: 10000;
            `;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 2000);
        }
    </script>
    
    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>
