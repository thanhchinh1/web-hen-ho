<?php
require_once __DIR__ . '/../../models/mSession.php';
require_once __DIR__ . '/../../models/mProfile.php';
require_once __DIR__ . '/../../models/mLike.php';
require_once __DIR__ . '/../../models/mMatch.php';
require_once __DIR__ . '/../../models/mDbconnect.php';

Session::start();

// Lấy ID người dùng cần xem từ URL
$profileId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!Session::isLoggedIn()) {
    // Chưa đăng nhập → Chuyển đến trang login với redirect params
    $redirectUrl = "/views/dangnhap/login.php?redirect=profile&id=" . $profileId;
    header("Location: " . $redirectUrl);
    exit();
}

// Kiểm tra role - nếu là admin thì chuyển về trang admin
$userRole = Session::get('user_role');
if ($userRole === 'admin') {
    header('Location: /views/admin/index.php');
    exit;
}

$currentUserId = Session::getUserId();

if ($profileId === 0 || $profileId === $currentUserId) {
    // Không có ID hoặc đang xem chính mình
    header('Location: index.php');
    exit;
}

// Lấy thông tin hồ sơ
$profileModel = new Profile();
$likeModel = new Like();
$matchModel = new MatchModel();
$profile = $profileModel->getProfile($profileId);

if (!$profile) {
    // Không tìm thấy hồ sơ
    header('Location: ../trangchu/index.php');
    exit;
}

// Kiểm tra đã like chưa
$hasLiked = $likeModel->hasLiked($currentUserId, $profileId);

// Kiểm tra đã ghép đôi chưa
$isMatched = $matchModel->isMatched($currentUserId, $profileId);

// Tính tuổi
$age = $profileModel->calculateAge($profile['ngaySinh']);

// Xử lý đường dẫn avatar
if (!empty($profile['avt'])) {
    // Nếu đã có 'public/' trong đường dẫn
    if (strpos($profile['avt'], 'public/') === 0) {
        $avatarSrc = '/' . htmlspecialchars($profile['avt']);
    } else {
        $avatarSrc = '/public/uploads/avatars/' . htmlspecialchars($profile['avt']);
    }
} else {
    $avatarSrc = '/public/img/default-avatar.jpg';
}

