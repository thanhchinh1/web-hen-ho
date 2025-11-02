<?php
require_once '../../models/mSession.php';
require_once '../../models/mMatch.php';

Session::start();

// Kiểm tra đăng nhập
if (!Session::isLoggedIn()) {
    header('Location: ../dangnhap/login.php');
    exit;
}

$userId = Session::getUserId();
$matchModel = new MatchModel();

// Lấy danh sách người đã ghép đôi
$matches = $matchModel->getMyMatches($userId);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đã ghép đôi - DuyenHub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/home.css">
    <style>
        .matches-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-header h1 {
            color: #2C3E50;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .page-header p {
            color: #7F8C8D;
            font-size: 16px;
        }

        .matches-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .match-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }

        .match-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .match-avatar {
            width: 100%;
            height: 300px;
            object-fit: cover;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .match-info {
            padding: 20px;
        }

        .match-name {
            font-size: 24px;
            font-weight: 700;
            color: #2C3E50;
            margin-bottom: 10px;
        }

        .match-details {
            color: #7F8C8D;
            font-size: 14px;
            margin: 5px 0;
        }

        .match-details i {
            width: 20px;
            margin-right: 8px;
            color: #667eea;
        }

        .match-date {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #ECF0F1;
            font-size: 13px;
            color: #95A5A6;
        }

        .match-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #ECF0F1;
            color: #2C3E50;
        }

        .btn-secondary:hover {
            background: #BDC3C7;
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
        }

        .empty-state i {
            font-size: 80px;
            color: #ECF0F1;
            margin-bottom: 20px;
        }

        .empty-state h2 {
            color: #2C3E50;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #7F8C8D;
            margin-bottom: 30px;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .badge-match {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .back-link i {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <?php include '../layouts/header.php'; ?>

    <div class="matches-container">
        <a href="../trangchu/index.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Quay lại trang chủ
        </a>

        <div class="page-header">
            <h1><i class="fas fa-heart"></i> Đã ghép đôi</h1>
            <p>Những người bạn đã kết nối thành công (<?php echo count($matches); ?>)</p>
        </div>

        <?php if (empty($matches)): ?>
            <div class="empty-state">
                <i class="fas fa-heart-broken"></i>
                <h2>Chưa có ai ghép đôi với bạn</h2>
                <p>Hãy thả tim cho những người bạn thích hoặc sử dụng tính năng ghép đôi nhanh!</p>
                <a href="../trangchu/index.php" class="btn btn-primary" style="max-width: 300px; margin: 0 auto;">
                    <i class="fas fa-search"></i> Bắt đầu tìm kiếm
                </a>
            </div>
        <?php else: ?>
            <div class="matches-grid">
                <?php foreach ($matches as $match): 
                    $age = date('Y') - date('Y', strtotime($match['ngaySinh']));
                    $avatarSrc = !empty($match['avt']) ? '../../' . $match['avt'] : 'https://via.placeholder.com/300?text=' . urlencode($match['ten']);
                    $matchDate = date('d/m/Y', strtotime($match['thoiDiemGhepDoi']));
                ?>
                <div class="match-card" onclick="window.location.href='../hoso/xemnguoikhac.php?id=<?php echo $match['maNguoiDung']; ?>'">
                    <img src="<?php echo htmlspecialchars($avatarSrc); ?>" 
                         alt="<?php echo htmlspecialchars($match['ten']); ?>" 
                         class="match-avatar"
                         onerror="this.src='https://via.placeholder.com/300?text=<?php echo urlencode($match['ten']); ?>'">
                    
                    <div class="match-info">
                        <span class="badge badge-match">
                            <i class="fas fa-heart"></i> Đã ghép đôi
                        </span>
                        
                        <h2 class="match-name"><?php echo htmlspecialchars($match['ten']); ?></h2>
                        
                        <div class="match-details">
                            <i class="fas fa-birthday-cake"></i>
                            <?php echo $age; ?> tuổi
                        </div>
                        
                        <div class="match-details">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($match['noiSong'] ?? 'Không rõ'); ?>
                        </div>
                        
                        <div class="match-date">
                            <i class="fas fa-calendar"></i>
                            Ghép đôi: <?php echo $matchDate; ?>
                        </div>
                        
                        <div class="match-actions">
                            <a href="../nhantin/chat.php?matchId=<?php echo $match['maGhepDoi']; ?>" 
                               class="btn btn-primary"
                               onclick="event.stopPropagation();">
                                <i class="fas fa-comment"></i> Nhắn tin
                            </a>
                            <a href="../hoso/xemnguoikhac.php?id=<?php echo $match['maNguoiDung']; ?>" 
                               class="btn btn-secondary"
                               onclick="event.stopPropagation();">
                                <i class="fas fa-user"></i> Xem hồ sơ
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../layouts/footer.php'; ?>
</body>
</html>
