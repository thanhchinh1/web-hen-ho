<?php
require_once '../../models/mSession.php';
require_once '../../models/mMatch.php';
require_once '../../models/mMessage.php';
require_once '../../models/mProfile.php';
require_once '../../models/mUser.php';
require_once '../../models/mNotification.php';

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

$currentUserId = Session::getUserId();
$matchModel = new MatchModel();
$messageModel = new Message();
$profileModel = new Profile();
$userModel = new User();

// Đếm số ghép đôi mới và tin nhắn chưa đọc
$notificationModel = new Notification();
$newMatchesCount = $notificationModel->getNewMatchesCount($currentUserId);
$unreadMessagesCount = $messageModel->getTotalUnreadCount($currentUserId);

// Lấy danh sách tất cả người đã ghép đôi
$myMatches = $matchModel->getMyMatches($currentUserId);

// Lấy matchId từ URL (nếu có) - hỗ trợ cả 'match' và 'matchId'
$selectedMatchId = isset($_GET['matchId']) ? intval($_GET['matchId']) : (isset($_GET['match']) ? intval($_GET['match']) : null);

// Nếu có match được chọn, lấy thông tin chi tiết
$selectedMatch = null;
if ($selectedMatchId) {
    foreach ($myMatches as $match) {
        if ($match['maGhepDoi'] == $selectedMatchId) {
            $selectedMatch = $match;
            break;
        }
    }
}

// Không tự động chọn match đầu tiên - để người dùng tự chọn
// if (!$selectedMatch && !empty($myMatches)) {
//     $selectedMatch = $myMatches[0];
//     $selectedMatchId = $selectedMatch['maGhepDoi'];
// }

// Lấy danh sách tin nhắn nếu có match
$messages = [];
if ($selectedMatchId) {
    $messages = $messageModel->getMessages($selectedMatchId);
}

// Helper function để hiển thị thời gian
function formatTime($datetime) {
    $timestamp = strtotime($datetime);
    $now = time();
    $diff = $now - $timestamp;
    
    if ($diff < 60) {
        return "Vừa xong";
    } elseif ($diff < 3600) {
        return floor($diff / 60) . " phút trước";
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . " giờ trước";
    } else {
        return date("H:i d/m", $timestamp);
    }
}

// Lấy flash message nếu có (từ login → match ngay)
$successMessage = Session::getFlash('success_message');
$matchId = Session::getFlash('match_id');
$matchedUserId = Session::getFlash('matched_user_id');