$interests = !empty($profile['soThich']) ? explode(', ', $profile['soThich']) : [];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ <?php echo htmlspecialchars($profile['ten']); ?> - WebHenHo</title>
    <link rel="stylesheet" href="/public/css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="page-wrapper">
    <div class="profile-container">
        <!-- Header -->
        <header class="profile-header">
           <a href="../trangchu/index.php" class="logo">
                <img src="/public/img/logo.jpg" alt="WebHenHo">
                <span class="logo-text">WebHenHo</span>
            </a>
        </header>

        <!-- Back button -->
        <div class="back-button-container">
            <button class="btn-back" onclick="window.location.href='../trangchu/index.php'">
                <i class="fas fa-arrow-left"></i>
            </button>
        </div>

        <!-- Profile Content -->
        <div class="profile-content">
            <!-- Profile Header Section -->
            <div class="profile-hero">
                <div class="profile-avatar-section">
                    <img src="<?php echo $avatarSrc; ?>" alt="<?php echo htmlspecialchars($profile['ten']); ?>" class="profile-avatar" id="userAvatar">
                    <h1 class="profile-name" id="userName"><?php echo htmlspecialchars($profile['ten']); ?></h1>
                </div>

                <!-- Action Buttons -->
                <div class="profile-actions">
                    <?php if ($isMatched): ?>
                        <!-- Đã ghép đôi -->
                        <button class="btn-action btn-matched" 
                                onclick="confirmUnmatch(<?php echo $profileId; ?>)" 
                                id="matchBtn">
                            <i class="fas fa-check-circle"></i>
                            <span>Đã ghép đôi</span>
                        </button>
                    <?php else: ?>
                        <!-- Chưa ghép đôi - hiển thị nút like bình thường -->
                        <button class="btn-action btn-like <?php echo $hasLiked ? 'liked' : ''; ?>" 
                                onclick="toggleLike(<?php echo $profileId; ?>)" 
                                id="likeBtn">
                            <i class="<?php echo $hasLiked ? 'fas' : 'far'; ?> fa-heart"></i>
                            <span id="likeText"><?php echo $hasLiked ? 'Đã thích' : 'Thả tim'; ?></span>
                        </button>
                    <?php endif; ?>
                    <button class="btn-action btn-report" onclick="openReportModal()">
                        <i class="far fa-flag"></i>
                        Báo cáo
                    </button>
                    <button class="btn-action btn-block" onclick="confirmBlock(<?php echo $profileId; ?>)">
                        <i class="fas fa-ban"></i>
                        Chặn
                    </button>
                </div>
            </div>

            <!-- Profile Details -->
            <div class="profile-details">
                <!-- Personal Information -->
                <section class="detail-section">
                    <h2 class="section-title">Thông tin cá nhân</h2>
                    <div class="info-list">
                        <div class="info-item">
                            <i class="fas fa-venus-mars"></i>
                            <span class="info-label">Giới tính</span>
                            <span class="info-value"><?php echo htmlspecialchars($profile['gioiTinh'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span class="info-label">Tuổi</span>
                            <span class="info-value"><?php echo $age; ?> tuổi</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span class="info-label">Địa chỉ</span>
                            <span class="info-value"><?php echo htmlspecialchars($profile['noiSong'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-heart"></i>
                            <span class="info-label">Tình trạng hôn nhân</span>
                            <span class="info-value"><?php echo htmlspecialchars($profile['tinhTrangHonNhan'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-weight"></i>
                            <span class="info-label">Cân nặng</span>
                            <span class="info-value"><?php echo htmlspecialchars($profile['canNang'] ?? 'N/A'); ?> kg</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-ruler-vertical"></i>
                            <span class="info-label">Chiều cao</span>
                            <span class="info-value"><?php echo htmlspecialchars($profile['chieuCao'] ?? 'N/A'); ?> cm</span>
                        </div>
                    </div>
                </section>

                <!-- Career & Education -->
                <section class="detail-section">
                    <h2 class="section-title">Học vấn & Mục tiêu</h2>
                    
                    <div class="info-list">
                        <div class="info-item">
                            <i class="fas fa-graduation-cap"></i>
                            <span class="info-label">Học vấn</span>
                            <span class="info-value"><?php echo htmlspecialchars($profile['hocVan'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-bullseye"></i>
                            <span class="info-label">Mục tiêu</span>
                            <span class="info-value"><?php echo htmlspecialchars($profile['mucTieuPhatTrien'] ?? 'N/A'); ?></span>
                        </div>
                    </div>
                </section>

                <!-- Interests -->
                <section class="detail-section">
                    <h2 class="section-title">Sở thích</h2>
                    <div class="interests-tags">
                        <?php foreach ($interests as $interest): ?>
                            <?php if (!empty(trim($interest))): ?>
                                <span class="interest-tag"><?php echo htmlspecialchars(trim($interest)); ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- About Me -->
                <section class="detail-section">
                    <h2 class="section-title">Về tôi</h2>
                    <p class="about-text">
                        <?php echo nl2br(htmlspecialchars($profile['moTa'] ?? 'Chưa có giới thiệu')); ?>
                    </p>
                </section>
            </div>
        </div>
    </div>

    <!-- Report Modal -->
    <div id="reportModal" style="
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.7);
        z-index: 10000;
        align-items: center;
        justify-content: center;
    ">
        <div style="
            background: white;
            padding: 30px;
            border-radius: 20px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        ">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="margin: 0; color: #333; font-size: 24px;">
                    <i class="far fa-flag" style="color: #e94057; margin-right: 10px;"></i>
                    Báo cáo vi phạm
                </h2>
                <button onclick="closeReportModal()" style="
                    background: none;
                    border: none;
                    font-size: 28px;
                    cursor: pointer;
                    color: #999;
                    line-height: 1;
                ">&times;</button>
            </div>
            
            <p style="color: #666; margin-bottom: 20px;">
                Vui lòng chọn lý do báo cáo và mô tả chi tiết vi phạm
            </p>
            
            <form id="reportForm">
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; color: #333; font-weight: 600;">
                        Loại vi phạm *
                    </label>
                    <select id="reportType" style="
                        width: 100%;
                        padding: 12px;
                        border: 2px solid #ddd;
                        border-radius: 10px;
                        font-size: 14px;
                        outline: none;
                    ">
                        <option value="spam">Spam / Lừa đảo</option>
                        <option value="fake">Hồ sơ giả mạo</option>
                        <option value="harassment">Quấy rối / Bắt nạt</option>
                        <option value="inappropriate">Nội dung không phù hợp</option>
                        <option value="underage">Người dùng chưa đủ tuổi</option>
                        <option value="other">Lý do khác</option>
                    </select>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; color: #333; font-weight: 600;">
                        Mô tả chi tiết * (tối thiểu 10 ký tự)
                    </label>
                    <textarea id="reportReason" rows="4" placeholder="Hãy mô tả cụ thể vi phạm bạn đã gặp..." style="
                        width: 100%;
                        padding: 12px;
                        border: 2px solid #ddd;
                        border-radius: 10px;
                        font-size: 14px;
                        resize: vertical;
                        outline: none;
                        font-family: inherit;
                    "></textarea>
                    <small style="color: #999; display: block; margin-top: 5px;">
                        Báo cáo của bạn sẽ được quản trị viên xem xét và xử lý
                    </small>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 25px;">
                    <button type="button" onclick="submitReport(<?php echo $profileId; ?>)" style="
                        flex: 1;
                        padding: 14px;
                        background: linear-gradient(135deg, #e94057 0%, #f27121 100%);
                        color: white;
                        border: none;
                        border-radius: 10px;
                        font-size: 16px;
                        font-weight: 600;
                        cursor: pointer;
                        transition: transform 0.2s;
                    " onmouseover="this.style.transform='translateY(-2px)'" 
                       onmouseout="this.style.transform='translateY(0)'">
                        <i class="fas fa-paper-plane" style="margin-right: 8px;"></i>
                        Gửi báo cáo
                    </button>
                    <button type="button" onclick="closeReportModal()" style="
                        flex: 1;
                        padding: 14px;
                        background: #f0f0f0;
                        color: #666;
                        border: none;
                        border-radius: 10px;
                        font-size: 16px;
                        font-weight: 600;
                        cursor: pointer;
                    ">
                        Hủy bỏ
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Toggle like/unlike
        function toggleLike(targetUserId) {
            const likeBtn = document.getElementById('likeBtn');
            const icon = likeBtn.querySelector('i');
            const text = document.getElementById('likeText');
            const isLiked = likeBtn.classList.contains('liked');
            
            console.log('toggleLike called, targetUserId:', targetUserId, 'isLiked:', isLiked);
            
            fetch('/controller/cLike.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'targetUserId=' + targetUserId + '&csrf_token=<?php echo Session::getCSRFToken(); ?>'
            })
            .then(res => res.json())
            .then(data => {
                console.log('Response:', data);
                
                if (data.success) {
                    if (data.matched) {
                        // Ghép đôi thành công!
                        showMatchNotification(data.message, data.redirect);
                    } else if (data.action === 'liked') {
                        // Đã thả tim thành công
                        showNotification('Đã thích! 💖 Chuyển về trang chủ...');
                        
                        // Chuyển hướng về trang chủ sau 1 giây
                        setTimeout(() => {
                            window.location.href = '/views/trangchu/index.php';
                        }, 1000);
                    } else if (data.action === 'unliked') {
                        // Bỏ thích thành công
                        showNotification('Đã bỏ thích!');
                        
                        // Chuyển hướng về trang chủ sau 1 giây
                        // Hồ sơ sẽ xuất hiện lại trên trang chủ
                        setTimeout(() => {
                            window.location.href = '/views/thich/nguoibanthich.php';
                        }, 1000);
                    }
                } else {
                    showNotification(data.message || 'Có lỗi xảy ra!', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Có lỗi xảy ra!', 'error');
            });
        }
        
        // Open Report Modal
        function openReportModal() {
            document.getElementById('reportModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        // Close Report Modal
        function closeReportModal() {
            document.getElementById('reportModal').style.display = 'none';
            document.body.style.overflow = 'auto';
            document.getElementById('reportReason').value = '';
        }
        
        // Submit Report
        function submitReport(targetUserId) {
            const reportType = document.getElementById('reportType').value;
            const reportReason = document.getElementById('reportReason').value.trim();
            
            if (reportReason.length < 10) {
                showNotification('Vui lòng nhập lý do báo cáo (tối thiểu 10 ký tự)!', 'error');
                return;
            }
            
            // Disable button
            const submitBtn = event.target;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang gửi...';
            
            fetch('/controller/cReport.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=report&targetUserId=' + targetUserId + 
                      '&reportType=' + encodeURIComponent(reportType) + 
                      '&reportReason=' + encodeURIComponent(reportReason) + 
                      '&csrf_token=<?php echo Session::getCSRFToken(); ?>'
            })
            .then(res => res.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Gửi báo cáo';
                
                if (data.success) {
                    closeReportModal();
                    
                    // Hiển thị thông báo xác nhận với thông tin chi tiết
                    let message = data.message;
                    let icon = '✅';
                    let color = '#28a745';
                    
                    if (data.locked) {
                        icon = '🔒';
                        color = '#dc3545';
                    }
                    
                    const confirmNotif = document.createElement('div');
                    confirmNotif.innerHTML = `
                        <div style="
                            position: fixed;
                            top: 50%;
                            left: 50%;
                            transform: translate(-50%, -50%);
                            background: white;
                            padding: 40px;
                            border-radius: 20px;
                            box-shadow: 0 15px 50px rgba(0,0,0,0.3);
                            z-index: 10001;
                            text-align: center;
                            max-width: 500px;
                        ">
                            <div style="font-size: 60px; margin-bottom: 20px;">${icon}</div>
                            <h2 style="color: ${color}; margin: 0 0 15px 0; font-size: 24px;">
                                ${data.locked ? 'Tài khoản đã bị khóa!' : 'Báo cáo thành công!'}
                            </h2>
                            <p style="color: #666; margin: 0 0 20px 0; font-size: 16px; line-height: 1.6; white-space: pre-line;">
                                ${message}
                            </p>
                            ${data.reportCount ? `
                                <div style="
                                    background: #f8f9fa;
                                    padding: 15px;
                                    border-radius: 10px;
                                    margin-bottom: 20px;
                                    border-left: 4px solid ${color};
                                ">
                                    <p style="margin: 0; color: #333; font-size: 14px;">
                                        <strong>Tổng số báo cáo:</strong> ${data.reportCount} lần
                                    </p>
                                </div>
                            ` : ''}
                            <button onclick="this.closest('div').parentElement.remove(); window.location.href='/views/trangchu/index.php';" style="
                                padding: 12px 30px;
                                background: ${color};
                                color: white;
                                border: none;
                                border-radius: 8px;
                                font-size: 16px;
                                font-weight: 600;
                                cursor: pointer;
                            ">
                                Đóng
                            </button>
                        </div>
                        <div onclick="this.parentElement.remove(); window.location.href='/views/trangchu/index.php';" style="
                            position: fixed;
                            top: 0;
                            left: 0;
                            right: 0;
                            bottom: 0;
                            background: rgba(0,0,0,0.6);
                            z-index: 10000;
                            cursor: pointer;
                        "></div>
                    `;
                    document.body.appendChild(confirmNotif);
                } else {
                    showNotification(data.message || 'Có lỗi xảy ra!', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Gửi báo cáo';
                showNotification('Có lỗi xảy ra khi gửi báo cáo!', 'error');
            });
        }
        
        // Confirm Block
        function confirmBlock(targetUserId) {
            if (confirm('⚠️ Bạn có chắc muốn chặn người dùng này?\n\n' +
                       'Sau khi chặn:\n' +
                       '• Bạn sẽ không thấy hồ sơ của họ\n' +
                       '• Họ không thể nhắn tin cho bạn\n' +
                       '• Tất cả kết nối sẽ bị xóa')) {
                
                fetch('/controller/cBlock.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=block&targetUserId=' + targetUserId + 
                          '&csrf_token=<?php echo Session::getCSRFToken(); ?>'
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message);
                        setTimeout(() => {
                            window.location.href = '../trangchu/index.php';
                        }, 1500);
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

        // Hủy ghép đôi
        function confirmUnmatch(targetUserId) {
            if (confirm('⚠️ BẠN CÓ CHẮC MUỐN HỦY GHÉP ĐÔI?\n\n❌ Tất cả tin nhắn sẽ bị XÓA VĨNH VIỄN\n❌ Hồ sơ sẽ biến mất khỏi danh sách "Người thích bạn"\n✅ Hồ sơ xuất hiện lại trên trang chủ\n\nHành động này KHÔNG THỂ HOÀN TÁC!')) {
                fetch('../../controller/cMatch.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=unmatch&targetUserId=' + targetUserId + '&csrf_token=<?php echo Session::getCSRFToken(); ?>'
                })
                .then(res => res.json())
                .then(data => {
                    console.log('Unmatch response:', data);
                    
                    if (data.success) {
                        showNotification('✅ Đã hủy ghép đôi!\n🗑️ Tất cả tin nhắn đã bị xóa!');
                        
                        // Lưu ID người dùng đã unmatch vào localStorage
                        // Để trang nguoithichban.php có thể ẩn card này
                        const unmatchedUsers = JSON.parse(localStorage.getItem('unmatchedUsers') || '[]');
                        if (!unmatchedUsers.includes(targetUserId)) {
                            unmatchedUsers.push(targetUserId);
                            localStorage.setItem('unmatchedUsers', JSON.stringify(unmatchedUsers));
                        }
                        
                        // Quay về trang nguoithichban sau 1.5 giây
                        setTimeout(() => {
                            window.location.href = '../thich/nguoithichban.php';
                        }, 1500);
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
    </script>
    <style>
        .btn-like.liked {
            background: linear-gradient(135deg, #e94057 0%, #ff6b9d 100%) !important;
            color: white !important;
        }
        
        .btn-matched {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
            color: white !important;
            cursor: pointer;
        }
        
        .btn-matched:hover {
            background: linear-gradient(135deg, #218838 0%, #1ea87a 100%) !important;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(40, 167, 69, 0.4);
        }
        
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
    </style>
    </div>
    </div>
</body>
</html>
