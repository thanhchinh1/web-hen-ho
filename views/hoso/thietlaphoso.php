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
        <header class="main-header">
            <div class="header-container">
            <a href="../trangchu/index.php" class="logo">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="logo-icon">
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" fill="#ff6b9d"/>
                </svg>
            </a>
            <div class="nav-right">
                <a href="../../controller/logout.php" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i>
                Đăng Xuất
                </a>
            </div>
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
                        <img src="" alt="" id="avatarImage">
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
                                <input type="text" id="fullName" name="fullName" placeholder="Nhập tên đầy đủ" required>
                            </div>

                            <div class="form-group">
                                <label for="gender">Giới tính</label>
                                <select id="gender" name="gender" required>
                                    <option value="" selected>Chọn giới tính</option>
                                    <option value="Nam">Nam</option>
                                    <option value="Nữ">Nữ</option>
                                    <option value="Khác">Khác</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group birthday-group">
                                <label for="birthday">Ngày sinh</label>
                                <div class="birthday-inputs">
                                    <select id="day" name="day" required>
                                        <option value="" selected>Ngày</option>
                                        <?php for($i = 1; $i <= 31; $i++): ?>
                                            <option value="<?= $i ?>"><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <select id="month" name="month" required>
                                        <option value="" selected>Tháng</option>
                                        <?php for($i = 1; $i <= 12; $i++): ?>
                                            <option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>"><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <select id="year" name="year" required>
                                        <option value="" selected>Năm</option>
                                        <?php for($i = date('Y') - 18; $i >= 1950; $i--): ?>
                                            <option value="<?= $i ?>"><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                            </div>

                            <div class="form-group">
                                <label for="maritalStatus">Tình trạng hôn nhân</label>
                                <select id="maritalStatus" name="maritalStatus" required>
                                    <option value="">Chọn tình trạng</option>
                                    <option value="Độc thân">Độc thân</option>
                                    <option value="Đã ly hôn">Đã ly hôn</option>
                                    <option value="Góa">Góa</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="weight">Cân nặng (kg)</label>
                                <input type="number" id="weight" name="weight" placeholder="Nhập cân nặng" min="30" max="200" required>
                            </div>

                            <div class="form-group">
                                <label for="height">Chiều cao (cm)</label>
                                <input type="number" id="height" name="height" placeholder="Nhập chiều cao" min="100" max="250" required>
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
                                    <option value="" selected>Chọn mục tiêu</option>
                                    <option value="Hẹn hò nghiêm túc">Hẹn hò nghiêm túc</option>
                                    <option value="Kết bạn">Kết bạn</option>
                                    <option value="Kết hôn">Kết hôn</option>
                                    <option value="Phát triển bản thân">Phát triển bản thân</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="education">Học vấn</label>
                                <select id="education" name="education" required>
                                    <option value="" selected>Chọn học vấn</option>
                                    <option value="Trung học">Trung học</option>
                                    <option value="Cao đẳng">Cao đẳng</option>
                                    <option value="Đại học">Đại học</option>
                                    <option value="Thạc sĩ">Thạc sĩ</option>
                                    <option value="Tiến sĩ">Tiến sĩ</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="location">Nơi ở</label>
                                <select id="location" name="location" required>
                                    <option value="" selected>Chọn tỉnh/thành phố</option>
                                    <option value="An Giang">An Giang</option>
                                    <option value="Bà Rịa - Vũng Tàu">Bà Rịa - Vũng Tàu</option>
                                    <option value="Bắc Giang">Bắc Giang</option>
                                    <option value="Bắc Kạn">Bắc Kạn</option>
                                    <option value="Bạc Liêu">Bạc Liêu</option>
                                    <option value="Bắc Ninh">Bắc Ninh</option>
                                    <option value="Bến Tre">Bến Tre</option>
                                    <option value="Bình Định">Bình Định</option>
                                    <option value="Bình Dương">Bình Dương</option>
                                    <option value="Bình Phước">Bình Phước</option>
                                    <option value="Bình Thuận">Bình Thuận</option>
                                    <option value="Cà Mau">Cà Mau</option>
                                    <option value="Cần Thơ">Cần Thơ</option>
                                    <option value="Cao Bằng">Cao Bằng</option>
                                    <option value="Đà Nẵng">Đà Nẵng</option>
                                    <option value="Đắk Lắk">Đắk Lắk</option>
                                    <option value="Đắk Nông">Đắk Nông</option>
                                    <option value="Điện Biên">Điện Biên</option>
                                    <option value="Đồng Nai">Đồng Nai</option>
                                    <option value="Đồng Tháp">Đồng Tháp</option>
                                    <option value="Gia Lai">Gia Lai</option>
                                    <option value="Hà Giang">Hà Giang</option>
                                    <option value="Hà Nam">Hà Nam</option>
                                    <option value="Hà Nội">Hà Nội</option>
                                    <option value="Hà Tĩnh">Hà Tĩnh</option>
                                    <option value="Hải Dương">Hải Dương</option>
                                    <option value="Hải Phòng">Hải Phòng</option>
                                    <option value="Hậu Giang">Hậu Giang</option>
                                    <option value="Hòa Bình">Hòa Bình</option>
                                    <option value="Hưng Yên">Hưng Yên</option>
                                    <option value="Khánh Hòa">Khánh Hòa</option>
                                    <option value="Kiên Giang">Kiên Giang</option>
                                    <option value="Kon Tum">Kon Tum</option>
                                    <option value="Lai Châu">Lai Châu</option>
                                    <option value="Lâm Đồng">Lâm Đồng</option>
                                    <option value="Lạng Sơn">Lạng Sơn</option>
                                    <option value="Lào Cai">Lào Cai</option>
                                    <option value="Long An">Long An</option>
                                    <option value="Nam Định">Nam Định</option>
                                    <option value="Nghệ An">Nghệ An</option>
                                    <option value="Ninh Bình">Ninh Bình</option>
                                    <option value="Ninh Thuận">Ninh Thuận</option>
                                    <option value="Phú Thọ">Phú Thọ</option>
                                    <option value="Phú Yên">Phú Yên</option>
                                    <option value="Quảng Bình">Quảng Bình</option>
                                    <option value="Quảng Nam">Quảng Nam</option>
                                    <option value="Quảng Ngãi">Quảng Ngãi</option>
                                    <option value="Quảng Ninh">Quảng Ninh</option>
                                    <option value="Quảng Trị">Quảng Trị</option>
                                    <option value="Sóc Trăng">Sóc Trăng</option>
                                    <option value="Sơn La">Sơn La</option>
                                    <option value="Tây Ninh">Tây Ninh</option>
                                    <option value="Thái Bình">Thái Bình</option>
                                    <option value="Thái Nguyên">Thái Nguyên</option>
                                    <option value="Thanh Hóa">Thanh Hóa</option>
                                    <option value="Thừa Thiên Huế">Thừa Thiên Huế</option>
                                    <option value="Tiền Giang">Tiền Giang</option>
                                    <option value="TP Hồ Chí Minh">TP Hồ Chí Minh</option>
                                    <option value="Trà Vinh">Trà Vinh</option>
                                    <option value="Tuyên Quang">Tuyên Quang</option>
                                    <option value="Vĩnh Long">Vĩnh Long</option>
                                    <option value="Vĩnh Phúc">Vĩnh Phúc</option>
                                    <option value="Yên Bái">Yên Bái</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <label>Sở thích</label>
                            <div class="interests-grid">
                                <button type="button" class="interest-tag" data-interest="Đọc sách">Đọc sách</button>
                                <button type="button" class="interest-tag" data-interest="Xem phim">Xem phim</button>
                                <button type="button" class="interest-tag" data-interest="Nghe nhạc">Nghe nhạc</button>
                                <button type="button" class="interest-tag" data-interest="Du lịch">Du lịch</button>
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
