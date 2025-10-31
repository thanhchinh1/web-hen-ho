<?php
require_once '../../models/session.php';
require_once '../../models/mLike.php';
require_once '../../models/mProfile.php';

requireLogin();

$currentUserId = getCurrentUserId();
$likeModel = new LikeModel();
$profileModel = new Profile();

$likedProfiles = $likeModel->getUsersLikedBy($currentUserId);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Người bạn đã thích - Kết Nối Yêu Thương</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/likes.css">
</head>
<body>
    <div class="likes-container">
        <button class="close-btn" onclick="window.location.href='../trangchu/index.php'">
            <i class="fas fa-times"></i>
        </button>

        <div class="likes-header">
            <h1>Danh sách bạn đã thích</h1>
            <p>Quản lý những hồ sơ mà bạn đã gửi "thả tim".</p>
        </div>

        <?php if (empty($likedProfiles)): ?>
            <div class="empty-state">
                <i class="fas fa-heart-broken"></i>
                <h2>Bạn chưa thích ai cả</h2>
                <p>Hãy quay lại trang chủ và bắt đầu khám phá những hồ sơ mới.</p>
                <a class="btn-back-home" href="../trangchu/index.php">Khám phá ngay</a>
            </div>
        <?php else: ?>
            <div class="likes-grid">
                <?php foreach ($likedProfiles as $profile): ?>
                    <?php
                        $age = $profileModel->calculateAge($profile['ngaySinh']);
                        $avatar = !empty($profile['avt']) ? '../../' . htmlspecialchars($profile['avt']) : 'https://i.pravatar.cc/150';
                        $likedAt = !empty($profile['thoiDiemThich']) ? date('d/m/Y H:i', strtotime($profile['thoiDiemThich'])) : '';
                    ?>
                    <div class="like-card">
                        <span class="badge-new">Đã thích</span>
                        <div class="card-avatar">
                            <img src="<?php echo $avatar; ?>" alt="<?php echo htmlspecialchars($profile['ten']); ?>">
                        </div>
                        <div class="card-info">
                            <h3><?php echo htmlspecialchars($profile['ten']); ?></h3>
                            <p class="card-year"><?php echo $age; ?> tuổi - <?php echo htmlspecialchars($profile['noiSong']); ?></p>
                            <p class="card-status"><?php echo htmlspecialchars($profile['tinhTrangHonNhan']); ?></p>
                            <?php if ($likedAt): ?>
                                <p class="card-date">Thích lúc: <?php echo $likedAt; ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="card-actions">
                            <button class="btn-view" onclick="window.location.href='../hoso/xemnguoikhac.php?id=<?php echo $profile['maNguoiDung']; ?>'">
                                <i class="fas fa-user"></i> Xem hồ sơ
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
