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
    <title>Người bạn đã thích - WebHenHo</title>
    <link rel="stylesheet" href="/public/css/home.css">
    <link rel="stylesheet" href="/public/css/likes.css">
    <link rel="stylesheet" href="/public/css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Header styles */
        .profile-header {
            background: white;
            padding: 12px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .logo img {
            display: block;
            max-height: 60px;
            width: auto;
        }

        .logo-text {
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, #FF6B9D 0%, #FF8DB4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-family: 'Segoe UI', sans-serif;
            letter-spacing: 0.5px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .btn-logout {
            background: linear-gradient(135deg, #FF6B9D 0%, #FF8DB4 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 107, 157, 0.3);
        }

        .likes-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .page-header {
            margin-bottom: 30px;
        }
        
        .page-header h1 {
            color: #e94057;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .page-header p {
            color: #666;
            font-size: 16px;
        }
        
        .profiles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .profile-card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }
        
        .profile-image {
            position: relative;
            width: 100%;
            height: 300px;
            overflow: hidden;
            cursor: pointer;
        }
        
        .profile-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .liked-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(233, 64, 87, 0.9);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .profile-info {
            padding: 15px;
        }
        
        .profile-name {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .profile-details {
            color: #666;
            font-size: 14px;
            margin-bottom: 12px;
        }
        
        .profile-details span {
            display: inline-block;
            margin-right: 15px;
        }
        
        .profile-bio {
            color: #888;
            font-size: 14px;
            line-height: 1.4;
            margin-bottom: 15px;
            max-height: 60px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .profile-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn-view-profile, .btn-unlike {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-view-profile {
            background: #e94057;
            color: white;
        }
        
        .btn-view-profile:hover {
            background: #d63447;
        }
        
        .btn-unlike {
            background: #f5f5f5;
            color: #666;
        }
        
        .btn-unlike:hover {
            background: #e0e0e0;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-state svg {
            width: 120px;
            height: 120px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-state h2 {
            color: #666;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #999;
            font-size: 16px;
            margin-bottom: 20px;
        }

        /* Back button */
        .back-button-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .btn-back {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #5BC0DE;
            color: white;
            border: none;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(91, 192, 222, 0.3);
        }

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(91, 192, 222, 0.4);
        }
        
        .btn-explore {
            display: inline-block;
            padding: 12px 30px;
            background: #e94057;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: background 0.3s;
        }
        
        .btn-explore:hover {
            background: #d63447;
        }
    </style>
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
    
    <div class="likes-container">
        <div class="page-header">
            <h1>Người bạn đã thích</h1>
        </div>
        
        <?php if (empty($likedUsers)): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" 
                          stroke="#e94057" stroke-width="2" fill="none"/>
                </svg>
                <h2>Chưa có ai được thích</h2>
                <p>Hãy khám phá và tìm kiếm người phù hợp với bạn!</p>
                <a href="/views/trangchu/index.php" class="btn-explore">
                    <i class="fas fa-search"></i> Khám phá ngay
                </a>
            </div>
        <?php else: ?>
            <div class="profiles-grid">
                <?php foreach ($likedUsers as $person): 
                    $age = $profileModel->calculateAge($person['ngaySinh']);
                    // Xử lý đường dẫn avatar
                    if (!empty($person['avt'])) {
                        // Nếu đã có 'public/' trong đường dẫn
                        if (strpos($person['avt'], 'public/') === 0) {
                            $avatarSrc = '/' . htmlspecialchars($person['avt']);
                        } else {
                            $avatarSrc = '/public/uploads/avatars/' . htmlspecialchars($person['avt']);
                        }
                    } else {
                        $avatarSrc = '/public/img/default-avatar.jpg';
                    }
                ?>
                    <div class="profile-card">
                        <div class="profile-image" onclick="window.location.href='/views/hoso/xemnguoikhac.php?id=<?php echo $person['maNguoiDung']; ?>'">
                            <img src="<?php echo $avatarSrc; ?>" 
                                 alt="<?php echo htmlspecialchars($person['ten']); ?>">
                            <div class="liked-badge">
                                ❤️ <?php echo timeAgo($person['thoiDiemThich']); ?>
                            </div>
                        </div>
                        <div class="profile-info">
                            <div class="profile-name"><?php echo htmlspecialchars($person['ten']); ?>, <?php echo $age; ?></div>
                            <div class="profile-details">
                                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($person['noiSong'] ?? 'N/A'); ?></span>
                            </div>
                            <?php if (!empty($person['moTa'])): ?>
                                <div class="profile-bio">
                                    <?php echo htmlspecialchars($person['moTa']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="profile-actions">
                                <a href="/views/hoso/xemnguoikhac.php?id=<?php echo $person['maNguoiDung']; ?>" 
                                   class="btn-view-profile">
                                    Xem hồ sơ
                                </a>
                                <button class="btn-unlike" onclick="unlikeProfile(<?php echo $person['maNguoiDung']; ?>, this)">
                                    <i class="fas fa-heart-broken"></i> Bỏ thích
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include __DIR__ . '/../layouts/footer.php'; ?>
    
    <script>
        function unlikeProfile(targetUserId, button) {
            console.log('unlikeProfile called with userId:', targetUserId);
            
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
                    const card = button.closest('.profile-card');
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.8)';
                    setTimeout(() => {
                        card.remove();
                        
                        // Check if empty
                        const grid = document.querySelector('.profiles-grid');
                        if (grid && grid.children.length === 0) {
                            location.reload();
                        }
                    }, 300);
                    
                    showNotification('Đã bỏ thích!');
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
            notification.textContent = message;
            const bgColor = type === 'error' ? '#dc3545' : '#e94057';
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
                box-shadow: 0 5px 20px rgba(233, 64, 87, 0.3);
                z-index: 10000;
                animation: slideDown 0.3s ease;
            `;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 2000);
        }
    </script>
    <style>
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translate(-50%, -20px);
            }
            to {
                opacity: 1;
                transform: translate(-50%, 0);
            }
        }
        
        .profile-card {
            transition: opacity 0.3s, transform 0.3s;
        }
    </style>
</body>
</html>