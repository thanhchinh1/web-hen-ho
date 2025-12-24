<?php
require_once __DIR__ . '/../../models/mSession.php';
require_once __DIR__ . '/../../models/mLike.php';
require_once __DIR__ . '/../../models/mProfile.php';
require_once __DIR__ . '/../../models/mDbconnect.php';
require_once __DIR__ . '/../../models/mNotification.php';
require_once __DIR__ . '/../../models/mMessage.php';

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

// Đếm số ghép đôi mới và tin nhắn chưa đọc
$notificationModel = new Notification();
$newMatchesCount = $notificationModel->getNewMatchesCount($currentUserId);
$messageModel = new Message();
$unreadMessagesCount = $messageModel->getTotalUnreadCount($currentUserId);

// Lấy danh sách người đã thích mình
$likedByUsers = $likeModel->getPeopleWhoLikedUser($currentUserId);

// Lọc bỏ những người đã ghép đôi (mutual match)
require_once __DIR__ . '/../../models/mMatch.php';
$matchModel = new MatchModel();
$filteredUsers = [];

foreach ($likedByUsers as $person) {
    // Kiểm tra đã matched chưa
    $isMatched = $matchModel->isMatched($currentUserId, $person['maNguoiDung']);
    
    // Kiểm tra mình đã like lại chưa
    $alreadyLikedBack = $likeModel->hasLiked($currentUserId, $person['maNguoiDung']);
    
    // Chỉ hiển thị những người chưa matched
    if (!$isMatched) {
        $person['alreadyLikedBack'] = $alreadyLikedBack;
        $filteredUsers[] = $person;
    }
}

$likedByUsers = $filteredUsers;

