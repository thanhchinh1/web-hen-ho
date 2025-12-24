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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated background particles */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 15s infinite;
        }

        @keyframes float {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translate(100px, -1000px) rotate(360deg);
                opacity: 0;
            }
        }

        .container {
            max-width: 450px;
            width: 100%;
            position: relative;
            z-index: 1;
        }

        /* Header Navigation */
        .top-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
            padding: 0 10px;
        }

        .back-btn {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateX(-5px);
        }

        .vip-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #333;
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 5px 20px rgba(255, 215, 0, 0.4);
        }

        .vip-badge i {
            font-size: 16px;
        }

        /* Main Card */
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            padding: 0;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            position: relative;
        }

        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .card-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 10s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .card-header h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .card-header p {
            font-size: 16px;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .card-body {
            padding: 40px 30px;
        }

        /* Animation container */
        .animation-container {
            margin: 30px 0;
            position: relative;
            height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Floating hearts animation */
        .floating-hearts {
            position: absolute;
            width: 100%;
            height: 100%;
        }

        .floating-hearts i {
            position: absolute;
            bottom: -20px;
            font-size: 20px;
            color: #FF6B9D;
            opacity: 0;
            animation: float-up 4s ease-in infinite;
            animation-delay: calc(var(--i) * 0.5s);
        }

        .floating-hearts i:nth-child(1) { left: 10%; }
        .floating-hearts i:nth-child(2) { left: 30%; }
        .floating-hearts i:nth-child(3) { left: 50%; }
        .floating-hearts i:nth-child(4) { left: 70%; }
        .floating-hearts i:nth-child(5) { left: 90%; }

        @keyframes float-up {
            0% {
                opacity: 0;
                transform: translateY(0) scale(0.5);
            }
            20% {
                opacity: 1;
            }
            100% {
                opacity: 0;
                transform: translateY(-250px) scale(1.2);
            }
        }

        .heart-icon {
            font-size: 100px;
            background: linear-gradient(135deg, #FF6B9D 0%, #FF8E53 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: heartbeat 1.5s ease-in-out infinite;
            filter: drop-shadow(0 10px 30px rgba(255, 107, 157, 0.4));
            position: relative;
            z-index: 2;
        }

        @keyframes heartbeat {
            0%, 100% { transform: scale(1); }
            25% { transform: scale(1.1); }
            50% { transform: scale(1); }
            75% { transform: scale(1.05); }
        }

        .pulse-ring {
            position: absolute;
            width: 160px;
            height: 160px;
            border: 3px solid #FF6B9D;
            border-radius: 50%;
            animation: pulse 2s ease-out infinite;
        }

        .pulse-ring:nth-child(2) {
            animation-delay: 0.5s;
        }

        .pulse-ring:nth-child(3) {
            animation-delay: 1s;
        }

        @keyframes pulse {
            0% {
                transform: scale(0.8);
                opacity: 1;
            }
            100% {
                transform: scale(1.8);
                opacity: 0;
            }
        }

        /* Description */
        .description {
            text-align: center;
            color: #666;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 30px;
            padding: 0 10px;
        }

        .feature-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 30px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .feature-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }

        .feature-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            flex-shrink: 0;
        }

        .feature-text {
            flex: 1;
            font-size: 14px;
            color: #333;
            font-weight: 500;
        }

        /* Buttons */
        .btn {
            padding: 16px 40px;
            border: none;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 8px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .btn i {
            font-size: 18px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 5px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.5);
        }

        .btn-primary:active {
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e9ecef;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            box-shadow: 0 5px 25px rgba(245, 87, 108, 0.4);
        }

        .btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(245, 87, 108, 0.5);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        .btn-container {
            text-align: center;
            margin-top: 20px;
        }

        /* Searching Screen */
        .searching-dots {
            margin-top: 20px;
            font-size: 18px;
            color: #667eea;
            font-weight: 600;
            text-align: center;
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
            text-align: center;
        }

        .progress-bar {
            width: 100%;
            height: 6px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin: 20px 0;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            animation: progress 30s linear;
        }

        @keyframes progress {
            from { width: 0%; }
            to { width: 100%; }
        }

        /* Match Found Screen */
        .match-found {
            display: none;
        }

        .match-found.active {
            display: block;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from { 
                opacity: 0; 
                transform: translateY(30px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }

        .match-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .match-header h2 {
            font-size: 28px;
            color: #333;
            margin-bottom: 12px;
            font-weight: 700;
        }

        .match-emoji {
            font-size: 70px;
            margin-bottom: 20px;
            animation: bounce 1s ease infinite;
            display: block;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .partner-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 25px;
            padding: 35px 25px;
            margin: 25px 0;
            text-align: center;
            position: relative;
        }

        .partner-avatar {
            width: 160px;
            height: 160px;
            border-radius: 50%;
            object-fit: cover;
            border: 6px solid white;
            margin: 0 auto 25px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
            display: block;
        }

        .compatibility-badge {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .partner-info h3 {
            color: #333;
            font-size: 28px;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .info-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: center;
            margin-top: 20px;
        }

        .info-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: white;
            padding: 10px 18px;
            border-radius: 25px;
            font-size: 14px;
            color: #666;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
            font-weight: 500;
        }

        .info-tag i {
            color: #667eea;
            font-size: 15px;
        }

        /* Confetti */
        .confetti {
            position: fixed;
            width: 10px;
            height: 10px;
            background: #FF6B9D;
            z-index: 9999;
        }

        @keyframes confetti-fall {
            to {
                transform: translateY(100vh) rotate(360deg);
                opacity: 0;
            }
        }

        /* Responsive */
        @media (max-width: 576px) {
            .container {
                padding: 0 10px;
            }

            .card-header {
                padding: 30px 20px;
            }

            .card-header h1 {
                font-size: 26px;
            }

            .card-body {
                padding: 30px 20px;
            }

            .heart-icon {
                font-size: 80px;
            }

            .btn {
                padding: 14px 30px;
                font-size: 15px;
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Background Animation -->
    <div class="bg-animation" id="bgAnimation"></div>

    <div class="container">
        <!-- Top Navigation -->
        <div class="top-nav">
            <div class="back-btn" onclick="window.location.href='../trangchu/index.php'">
                <i class="fas fa-arrow-left"></i>
            </div>
            <div class="vip-badge">
                <i class="fas fa-crown"></i>
                <span>VIP Member</span>
            </div>
        </div>

        <div class="card">
            <!-- Idle Screen -->
            <div id="idleScreen">
                <div class="card-header">
                    <h1>⚡ Ghép Đôi Nhanh</h1>
                    <p>Tìm kiếm người phù hợp trong tích tắc!</p>
                </div>

                <div class="card-body">
                    <div class="animation-container">
                        <div class="floating-hearts">
                            <i class="fas fa-heart" style="--i: 1"></i>
                            <i class="fas fa-heart" style="--i: 2"></i>
                            <i class="fas fa-heart" style="--i: 3"></i>
                            <i class="fas fa-heart" style="--i: 4"></i>
                            <i class="fas fa-heart" style="--i: 5"></i>
                        </div>
                        <i class="fas fa-heart heart-icon"></i>
                    </div>

                    <div class="btn-container">
                        <button class="btn btn-primary" onclick="startSearching()">
                            <i class="fas fa-rocket"></i>
                            <span>Bắt đầu tìm kiếm</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Searching Screen -->
            <div id="searchingScreen" style="display: none;">
                <div class="card-header">
                    <h1>🔍 Đang Tìm Kiếm</h1>
                    <p>AI đang phân tích để tìm người phù hợp nhất...</p>
                </div>

                <div class="card-body">
                    <div class="animation-container">
                        <div class="pulse-ring"></div>
                        <div class="pulse-ring"></div>
                        <div class="pulse-ring"></div>
                        <i class="fas fa-heart heart-icon"></i>
                    </div>

                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>

                    <div class="searching-dots">
                        Đang phân tích<span>.</span><span>.</span><span>.</span>
                    </div>
                    <div class="timer" id="timer">Đã tìm: 0 giây</div>

                    <div class="btn-container">
                        <button class="btn btn-danger" onclick="cancelSearching()">
                            <i class="fas fa-times"></i>
                            <span>Hủy tìm kiếm</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Match Found Screen -->
            <div id="matchScreen" class="match-found">
                <div class="card-header">
                    <h1>💕 Tìm Thấy!</h1>
                    <p>Chúc mừng! Bạn đã được ghép đôi thành công</p>
                </div>

                <div class="card-body">
                    <div class="match-header">
                        <div class="match-emoji">🎉</div>
                    </div>

                    <div class="partner-card">
                        <img id="partnerAvatar" src="" alt="Partner" class="partner-avatar">
                        
                        <div class="compatibility-badge" id="compatibilityScore">
                            0% Phù hợp
                        </div>

                        <div class="partner-info">
                            <h3 id="partnerName">Đang tải...</h3>
                            
                            <div class="info-tags">
                                <div class="info-tag">
                                    <i class="fas fa-birthday-cake"></i>
                                    <span id="partnerAge">-- tuổi</span>
                                </div>
                                <div class="info-tag">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span id="partnerLocation">Không rõ</span>
                                </div>
                                <div class="info-tag">
                                    <i class="fas fa-heart"></i>
                                    <span id="partnerGoal">Không rõ</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="btn-container">
                        <button class="btn btn-primary" onclick="openChat()">
                            <i class="fas fa-comment"></i>
                            <span>Bắt đầu trò chuyện</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let searchInterval = null;
        let timerInterval = null;
        let searchStartTime = 0;
        let matchId = null;

        // Create animated background particles
        function createBackgroundParticles() {
            const bgAnimation = document.getElementById('bgAnimation');
            for (let i = 0; i < 15; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                const size = Math.random() * 60 + 20;
                particle.style.width = size + 'px';
                particle.style.height = size + 'px';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.bottom = '-100px';
                particle.style.animationDelay = Math.random() * 15 + 's';
                particle.style.animationDuration = (Math.random() * 10 + 10) + 's';
                bgAnimation.appendChild(particle);
            }
        }

        // Initialize on load
        document.addEventListener('DOMContentLoaded', createBackgroundParticles);

        function startSearching() {
            // Hiển thị màn hình tìm kiếm
            document.getElementById('idleScreen').style.display = 'none';
            document.getElementById('searchingScreen').style.display = 'block';
            
            searchStartTime = Date.now();
            
            // Bắt đầu timer
            timerInterval = setInterval(() => {
                const duration = Math.floor((Date.now() - searchStartTime) / 1000);
                document.getElementById('timer').textContent = `Đã tìm: ${duration} giây`;
                
                // Kiểm tra nếu đã quá 60 giây (1 phút)
                if (duration >= 60) {
                    console.log('⏱️ Timeout: Đã tìm kiếm quá 60 giây');
                    stopSearchingWithTimeout();
                }
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

        function stopSearchingWithTimeout() {
            clearInterval(searchInterval);
            clearInterval(timerInterval);
            
            // Hủy tìm kiếm trên server
            fetch('../../controller/cQuickMatch.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=cancel'
            })
            .then(() => {
                // Hiển thị thông báo đẹp
                const notification = document.createElement('div');
                notification.innerHTML = `
                    <div style="
                        position: fixed;
                        top: 50%;
                        left: 50%;
                        transform: translate(-50%, -50%);
                        background: white;
                        padding: 40px 50px;
                        border-radius: 20px;
                        box-shadow: 0 15px 50px rgba(0,0,0,0.3);
                        z-index: 10001;
                        text-align: center;
                        max-width: 450px;
                    ">
                        <div style="font-size: 70px; margin-bottom: 20px;">
                            😔
                        </div>
                        <h2 style="margin: 0 0 15px 0; color: #FF6B9D; font-size: 26px; font-weight: 700;">
                            Không tìm thấy người phù hợp
                        </h2>
                        <p style="margin: 0 0 25px 0; color: #7F8C8D; font-size: 16px; line-height: 1.6;">
                            Hiện tại không có người dùng phù hợp đang online.<br>
                            Vui lòng thử lại sau nhé!
                        </p>
                        <button onclick="location.reload()" style="
                            padding: 14px 40px;
                            border: none;
                            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                            color: white;
                            border-radius: 50px;
                            font-size: 16px;
                            font-weight: 600;
                            cursor: pointer;
                            transition: all 0.3s ease;
                            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
                        " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(102, 126, 234, 0.4)'" onmouseout="this.style.transform=''; this.style.boxShadow='0 4px 15px rgba(102, 126, 234, 0.3)'">
                            <i class="fas fa-redo"></i> Thử lại
                        </button>
                    </div>
                    <div style="
                        position: fixed;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        background: rgba(0,0,0,0.6);
                        z-index: 10000;
                    "></div>
                `;
                document.body.appendChild(notification);
            })
            .catch(error => {
                console.error('❌ Error canceling:', error);
                alert('Không tìm thấy người phù hợp. Vui lòng thử lại sau!');
                location.reload();
            });
        }

        function startPolling() {
            console.log('📊 Bắt đầu polling mỗi 5 giây...');
            // Kiểm tra trạng thái mỗi 5 giây
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
            }, 5000);
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
            
            // Hiển thị màn hình match
            document.getElementById('searchingScreen').style.display = 'none';
            document.getElementById('matchScreen').classList.add('active');
            
            // Cập nhật thông tin partner
            const avatarSrc = partner.avt ? 
                (partner.avt.startsWith('public/') ? '/' + partner.avt : '../../public/uploads/avatars/' + partner.avt) : 
                '../../public/img/default-avatar.jpg';
            
            document.getElementById('partnerAvatar').src = avatarSrc;
            document.getElementById('compatibilityScore').textContent = Math.round(data.score) + '% Phù hợp';
            document.getElementById('partnerName').textContent = partner.ten;
            document.getElementById('partnerAge').textContent = partner.tuoi + ' tuổi';
            document.getElementById('partnerLocation').textContent = partner.noiSong || 'Không rõ';
            document.getElementById('partnerGoal').textContent = partner.mucTieuPhatTrien || 'Không rõ';
            
            // Hiệu ứng confetti
            createConfetti();
        }

        function createConfetti() {
            const colors = ['#FF6B9D', '#667eea', '#764ba2', '#f093fb', '#FFD700'];
            for (let i = 0; i < 100; i++) {
                setTimeout(() => {
                    const confetti = document.createElement('div');
                    confetti.className = 'confetti';
                    confetti.style.left = Math.random() * 100 + '%';
                    confetti.style.top = '-10px';
                    confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
                    confetti.style.animation = `confetti-fall ${Math.random() * 3 + 2}s linear forwards`;
                    confetti.style.animationDelay = Math.random() * 0.5 + 's';
                    document.body.appendChild(confetti);
                    
                    setTimeout(() => confetti.remove(), 5000);
                }, i * 30);
            }
        }

        function openChat() {
            if (matchId) {
                window.location.href = '../nhantin/message.php?matchId=' + matchId;
            }
        }
    </script>
</body>
</html>
