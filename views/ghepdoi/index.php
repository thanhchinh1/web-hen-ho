<?php
require_once '../../models/mSession.php';
require_once '../../models/mMatch.php';
require_once '../../models/mProfile.php';

Session::start();

// Kiểm tra đăng nhập
if (!Session::isLoggedIn()) {
    header('Location: ../dangnhap/login.php');
    exit;
}

// Kiểm tra role - nếu là admin thì chuyển về trang admin
$userRole = Session::get('user_role');
if ($userRole === 'admin') {
    header('Location: ../admin/index.php');
    exit;
}

$userId = Session::getUserId();
$matchModel = new MatchModel();
$profileModel = new Profile();

// Lấy danh sách người đã ghép đôi
$matches = $matchModel->getMyMatches($userId);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đã ghép đôi - DuyenHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/ghepdoi.css?v=<?php echo time(); ?>">
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="header-container">
            <a href="../trangchu/index.php" class="logo">
                <img src="../../public/img/logo.jpg" alt="DuyenHub Logo">
                <span class="logo-text">DuyenHub</span>
            </a>
            <nav class="header-nav">
                <a href="../../controller/cLogout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                </a>
            </nav>
        </div>
    </header>

    <div class="matches-wrapper">
        <button class="back-btn" onclick="window.history.back()" title="Quay lại">
            <i class="fas fa-arrow-left"></i>
        </button>
        
        <div class="matches-container">
            <div class="matches-header">
                <h1>Đã ghép đôi</h1>
                <p>Những người bạn đã kết nối thành công (<?php echo count($matches); ?>)</p>
            </div>

            
        <?php if (empty($matches)): ?>
            <div class="empty-state">
                <i class="fas fa-heart-broken"></i>
                <h2>Chưa có ai ghép đôi với bạn</h2>
                <p>Hãy thả tim cho những người bạn thích hoặc sử dụng tính năng ghép đôi nhanh!</p>
                <a href="../trangchu/index.php" class="btn-explore">
                    <i class="fas fa-search"></i> Bắt đầu tìm kiếm
                </a>
            </div>
        <?php else: ?>
            <div class="matches-grid">
                <?php foreach ($matches as $match): 
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
                <div class="match-card" onclick="window.location.href='../hoso/xemnguoikhac.php?id=<?php echo $match['maNguoiDung']; ?>'">
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
                        <a href="../nhantin/message.php?matchId=<?php echo $match['maGhepDoi']; ?>" 
                           class="btn-chat"
                           onclick="event.stopPropagation();">
                            <i class="fas fa-comment"></i> Nhắn tin
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        </div>
    </div>
</body>
</html>
