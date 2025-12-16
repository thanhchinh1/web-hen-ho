<?php
require_once '../../models/mSession.php';
require_once '../../models/mEmailVerification.php';
require_once '../../models/mEmailService.php';

Session::start();

// Kiểm tra có email trong session không
$email = Session::get('verify_email');
if (empty($email)) {
    header('Location: register.php');
    exit;
}

// Lấy thông báo
$successMessage = Session::getFlash('otp_sent');
$errors = Session::get('verify_errors', []);
Session::delete('verify_errors');

// Lấy action và targetUser từ URL nếu có
$action = $_GET['action'] ?? '';
$targetUser = $_GET['targetUser'] ?? '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực email - DuyenHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/register.css?v=<?php echo time(); ?>">
    <style>
        .otp-input-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin: 30px 0;
        }
        .otp-input {
            width: 50px;
            height: 60px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            border: 2px solid #ddd;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .otp-input:focus {
            border-color: #FF6B6B;
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.1);
        }
        .timer {
            text-align: center;
            margin: 20px 0;
            font-size: 14px;
            color: #666;
        }
        .timer.warning {
            color: #dc3545;
            font-weight: bold;
        }
        .resend-btn {
            background: none;
            border: none;
            color: #FF6B6B;
            text-decoration: underline;
            cursor: pointer;
            font-size: 14px;
            margin-top: 15px;
        }
        .resend-btn:hover {
            color: #EE5A6F;
        }
        .resend-btn:disabled {
            color: #999;
            cursor: not-allowed;
            text-decoration: none;
        }
        .email-display {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }
        .email-display strong {
            color: #FF6B6B;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="header-container">
            <a href="../../index.php" class="logo">
                <img src="../../public/img/logo.jpg" alt="DuyenHub Logo">
                <span class="logo-text">DuyenHub</span>
            </a>
        </div>
    </header>

    <div class="register-wrapper">
        <div class="register-container">
            <button class="back-btn" onclick="window.location.href='register.php'" title="Quay lại">
                <i class="fas fa-arrow-left"></i>
            </button>
            
            <div class="register-header">
                <div style="font-size: 48px; margin-bottom: 10px;">📧</div>
                <h1>Xác thực email</h1>
                <p>Nhập mã OTP đã được gửi đến email của bạn</p>
            
            <?php if (!empty($successMessage)): ?>
                <div class="success-container" style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 8px; margin: 10px 0; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-check-circle" style="font-size: 20px;"></i>
                    <span><?php echo htmlspecialchars($successMessage); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="error-container" style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 8px; margin: 10px 0;">
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <div class="email-display">
            <div style="color: #666; font-size: 14px; margin-bottom: 5px;">Email xác thực:</div>
            <strong><?php echo htmlspecialchars($email); ?></strong>
        </div>

        <?php
        // Build form action URL with params
        $formAction = '../../controller/cVerifyEmail.php';
        $params = [];
        if ($action === 'like' && !empty($targetUser)) {
            $params[] = 'action=' . urlencode($action);
            $params[] = 'targetUser=' . urlencode($targetUser);
        }
        if (!empty($params)) {
            $formAction .= '?' . implode('&', $params);
        }
        ?>

        <form action="<?php echo $formAction; ?>" method="POST" id="verifyForm">
            <div class="form-group">
                <label>Mã OTP (6 số)</label>
                <div class="otp-input-group">
                    <input type="text" class="otp-input" maxlength="1" name="otp1" id="otp1" required autofocus>
                    <input type="text" class="otp-input" maxlength="1" name="otp2" id="otp2" required>
                    <input type="text" class="otp-input" maxlength="1" name="otp3" id="otp3" required>
                    <input type="text" class="otp-input" maxlength="1" name="otp4" id="otp4" required>
                    <input type="text" class="otp-input" maxlength="1" name="otp5" id="otp5" required>
                    <input type="text" class="otp-input" maxlength="1" name="otp6" id="otp6" required>
                </div>
                <input type="hidden" name="otp_code" id="otp_code">
            </div>

            <div class="timer" id="timer">
                ⏱️ Mã có hiệu lực trong: <span id="countdown">10:00</span>
            </div>

            <button type="submit" class="btn-verify" style="
                width: 100%; 
                font-size: 18px; 
                padding: 16px 24px; 
                background: linear-gradient(135deg, #FF6B6B 0%, #EE5A6F 100%);
                color: white;
                border: none;
                border-radius: 12px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
            " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(255, 107, 107, 0.6)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(255, 107, 107, 0.4)';">
                <i class="fas fa-check-circle" style="font-size: 20px;"></i> 
                <span>Xác thực OTP</span>
            </button>

            <div style="text-align: center; margin-top: 20px;">
                <p style="color: #666; font-size: 14px;">Không nhận được mã?</p>
                <button type="button" class="resend-btn" id="resendBtn" onclick="resendOTP()" style="
                    background: none;
                    border: none;
                    color: #FF6B6B;
                    text-decoration: none;
                    cursor: pointer;
                    font-size: 15px;
                    font-weight: 500;
                    padding: 8px 16px;
                    border-radius: 6px;
                    transition: all 0.3s ease;
                " onmouseover="this.style.backgroundColor='#ffe5e5';" onmouseout="this.style.backgroundColor='transparent';">
                    <i class="fas fa-redo"></i> Gửi lại mã OTP
                </button>
            </div>
        </form>

        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
            <p style="color: #666; font-size: 14px;">
                Đã có tài khoản? 
                <a href="../dangnhap/login.php" style="color: #FF6B6B; text-decoration: none; font-weight: 500;">
                    Đăng nhập ngay
                </a>
            </p>
        </div>
    </div>
    </div>

    <script>
        // Auto focus và tự động chuyển ô input
        const otpInputs = document.querySelectorAll('.otp-input');
        
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                const value = e.target.value;
                
                // Chỉ cho phép số
                if (!/^\d*$/.test(value)) {
                    e.target.value = '';
                    return;
                }
                
                // Auto focus ô tiếp theo
                if (value && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            });
            
            // Xử lý phím Backspace
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });
            
            // Xử lý paste
            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text');
                const digits = pastedData.replace(/\D/g, '').slice(0, 6);
                
                digits.split('').forEach((digit, i) => {
                    if (otpInputs[i]) {
                        otpInputs[i].value = digit;
                    }
                });
                
                if (digits.length > 0) {
                    otpInputs[Math.min(digits.length, 5)].focus();
                }
            });
        });
        
        // Submit form - ghép các số OTP lại
        document.getElementById('verifyForm').addEventListener('submit', (e) => {
            let otpCode = '';
            otpInputs.forEach(input => {
                otpCode += input.value;
            });
            
            if (otpCode.length !== 6) {
                e.preventDefault();
                alert('Vui lòng nhập đủ 6 số!');
                return;
            }
            
            document.getElementById('otp_code').value = otpCode;
        });
        
        // Countdown timer (10 phút = 600 giây)
        let timeLeft = 600;
        const countdownEl = document.getElementById('countdown');
        const timerEl = document.getElementById('timer');
        
        const countdown = setInterval(() => {
            timeLeft--;
            
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            countdownEl.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            // Cảnh báo khi còn 2 phút
            if (timeLeft <= 120) {
                timerEl.classList.add('warning');
            }
            
            // Hết thời gian
            if (timeLeft <= 0) {
                clearInterval(countdown);
                countdownEl.textContent = 'Đã hết hạn';
                alert('Mã OTP đã hết hạn! Vui lòng gửi lại mã mới.');
            }
        }, 1000);
        
        // Resend OTP
        let resendCooldown = 0;
        const resendBtn = document.getElementById('resendBtn');
        
        function resendOTP() {
            if (resendCooldown > 0) {
                alert('Vui lòng đợi ' + resendCooldown + ' giây trước khi gửi lại!');
                return;
            }
            
            if (!confirm('Bạn có chắc muốn gửi lại mã OTP?')) {
                return;
            }
            
            resendBtn.disabled = true;
            resendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang gửi...';
            
            // Gọi API resend
            fetch('../../controller/cResendOTP.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Mã OTP mới đã được gửi đến email của bạn!');
                    
                    // Reset countdown
                    clearInterval(countdown);
                    timeLeft = 600;
                    timerEl.classList.remove('warning');
                    
                    // Cooldown 60 giây
                    resendCooldown = 60;
                    const cooldownInterval = setInterval(() => {
                        resendCooldown--;
                        resendBtn.innerHTML = `<i class="fas fa-clock"></i> Gửi lại sau ${resendCooldown}s`;
                        
                        if (resendCooldown <= 0) {
                            clearInterval(cooldownInterval);
                            resendBtn.disabled = false;
                            resendBtn.innerHTML = '<i class="fas fa-redo"></i> Gửi lại mã OTP';
                        }
                    }, 1000);
                } else {
                    alert(data.message || 'Không thể gửi lại mã OTP. Vui lòng thử lại!');
                    resendBtn.disabled = false;
                    resendBtn.innerHTML = '<i class="fas fa-redo"></i> Gửi lại mã OTP';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra. Vui lòng thử lại!');
                resendBtn.disabled = false;
                resendBtn.innerHTML = '<i class="fas fa-redo"></i> Gửi lại mã OTP';
            });
        }
    </script>
</body>
</html>
