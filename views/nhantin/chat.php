<?php
require_once '../../models/mSession.php';
require_once '../../models/mMatch.php';
require_once '../../models/mMessage.php';
require_once '../../models/mProfile.php';
require_once '../../models/mUser.php';

Session::start();

// Kiểm tra đăng nhập
if (!Session::isLoggedIn()) {
    header('Location: ../dangnhap/login.php');
    exit;
}

$currentUserId = Session::getUserId();
$matchModel = new MatchModel();
$messageModel = new Message();
$profileModel = new Profile();
$userModel = new User();

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

// Nếu không có match nào được chọn, chọn match đầu tiên
if (!$selectedMatch && !empty($myMatches)) {
    $selectedMatch = $myMatches[0];
    $selectedMatchId = $selectedMatch['maGhepDoi'];
}

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
    <title>Tin nhắn - Kết Nối Yêu Thương</title>
    <link rel="stylesheet" href="../../public/css/chat.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php if ($successMessage && $matchedUserProfile): ?>
    <!-- Match Celebration Popup -->
    <div id="matchCelebration" style="
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.85);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.3s ease;
    ">
        <div style="
            background: white;
            padding: 50px;
            border-radius: 25px;
            text-align: center;
            max-width: 500px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
            animation: scaleIn 0.4s ease;
        ">
            <div style="font-size: 100px; margin-bottom: 25px; animation: heartBeat 1.5s infinite;">
                💕
            </div>
            <h1 style="
                color: #e94057; 
                margin: 0 0 15px 0; 
                font-size: 36px;
                font-weight: 700;
            ">
                Ghép Đôi Thành Công!
            </h1>
            <div style="
                width: 80px;
                height: 80px;
                border-radius: 50%;
                overflow: hidden;
                margin: 20px auto;
                border: 4px solid #e94057;
            ">
                <?php 
                $matchedAvatar = '/public/img/default-avatar.jpg';
                if (!empty($matchedUserProfile['avt'])) {
                    if (strpos($matchedUserProfile['avt'], 'public/') === 0) {
                        $matchedAvatar = '/' . htmlspecialchars($matchedUserProfile['avt']);
                    } else {
                        $matchedAvatar = '/public/uploads/avatars/' . htmlspecialchars($matchedUserProfile['avt']);
                    }
                }
                ?>
                <img src="<?php echo $matchedAvatar; ?>" 
                     alt="<?php echo htmlspecialchars($matchedUserProfile['ten']); ?>"
                     style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <p style="
                color: #333; 
                margin: 15px 0 25px 0; 
                font-size: 18px;
                line-height: 1.6;
            ">
                Bạn và <strong><?php echo htmlspecialchars($matchedUserProfile['ten']); ?></strong> đã thích nhau!<br>
                Hãy bắt đầu trò chuyện ngay! 💬
            </p>
            <button onclick="closeMatchCelebration()" style="
                padding: 15px 50px;
                background: linear-gradient(135deg, #e94057 0%, #f27121 100%);
                color: white;
                border: none;
                border-radius: 30px;
                font-size: 18px;
                font-weight: 600;
                cursor: pointer;
                box-shadow: 0 5px 25px rgba(233,64,87,0.4);
                transition: all 0.3s ease;
            " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 30px rgba(233,64,87,0.5)'"
               onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 5px 25px rgba(233,64,87,0.4)'">
                Bắt đầu trò chuyện! 🚀
            </button>
        </div>
    </div>
    
    <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes scaleIn {
            from { 
                opacity: 0;
                transform: scale(0.7);
            }
            to { 
                opacity: 1;
                transform: scale(1);
            }
        }
        
        @keyframes heartBeat {
            0%, 100% { transform: scale(1); }
            10%, 30% { transform: scale(0.85); }
            20%, 40%, 60%, 80% { transform: scale(1.15); }
            50%, 70% { transform: scale(1.05); }
        }
    </style>
    
    <script>
        function closeMatchCelebration() {
            const popup = document.getElementById('matchCelebration');
            popup.style.animation = 'fadeOut 0.3s ease';
            setTimeout(() => {
                popup.remove();
            }, 300);
        }
        
        // Auto close sau 10 giây
        setTimeout(() => {
            const popup = document.getElementById('matchCelebration');
            if (popup) {
                closeMatchCelebration();
            }
        }, 10000);
    </script>
    
    <style>
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
    </style>
    <?php endif; ?>
    
    <div class="chat-container">
        <!-- Left sidebar - Messages list -->
        <div class="chat-sidebar">
            <!-- Header -->
            <div class="sidebar-header">
                <button class="btn-back" onclick="window.location.href='../trangchu/index.php'">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <h1>Danh sách tin nhắn</h1>
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
                        <div class="message-item <?php echo $isActive; ?>" 
                             onclick="window.location.href='?match=<?php echo $match['maGhepDoi']; ?>'">
                            <div class="message-avatar">
                                <img src="<?php echo $avatarSrc; ?>" alt="<?php echo htmlspecialchars($match['ten']); ?>">
                                <?php if ($userModel->isUserOnline($match['maNguoiDung'])): ?>
                                    <div class="online-dot" title="Đang online"></div>
                                <?php endif; ?>
                            </div>
                            <div class="message-content">
                                <h3 class="message-name"><?php echo htmlspecialchars($match['ten']); ?></h3>
                                <p class="message-text">
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
                            <?php if ($lastMessage): ?>
                                <div class="message-time-badge" style="font-size: 11px; color: #999;">
                                    <?php echo formatTime($lastMessage['thoiDiemGui']); ?>
                                </div>
                            <?php endif; ?>
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
                            <?php 
                            $isOnline = $userModel->isUserOnline($selectedMatch['maNguoiDung']);
                            if ($isOnline): 
                            ?>
                                <p style="font-size: 11px; color: #28a745; margin: 4px 0 0 0; font-weight: 600;">
                                    <i class="fas fa-circle" style="font-size: 8px;"></i> Đang hoạt động
                                </p>
                            <?php else:
                                $lastActivity = $userModel->getLastActivity($selectedMatch['maNguoiDung']);
                                if ($lastActivity && $lastActivity['minutesAgo'] !== null):
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
                                <p style="font-size: 11px; color: #95a5a6; margin: 4px 0 0 0; font-style: italic;">
                                    <i class="far fa-clock"></i> Hoạt động <?php echo $lastSeenText; ?>
                                </p>
                            <?php 
                                endif;
                            endif; 
                            ?>
                        </div>
                    </div>
                    <button class="btn-close" onclick="window.location.href='../trangchu/index.php'">
                        <i class="fas fa-times"></i>
                    </button>
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
                            <div class="message <?php echo $messageClass; ?>">
                                <?php if (!$isSent): ?>
                                    <img src="<?php echo $chatAvatarSrc; ?>" alt="" class="message-avatar-small">
                                <?php endif; ?>
                                <div class="message-bubble">
                                    <p><?php echo nl2br(htmlspecialchars($msg['noiDung'])); ?></p>
                                    <span class="message-time">
                                        <?php echo date('H:i', strtotime($msg['thoiDiemGui'])); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Chat input -->
                <div class="chat-input-container">
                    <input type="text" class="chat-input" placeholder="Nhập tin nhắn..." id="messageInput">
                    <button class="btn-send" onclick="sendMessage()">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            <?php else: ?>
                <div class="no-chat-selected" style="display: flex; align-items: center; justify-content: center; height: 100%; flex-direction: column; color: #999;">
                    <i class="fas fa-comments" style="font-size: 64px; margin-bottom: 20px; opacity: 0.3;"></i>
                    <h3 style="margin: 0 0 10px 0;">Chưa có cuộc trò chuyện</h3>
                    <p>Hãy ghép đôi với ai đó để bắt đầu chat!</p>
                    <button onclick="window.location.href='../trangchu/index.php'" style="
                        margin-top: 20px;
                        padding: 12px 30px;
                        background: linear-gradient(135deg, #e94057 0%, #f27121 100%);
                        color: white;
                        border: none;
                        border-radius: 25px;
                        cursor: pointer;
                        font-weight: 600;
                    ">
                        Khám phá ngay
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const currentMatchId = <?php echo $selectedMatchId ? $selectedMatchId : 'null'; ?>;
        const currentUserId = <?php echo $currentUserId; ?>;
        let lastMessageId = <?php echo !empty($messages) ? end($messages)['maTinNhan'] : 0; ?>;
        let pollingInterval = null;

        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();

            if (message === '' || !currentMatchId) return;

            // Disable input
            input.disabled = true;

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
            
            const time = new Date(message.thoiDiemGui).toLocaleTimeString('vi-VN', {
                hour: '2-digit',
                minute: '2-digit'
            });
            
            let html = '';
            if (!isSent) {
                html += `<img src="<?php echo $chatAvatarSrc ?? ''; ?>" alt="" class="message-avatar-small">`;
            }
            html += `
                <div class="message-bubble">
                    <p>${escapeHtml(message.noiDung)}</p>
                    <span class="message-time">${time}</span>
                </div>
            `;
            
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

                    // Scroll to bottom
                    scrollToBottom();
                }
            })
            .catch(error => {
                console.error('Polling error:', error);
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
            }

            // Scroll to bottom on load
            scrollToBottom();

            // Start polling nếu có match được chọn
            if (currentMatchId) {
                pollingInterval = setInterval(checkNewMessages, 2000); // Poll mỗi 2 giây
            }
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
                    // Tìm header status element
                    const headerDiv = document.querySelector('.chat-header .chat-user-info > div');
                    if (!headerDiv) return;
                    
                    // Xóa status cũ
                    const oldStatus = headerDiv.querySelector('.online-status-text');
                    if (oldStatus) oldStatus.remove();
                    
                    // Thêm status mới
                    const statusP = document.createElement('p');
                    statusP.className = 'online-status-text';
                    statusP.style.fontSize = '11px';
                    statusP.style.margin = '4px 0 0 0';
                    
                    if (data.isOnline) {
                        statusP.style.color = '#28a745';
                        statusP.style.fontWeight = '600';
                        statusP.innerHTML = '<i class="fas fa-circle" style="font-size: 8px;"></i> Đang hoạt động';
                    } else if (data.lastSeen) {
                        statusP.style.color = '#95a5a6';
                        statusP.style.fontStyle = 'italic';
                        statusP.innerHTML = '<i class="far fa-clock"></i> Hoạt động ' + data.lastSeen;
                    }
                    
                    headerDiv.appendChild(statusP);
                    
                    // Cập nhật chấm online trong sidebar
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
    
    <style>
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
