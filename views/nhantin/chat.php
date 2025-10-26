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
                <!-- Message item 1 - Active -->
                <div class="message-item active" onclick="selectChat(1, 'Lý Hồng Ngọc', 'https://i.pravatar.cc/100?img=45')">
                    <div class="message-avatar">
                        <img src="https://i.pravatar.cc/100?img=45" alt="Lý Hồng Ngọc">
                    </div>
                    <div class="message-content">
                        <h3 class="message-name">Lý Hồng Ngọc</h3>
                        <p class="message-text">Bạn vừa kết nối với Lý Hồng Ngọc, hãy gửi lời chào</p>
                    </div>
                </div>

                <!-- Message item 2 -->
                <div class="message-item" onclick="selectChat(2, 'Quang Minh', 'https://i.pravatar.cc/100?img=33')">
                    <div class="message-avatar">
                        <img src="https://i.pravatar.cc/100?img=33" alt="Quang Minh">
                        <span class="online-dot"></span>
                    </div>
                    <div class="message-content">
                        <h3 class="message-name">Quang Minh</h3>
                        <p class="message-text">Mình rất thích đi cafe và đi xem phim, còn bạn</p>
                    </div>
                </div>

                <!-- Message item 3 -->
                <div class="message-item" onclick="selectChat(3, 'Ngọc Mai', 'https://i.pravatar.cc/100?img=28')">
                    <div class="message-avatar">
                        <img src="https://i.pravatar.cc/100?img=28" alt="Ngọc Mai">
                    </div>
                    <div class="message-content">
                        <h3 class="message-name">Ngọc Mai</h3>
                        <p class="message-text">Chủ nhật này mình rảnh, bạn muốn đi đâu?</p>
                    </div>
                </div>

                <!-- Message item 4 -->
                <div class="message-item" onclick="selectChat(4, 'Tuấn Anh', 'https://i.pravatar.cc/100?img=52')">
                    <div class="message-avatar">
                        <img src="https://i.pravatar.cc/100?img=52" alt="Tuấn Anh">
                    </div>
                    <div class="message-content">
                        <h3 class="message-name">Tuấn Anh</h3>
                        <p class="message-text">Hôm qua mình gặp bạn ở quán cà phê đó.</p>
                    </div>
                </div>

                <!-- Message item 5 -->
                <div class="message-item" onclick="selectChat(5, 'Phạm Hương', 'https://i.pravatar.cc/100?img=23')">
                    <div class="message-avatar">
                        <img src="https://i.pravatar.cc/100?img=23" alt="Phạm Hương">
                    </div>
                    <div class="message-content">
                        <h3 class="message-name">Phạm Hương</h3>
                        <p class="message-text">Công việc của bạn dao nấy thế nào?</p>
                    </div>
                </div>

                <!-- Message item 6 -->
                <div class="message-item" onclick="selectChat(6, 'Đức Thịnh', 'https://i.pravatar.cc/100?img=68')">
                    <div class="message-avatar">
                        <img src="https://i.pravatar.cc/100?img=68" alt="Đức Thịnh">
                    </div>
                    <div class="message-content">
                        <h3 class="message-name">Đức Thịnh</h3>
                        <p class="message-text">Có vẻ bạn rất thích du lịch.</p>
                    </div>
                </div>

                <!-- Message item 7 -->
                <div class="message-item" onclick="selectChat(7, 'Hồng Nhung', 'https://i.pravatar.cc/100?img=41')">
                    <div class="message-avatar">
                        <img src="https://i.pravatar.cc/100?img=41" alt="Hồng Nhung">
                    </div>
                    <div class="message-content">
                        <h3 class="message-name">Hồng Nhung</h3>
                        <p class="message-text">Bạn có kế hoạch gì cho cuối tuần chưa?</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right side - Chat box -->
        <div class="chat-box">
            <!-- Chat header -->
            <div class="chat-header">
                <div class="chat-user-info">
                    <img src="https://i.pravatar.cc/100?img=45" alt="Lý Hồng Ngọc" id="chatAvatar">
                    <h2 id="chatUserName">Lý Hồng Ngọc</h2>
                </div>
                <button class="btn-close" onclick="closeChat()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Chat messages area -->
            <div class="chat-messages" id="chatMessages">
                <!-- Welcome message -->
                <div class="chat-welcome">
                    <p>Bạn vừa kết nối với <span id="welcomeName">Lý Hồng Ngọc</span>, hãy gửi lời chào!</p>
                </div>
            </div>

            <!-- Chat input -->
            <div class="chat-input-container">
                <input type="text" class="chat-input" placeholder="Nhập tin nhắn..." id="messageInput">
                <button class="btn-send" onclick="sendMessage()">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentChatUser = {
            id: 1,
            name: 'Lý Hồng Ngọc',
            avatar: 'https://i.pravatar.cc/100?img=45'
        };

        function selectChat(userId, userName, avatar) {
            // Update current chat user
            currentChatUser = { id: userId, name: userName, avatar: avatar };

            // Remove active class from all items
            document.querySelectorAll('.message-item').forEach(item => {
                item.classList.remove('active');
            });

            // Add active class to clicked item
            event.currentTarget.classList.add('active');

            // Update chat header
            document.getElementById('chatAvatar').src = avatar;
            document.getElementById('chatUserName').textContent = userName;
            document.getElementById('welcomeName').textContent = userName;

            // Clear messages
            document.getElementById('chatMessages').innerHTML = `
                <div class="chat-welcome">
                    <p>Bạn vừa kết nối với <span>${userName}</span>, hãy gửi lời chào!</p>
                </div>
            `;
        }

        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();

            if (message === '') return;

            const messagesContainer = document.getElementById('chatMessages');
            
            // Remove welcome message if exists
            const welcome = messagesContainer.querySelector('.chat-welcome');
            if (welcome) welcome.remove();

            // Add user message
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message sent';
            messageDiv.innerHTML = `
                <div class="message-bubble">
                    <p>${message}</p>
                    <span class="message-time">${new Date().toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit'})}</span>
                </div>
            `;
            messagesContainer.appendChild(messageDiv);

            // Clear input
            input.value = '';

            // Scroll to bottom
            messagesContainer.scrollTop = messagesContainer.scrollHeight;

            // Simulate auto reply after 1 second
            setTimeout(() => {
                const replyDiv = document.createElement('div');
                replyDiv.className = 'message received';
                replyDiv.innerHTML = `
                    <div class="message-bubble">
                        <p>Cảm ơn bạn đã nhắn tin! 😊</p>
                        <span class="message-time">${new Date().toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit'})}</span>
                    </div>
                `;
                messagesContainer.appendChild(replyDiv);
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }, 1000);
        }

        function closeChat() {
            window.location.href = 'index.php';
        }

        // Send message on Enter key
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('messageInput').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    sendMessage();
                }
            });
        });
    </script>
</body>
</html>