// Lấy thông tin người được match nếu có
$matchedUserProfile = null;
if ($matchedUserId) {
    $matchedUserProfile = $profileModel->getProfile($matchedUserId);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tin nhắn - DuyenHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/message.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="chat-container">
        <!-- Left sidebar - Messages list -->
        <div class="chat-sidebar">
            <!-- Header -->
            <div class="sidebar-header">
                <button class="btn-back" onclick="window.location.href='../trangchu/index.php'">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <div class="sidebar-logo">
                    <a href="../trangchu/index.php" style="display: flex; align-items: center; text-decoration: none; color: inherit;">
                        <img src="../../public/img/logo.jpg" alt="DuyenHub Logo">
                        <span style="margin-left: 6px; font-weight: 600; font-size: 18px; color: #e94057;">DuyenHub</span>
                    </a>
                </div>
                <h1>Tin nhắn</h1>
                <!-- Notification Badge -->
                <div style="position: absolute; top: 15px; right: 15px; display: flex; gap: 10px;">
                    <?php if ($newMatchesCount > 0): ?>
                    <a href="../timkiem/ghepdoinhanh.php" style="position: relative; text-decoration: none;">
                        <i class="fas fa-heart" style="color: #e94057; font-size: 20px;"></i>
                        <span id="matchesBadge" style="position: absolute; top: -8px; right: -8px; background: #ff6b9d; color: white; border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold;"><?php echo $newMatchesCount; ?></span>
                    </a>
                    <?php endif; ?>
                    <a href="../trangchu/index.php" style="position: relative; text-decoration: none;">
                        <i class="fas fa-home" style="color: #667eea; font-size: 20px;"></i>
                    </a>
                </div>
            </div>

            <!-- Toggle button -->
            <div class="toggle-section">
                <button class="btn-toggle active">Tất cả</button>
            </div>

            <!-- Messages list -->
            <div class="messages-list">
                <?php if (empty($myMatches)): ?>
                    <div class="empty-state" style="padding: 40px 20px; text-align: center; color: #999;">
                        <i class="fas fa-comments" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                        <p>Chưa có cuộc trò chuyện nào</p>
                        <p style="font-size: 14px;">Hãy thích và ghép đôi để bắt đầu chat!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($myMatches as $match): 
                        $isActive = ($selectedMatchId == $match['maGhepDoi']) ? 'active' : '';
                        $lastMessage = $messageModel->getLastMessage($match['maGhepDoi']);
                        
                        // Đếm số tin nhắn chưa đọc
                        $unreadCount = $messageModel->getUnreadCount($match['maGhepDoi'], $currentUserId);
                        $hasUnread = $unreadCount > 0;
                        
                        // Xử lý avatar
                        $avatarSrc = '/public/img/default-avatar.jpg';
                        if (!empty($match['avt'])) {
                            if (strpos($match['avt'], 'public/') === 0) {
                                $avatarSrc = '/' . htmlspecialchars($match['avt']);
                            } else {
                                $avatarSrc = '/public/uploads/avatars/' . htmlspecialchars($match['avt']);
                            }
                        }
                    ?>
                        <div class="message-item <?php echo $isActive; ?> <?php echo $hasUnread ? 'has-unread' : ''; ?>" 
                             onclick="window.location.href='?match=<?php echo $match['maGhepDoi']; ?>'">
                            <div class="message-avatar">
                                <img src="<?php echo $avatarSrc; ?>" alt="<?php echo htmlspecialchars($match['ten']); ?>">
                                <?php 
                                $isOnline = $userModel->isUserOnline($match['maNguoiDung']);
                                $isInactive = $userModel->isUserInactive($match['maNguoiDung']);
                                if ($isOnline): 
                                ?>
                                    <div class="online-dot pulse" title="Đang online"></div>
                                <?php elseif ($isInactive): ?>
                                    <div class="offline-dot" title="Không hoạt động"></div>
                                <?php endif; ?>
                            </div>
                            <div class="message-content">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                                    <h3 class="message-name"><?php echo htmlspecialchars($match['ten']); ?></h3>
                                    <?php if ($hasUnread): ?>
                                        <span class="unread-badge"><?php echo $unreadCount; ?></span>
                                    <?php endif; ?>
                                </div>
                                <p class="message-text <?php echo $hasUnread ? 'unread-text' : ''; ?>">
                                    <?php 
                                    if ($lastMessage) {
                                        echo htmlspecialchars(mb_substr($lastMessage['noiDung'], 0, 50));
                                        if (mb_strlen($lastMessage['noiDung']) > 50) echo '...';
                                    } else {
                                        echo 'Bạn vừa ghép đôi với ' . htmlspecialchars($match['ten']) . ', hãy gửi lời chào!';
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right side - Chat box -->
        <div class="chat-box">
            <?php if ($selectedMatch): 
                // Xử lý avatar cho chat header
                $chatAvatarSrc = '/public/img/default-avatar.jpg';
                if (!empty($selectedMatch['avt'])) {
                    if (strpos($selectedMatch['avt'], 'public/') === 0) {
                        $chatAvatarSrc = '/' . htmlspecialchars($selectedMatch['avt']);
                    } else {
                        $chatAvatarSrc = '/public/uploads/avatars/' . htmlspecialchars($selectedMatch['avt']);
                    }
                }
            ?>
                <!-- Chat header -->
                <div class="chat-header">
                    <div class="chat-user-info">
                        <img src="<?php echo $chatAvatarSrc; ?>" alt="<?php echo htmlspecialchars($selectedMatch['ten']); ?>">
                        <div>
                            <h2><?php echo htmlspecialchars($selectedMatch['ten']); ?></h2>
                            <p style="font-size: 12px; color: #999; margin: 0;">
                                <?php 
                                $age = $profileModel->calculateAge($selectedMatch['ngaySinh']);
                                echo $age . ' tuổi • ' . htmlspecialchars($selectedMatch['noiSong']);
                                ?>
                            </p>
                            <!-- Trạng thái online sẽ được cập nhật bởi JavaScript -->
                            <p class="online-status-text" style="font-size: 11px; margin: 4px 0 0 0;">
                                <?php 
                                $isOnline = $userModel->isUserOnline($selectedMatch['maNguoiDung']);
                                $isInactive = $userModel->isUserInactive($selectedMatch['maNguoiDung']);
                                if ($isOnline): 
                                ?>
                                    <span style="color: #28a745; font-weight: 600;">
                                        <i class="fas fa-circle" style="font-size: 8px;"></i> Đang hoạt động
                                    </span>
                                <?php elseif ($isInactive): ?>
                                    <span style="color: #95a5a6; font-weight: 500;">
                                        <i class="fas fa-circle" style="font-size: 8px;"></i> Không hoạt động
                                    </span>
                                <?php else:
                                    $lastActivity = $userModel->getLastActivity($selectedMatch['maNguoiDung']);
                                    if ($lastActivity && $lastActivity['lanHoatDongCuoi'] !== null && $lastActivity['minutesAgo'] !== null):
                                        $minutes = $lastActivity['minutesAgo'];
                                        $lastSeenText = '';
                                        if ($minutes < 60) {
                                            $lastSeenText = $minutes . ' phút trước';
                                        } elseif ($minutes < 1440) {
                                            $lastSeenText = floor($minutes / 60) . ' giờ trước';
                                        } else {
                                            $lastSeenText = floor($minutes / 1440) . ' ngày trước';
                                        }
                                ?>
                                    <span style="color: #95a5a6; font-style: italic;">
                                        <i class="far fa-clock"></i> <?php echo $lastSeenText; ?>
                                    </span>
                                <?php 
                                    endif;
                                endif; 
                                ?>
                            </p>
                        </div>
                    </div>
                    <div class="chat-header-actions">
                        <div class="dropdown-menu-container">
                            <button class="btn-menu" onclick="toggleChatMenu(event)">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu" id="chatMenu">
                                <a href="../hoso/xemnguoikhac.php?id=<?php echo $selectedMatch['maNguoiDung']; ?>" class="dropdown-item">
                                    <i class="fas fa-user"></i> Xem hồ sơ
                                </a>
                                <a href="#" onclick="confirmDeleteChat(event, <?php echo $selectedMatch['maNguoiDung']; ?>)" class="dropdown-item delete-item">
                                    <i class="fas fa-trash"></i> Xóa tin nhắn
                                </a>
                            </div>
                        </div>
                        <button class="btn-close" onclick="closeConversation()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Chat messages area -->
                <div class="chat-messages" id="chatMessages">
                    <?php if (empty($messages)): ?>
                        <div class="chat-welcome">
                            <p>Bạn vừa kết nối với <span><?php echo htmlspecialchars($selectedMatch['ten']); ?></span>, hãy gửi lời chào!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): 
                            $isSent = ($msg['maNguoiGui'] == $currentUserId);
                            $messageClass = $isSent ? 'sent' : 'received';
                        ?>
                            <div class="message <?php echo $messageClass; ?>" data-message-id="<?php echo $msg['maTinNhan']; ?>">
                                <?php if (!$isSent): ?>
                                    <img src="<?php echo $chatAvatarSrc; ?>" alt="" class="message-avatar-small">
                                <?php endif; ?>
                                
                                <?php 
                                $msgStatus = $msg['trangThai'] ?? 'sent';
                                if ($msgStatus === 'recalled'): 
                                ?>
                                    <div class="message-bubble message-recalled">
                                        <p><i class="fas fa-ban"></i> Tin nhắn đã bị thu hồi</p>
                                        <span class="message-time">
                                            <?php echo date('H:i', strtotime($msg['thoiDiemGui'])); ?>
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <div class="message-bubble">
                                        <p><?php echo nl2br(htmlspecialchars($msg['noiDung'])); ?></p>
                                        <span class="message-time">
                                            <?php echo date('H:i', strtotime($msg['thoiDiemGui'])); ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($isSent): ?>
                                    <span class="message-status<?php echo ($msgStatus === 'failed') ? ' message-failed' : ''; ?>" 
                                          data-status="<?php echo $msgStatus; ?>"
                                          <?php echo ($msgStatus === 'failed') ? 'data-message-id="' . $msg['maTinNhan'] . '"' : ''; ?>>
                                        <?php 
                                        if ($msgStatus === 'recalled'): ?>
                                            <i class="fas fa-ban" style="color: #e74c3c;" title="Đã thu hồi"></i>
                                        <?php elseif ($msgStatus === 'failed'): ?>
                                            <i class="fas fa-exclamation-circle" style="color: #e74c3c;" title="Gửi thất bại - Nhấn để thử lại"></i>
                                        <?php elseif ($msgStatus === 'sending'): ?>
                                            <i class="fas fa-clock" style="color: #95a5a6;" title="Đang gửi"></i>
                                        <?php elseif ($msgStatus === 'seen'): ?>
                                            <i class="fas fa-eye" style="color: #2E7D32;" title="Đã xem"></i>
                                        <?php elseif ($msgStatus === 'delivered'): ?>
                                            <i class="fas fa-check-double" style="color: #95a5a6;" title="Đã nhận"></i>
                                        <?php else: ?>
                                            <i class="fas fa-check" style="color: #95a5a6;" title="Đã gửi"></i>
                                        <?php endif; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Chat input -->
                <div class="chat-input-container">
                    <button class="btn-input-action" title="Emoji" onclick="toggleEmojiPicker()">
                        <i class="far fa-smile"></i>
                    </button>
                    
                    <input type="text" class="chat-input" placeholder="Nhập tin nhắn..." id="messageInput">
                    
                    <button class="btn-send" onclick="sendMessage()">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
                
                <!-- Emoji Picker (Simple) -->
                <div id="emojiPicker" class="emoji-picker" style="display: none;">
                    <div class="emoji-grid">
                        <span class="emoji-item" onclick="insertEmoji('😀')">😀</span>
                        <span class="emoji-item" onclick="insertEmoji('😃')">😃</span>
                        <span class="emoji-item" onclick="insertEmoji('😄')">😄</span>
                        <span class="emoji-item" onclick="insertEmoji('😁')">😁</span>
                        <span class="emoji-item" onclick="insertEmoji('😊')">😊</span>
                        <span class="emoji-item" onclick="insertEmoji('😍')">😍</span>
                        <span class="emoji-item" onclick="insertEmoji('🥰')">🥰</span>
                        <span class="emoji-item" onclick="insertEmoji('😘')">😘</span>
                        <span class="emoji-item" onclick="insertEmoji('😗')">😗</span>
                        <span class="emoji-item" onclick="insertEmoji('😙')">😙</span>
                        <span class="emoji-item" onclick="insertEmoji('😚')">😚</span>
                        <span class="emoji-item" onclick="insertEmoji('🤗')">🤗</span>
                        <span class="emoji-item" onclick="insertEmoji('🤩')">🤩</span>
                        <span class="emoji-item" onclick="insertEmoji('🤔')">🤔</span>
                        <span class="emoji-item" onclick="insertEmoji('🤨')">🤨</span>
                        <span class="emoji-item" onclick="insertEmoji('😐')">😐</span>
                        <span class="emoji-item" onclick="insertEmoji('😑')">😑</span>
                        <span class="emoji-item" onclick="insertEmoji('😶')">😶</span>
                        <span class="emoji-item" onclick="insertEmoji('🙄')">🙄</span>
                        <span class="emoji-item" onclick="insertEmoji('😏')">😏</span>
                        <span class="emoji-item" onclick="insertEmoji('😣')">😣</span>
                        <span class="emoji-item" onclick="insertEmoji('😥')">😥</span>
                        <span class="emoji-item" onclick="insertEmoji('😮')">😮</span>
                        <span class="emoji-item" onclick="insertEmoji('🤐')">🤐</span>
                        <span class="emoji-item" onclick="insertEmoji('😯')">😯</span>
                        <span class="emoji-item" onclick="insertEmoji('😪')">😪</span>
                        <span class="emoji-item" onclick="insertEmoji('😫')">😫</span>
                        <span class="emoji-item" onclick="insertEmoji('😴')">😴</span>
                        <span class="emoji-item" onclick="insertEmoji('😌')">😌</span>
                        <span class="emoji-item" onclick="insertEmoji('😛')">😛</span>
                        <span class="emoji-item" onclick="insertEmoji('😜')">😜</span>
                        <span class="emoji-item" onclick="insertEmoji('😝')">😝</span>
                        <span class="emoji-item" onclick="insertEmoji('🤤')">🤤</span>
                        <span class="emoji-item" onclick="insertEmoji('😒')">😒</span>
                        <span class="emoji-item" onclick="insertEmoji('😓')">😓</span>
                        <span class="emoji-item" onclick="insertEmoji('😔')">😔</span>
                        <span class="emoji-item" onclick="insertEmoji('😕')">😕</span>
                        <span class="emoji-item" onclick="insertEmoji('🙃')">🙃</span>
                        <span class="emoji-item" onclick="insertEmoji('🤑')">🤑</span>
                        <span class="emoji-item" onclick="insertEmoji('😲')">😲</span>
                        <span class="emoji-item" onclick="insertEmoji('☹️')">☹️</span>
                        <span class="emoji-item" onclick="insertEmoji('🙁')">🙁</span>
                        <span class="emoji-item" onclick="insertEmoji('😖')">😖</span>
                        <span class="emoji-item" onclick="insertEmoji('😞')">😞</span>
                        <span class="emoji-item" onclick="insertEmoji('😟')">😟</span>
                        <span class="emoji-item" onclick="insertEmoji('😤')">😤</span>
                        <span class="emoji-item" onclick="insertEmoji('😢')">😢</span>
                        <span class="emoji-item" onclick="insertEmoji('😭')">😭</span>
                        <span class="emoji-item" onclick="insertEmoji('😦')">😦</span>
                        <span class="emoji-item" onclick="insertEmoji('😧')">😧</span>
                        <span class="emoji-item" onclick="insertEmoji('😨')">😨</span>
                        <span class="emoji-item" onclick="insertEmoji('😩')">😩</span>
                        <span class="emoji-item" onclick="insertEmoji('🤯')">🤯</span>
                        <span class="emoji-item" onclick="insertEmoji('😬')">😬</span>
                        <span class="emoji-item" onclick="insertEmoji('😰')">😰</span>
                        <span class="emoji-item" onclick="insertEmoji('😱')">😱</span>
                        <span class="emoji-item" onclick="insertEmoji('🥵')">🥵</span>
                        <span class="emoji-item" onclick="insertEmoji('🥶')">🥶</span>
                        <span class="emoji-item" onclick="insertEmoji('😳')">😳</span>
                        <span class="emoji-item" onclick="insertEmoji('🤪')">🤪</span>
                        <span class="emoji-item" onclick="insertEmoji('😵')">😵</span>
                        <span class="emoji-item" onclick="insertEmoji('🥴')">🥴</span>
                        <span class="emoji-item" onclick="insertEmoji('😠')">😠</span>
                        <span class="emoji-item" onclick="insertEmoji('😡')">😡</span>
                        <span class="emoji-item" onclick="insertEmoji('🤬')">🤬</span>
                        <span class="emoji-item" onclick="insertEmoji('❤️')">❤️</span>
                        <span class="emoji-item" onclick="insertEmoji('💕')">💕</span>
                        <span class="emoji-item" onclick="insertEmoji('💖')">💖</span>
                        <span class="emoji-item" onclick="insertEmoji('💗')">💗</span>
                        <span class="emoji-item" onclick="insertEmoji('💘')">💘</span>
                        <span class="emoji-item" onclick="insertEmoji('💝')">💝</span>
                        <span class="emoji-item" onclick="insertEmoji('💞')">💞</span>
                        <span class="emoji-item" onclick="insertEmoji('💓')">💓</span>
                        <span class="emoji-item" onclick="insertEmoji('💔')">💔</span>
                        <span class="emoji-item" onclick="insertEmoji('🔥')">🔥</span>
                        <span class="emoji-item" onclick="insertEmoji('✨')">✨</span>
                        <span class="emoji-item" onclick="insertEmoji('⭐')">⭐</span>
                        <span class="emoji-item" onclick="insertEmoji('🌟')">🌟</span>
                        <span class="emoji-item" onclick="insertEmoji('💫')">💫</span>
                        <span class="emoji-item" onclick="insertEmoji('👍')">👍</span>
                        <span class="emoji-item" onclick="insertEmoji('👎')">👎</span>
                        <span class="emoji-item" onclick="insertEmoji('👏')">👏</span>
                        <span class="emoji-item" onclick="insertEmoji('🙌')">🙌</span>
                        <span class="emoji-item" onclick="insertEmoji('👋')">👋</span>
                        <span class="emoji-item" onclick="insertEmoji('🤝')">🤝</span>
                        <span class="emoji-item" onclick="insertEmoji('🙏')">🙏</span>
                    </div>
                </div>
            <?php else: ?>
                <div class="no-chat-selected" style="display: flex; align-items: center; justify-content: center; height: 100%; flex-direction: column; color: #999;">
                    <i class="fas fa-comments" style="font-size: 64px; margin-bottom: 20px; opacity: 0.3;"></i>
                    <h3 style="margin: 0 0 10px 0;">Chọn một cuộc trò chuyện</h3>
                    <p>Chọn một người từ danh sách bên trái để bắt đầu nhắn tin</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const currentMatchId = <?php echo $selectedMatchId ? $selectedMatchId : 'null'; ?>;
        const currentUserId = <?php echo $currentUserId; ?>;
        const otherUserId = <?php echo $selectedMatch ? $selectedMatch['maNguoiDung'] : 'null'; ?>;
        let lastMessageId = <?php echo !empty($messages) ? end($messages)['maTinNhan'] : 0; ?>;
        let pollingInterval = null;

        // Close conversation and show empty state
        function closeConversation() {
            // Redirect to message page without matchId parameter
            window.location.href = 'message.php';
        }

        // Toggle chat menu
        function toggleChatMenu(event) {
            event.stopPropagation();
            const menu = document.getElementById('chatMenu');
            menu.classList.toggle('show');
        }

        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('chatMenu');
            if (menu && menu.classList.contains('show')) {
                menu.classList.remove('show');
            }
        });

        // Confirm and delete chat (unmatch)
        function confirmDeleteChat(event, targetUserId) {
            event.preventDefault();
            event.stopPropagation();
            
            console.log('confirmDeleteChat called with targetUserId:', targetUserId);
            
            if (!confirm('Bạn có chắc chắn muốn XÓA TIN NHẮN với người này?\n\nLưu ý: Việc này sẽ hủy ghép đôi và xóa toàn bộ tin nhắn. Bạn sẽ không thể trò chuyện với người này nữa trừ khi ghép đôi lại.')) {
                return;
            }
            
            // Close menu
            const menu = document.getElementById('chatMenu');
            if (menu) {
                menu.classList.remove('show');
            }
            
            console.log('Sending unmatch request to server...');
            console.log('Current matchId:', currentMatchId);
            
            // Call unmatch API với matchId cụ thể
            const formData = 'action=unmatch&targetUserId=' + targetUserId + 
                           '&matchId=' + (currentMatchId || 0) +
                           '&csrf_token=<?php echo Session::getCSRFToken(); ?>';
            
            fetch('/controller/cMatch.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    alert('Đã xóa tin nhắn và hủy ghép đôi thành công!');
                    window.location.href = 'message.php';
                } else {
                    alert(data.message || 'Không thể xóa tin nhắn!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi xóa tin nhắn!');
            });
        }

        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();

            if (message === '' || !currentMatchId) return;

            // Disable input
            input.disabled = true;
            
            // Tắt typing indicator ngay khi gửi
            clearTimeout(typingTimeout);
            updateTypingStatus(false);

            // Gửi tin nhắn
            fetch('/controller/cMessage.php?action=send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'matchId=' + currentMatchId + '&content=' + encodeURIComponent(message)
            })
            .then(response => response.json())
            .then(data => {
                console.log('Send message response:', data);
                
                if (data.success) {
                    // Xóa welcome message nếu có
                    const welcome = document.querySelector('.chat-welcome');
                    if (welcome) welcome.remove();

                    // Thêm tin nhắn vào UI
                    addMessageToUI({
                        maTinNhan: data.messageId,
                        maNguoiGui: currentUserId,
                        noiDung: message,
                        thoiDiemGui: data.timestamp
                    }, true);

                    // Cập nhật lastMessageId
                    lastMessageId = data.messageId;

                    // Clear input
                    input.value = '';
                    input.disabled = false;
                    input.focus();

                    // Scroll to bottom
                    scrollToBottom();
                } else {
                    alert(data.message || 'Không thể gửi tin nhắn!');
                    input.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi gửi tin nhắn!');
                input.disabled = false;
            });
        }

        function addMessageToUI(message, isSent) {
            const messagesContainer = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message ' + (isSent ? 'sent' : 'received');
            messageDiv.setAttribute('data-message-id', message.maTinNhan);
            
            const time = new Date(message.thoiDiemGui).toLocaleTimeString('vi-VN', {
                hour: '2-digit',
                minute: '2-digit'
            });
            
            // Xác định trạng thái
            const status = message.trangThai || 'sent';
            let statusIcon = '';
            if (isSent) {
                if (status === 'recalled') {
                    // Tin nhắn đã thu hồi
                    statusIcon = '<span class="message-status" data-status="recalled"><i class="fas fa-ban" style="color: #e74c3c;" title="Đã thu hồi"></i></span>';
                } else if (status === 'failed') {
                    // Tin nhắn gửi thất bại
                    statusIcon = '<span class="message-status message-failed" data-status="failed" data-message-id="' + message.maTinNhan + '"><i class="fas fa-exclamation-circle" style="color: #e74c3c;" title="Gửi thất bại - Nhấn để thử lại"></i></span>';
                } else if (status === 'sending') {
                    // Đang gửi
                    statusIcon = '<span class="message-status" data-status="sending"><i class="fas fa-clock" style="color: #95a5a6;" title="Đang gửi"></i></span>';
                } else if (status === 'seen') {
                    // Đã xem
                    statusIcon = '<span class="message-status" data-status="seen"><i class="fas fa-eye" style="color: #2E7D32;" title="Đã xem"></i></span>';
                } else if (status === 'delivered') {
                    // Đã nhận
                    statusIcon = '<span class="message-status" data-status="delivered"><i class="fas fa-check-double" style="color: #95a5a6;" title="Đã nhận"></i></span>';
                } else {
                    // Đã gửi
                    statusIcon = '<span class="message-status" data-status="sent"><i class="fas fa-check" style="color: #95a5a6;" title="Đã gửi"></i></span>';
                }
            }
            
            let html = '';
            if (!isSent) {
                html += `<img src="<?php echo $chatAvatarSrc ?? ''; ?>" alt="" class="message-avatar-small">`;
            }
            
            // Kiểm tra nếu tin nhắn bị thu hồi
            if (status === 'recalled') {
                html += `
                    <div class="message-bubble message-recalled">
                        <p><i class="fas fa-ban"></i> Tin nhắn đã bị thu hồi</p>
                        <span class="message-time">${time}</span>
                    </div>
                `;
            } else {
                html += `
                    <div class="message-bubble">
                        <p>${escapeHtml(message.noiDung)}</p>
                        <span class="message-time">${time}</span>
                    </div>
                `;
            }
            
            // Thêm trạng thái bên ngoài message-bubble
            if (isSent) {
                html += statusIcon;
            }
            
            messageDiv.innerHTML = html;
            messagesContainer.appendChild(messageDiv);
        }

        function scrollToBottom() {
            const messagesContainer = document.getElementById('chatMessages');
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        // Polling để lấy tin nhắn mới
        function checkNewMessages() {
            if (!currentMatchId) return;

            fetch(`/controller/cMessage.php?action=get_new_messages&matchId=${currentMatchId}&lastMessageId=${lastMessageId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.count > 0) {
                    console.log('New messages:', data.messages);
                    
                    data.messages.forEach(msg => {
                        // Xóa welcome message nếu có
                        const welcome = document.querySelector('.chat-welcome');
                        if (welcome) welcome.remove();

                        // Thêm tin nhắn vào UI
                        const isSent = (msg.maNguoiGui == currentUserId);
                        addMessageToUI(msg, isSent);

                        // Cập nhật lastMessageId
                        if (msg.maTinNhan > lastMessageId) {
                            lastMessageId = msg.maTinNhan;
                        }
                    });
                    
                    // Xóa typing indicator khi có tin nhắn mới đến
                    const typingDiv = document.getElementById('typingIndicator');
                    if (typingDiv) {
                        typingDiv.remove();
                    }

                    // Scroll to bottom
                    scrollToBottom();
                    
                    // Đánh dấu đã xem tin nhắn
                    markMessagesAsSeen();
                }
            })
            .catch(error => {
                console.error('Polling error:', error);
            });
        }
        
        // Polling để cập nhật status của tin nhắn đã gửi (realtime)
        function checkMessageStatusUpdates() {
            if (!currentMatchId) return;
            
            // Lấy tất cả tin nhắn đã gửi bởi user hiện tại
            const sentMessages = document.querySelectorAll('.message.sent[data-message-id]');
            if (sentMessages.length === 0) return;
            
            const messageIds = Array.from(sentMessages).map(msg => msg.getAttribute('data-message-id')).filter(id => id);
            if (messageIds.length === 0) return;
            
            fetch(`/controller/cMessage.php?action=get_status_updates&matchId=${currentMatchId}&messageIds=${messageIds.join(',')}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.statuses) {
                    // Cập nhật status cho từng tin nhắn
                    Object.keys(data.statuses).forEach(messageId => {
                        const newStatus = data.statuses[messageId];
                        updateMessageStatus(messageId, newStatus);
                    });
                }
            })
            .catch(error => {
                console.error('Status polling error:', error);
            });
        }
        
        // Cập nhật status của một tin nhắn trong UI
        function updateMessageStatus(messageId, newStatus) {
            const messageElement = document.querySelector(`.message.sent[data-message-id="${messageId}"]`);
            if (!messageElement) return;
            
            const statusElement = messageElement.querySelector('.message-status');
            if (!statusElement) return;
            
            const currentStatus = statusElement.getAttribute('data-status');
            if (currentStatus === newStatus) return; // Không thay đổi
            
            // Cập nhật attribute
            statusElement.setAttribute('data-status', newStatus);
            
            // Xóa class message-failed nếu không còn failed
            if (newStatus !== 'failed') {
                statusElement.classList.remove('message-failed');
            }
            
            // Cập nhật icon
            let iconHTML = '';
            switch(newStatus) {
                case 'recalled':
                    iconHTML = '<i class="fas fa-ban" style="color: #e74c3c;" title="Đã thu hồi"></i>';
                    break;
                case 'failed':
                    iconHTML = '<i class="fas fa-exclamation-circle" style="color: #e74c3c;" title="Gửi thất bại - Nhấn để thử lại"></i>';
                    statusElement.classList.add('message-failed');
                    break;
                case 'sending':
                    iconHTML = '<i class="fas fa-clock" style="color: #95a5a6;" title="Đang gửi"></i>';
                    break;
                case 'seen':
                    iconHTML = '<i class="fas fa-eye" style="color: #2E7D32;" title="Đã xem"></i>';
                    break;
                case 'delivered':
                    iconHTML = '<i class="fas fa-check-double" style="color: #95a5a6;" title="Đã nhận"></i>';
                    break;
                default: // sent
                    iconHTML = '<i class="fas fa-check" style="color: #95a5a6;" title="Đã gửi"></i>';
            }
            
            statusElement.innerHTML = iconHTML;
        }
        
        // Đánh dấu tin nhắn đã xem
        function markMessagesAsSeen() {
            if (!currentMatchId) return;
            
            fetch('/controller/cMessage.php?action=mark_seen', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'matchId=' + currentMatchId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Messages marked as seen');
                }
            })
            .catch(error => {
                console.error('Error marking messages as seen:', error);
            });
        }
        
        // Cập nhật badge tin nhắn chưa đọc cho một match
        function updateUnreadBadge(matchId, count) {
            const messageItems = document.querySelectorAll('.message-item');
            messageItems.forEach(item => {
                const itemLink = item.getAttribute('onclick');
                if (itemLink && itemLink.includes('match=' + matchId)) {
                    const badge = item.querySelector('.unread-badge');
                    const messageText = item.querySelector('.message-text');
                    
                    if (count > 0) {
                        // Có tin nhắn chưa đọc
                        if (!badge) {
                            // Tạo badge mới
                            const nameDiv = item.querySelector('.message-name').parentElement;
                            const newBadge = document.createElement('span');
                            newBadge.className = 'unread-badge';
                            newBadge.textContent = count;
                            nameDiv.appendChild(newBadge);
                        } else {
                            // Cập nhật badge có sẵn
                            badge.textContent = count;
                        }
                        
                        // Thêm class has-unread
                        item.classList.add('has-unread');
                        if (messageText) {
                            messageText.classList.add('unread-text');
                        }
                    } else {
                        // Không còn tin nhắn chưa đọc
                        if (badge) {
                            badge.remove();
                        }
                        item.classList.remove('has-unread');
                        if (messageText) {
                            messageText.classList.remove('unread-text');
                        }
                    }
                }
            });
        }
        
        // Cập nhật tất cả các badge
        function updateAllUnreadBadges() {
            fetch('/controller/cMessage.php?action=get_all_unread_counts')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.counts) {
                    Object.keys(data.counts).forEach(matchId => {
                        const count = data.counts[matchId];
                        // Không cập nhật badge cho match đang mở
                        if (matchId != currentMatchId) {
                            updateUnreadBadge(matchId, count);
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error updating unread badges:', error);
            });
        }

        // Send message on Enter key
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('messageInput');
            if (input) {
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        sendMessage();
                    }
                });

                // Auto focus
                input.focus();
                
                // Lắng nghe sự kiện nhập tin nhắn để hiển thị typing indicator
                input.addEventListener('input', handleTypingInput);
                
                // Tắt typing khi blur (rời khỏi input)
                input.addEventListener('blur', function() {
                    clearTimeout(typingTimeout);
                    updateTypingStatus(false);
                });
            }

            // Scroll to bottom on load
            scrollToBottom();

            // Start polling nếu có match được chọn
            if (currentMatchId) {
                // Poll tin nhắn mới mỗi 0.3 giây (300ms) - TỨC THÌ REAL-TIME!
                pollingInterval = setInterval(checkNewMessages, 300);
                
                // Poll status updates mỗi 0.5 giây (500ms) - trạng thái siêu nhanh
                setInterval(checkMessageStatusUpdates, 500);
                
                // Poll typing status mỗi 0.3 giây (300ms) - typing tức thì
                setInterval(checkTypingStatus, 300);
                
                // Đánh dấu tin nhắn đã xem khi mở trang
                markMessagesAsSeen();
                
                // Xóa badge ngay lập tức khi mở chat
                updateUnreadBadge(currentMatchId, 0);
            }
            
            // Update unread badges cho tất cả các match mỗi 2 giây
            setInterval(updateAllUnreadBadges, 2000);
            
            // Xử lý click vào tin nhắn gửi thất bại để retry
            document.addEventListener('click', function(e) {
                if (e.target.closest('.message-failed')) {
                    const failedStatus = e.target.closest('.message-failed');
                    const messageId = failedStatus.getAttribute('data-message-id');
                    if (messageId) {
                        retryFailedMessage(messageId);
                    }
                }
            });
        });

        // Cleanup khi rời trang
        window.addEventListener('beforeunload', function() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
            }
        });

        // Cập nhật trạng thái online của user hiện tại
        function updateOnlineStatus() {
            fetch('../../controller/cUpdateOnlineStatus.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Online status updated:', data.timestamp);
                }
            })
            .catch(error => {
                console.error('Error updating online status:', error);
            });
        }

        // Cập nhật ngay khi trang load
        updateOnlineStatus();

        // Cập nhật mỗi 2 phút
        setInterval(updateOnlineStatus, 120000);
        
        // ===== TYPING INDICATOR =====
        let typingTimeout;
        let lastTypingUpdate = 0; // Timestamp của lần update cuối
        
        function handleTypingInput() {
            if (!currentMatchId) return;
            
            const now = Date.now();
            
            // Chỉ gửi typing update nếu đã qua 500ms từ lần cuối (throttle)
            if (now - lastTypingUpdate > 500) {
                updateTypingStatus(true);
                lastTypingUpdate = now;
            }
            
            // Clear timeout cũ
            clearTimeout(typingTimeout);
            
            // Sau 2 giây không nhập thì ngừng hiển thị "đang nhập"
            typingTimeout = setTimeout(() => {
                updateTypingStatus(false);
            }, 2000);
        }
        
        function updateTypingStatus(isTyping) {
            fetch('/controller/cUpdateTyping.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    matchId: currentMatchId,
                    isTyping: isTyping
                })
            })
            .catch(error => {
                console.error('Error updating typing status:', error);
            });
        }
        
        function checkTypingStatus() {
            if (!currentMatchId) return;
            
            fetch('/controller/cCheckTyping.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    matchId: currentMatchId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showTypingIndicator(data.isTyping, data.userName);
                }
            })
            .catch(error => {
                console.error('Error checking typing status:', error);
            });
        }
        
        function showTypingIndicator(isTyping, userName) {
            const messagesContainer = document.getElementById('chatMessages');
            let typingDiv = document.getElementById('typingIndicator');
            
            if (isTyping) {
                if (!typingDiv) {
                    typingDiv = document.createElement('div');
                    typingDiv.id = 'typingIndicator';
                    typingDiv.className = 'typing-indicator';
                    typingDiv.innerHTML = `
                        <img src="<?php echo $chatAvatarSrc ?? ''; ?>" alt="" class="message-avatar-small">
                        <div class="typing-bubble">
                            <span></span><span></span><span></span>
                        </div>
                    `;
                    messagesContainer.appendChild(typingDiv);
                    scrollToBottom();
                }
            } else {
                if (typingDiv) {
                    typingDiv.remove();
                }
            }
        }
        
        // ===== RETRY FAILED MESSAGE =====
        function retryFailedMessage(messageId) {
            fetch('/controller/cRetryMessage.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    messageId: messageId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Cập nhật UI - xóa icon lỗi, hiển thị "đã gửi"
                    const messageElement = document.querySelector(`[data-message-id="${messageId}"]`);
                    if (messageElement) {
                        updateMessageStatusUI(messageElement, 'sent');
                    }
                } else {
                    alert('Không thể gửi lại tin nhắn. Vui lòng thử lại sau.');
                }
            })
            .catch(error => {
                console.error('Error retrying message:', error);
                alert('Có lỗi xảy ra. Vui lòng thử lại sau.');
            });
        }

        
        // ===== EMOJI PICKER =====
        function toggleEmojiPicker() {
            const picker = document.getElementById('emojiPicker');
            if (picker.style.display === 'none' || !picker.style.display) {
                picker.style.display = 'block';
            } else {
                picker.style.display = 'none';
            }
        }
        
        function insertEmoji(emoji) {
            const input = document.getElementById('messageInput');
            const cursorPos = input.selectionStart;
            const textBefore = input.value.substring(0, cursorPos);
            const textAfter = input.value.substring(cursorPos);
            
            input.value = textBefore + emoji + textAfter;
            input.focus();
            
            // Đặt cursor sau emoji
            const newCursorPos = cursorPos + emoji.length;
            input.setSelectionRange(newCursorPos, newCursorPos);
            
            // Đóng emoji picker
            document.getElementById('emojiPicker').style.display = 'none';
        }
        
        // Đóng emoji picker khi click ra ngoài
        document.addEventListener('click', function(e) {
            const picker = document.getElementById('emojiPicker');
            const emojiBtn = e.target.closest('.btn-input-action');
            
            if (picker && picker.style.display === 'block' && 
                !picker.contains(e.target) && 
                (!emojiBtn || emojiBtn.title !== 'Emoji')) {
                picker.style.display = 'none';
            }
        });


        // Cập nhật khi user tương tác
        let activityTimeout;
        function resetActivityTimer() {
            clearTimeout(activityTimeout);
            activityTimeout = setTimeout(updateOnlineStatus, 5000);
        }

        ['mousedown', 'keydown', 'scroll', 'touchstart'].forEach(event => {
            document.addEventListener(event, resetActivityTimer, true);
        });

        // Cập nhật trạng thái online của người đối phương
        <?php if ($selectedMatch): ?>
        const partnerUserId = <?php echo $selectedMatch['maNguoiDung']; ?>;
        
        function updatePartnerOnlineStatus() {
            fetch('../../controller/cCheckOnlineStatus.php?userId=' + partnerUserId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const statusElement = document.querySelector('.online-status-text');
                    if (!statusElement) return;
                    if (data.isOnline) {
                        statusElement.innerHTML = '<span style="color: #28a745; font-weight: 600;"><i class="fas fa-circle" style="font-size: 8px;"></i> Đang hoạt động</span>';
                    } else if (data.isInactive) {
                        statusElement.innerHTML = '<span style="color: #95a5a6; font-weight: 500;"><i class="fas fa-circle" style="font-size: 8px;"></i> Không hoạt động</span>';
                    } else if (data.lastSeen && data.lastSeen !== '') {
                        statusElement.innerHTML = '<span style="color: #95a5a6; font-style: italic;"><i class="far fa-clock"></i> ' + data.lastSeen + '</span>';
                    } else {
                        statusElement.innerHTML = '<span style="color: #95a5a6; font-style: italic;">Không hoạt động</span>';
                    }
                    updateSidebarOnlineStatus(partnerUserId, data.isOnline);
                }
            })
            .catch(error => {
                console.error('Error checking partner online status:', error);
            });
        }
        
        function updateSidebarOnlineStatus(userId, isOnline) {
            const messageItems = document.querySelectorAll('.message-item');
            messageItems.forEach(item => {
                const avatar = item.querySelector('.message-avatar');
                if (!avatar) return;
                
                // Xóa chấm online cũ
                const oldDot = avatar.querySelector('.online-dot');
                if (oldDot) oldDot.remove();
                
                // Thêm chấm online mới nếu online
                if (isOnline) {
                    const onlineDot = document.createElement('div');
                    onlineDot.className = 'online-dot';
                    onlineDot.title = 'Đang online';
                    avatar.appendChild(onlineDot);
                }
            });
        }
        
        // Cập nhật ngay
        updatePartnerOnlineStatus();
        
        // Cập nhật mỗi 30 giây
        setInterval(updatePartnerOnlineStatus, 30000);
        <?php endif; ?>
    </script>

    <!-- Script cập nhật trạng thái online của bản thân -->
    <script>
        function updateOnlineStatus() {
            fetch('../../controller/cUpdateOnlineStatus.php', {method: 'POST'})
            .then(response => response.json())
            .catch(error => console.error('Error:', error));
        }
        updateOnlineStatus();
        setInterval(updateOnlineStatus, 120000);
        let activityTimeout;
        function resetActivityTimer() {
            clearTimeout(activityTimeout);
            activityTimeout = setTimeout(updateOnlineStatus, 5000);
        }
        ['mousedown', 'keydown', 'scroll', 'touchstart'].forEach(event => {
            document.addEventListener(event, resetActivityTimer, true);
        });
    </script>

    <!-- Script check thông báo real-time -->
    <script>
        let lastNotificationCount = <?php echo ($newMatchesCount + $unreadMessagesCount); ?>;
        let lastUnreadCount = <?php echo $unreadMessagesCount; ?>;
        
        function checkNotifications() {
            fetch('../../controller/cCheckNotifications.php', {method: 'GET', cache: 'no-cache'})
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Cập nhật badge ghép đôi
                    const matchesBadge = document.getElementById('matchesBadge');
                    const matchIcon = document.querySelector('a[href="../timkiem/ghepdoinhanh.php"]');
                    
                    if (data.newMatches > 0) {
                        if (matchesBadge) {
                            matchesBadge.textContent = data.newMatches;
                        } else if (matchIcon) {
                            const badge = document.createElement('span');
                            badge.id = 'matchesBadge';
                            badge.textContent = data.newMatches;
                            badge.style.cssText = 'position: absolute; top: -8px; right: -8px; background: #ff6b9d; color: white; border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold;';
                            matchIcon.appendChild(badge);
                        }
                    } else if (matchesBadge) {
                        matchesBadge.remove();
                    }
                    
                    // Cập nhật số đếm
                    const currentTotal = data.unreadMessages + data.newMatches;
                    
                    // Kiểm tra tin nhắn mới (so sánh số tin nhắn chưa đọc)
                    if (data.unreadMessages > lastUnreadCount) {
                        // Có tin nhắn mới -> reload trang để cập nhật
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    }
                    
                    lastNotificationCount = currentTotal;
                    lastUnreadCount = data.unreadMessages;
                }
            })
            .catch(error => console.error('Error:', error));
        }
        
        // Check ngay khi trang load (sau 0.5 giây - SIÊU NHANH)
        setTimeout(checkNotifications, 500);
        
        // Check mỗi 0.5 giây (500ms) - REAL-TIME TỨC THÌ!
        setInterval(checkNotifications, 500);
        
        // Check khi user quay lại tab
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) checkNotifications();
        });
        
        window.addEventListener('focus', checkNotifications);
    </script>
    
    <style>
        @keyframes slideInRight {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(400px); opacity: 0; }
        }
        
        .message-avatar-small {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }
        
        .message.received {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .message.sent {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 15px;
        }
        
        .message-time-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 11px;
            color: #999;
        }
    </style>
</body>
</html>
