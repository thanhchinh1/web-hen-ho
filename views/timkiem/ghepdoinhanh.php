<?php
require_once '../../models/mSession.php';
require_once '../../models/mVIP.php';

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

// Kiểm tra VIP
$vipModel = new VIP();
if (!$vipModel->isVIP($userId)) {
    header('Location: ../goivip/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ghép đôi nhanh - DuyenHub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            width: 100%;
        }

        .card {
            background: white;
            border-radius: 30px;
            padding: 50px 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        .header {
            margin-bottom: 40px;
        }

        .header h1 {
            color: #2C3E50;
            font-size: 32px;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .header p {
            color: #7F8C8D;
            font-size: 16px;
        }

        .vip-badge {
            display: inline-block;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .vip-badge i {
            margin-right: 5px;
        }

        /* Animation container */
        .animation-container {
            margin: 40px 0;
            position: relative;
            height: 200px;
        }

        .heart-animation {
            font-size: 80px;
            color: #FF6B9D;
            animation: heartbeat 1.5s ease-in-out infinite;
        }

        @keyframes heartbeat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .pulse-ring {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 150px;
            height: 150px;
            border: 3px solid #FF6B9D;
            border-radius: 50%;
            animation: pulse 2s ease-out infinite;
        }

        @keyframes pulse {
            0% {
                transform: translate(-50%, -50%) scale(0.8);
                opacity: 1;
            }
            100% {
                transform: translate(-50%, -50%) scale(1.5);
                opacity: 0;
            }
        }

        .searching-dots {
            margin-top: 20px;
            font-size: 18px;
            color: #667eea;
            font-weight: 600;
        }

        .searching-dots span {
            animation: blink 1.4s infinite;
        }

        .searching-dots span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .searching-dots span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes blink {
            0%, 20%, 100% { opacity: 0; }
            50% { opacity: 1; }
        }

        .timer {
            margin-top: 15px;
            font-size: 14px;
            color: #95A5A6;
        }

        .btn {
            padding: 15px 40px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
        }

        .btn-secondary {
            background: #ECF0F1;
            color: #2C3E50;
        }

        .btn-secondary:hover {
            background: #BDC3C7;
        }

        .btn-danger {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 87, 108, 0.5);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Match found screen */
        .match-found {
            display: none;
        }

        .match-found.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        .partner-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #FF6B9D;
            margin: 20px auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .compatibility-score {
            font-size: 48px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 20px 0;
        }

        .partner-info {
            margin: 20px 0;
        }

        .partner-info h2 {
            color: #2C3E50;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .partner-info p {
            color: #7F8C8D;
            font-size: 16px;
            margin: 5px 0;
        }

        .confetti {
            position: fixed;
            width: 10px;
            height: 10px;
            background: #FF6B9D;
            position: absolute;
            animation: confetti-fall 3s linear;
        }

        @keyframes confetti-fall {
            to {
                transform: translateY(100vh) rotate(360deg);
                opacity: 0;
            }
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <!-- Idle Screen -->
            <div id="idleScreen">
                <div class="header">
                    <span class="vip-badge">
                        <i class="fas fa-crown"></i> VIP Member
                    </span>
                    <h1>Ghép đôi nhanh</h1>
                    <p>Tìm kiếm người phù hợp với bạn ngay bây giờ!</p>
                </div>

                <div class="animation-container">
                    <i class="fas fa-heart heart-animation"></i>
                </div>

                <p style="color: #7F8C8D; margin-bottom: 30px;">
                    Hệ thống sẽ tự động tìm kiếm những người đang online và có độ tương thích cao với bạn
                </p>

                <button class="btn btn-primary" onclick="startSearching()">
                    <i class="fas fa-search"></i> Bắt đầu tìm kiếm
                </button>
                <br>
                <a href="../trangchu/index.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Quay lại trang chủ
                </a>
            </div>

            <!-- Searching Screen -->
            <div id="searchingScreen" style="display: none;">
                <div class="header">
                    <h1>Đang tìm kiếm...</h1>
                    <p>Vui lòng đợi trong giây lát</p>
                </div>

                <div class="animation-container">
                    <div class="pulse-ring"></div>
                    <div class="pulse-ring" style="animation-delay: 0.5s;"></div>
                    <div class="pulse-ring" style="animation-delay: 1s;"></div>
                    <i class="fas fa-heart heart-animation"></i>
                </div>

                <div class="searching-dots">
                    Đang tìm kiếm<span>.</span><span>.</span><span>.</span>
                </div>
                <div class="timer" id="timer">Đã tìm: 0 giây</div>

                <button class="btn btn-danger" onclick="cancelSearching()">
                    <i class="fas fa-times"></i> Hủy tìm kiếm
                </button>
            </div>

            <!-- Match Found Screen -->
            <div id="matchScreen" class="match-found">
                <div class="header">
                    <h1>🎉 Tìm thấy người phù hợp!</h1>
                </div>

                <img id="partnerAvatar" src="" alt="Partner" class="partner-avatar">
                
                <div class="compatibility-score" id="compatibilityScore">
                    0%
                </div>

                <div class="partner-info">
                    <h2 id="partnerName">Đang tải...</h2>
                    <p id="partnerAge"></p>
                    <p id="partnerLocation"></p>
                    <p id="partnerGoal"></p>
                </div>

                <button class="btn btn-primary" onclick="openChat()">
                    <i class="fas fa-comment"></i> Bắt đầu trò chuyện
                </button>
                <button class="btn btn-secondary" onclick="location.reload()">
                    <i class="fas fa-search"></i> Tìm người khác
                </button>
            </div>
        </div>
    </div>

    <script>
        let searchInterval = null;
        let timerInterval = null;
        let searchStartTime = 0;
        let matchId = null;

        function startSearching() {
            // Hiển thị màn hình tìm kiếm
            document.getElementById('idleScreen').style.display = 'none';
            document.getElementById('searchingScreen').style.display = 'block';
            
            searchStartTime = Date.now();
            
            // Bắt đầu timer
            timerInterval = setInterval(() => {
                const duration = Math.floor((Date.now() - searchStartTime) / 1000);
                document.getElementById('timer').textContent = `Đã tìm: ${duration} giây`;
            }, 1000);

            // Gửi request bắt đầu tìm kiếm
            console.log('🔍 Bắt đầu tìm kiếm...');
            fetch('../../controller/cQuickMatch.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=start'
            })
            .then(response => {
                console.log('📡 Response status:', response.status);
                return response.text().then(text => {
                    console.log('📄 Response text:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('❌ JSON parse error:', e);
                        throw new Error('Invalid JSON: ' + text);
                    }
                });
            })
            .then(data => {
                console.log('✅ Data received:', data);
                if (data.status === 'matched') {
                    console.log('💕 Tìm thấy match ngay!');
                    showMatch(data);
                } else if (data.status === 'searching') {
                    console.log('⏳ Đang tìm kiếm, bắt đầu polling...');
                    startPolling();
                } else if (data.error) {
                    console.error('❌ Error từ server:', data.error, data.message);
                    alert(data.message || 'Có lỗi xảy ra!');
                    location.reload();
                }
            })
            .catch(error => {
                console.error('❌ Catch error:', error);
                alert('Có lỗi xảy ra: ' + error.message);
                location.reload();
            });
        }

        function startPolling() {
            console.log('📊 Bắt đầu polling mỗi 2 giây...');
            // Kiểm tra trạng thái mỗi 2 giây
            searchInterval = setInterval(() => {
                console.log('🔄 Polling check...');
                fetch('../../controller/cQuickMatch.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=check'
                })
                .then(response => response.json())
                .then(data => {
                    console.log('📊 Polling response:', data);
                    if (data.status === 'matched') {
                        console.log('💕 Match found!');
                        showMatch(data);
                    } else if (data.status === 'not_found') {
                        console.log('😢 Không tìm thấy');
                        // Không tìm thấy sau thời gian polling
                        clearInterval(searchInterval);
                        clearInterval(timerInterval);
                        alert('Không tìm thấy người phù hợp. Vui lòng thử lại sau!');
                        location.reload();
                    } else if (data.status === 'searching') {
                        console.log('⏳ Vẫn đang tìm... (duration: ' + data.duration + 's)');
                    }
                })
                .catch(error => {
                    console.error('❌ Polling error:', error);
                });
            }, 2000);
        }

        function cancelSearching() {
            clearInterval(searchInterval);
            clearInterval(timerInterval);
            
            fetch('../../controller/cQuickMatch.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=cancel'
            })
            .then(() => {
                location.reload();
            });
        }

        function showMatch(data) {
            clearInterval(searchInterval);
            clearInterval(timerInterval);
            
            matchId = data.matchId;
            const partner = data.partner;
            
            // LƯU VÀO LOCALSTORAGE để trigger update cho trang "Đã ghép đôi"
            localStorage.setItem('new_match', Date.now());
            // Xóa sau 1 giây để có thể trigger lại lần sau
            setTimeout(() => localStorage.removeItem('new_match'), 1000);
            
            // Hiển thị màn hình match
            document.getElementById('searchingScreen').style.display = 'none';
            document.getElementById('matchScreen').classList.add('active');
            
            // Cập nhật thông tin partner
            const avatarSrc = partner.avt ? '../../' + partner.avt : 'https://i.pravatar.cc/150';
            document.getElementById('partnerAvatar').src = avatarSrc;
            document.getElementById('compatibilityScore').textContent = Math.round(data.score) + '%';
            document.getElementById('partnerName').textContent = partner.ten;
            document.getElementById('partnerAge').innerHTML = `<i class="fas fa-birthday-cake"></i> ${partner.tuoi} tuổi`;
            document.getElementById('partnerLocation').innerHTML = `<i class="fas fa-map-marker-alt"></i> ${partner.noiSong || 'Không rõ'}`;
            document.getElementById('partnerGoal').innerHTML = `<i class="fas fa-heart"></i> ${partner.mucTieuPhatTrien || 'Không rõ'}`;
            
            // Hiệu ứng confetti
            createConfetti();
        }

        function createConfetti() {
            for (let i = 0; i < 50; i++) {
                setTimeout(() => {
                    const confetti = document.createElement('div');
                    confetti.className = 'confetti';
                    confetti.style.left = Math.random() * 100 + '%';
                    confetti.style.background = ['#FF6B9D', '#667eea', '#764ba2', '#f093fb'][Math.floor(Math.random() * 4)];
                    confetti.style.animationDelay = Math.random() * 3 + 's';
                    document.body.appendChild(confetti);
                    
                    setTimeout(() => confetti.remove(), 3000);
                }, i * 30);
            }
        }

        function openChat() {
            if (matchId) {
                window.location.href = '../nhantin/chat.php?matchId=' + matchId;
            }
        }
    </script>
</body>
</html>
