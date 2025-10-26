<?php
require_once '../../models/session.php';
requireLogin(); // Yêu cầu đăng nhập để truy cập trang này

$currentUserEmail = getCurrentUserEmail();
$currentUserId = getCurrentUserId();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thiết lập Hồ sơ Cá nhân - Kết Nối Yêu Thương</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/profile-setup.css">
</head>
<body>
    <div class="profile-setup-container">
        <!-- Header -->
        <header class="profile-header">
            <div class="header-content">
                <a href="../trangchu/index.php" class="nav-link">Trang chủ</a>
                <a href="#" class="nav-link active">Hồ sơ</a>
            </div>
            <button class="btn-logout" onclick="window.location.href='../../controller/logout.php'">
                <i class="fas fa-sign-out-alt"></i>
                Đăng Xuất
            </button>
            <div class="user-avatar-header">
                <img src="https://i.pravatar.cc/100" alt="Avatar">
            </div>
        </header>

        <!-- Main Content -->
        <div class="profile-main">
            <div class="profile-card">
                <h1 class="profile-title">Thiết lập Hồ sơ Cá nhân</h1>
                <p class="profile-subtitle">Cập nhật thông tin của bạn để chúng tôi có thể cung cấp trải nghiệm tốt hơn.</p>

                <!-- Avatar Upload Section -->
                <div class="avatar-section">
                    <div class="avatar-preview" id="avatarPreview">
                        <img src="https://i.pravatar.cc/150" alt="Avatar" id="avatarImage">
                    </div>
                    <button class="btn-upload-avatar" onclick="document.getElementById('avatarInput').click()">
                        <i class="fas fa-plus"></i>
                        Tải ảnh lên
                    </button>
                    <input type="file" id="avatarInput" accept="image/*" style="display: none;" onchange="previewAvatar(event)">
                </div>

                <!-- Profile Form -->
                <form id="profileForm" onsubmit="handleSubmit(event)">
                    <!-- Thông tin cá nhân -->
                    <div class="form-section">
                        <h2 class="section-title">Thông tin cá nhân</h2>
                        <p class="section-subtitle">Cung cấp các chi tiết cơ bản về bạn.</p>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="fullName">Tên đầy đủ</label>
                                <input type="text" id="fullName" name="fullName" placeholder="Nguyễn Thị Mai" required>
                            </div>

                            <div class="form-group">
                                <label for="gender">Giới tính</label>
                                <select id="gender" name="gender" required>
                                    <option value="">Nữ</option>
                                    <option value="Nam">Nam</option>
                                    <option value="Nữ" selected>Nữ</option>
                                    <option value="Khác">Khác</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group birthday-group">
                                <label>Ngày sinh</label>
                                <div class="birthday-inputs">
                                    <select id="day" name="day" required>
                                        <option value="15">15</option>
                                        <?php for($i = 1; $i <= 31; $i++): ?>
                                            <option value="<?= $i ?>" <?= $i == 15 ? 'selected' : '' ?>><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <select id="month" name="month" required>
                                        <option value="07">07</option>
                                        <?php for($i = 1; $i <= 12; $i++): ?>
                                            <option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>" <?= $i == 7 ? 'selected' : '' ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <select id="year" name="year" required>
                                        <option value="1998">1998</option>
                                        <?php for($i = 2005; $i >= 1950; $i--): ?>
                                            <option value="<?= $i ?>" <?= $i == 1998 ? 'selected' : '' ?>><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="maritalStatus">Tình trạng hôn nhân</label>
                                <select id="maritalStatus" name="maritalStatus" required>
                                    <option value="Độc thân" selected>Độc thân</option>
                                    <option value="Đã ly hôn">Đã ly hôn</option>
                                    <option value="Góa">Góa</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="weight">Cân nặng (kg)</label>
                                <input type="number" id="weight" name="weight" placeholder="55" value="55" required>
                            </div>

                            <div class="form-group">
                                <label for="height">Chiều cao (cm)</label>
                                <input type="number" id="height" name="height" placeholder="165" value="165" required>
                            </div>
                        </div>
                    </div>

                    <!-- Sở thích & Phong cách sống -->
                    <div class="form-section">
                        <h2 class="section-title">Sở thích & Phong cách sống</h2>
                        <p class="section-subtitle">Chia sẻ sở thích của bạn để nhận các đề xuất phù hợp.</p>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="goal">Mục tiêu</label>
                                <select id="goal" name="goal" required>
                                    <option value="">Phát triển bản thân</option>
                                    <option value="Hẹn hò nghiêm túc">Hẹn hò nghiêm túc</option>
                                    <option value="Kết bạn">Kết bạn</option>
                                    <option value="Kết hôn">Kết hôn</option>
                                    <option value="Phát triển bản thân" selected>Phát triển bản thân</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="education">Học vấn</label>
                                <select id="education" name="education" required>
                                    <option value="">Đại học</option>
                                    <option value="Trung học">Trung học</option>
                                    <option value="Cao đẳng">Cao đẳng</option>
                                    <option value="Đại học" selected>Đại học</option>
                                    <option value="Thạc sĩ">Thạc sĩ</option>
                                    <option value="Tiến sĩ">Tiến sĩ</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="location">Nơi ở</label>
                                <select id="location" name="location" required>
                                    <option value="">Hồ Chí Minh</option>
                                    <option value="Hà Nội">Hà Nội</option>
                                    <option value="Hồ Chí Minh" selected>Hồ Chí Minh</option>
                                    <option value="Đà Nẵng">Đà Nẵng</option>
                                    <option value="Cần Thơ">Cần Thơ</option>
                                    <option value="Hải Phòng">Hải Phòng</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <label>Sở thích</label>
                            <div class="interests-grid">
                                <button type="button" class="interest-tag active" data-interest="Đọc sách">Đọc sách</button>
                                <button type="button" class="interest-tag" data-interest="Xem phim">Xem phim</button>
                                <button type="button" class="interest-tag" data-interest="Nghe nhạc">Nghe nhạc</button>
                                <button type="button" class="interest-tag active" data-interest="Du lịch">Du lịch</button>
                                <button type="button" class="interest-tag" data-interest="Thể thao">Thể thao</button>
                                <button type="button" class="interest-tag" data-interest="Nấu ăn">Nấu ăn</button>
                                <button type="button" class="interest-tag" data-interest="Chụp ảnh">Chụp ảnh</button>
                                <button type="button" class="interest-tag" data-interest="Học ngoại ngữ">Học ngoại ngữ</button>
                                <button type="button" class="interest-tag" data-interest="Làm vườn">Làm vườn</button>
                                <button type="button" class="interest-tag" data-interest="Chơi game">Chơi game</button>
                                <button type="button" class="interest-tag" data-interest="Thiền">Thiền</button>
                                <button type="button" class="interest-tag" data-interest="Vẽ">Vẽ</button>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="form-actions">
                        <button type="submit" class="btn-save">
                            Lưu Thông tin
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Footer -->
        <footer class="profile-footer">
            <div class="social-links">
                <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </footer>
    </div>

    <script>
        // Preview avatar before upload
        function previewAvatar(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatarImage').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }

        // Handle interest tags selection
        document.querySelectorAll('.interest-tag').forEach(tag => {
            tag.addEventListener('click', function() {
                this.classList.toggle('active');
            });
        });

        // Handle form submission
        function handleSubmit(event) {
            event.preventDefault();
            
            // Get selected interests
            const selectedInterests = [];
            document.querySelectorAll('.interest-tag.active').forEach(tag => {
                selectedInterests.push(tag.dataset.interest);
            });
            
            // Show success notification
            const notification = document.createElement('div');
            notification.innerHTML = `
                <div style="
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background: white;
                    padding: 30px 50px;
                    border-radius: 15px;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                    z-index: 10000;
                    text-align: center;
                ">
                    <i class="fas fa-check-circle" style="font-size: 48px; color: #28a745; margin-bottom: 15px;"></i>
                    <h3 style="margin: 0 0 10px 0; color: #2C3E50;">Lưu thành công!</h3>
                    <p style="margin: 0; color: #666;">Đang chuyển đến trang chủ...</p>
                </div>
                <div style="
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0,0,0,0.5);
                    z-index: 9999;
                "></div>
            `;
            document.body.appendChild(notification);
            
            // Redirect to home page after 2 seconds
            setTimeout(() => {
                window.location.href = '../trangchu/index.php';
            }, 2000);
        }
    </script>
</body>
</html>
