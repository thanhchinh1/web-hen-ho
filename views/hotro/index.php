<?php
require_once '../../models/mSession.php';
require_once '../../models/mSupport.php';

Session::start();

// Kiểm tra đăng nhập
if (!Session::isLoggedIn()) {
    header('Location: ../dangnhap/login.php');
    exit;
}

$currentUserId = Session::getUserId();
$supportModel = new Support();

// Lấy danh sách yêu cầu hỗ trợ của người dùng
$supportRequests = $supportModel->getUserSupportRequests($currentUserId, 50);

// Đếm số yêu cầu đang chờ
$pendingCount = $supportModel->countPendingRequests($currentUserId);

$successMessage = Session::getFlash('success_message');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yêu cầu hỗ trợ - DuyenHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/home.css">
    <style>
        body {
            background: #f5f7fa;
            font-family: 'Poppins', sans-serif;
        }
        
        .support-container {
            max-width: 1000px;
            margin: 100px auto 40px;
            padding: 0 20px;
        }
        
        .support-header {
            background: linear-gradient(135deg, #FF6B9D, #ff4d6d);
            padding: 40px;
            border-radius: 20px;
            color: white;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(255, 107, 157, 0.3);
        }
        
        .support-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .support-header p {
            opacity: 0.9;
            font-size: 16px;
        }
        
        .support-stats {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.2);
            padding: 15px 25px;
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }
        
        .stat-number {
            font-size: 28px;
            font-weight: 700;
        }
        
        .stat-label {
            font-size: 13px;
            opacity: 0.9;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .btn-new-request {
            padding: 14px 28px;
            background: linear-gradient(135deg, #FF6B9D, #ff4d6d);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-new-request:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 107, 157, 0.4);
        }
        
        .btn-back {
            padding: 14px 28px;
            background: white;
            color: #2c3e50;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-back:hover {
            border-color: #FF6B9D;
            color: #FF6B9D;
        }
        
        .support-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .support-item {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
        }
        
        .support-item:hover {
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }
        
        .support-item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            gap: 15px;
        }
        
        .support-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .support-meta {
            display: flex;
            gap: 20px;
            font-size: 13px;
            color: #7f8c8d;
        }
        
        .support-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .support-status {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-in_progress {
            background: #cfe2ff;
            color: #084298;
        }
        
        .status-resolved {
            background: #d1e7dd;
            color: #0f5132;
        }
        
        .status-closed {
            background: #e2e3e5;
            color: #41464b;
        }
        
        .support-content {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 15px;
            color: #495057;
            line-height: 1.6;
        }
        
        .support-reply {
            border-left: 4px solid #27ae60;
            padding: 15px;
            background: #d4edda;
            border-radius: 10px;
            margin-top: 15px;
        }
        
        .reply-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            color: #155724;
            font-weight: 600;
        }
        
        .reply-content {
            color: #155724;
            line-height: 1.6;
        }
        
        .no-reply {
            padding: 15px;
            background: #fff9e6;
            border-radius: 10px;
            color: #856404;
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 16px;
        }
        
        .empty-state i {
            font-size: 80px;
            color: #e0e0e0;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            color: #2c3e50;
            font-size: 22px;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #7f8c8d;
            margin-bottom: 25px;
        }
        
        .type-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            margin-right: 10px;
        }
        
        .type-general { background: #e3f2fd; color: #1565c0; }
        .type-payment { background: #f3e5f5; color: #6a1b9a; }
        .type-technical { background: #e8f5e9; color: #2e7d32; }
        .type-report { background: #ffebee; color: #c62828; }
        .type-other { background: #fafafa; color: #616161; }
        
        @media (max-width: 768px) {
            .support-container {
                margin: 80px auto 20px;
            }
            
            .support-header h1 {
                font-size: 24px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .support-stats {
                flex-direction: column;
                gap: 10px;
            }
            
            .support-item-header {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="support-container">
        <!-- Header -->
        <div class="support-header">
            <h1>
                <i class="fas fa-headset"></i>
                Yêu cầu hỗ trợ của bạn
            </h1>
            <p>Theo dõi và quản lý các yêu cầu hỗ trợ bạn đã gửi</p>
            
            <div class="support-stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($supportRequests); ?></div>
                    <div class="stat-label">Tổng yêu cầu</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $pendingCount; ?></div>
                    <div class="stat-label">Đang chờ xử lý</div>
                </div>
            </div>
        </div>
        
        <!-- Success Message -->
        <?php if ($successMessage): ?>
            <div style="background:#d4edda; border-left:4px solid #27ae60; padding:15px; border-radius:10px; margin-bottom:20px; color:#155724;">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>
        
        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="../trangchu/index.php" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Quay lại trang chủ
            </a>
            <button onclick="openContactAdmin()" class="btn-new-request">
                <i class="fas fa-plus"></i>
                Gửi yêu cầu mới
            </button>
        </div>
        
        <!-- Support List -->
        <?php if (empty($supportRequests)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>Chưa có yêu cầu hỗ trợ nào</h3>
                <p>Bạn chưa gửi yêu cầu hỗ trợ nào. Nếu cần trợ giúp, hãy gửi yêu cầu mới.</p>
                <button onclick="openContactAdmin()" class="btn-new-request">
                    <i class="fas fa-plus"></i>
                    Gửi yêu cầu đầu tiên
                </button>
            </div>
        <?php else: ?>
            <div class="support-list">
                <?php foreach ($supportRequests as $request): 
                    $statusClass = 'status-' . $request['trangThai'];
                    $statusText = [
                        'pending' => 'Đang chờ',
                        'in_progress' => 'Đang xử lý',
                        'resolved' => 'Đã giải quyết',
                        'closed' => 'Đã đóng'
                    ][$request['trangThai']] ?? $request['trangThai'];
                    
                    $typeClass = 'type-' . $request['loai'];
                    $typeText = [
                        'general' => 'Câu hỏi chung',
                        'payment' => 'Thanh toán',
                        'technical' => 'Kỹ thuật',
                        'report' => 'Báo cáo',
                        'other' => 'Khác'
                    ][$request['loai']] ?? $request['loai'];
                ?>
                    <div class="support-item">
                        <div class="support-item-header">
                            <div style="flex:1;">
                                <span class="type-badge <?php echo $typeClass; ?>"><?php echo $typeText; ?></span>
                                <div class="support-title"><?php echo htmlspecialchars($request['tieuDe']); ?></div>
                                <div class="support-meta">
                                    <span>
                                        <i class="far fa-clock"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($request['thoiDiemTao'])); ?>
                                    </span>
                                    <?php if ($request['thoiDiemCapNhat']): ?>
                                    <span>
                                        <i class="fas fa-sync-alt"></i>
                                        Cập nhật: <?php echo date('d/m/Y H:i', strtotime($request['thoiDiemCapNhat'])); ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div>
                                <span class="support-status <?php echo $statusClass; ?>">
                                    <?php echo $statusText; ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="support-content">
                            <strong>Nội dung yêu cầu:</strong><br>
                            <?php echo nl2br(htmlspecialchars($request['noiDung'])); ?>
                        </div>
                        
                        <?php if (!empty($request['phanHoi'])): ?>
                            <div class="support-reply">
                                <div class="reply-header">
                                    <i class="fas fa-user-shield"></i>
                                    <span>Phản hồi từ Admin<?php echo $request['tenAdmin'] ? ' - ' . htmlspecialchars($request['tenAdmin']) : ''; ?></span>
                                </div>
                                <div class="reply-content">
                                    <?php echo nl2br(htmlspecialchars($request['phanHoi'])); ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="no-reply">
                                <i class="fas fa-hourglass-half"></i>
                                Admin đang xem xét yêu cầu của bạn. Vui lòng chờ trong giây lát.
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Include Contact Modal from trangchu -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function openContactAdmin() {
            window.location.href = '../trangchu/index.php#contact';
            setTimeout(() => {
                if (typeof window.opener !== 'undefined' && window.opener.openContactAdmin) {
                    window.opener.openContactAdmin();
                } else {
                    alert('Vui lòng gửi yêu cầu từ trang chủ');
                    window.location.href = '../trangchu/index.php';
                }
            }, 100);
        }
    </script>
</body>
</html>
