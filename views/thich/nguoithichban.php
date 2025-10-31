<?php
require_once '../../models/session.php';
require_once '../../models/mLike.php';
require_once '../../models/mProfile.php';

requireLogin();

$currentUserId = getCurrentUserId();
$likeModel = new LikeModel();
$profileModel = new Profile();

$likedByProfiles = $likeModel->getUsersWhoLiked($currentUserId);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ai thích bạn - Kết Nối Yêu Thương</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/likes.css">
</head>
<body>
    <div class="likes-container">
        <button class="close-btn" onclick="window.location.href='../trangchu/index.php'">
            <i class="fas fa-times"></i>
        </button>

        <div class="likes-header">
            <h1>Những người thích bạn</h1>
            <p>Xem những hồ sơ đã gửi "thả tim" cho bạn.</p>
        </div>

        <?php if (empty($likedByProfiles)): ?>
            <div class="empty-state">
                <i class="fas fa-heart"></i>
                <h2>Chưa có ai thích bạn</h2>
                <p>Đừng lo lắng, hãy cập nhật hồ sơ thật hấp dẫn để thu hút thêm lượt thích!</p>
                <a class="btn-back-home" href="../hoso/chinhsua.php">Cập nhật hồ sơ</a>
            </div>
        <?php else: ?>
            <div class="likes-grid">
                <?php foreach ($likedByProfiles as $profile): ?>
                    <?php
                        $age = $profileModel->calculateAge($profile['ngaySinh']);
                        $avatar = !empty($profile['avt']) ? '../../' . htmlspecialchars($profile['avt']) : 'https://i.pravatar.cc/150';
                        $likedAt = !empty($profile['thoiDiemThich']) ? date('d/m/Y H:i', strtotime($profile['thoiDiemThich'])) : '';
                    ?>
                    <div class="like-card">
                        <span class="badge-new badge-liked-you">Thích bạn</span>
                        <div class="card-avatar">
                            <img src="<?php echo $avatar; ?>" alt="<?php echo htmlspecialchars($profile['ten']); ?>">
                        </div>
                        <div class="card-info">
                            <h3><?php echo htmlspecialchars($profile['ten']); ?></h3>
                            <p class="card-year"><?php echo $age; ?> tuổi - <?php echo htmlspecialchars($profile['noiSong']); ?></p>
                            <p class="card-status"><?php echo htmlspecialchars($profile['tinhTrangHonNhan']); ?></p>
                            <?php if ($likedAt): ?>
                                <p class="card-date">Thời gian: <?php echo $likedAt; ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="card-actions">
                            <button class="btn-view" onclick="window.location.href='../hoso/xemnguoikhac.php?id=<?php echo $profile['maNguoiDung']; ?>'">
                                <i class="fas fa-user"></i> Xem hồ sơ
                            </button>
                            <button class="btn-message" onclick="window.location.href='../nhantin/chat.php?to=<?php echo $profile['maNguoiDung']; ?>'">
                                <i class="fas fa-comment-dots"></i> Nhắn tin
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