// Lấy danh sách người đã ghép đôi mà HỌ THÍCH MÌNH TRƯỚC
$matchedUsers = $matchModel->getMatchesTheyLikedFirst($currentUserId);

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
    <title>Người thích bạn - DuyenHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/nguoithichban.css?v=<?php echo time(); ?>">
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
                    <a href="../nhantin/message.php" class="menu-item" style="position: relative;">
                        <i class="fas fa-comments"></i>
                        <span>Tin nhắn</span>
                        <?php if ($unreadMessagesCount > 0): ?>
                        <span class="notification-badge" id="messagesBadge" style="position: absolute; top: -5px; right: -5px; background: #ff4757; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold;"><?php echo $unreadMessagesCount; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="../timkiem/ghepdoinhanh.php" class="menu-item" style="position: relative;">
                        <i class="fas fa-search"></i>
                        <span>Tìm kiếm</span>
                        <?php if ($newMatchesCount > 0): ?>
                        <span class="notification-badge" id="matchesBadge" style="position: absolute; top: -5px; right: -5px; background: #ff6b9d; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold;"><?php echo $newMatchesCount; ?></span>
                        <?php endif; ?>
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
        <div class="likes-container" style="position:relative;">
            <button class="back-btn" onclick="window.history.back()" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </button>
            <div class="likes-header">
                <h1>Người thích bạn</h1>
                <p>Những người đã thể hiện sự quan tâm đến bạn</p>
            </div>
        
            <!-- Card Đã ghép đôi -->
        <?php if (!empty($matchedUsers)): ?>
            <div class="matched-section">
                <h2 class="section-title">
                    <i class="fas fa-heart"></i> Đã ghép đôi (<?php echo count($matchedUsers); ?>)
                </h2>
                <div class="matches-grid">
                    <?php foreach ($matchedUsers as $match): 
                        $age = $profileModel->calculateAge($match['ngaySinh']);
                        
                        // Xử lý đường dẫn avatar
                        if (!empty($match['avt'])) {
                            if (strpos($match['avt'], '/') === 0) {
                                $avatarSrc = htmlspecialchars($match['avt']);
                            } elseif (strpos($match['avt'], 'public/') === 0) {
                                $avatarSrc = '/' . htmlspecialchars($match['avt']);
                            } else {
                                $avatarSrc = '/public/uploads/avatars/' . htmlspecialchars($match['avt']);
                            }
                        } else {
                            $avatarSrc = '/public/img/default-avatar.jpg';
                        }
                        
                        $matchDate = date('d/m/Y', strtotime($match['thoiDiemGhepDoi']));
                    ?>
                    <div class="match-card" onclick="window.location.href='/views/hoso/xemnguoikhac.php?id=<?php echo $match['maNguoiDung']; ?>'">
                        <div class="match-avatar">
                            <img src="<?php echo $avatarSrc; ?>" 
                                 alt="<?php echo htmlspecialchars($match['ten']); ?>">
                            <div class="match-badge">
                                <i class="fas fa-heart"></i>
                            </div>
                        </div>
                        <div class="match-info">
                            <h3 class="match-name"><?php echo htmlspecialchars($match['ten']); ?>, <?php echo $age; ?></h3>
                            <p class="match-location">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($match['noiSong'] ?? 'N/A'); ?>
                            </p>
                            <p class="match-date">
                                Ghép đôi: <?php echo $matchDate; ?>
                            </p>
                        </div>
                        <div class="match-actions">
                            <a href="/views/nhantin/message.php?matchId=<?php echo $match['maGhepDoi']; ?>" 
                               class="btn-chat"
                               onclick="event.stopPropagation();">
                                <i class="fas fa-comment"></i> Nhắn tin
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
            
        <?php if (empty($likedByUsers)): ?>
            <div class="empty-state">
                <i class="fas fa-heart-broken"></i>
                <h2>Chưa có ai thích bạn</h2>
                <p>Hoàn thiện hồ sơ của bạn để thu hút nhiều người hơn!</p>
                <a href="/views/hoso/chinhsua.php" class="btn-explore">
                    Chỉnh sửa hồ sơ
                </a>
            </div>
        <?php else: ?>
            <div class="likes-list">
                <?php foreach ($likedByUsers as $person): 
                    $age = $profileModel->calculateAge($person['ngaySinh']);
                    $alreadyLikedBack = $person['alreadyLikedBack'] ?? false;
                    
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
                                Đã thích bạn
                            </p>
                        </div>
                        <?php if (!$alreadyLikedBack): ?>
                            <button class="btn-like-back" onclick="event.stopPropagation(); likeBack(<?php echo $person['maNguoiDung']; ?>)">
                                <i class="fas fa-heart"></i> Thích lại
                            </button>
                        <?php else: ?>
                            <button class="btn-liked" onclick="event.stopPropagation();" disabled>
                                <i class="fas fa-check"></i> Đã thích
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        </div>
    </div>
    
    
    <script>
        function likeBack(userId) {
            console.log('likeBack called with userId:', userId);
            
            if (!confirm('Bạn có muốn thích lại người này?')) {
                return;
            }
            
            console.log('Sending request to /controller/cMatch.php');
            
            // Lấy CSRF token
            const csrfToken = '<?php echo Session::getCSRFToken(); ?>';
            
            fetch('/controller/cMatch.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=like_back&targetUserId=' + userId + '&csrf_token=' + csrfToken
            })
            .then(res => {
                console.log('Response status:', res.status);
                return res.json();
            })
            .then(data => {
                console.log('Response data:', data);
                
                if (data.success) {
                    // Lấy element
                    const likeItem = document.getElementById('like-' + userId);
                    
                    if (likeItem) {
                        // Animation
                        likeItem.style.opacity = '0';
                        likeItem.style.transform = 'translateX(-100px)';
                        
                        setTimeout(() => {
                            likeItem.remove();
                            
                            // Kiểm tra nếu không còn item nào
                            const likesList = document.querySelector('.likes-list');
                            if (likesList && likesList.children.length === 0) {
                                location.reload();
                            }
                        }, 300);
                    }
                    
                    // Hiển thị thông báo
                    if (data.matched) {
                        showNotification('success', '💕 Ghép đôi thành công! Bạn có thể nhắn tin với người này.');
                        
                        // Chuyển đến trang chat sau 2 giây
                        if (data.redirect) {
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 2000);
                        }
                    } else {
                        showNotification('success', data.message || 'Đã thích lại thành công!');
                    }
                } else {
                    showNotification('error', data.message || 'Có lỗi xảy ra!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('error', 'Có lỗi xảy ra, vui lòng thử lại!');
            });
        }
        
        function showNotification(type, message) {
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
            }, 3000);
        }
    </script>

    <!-- Script cập nhật trạng thái online -->
    <script>
        function updateOnlineStatus() {
            fetch('../../controller/cUpdateOnlineStatus.php', {method: 'POST'})
            .then(response => response.json())
            .catch(error => console.error('Error:', error));
        }
        updateOnlineStatus();
        setInterval(updateOnlineStatus, 120000);
    </script>

    <!-- Script check thông báo -->
    <script>
        let lastNotificationCount = <?php echo ($newMatchesCount + $unreadMessagesCount); ?>;
        function checkNotifications() {
            fetch('../../controller/cCheckNotifications.php', {method: 'GET', cache: 'no-cache'})
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const messagesBadge = document.getElementById('messagesBadge');
                    if (data.unreadMessages > 0) {
                        if (messagesBadge) {
                            messagesBadge.textContent = data.unreadMessages;
                        } else {
                            const messagesLink = document.querySelector('a[href="../nhantin/message.php"]');
                            if (messagesLink) {
                                const badge = document.createElement('span');
                                badge.id = 'messagesBadge';
                                badge.textContent = data.unreadMessages;
                                badge.style.cssText = 'position: absolute; top: -5px; right: -5px; background: #ff4757; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold;';
                                messagesLink.appendChild(badge);
                            }
                        }
                    } else if (messagesBadge) messagesBadge.remove();
                    const matchesBadge = document.getElementById('matchesBadge');
                    if (data.newMatches > 0) {
                        if (matchesBadge) {
                            matchesBadge.textContent = data.newMatches;
                        } else {
                            const searchLink = document.querySelector('a[href="../timkiem/ghepdoinhanh.php"]');
                            if (searchLink) {
                                const badge = document.createElement('span');
                                badge.id = 'matchesBadge';
                                badge.textContent = data.newMatches;
                                badge.style.cssText = 'position: absolute; top: -5px; right: -5px; background: #ff6b9d; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold;';
                                searchLink.appendChild(badge);
                            }
                        }
                    } else if (matchesBadge) matchesBadge.remove();
                    const currentTotal = data.unreadMessages + data.newMatches;
                    if (currentTotal > lastNotificationCount) {
                        let message = '';
                        if (data.unreadMessages > 0 && data.newMatches > 0) {
                            message = `💬 ${data.unreadMessages} tin nhắn mới và 💕 ${data.newMatches} ghép đôi mới!`;
                        } else if (data.unreadMessages > 0) {
                            message = `💬 Bạn có ${data.unreadMessages} tin nhắn mới!`;
                        } else if (data.newMatches > 0) {
                            message = `💕 Bạn có ${data.newMatches} ghép đôi mới!`;
                        }
                        if (message) {
                            const notif = document.createElement('div');
                            notif.style.cssText = 'position: fixed; top: 80px; right: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 18px 25px; border-radius: 15px; font-size: 15px; font-weight: 600; box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4); z-index: 10000; cursor: pointer; animation: slideInRight 0.5s ease; max-width: 350px;';
                            notif.innerHTML = `
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <i class="fas fa-bell" style="font-size: 24px;"></i>
                                    <div>
                                        <div style="font-size: 16px; margin-bottom: 4px;">${message}</div>
                                        <div style="font-size: 12px; opacity: 0.9;">Click để xem ngay →</div>
                                    </div>
                                </div>
                            `;
                            notif.onclick = () => { window.location.href = '../nhantin/message.php'; };
                            document.body.appendChild(notif);
                            setTimeout(() => { notif.style.animation = 'slideOutRight 0.3s ease'; setTimeout(() => notif.remove(), 300); }, 2000);
                        }
                    }
                    lastNotificationCount = currentTotal;
                }
            }).catch(error => console.error('Error:', error));
        }
        setTimeout(checkNotifications, 2000);
        setInterval(checkNotifications, 500);
    </script>
    <style>
        @keyframes slideInRight { from { transform: translateX(400px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes slideOutRight { from { transform: translateX(0); opacity: 1; } to { transform: translateX(400px); opacity: 0; } }
    </style>
</body>
</html>
    
</body>
</html>