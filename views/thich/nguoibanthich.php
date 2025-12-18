<?php
require_once __DIR__ . '/../../models/mSession.php';
require_once __DIR__ . '/../../models/mLike.php';
require_once __DIR__ . '/../../models/mProfile.php';
require_once __DIR__ . '/../../models/mDbconnect.php';

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
$likeModel = new Like();
$profileModel = new Profile();

// Lấy danh sách người mình đã thích
$likedUsers = $likeModel->getPeopleLikedByUser($currentUserId);

// Lọc bỏ những người đã ghép đôi (mutual match)
require_once __DIR__ . '/../../models/mMatch.php';
$matchModel = new MatchModel();
$filteredUsers = [];

foreach ($likedUsers as $person) {
    // Kiểm tra đã matched chưa
    $isMatched = $matchModel->isMatched($currentUserId, $person['maNguoiDung']);
    
    // Chỉ hiển thị những người chưa matched
    if (!$isMatched) {
        $filteredUsers[] = $person;
    }
}

$likedUsers = $filteredUsers;

// Helper function để hiển thị thời gian
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return "Vừa xong";
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . " phút trước";
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . " giờ trước";
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . " ngày trước";
    } else {
        return date("d/m/Y", $timestamp);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Người bạn đã thích - DuyenHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/nguoibanthich.css?v=<?php echo time(); ?>">
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
                    <a href="../nhantin/message.php" class="menu-item">
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

    <div class="likes-wrapper">
        <button class="back-btn" onclick="window.history.back()" title="Quay lại">
            <i class="fas fa-arrow-left"></i>
        </button>
        
        <div class="likes-container">
            <div class="likes-header">
                <h1>Người bạn đã thích</h1>
                <p>Danh sách những người bạn đã thể hiện sự quan tâm</p>
            </div>
            
        <?php if (empty($likedUsers)): ?>
            <div class="empty-state">
                <i class="fas fa-heart-broken"></i>
                <h2>Chưa có ai được thích</h2>
                <p>Hãy khám phá và tìm kiếm người phù hợp với bạn!</p>
                <a href="/views/trangchu/index.php" class="btn-explore">
                    Khám phá
                </a>
            </div>
        <?php else: ?>
            <div class="likes-list">
                <?php foreach ($likedUsers as $person): 
                    $age = $profileModel->calculateAge($person['ngaySinh']);
                    // Xử lý đường dẫn avatar
                    if (!empty($person['avt'])) {
                        // Kiểm tra nếu đã có đường dẫn đầy đủ
                        if (strpos($person['avt'], '/') === 0) {
                            $avatarSrc = htmlspecialchars($person['avt']);
                        } elseif (strpos($person['avt'], 'public/') === 0) {
                            $avatarSrc = '/' . htmlspecialchars($person['avt']);
                        } else {
                            $avatarSrc = '/public/uploads/avatars/' . htmlspecialchars($person['avt']);
                        }
                    } else {
                        $avatarSrc = '/public/img/default-avatar.jpg';
                    }
                ?>
                    <div class="like-item" id="like-<?php echo $person['maNguoiDung']; ?>" onclick="window.location.href='/views/hoso/xemnguoikhac.php?id=<?php echo $person['maNguoiDung']; ?>'">
                        <div class="like-avatar">
                            <img src="<?php echo $avatarSrc; ?>" 
                                 alt="<?php echo htmlspecialchars($person['ten']); ?>">
                        </div>
                        <div class="like-info">
                            <h3 class="like-name">
                                <?php echo htmlspecialchars($person['ten']); ?>, <?php echo $age; ?>
                            </h3>
                            <p class="like-location">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($person['noiSong'] ?? 'N/A'); ?>
                            </p>
                            <p class="like-date">
                                <i class="fas fa-heart"></i> Đã thích <?php echo timeAgo($person['thoiDiemThich']); ?>
                            </p>
                        </div>
                        <button class="btn-unlike" onclick="event.stopPropagation(); unlikeUser(<?php echo $person['maNguoiDung']; ?>)">
                            <i class="fas fa-heart-broken"></i> Bỏ thích
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        </div>
    </div>
    
    
    <script>
        function unlikeUser(targetUserId) {
            console.log('unlikeUser called with userId:', targetUserId);
            
            if (!confirm('Bạn có chắc muốn bỏ thích người này?')) {
                return;
            }
            
            console.log('Sending request to /controller/cLike.php');
            
            // Lấy CSRF token
            const csrfToken = '<?php echo Session::getCSRFToken(); ?>';
            
            fetch('/controller/cLike.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'targetUserId=' + targetUserId + '&csrf_token=' + csrfToken
            })
            .then(res => {
                console.log('Response status:', res.status);
                return res.json();
            })
            .then(data => {
                console.log('Response data:', data);
                
                if (data.success && data.action === 'unliked') {
                    // Remove card with animation
                    const item = document.getElementById('like-' + targetUserId);
                    if (item) {
                        item.style.opacity = '0';
                        item.style.transform = 'translateX(-20px)';
                        setTimeout(() => {
                            item.remove();
                            
                            // Check if empty
                            const list = document.querySelector('.likes-list');
                            if (list && list.children.length === 0) {
                                location.reload();
                            }
                        }, 300);
                    }   
                    
                    showNotification('Đã bỏ thích!', 'success');
                } else {
                    showNotification(data.message || 'Có lỗi xảy ra!', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Có lỗi xảy ra!', 'error');
            });
        }
        
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = 'notification ' + type;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <p>${message}</p>
            `;
            document.body.appendChild(notification);
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 2000);
        }
    </script>
</body>
</html>