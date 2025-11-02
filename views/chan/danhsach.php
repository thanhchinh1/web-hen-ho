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
    <title>Danh sách chặn - WebHenHo</title>
    <link rel="stylesheet" href="/public/css/blocked-list.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="profile-header">
        <a href="../trangchu/index.php" class="logo">
            <img src="/public/img/logo.jpg" alt="Kết Nối Yêu Thương">
            <span class="logo-text">DuyenHub</span>
        </a>
        
    </header>
    
    <!-- Back button -->
    <div class="back-button-container">
        <button class="btn-back" onclick="window.location.href='../trangchu/index.php'">
            <i class="fas fa-arrow-left"></i>
        </button>
    </div>
    
    <div class="blocked-container">
        <div class="page-header">
            <h1> Danh sách chặn</h1>
            
        </div>
        
        <?php if (empty($blockedUsers)): ?>
            <div class="empty-state">
                <i class="fas fa-user-check" style="font-size: 80px; color: #ddd; margin-bottom: 20px;"></i>
                <h2 style="color: #666;">Chưa chặn ai</h2>
                <p style="color: #999;">Bạn chưa chặn người dùng nào</p>
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
                        <img src="<?php echo $avatarSrc; ?>" 
                             alt="<?php echo htmlspecialchars($user['ten']); ?>" 
                             class="blocked-avatar">
                        <div class="blocked-info">
                            <div class="blocked-name">
                                <?php echo htmlspecialchars($user['ten']); ?>, <?php echo $age; ?>
                            </div>
                            <div class="blocked-date">
                                Chặn lúc: <?php echo date('d/m/Y H:i', strtotime($user['thoiDiemChan'])); ?>
                            </div>
                        </div>
                        <button class="btn-unblock" onclick="unblockUser(<?php echo $user['maNguoiDung']; ?>)">
                            <i class="fas fa-unlock"></i> Bỏ chặn
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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
