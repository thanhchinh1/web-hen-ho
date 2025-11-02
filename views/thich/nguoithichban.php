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
$likedByUsers = $likeModel->getPeopleWhoLikedUser($currentUserId);

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
    <title>Người đã thích bạn - WebHenHo</title>
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
        
        .btn-view-profile {
            flex: 1;
            padding: 10px;
            background: #e94057;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s;
        }
        
        .btn-view-profile:hover {
            background: #d63447;
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
            <h1>Người thích bạn</h1>
        </div>
        
        <?php if (empty($likedByUsers)): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" 
                          stroke="#e94057" stroke-width="2" fill="none"/>
                </svg>
                <h2>Chưa có ai thích bạn</h2>
                <p>Hoàn thiện hồ sơ của bạn để thu hút nhiều người hơn!</p>
            </div>
        <?php else: ?>
            <div class="profiles-grid">
                <?php foreach ($likedByUsers as $person): 
                    $age = $profileModel->calculateAge($person['ngaySinh']);
                    
                    // Kiểm tra mình đã like lại chưa
                    $alreadyLikedBack = $likeModel->hasLiked($currentUserId, $person['maNguoiDung']);
                    
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
                    <div class="profile-card" id="card-<?php echo $person['maNguoiDung']; ?>">
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
                                <?php if (!$alreadyLikedBack): ?>
                                    <button class="btn-like-back" onclick="likeBack(<?php echo $person['maNguoiDung']; ?>)">
                                        <i class="fas fa-heart"></i> Thích lại
                                    </button>
                                <?php else: ?>
                                    <button class="btn-matched" disabled>
                                        <i class="fas fa-check-circle"></i> Đã ghép đôi
                                    </button>
                                <?php endif; ?>
                                <a href="/views/hoso/xemnguoikhac.php?id=<?php echo $person['maNguoiDung']; ?>" 
                                   class="btn-view-profile">
                                    Xem hồ sơ
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function likeBack(userId) {
            console.log('Like back user:', userId);
            
            // Disable button ngay lập tức
            const button = event.target;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
            
            // Gửi request
            fetch('/controller/cMatch.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=like_back&targetUserId=' + userId + '&csrf_token=<?php echo Session::getCSRFToken(); ?>'
            })
            .then(response => response.json())
            .then(data => {
                console.log('Response:', data);
                
                if (data.success) {
                    if (data.matched) {
                        // Xóa card khỏi danh sách
                        const card = document.getElementById('card-' + userId);
                        if (card) {
                            card.style.transition = 'all 0.3s ease';
                            card.style.opacity = '0';
                            card.style.transform = 'scale(0.8)';
                            
                            setTimeout(() => {
                                card.remove();
                                
                                // Kiểm tra nếu không còn card nào
                                const grid = document.querySelector('.profiles-grid');
                                if (grid && grid.children.length === 0) {
                                    // Hiển thị empty state
                                    const container = document.querySelector('.likes-container');
                                    const header = container.querySelector('.page-header');
                                    container.innerHTML = '';
                                    container.appendChild(header);
                                    container.innerHTML += `
                                        <div class="empty-state">
                                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="width: 120px; height: 120px; margin: 0 auto 20px; opacity: 0.5;">
                                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" 
                                                      stroke="#e94057" stroke-width="2" fill="none"/>
                                            </svg>
                                            <h2 style="color: #666; font-size: 24px; margin-bottom: 10px;">Tuyệt vời!</h2>
                                            <p style="color: #999; font-size: 16px;">Bạn đã ghép đôi với tất cả mọi người! 💝</p>
                                        </div>
                                    `;
                                }
                            }, 300);
                        }
                        
                        // Ghép đôi thành công - hiển thị notification
                        setTimeout(() => {
                            showMatchNotification(data.message, data.redirect);
                        }, 400);
                    } else {
                        // Thích lại thành công nhưng chưa ghép đôi
                        button.innerHTML = '<i class="fas fa-check-circle"></i> Đã thích';
                        button.classList.remove('btn-like-back');
                        button.classList.add('btn-matched');
                    }
                } else {
                    alert(data.message || 'Có lỗi xảy ra!');
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-heart"></i> Thích lại';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra, vui lòng thử lại!');
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-heart"></i> Thích lại';
            });
        }
        
        function showMatchNotification(message, redirectUrl) {
            const notification = document.createElement('div');
            notification.innerHTML = `
                <div style="
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0,0,0,0.8);
                    z-index: 10000;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    animation: fadeIn 0.3s;
                ">
                    <div style="
                        background: white;
                        padding: 50px;
                        border-radius: 20px;
                        text-align: center;
                        max-width: 500px;
                        box-shadow: 0 10px 50px rgba(0,0,0,0.3);
                    ">
                        <div style="font-size: 80px; margin-bottom: 20px; animation: heartBeat 1s infinite;">
                            💕
                        </div>
                        <h2 style="color: #e94057; margin: 0 0 15px 0; font-size: 32px;">
                            ${message}
                        </h2>
                        <p style="color: #666; margin: 0 0 30px 0; font-size: 16px;">
                            Bạn và người này đã thích nhau! Hãy bắt đầu trò chuyện ngay! 💬
                        </p>
                        <button onclick="window.location.href='${redirectUrl}'" style="
                            padding: 15px 40px;
                            background: linear-gradient(135deg, #e94057 0%, #f27121 100%);
                            color: white;
                            border: none;
                            border-radius: 25px;
                            font-size: 18px;
                            font-weight: 600;
                            cursor: pointer;
                            box-shadow: 0 5px 20px rgba(233,64,87,0.4);
                        ">
                            Bắt đầu trò chuyện
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(notification);
        }
        
        // Ẩn các card của người dùng đã unmatch (từ localStorage)
        document.addEventListener('DOMContentLoaded', function() {
            const unmatchedUsers = JSON.parse(localStorage.getItem('unmatchedUsers') || '[]');
            
            if (unmatchedUsers.length > 0) {
                console.log('Hiding unmatched users:', unmatchedUsers);
                
                unmatchedUsers.forEach(userId => {
                    const card = document.getElementById('card-' + userId);
                    if (card) {
                        // Ẩn card với animation
                        card.style.transition = 'all 0.3s ease';
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.8)';
                        
                        setTimeout(() => {
                            card.remove();
                            
                            // Kiểm tra nếu không còn card nào
                            const grid = document.querySelector('.profiles-grid');
                            if (grid && grid.children.length === 0) {
                                // Hiển thị empty state
                                const container = document.querySelector('.likes-container');
                                const header = container.querySelector('.page-header');
                                container.innerHTML = '';
                                container.appendChild(header);
                                container.innerHTML += `
                                    <div class="empty-state">
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="width: 120px; height: 120px; margin: 0 auto 20px; opacity: 0.5;">
                                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" 
                                                  stroke="#e94057" stroke-width="2" fill="none"/>
                                        </svg>
                                        <h2 style="color: #666; font-size: 24px; margin-bottom: 10px;">Chưa có ai thích bạn</h2>
                                        <p style="color: #999; font-size: 16px;">Hoàn thiện hồ sơ của bạn để thu hút nhiều người hơn!</p>
                                    </div>
                                `;
                            }
                        }, 300);
                    }
                });
                
                // Xóa localStorage sau khi đã xử lý
                localStorage.removeItem('unmatchedUsers');
            }
        });
    </script>
    
    <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes heartBeat {
            0%, 100% { transform: scale(1); }
            10%, 30% { transform: scale(0.9); }
            20%, 40%, 60%, 80% { transform: scale(1.1); }
            50%, 70% { transform: scale(1.05); }
        }
        
        .btn-like-back {
            background: linear-gradient(135deg, #e94057 0%, #f27121 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-right: 10px;
        }
        
        .btn-like-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(233, 64, 87, 0.3);
        }
        
        .btn-matched {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            margin-right: 10px;
            opacity: 0.7;
        }
        
        .btn-view-profile {
            background: white;
            color: #e94057;
            border: 2px solid #e94057;
            padding: 10px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
            display: inline-block;
        }
        
        .btn-view-profile:hover {
            background: #e94057;
            color: white;
        }
        
        .profile-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
    </style>
    
    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>