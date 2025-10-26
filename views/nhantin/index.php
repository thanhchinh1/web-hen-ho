<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách tin nhắn - Kết Nối Yêu Thương</title>
    <link rel="stylesheet" href="../../public/css/messages.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="messages-container">
        <!-- Header with back button -->
        <div class="messages-header">
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
            <!-- Message item 1 -->
            <div class="message-item" onclick="window.location.href='chat.php?user=1'">
                <div class="message-avatar">
                    <img src="https://i.pravatar.cc/100?img=45" alt="Lý Hồng Ngọc">
                </div>
                <div class="message-content">
                    <div class="message-header">
                        <h3 class="message-name">Lý Hồng Ngọc</h3>
                    </div>
                    <p class="message-text">Bạn vừa kết nối với Lý Hồng Ngọc, hãy gửi lời chào</p>
                </div>
            </div>

            <!-- Message item 2 -->
            <div class="message-item" onclick="window.location.href='chat.php?user=2'">
                <div class="message-avatar">
                    <img src="https://i.pravatar.cc/100?img=33" alt="Quang Minh">
                    <span class="online-dot"></span>
                </div>
                <div class="message-content">
                    <div class="message-header">
                        <h3 class="message-name">Quang Minh</h3>
                    </div>
                    <p class="message-text">Mình rất thích bức ảnh đại diện của bạn!</p>
                </div>
            </div>

            <!-- Message item 3 -->
            <div class="message-item" onclick="window.location.href='chat.php?user=3'">
                <div class="message-avatar">
                    <img src="https://i.pravatar.cc/100?img=28" alt="Ngọc Mai">
                </div>
                <div class="message-content">
                    <div class="message-header">
                        <h3 class="message-name">Ngọc Mai</h3>
                    </div>
                    <p class="message-text">Chủ nhật này mình rảnh, bạn muốn đi đâu?</p>
                </div>
            </div>

            <!-- Message item 4 -->
            <div class="message-item" onclick="window.location.href='chat.php?user=4'">
                <div class="message-avatar">
                    <img src="https://i.pravatar.cc/100?img=52" alt="Tuấn Anh">
                </div>
                <div class="message-content">
                    <div class="message-header">
                        <h3 class="message-name">Tuấn Anh</h3>
                    </div>
                    <p class="message-text">Hôm qua mình gặp bạn ở quán cà phê đó.</p>
                </div>
            </div>

            <!-- Message item 5 -->
            <div class="message-item" onclick="window.location.href='chat.php?user=5'">
                <div class="message-avatar">
                    <img src="https://i.pravatar.cc/100?img=23" alt="Phạm Hương">
                </div>
                <div class="message-content">
                    <div class="message-header">
                        <h3 class="message-name">Phạm Hương</h3>
                    </div>
                    <p class="message-text">Công việc của bạn dao nấy thế nào?</p>
                </div>
            </div>

            <!-- Message item 6 -->
            <div class="message-item" onclick="window.location.href='chat.php?user=6'">
                <div class="message-avatar">
                    <img src="https://i.pravatar.cc/100?img=68" alt="Đức Thịnh">
                </div>
                <div class="message-content">
                    <div class="message-header">
                        <h3 class="message-name">Đức Thịnh</h3>
                    </div>
                    <p class="message-text">Có vẻ bạn rất thích du lịch.</p>
                </div>
            </div>

            <!-- Message item 7 -->
            <div class="message-item" onclick="window.location.href='chat.php?user=7'">
                <div class="message-avatar">
                    <img src="https://i.pravatar.cc/100?img=41" alt="Hồng Nhung">
                </div>
                <div class="message-content">
                    <div class="message-header">
                        <h3 class="message-name">Hồng Nhung</h3>
                    </div>
                    <p class="message-text">Bạn có kế hoạch gì cho cuối tuần chưa?</p>
                </div>
            </div>
        </div>

        <!-- Empty state message -->
        <div class="empty-state">
            <i class="far fa-comment-dots"></i>
            <p>Chào mừng đến với DallingTime Messenger!</p>
        </div>
    </div>
</body>
</html>
