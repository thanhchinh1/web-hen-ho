<?php
require_once '../../models/session.php';
require_once '../../models/mProfile.php';

requireLogin(); // Yêu cầu đăng nhập để truy cập trang này

$currentUserEmail = getCurrentUserEmail();
$currentUserId = getCurrentUserId();

// Lấy thông tin hồ sơ hiện tại
$profileModel = new Profile();
$profile = $profileModel->getProfile($currentUserId);

// Nếu chưa có hồ sơ, chuyển về trang thiết lập
if (!$profile) {
    header('Location: thietlaphoso.php');
    exit;
}

// Parse ngày sinh
$birthDate = explode('-', $profile['ngaySinh']);
$birthYear = $birthDate[0];
$birthMonth = $birthDate[1];
$birthDay = $birthDate[2];

// Parse sở thích
$interests = explode(', ', $profile['soThich']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa Hồ sơ - Kết Nối Yêu Thương</title>
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
                <h1 class="profile-title">Chỉnh sửa Hồ sơ Cá nhân</h1>
                <p class="profile-subtitle">Cập nhật thông tin của bạn (Ảnh đại diện không bắt buộc)</p>

                <!-- Avatar Upload Section -->
                <div class="avatar-section">
                    <div class="avatar-preview" id="avatarPreview">
                        <img src="../../<?php echo htmlspecialchars($profile['avt']); ?>" alt="Avatar" id="avatarImage">
                    </div>
                    <button class="btn-upload-avatar" onclick="document.getElementById('avatarInput').click()">
                        <i class="fas fa-camera"></i>
                        Thay đổi ảnh
                    </button>
                    <input type="file" id="avatarInput" accept="image/*" style="display: none;" onchange="previewAvatar(event)">
                </div>

                <!-- Profile Form -->
                <form id="profileForm" onsubmit="handleSubmit(event)">
                    <!-- Thông tin cá nhân -->
                    <div class="form-section">
                        <h2 class="section-title">Thông tin cá nhân</h2>
                        <p class="section-subtitle">Cập nhật các chi tiết cơ bản về bạn.</p>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="fullName">Tên đầy đủ</label>
                                <input type="text" id="fullName" name="fullName" placeholder="Nhập tên đầy đủ" value="<?php echo htmlspecialchars($profile['ten']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="gender">Giới tính</label>
                                <select id="gender" name="gender" required>
                                    <option value="">Chọn giới tính</option>
                                    <option value="Nam" <?php echo $profile['gioiTinh'] == 'Nam' ? 'selected' : ''; ?>>Nam</option>
                                    <option value="Nữ" <?php echo $profile['gioiTinh'] == 'Nữ' ? 'selected' : ''; ?>>Nữ</option>
                                    <option value="Khác" <?php echo $profile['gioiTinh'] == 'Khác' ? 'selected' : ''; ?>>Khác</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group birthday-group">
                                <label for="birthday">Ngày sinh</label>
                                <div class="birthday-inputs">
                                    <select id="day" name="day" required>
                                        <option value="">Ngày</option>
                                        <?php for($i = 1; $i <= 31; $i++): ?>
                                            <option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>" <?php echo $birthDay == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : ''; ?>><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <select id="month" name="month" required>
                                        <option value="">Tháng</option>
                                        <?php for($i = 1; $i <= 12; $i++): ?>
                                            <option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>" <?php echo $birthMonth == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : ''; ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <select id="year" name="year" required>
                                        <option value="">Năm</option>
                                        <?php for($i = date('Y') - 18; $i >= 1950; $i--): ?>
                                            <option value="<?= $i ?>" <?php echo $birthYear == $i ? 'selected' : ''; ?>><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="maritalStatus">Tình trạng hôn nhân</label>
                                <select id="maritalStatus" name="maritalStatus" required>
                                    <option value="">Chọn tình trạng</option>
                                    <option value="Độc thân" <?php echo $profile['tinhTrangHonNhan'] == 'Độc thân' ? 'selected' : ''; ?>>Độc thân</option>
                                    <option value="Đã ly hôn" <?php echo $profile['tinhTrangHonNhan'] == 'Đã ly hôn' ? 'selected' : ''; ?>>Đã ly hôn</option>
                                    <option value="Góa" <?php echo $profile['tinhTrangHonNhan'] == 'Góa' ? 'selected' : ''; ?>>Góa</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="weight">Cân nặng (kg)</label>
                                <input type="number" id="weight" name="weight" placeholder="Nhập cân nặng" min="30" max="200" value="<?php echo $profile['canNang']; ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="height">Chiều cao (cm)</label>
                                <input type="number" id="height" name="height" placeholder="Nhập chiều cao" min="100" max="250" value="<?php echo $profile['chieuCao']; ?>" required>
                            </div>
                        </div>
                    </div>

                    <!-- Sở thích & Phong cách sống -->
                    <div class="form-section">
                        <h2 class="section-title">Sở thích & Phong cách sống</h2>
                        <p class="section-subtitle">Cập nhật sở thích của bạn.</p>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="goal">Mục tiêu</label>
                                <select id="goal" name="goal" required>
                                    <option value="">Chọn mục tiêu</option>
                                    <option value="Hẹn hò nghiêm túc" <?php echo $profile['mucTieuPhatTrien'] == 'Hẹn hò nghiêm túc' ? 'selected' : ''; ?>>Hẹn hò nghiêm túc</option>
                                    <option value="Kết bạn" <?php echo $profile['mucTieuPhatTrien'] == 'Kết bạn' ? 'selected' : ''; ?>>Kết bạn</option>
                                    <option value="Kết hôn" <?php echo $profile['mucTieuPhatTrien'] == 'Kết hôn' ? 'selected' : ''; ?>>Kết hôn</option>
                                    <option value="Phát triển bản thân" <?php echo $profile['mucTieuPhatTrien'] == 'Phát triển bản thân' ? 'selected' : ''; ?>>Phát triển bản thân</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="education">Học vấn</label>
                                <select id="education" name="education" required>
                                    <option value="">Chọn học vấn</option>
                                    <option value="Trung học" <?php echo $profile['hocVan'] == 'Trung học' ? 'selected' : ''; ?>>Trung học</option>
                                    <option value="Cao đẳng" <?php echo $profile['hocVan'] == 'Cao đẳng' ? 'selected' : ''; ?>>Cao đẳng</option>
                                    <option value="Đại học" <?php echo $profile['hocVan'] == 'Đại học' ? 'selected' : ''; ?>>Đại học</option>
                                    <option value="Thạc sĩ" <?php echo $profile['hocVan'] == 'Thạc sĩ' ? 'selected' : ''; ?>>Thạc sĩ</option>
                                    <option value="Tiến sĩ" <?php echo $profile['hocVan'] == 'Tiến sĩ' ? 'selected' : ''; ?>>Tiến sĩ</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="location">Nơi ở</label>
                                <select id="location" name="location" required>
                                    <option value="">Chọn tỉnh/thành phố</option>
                                    <?php 
                                    $cities = ["An Giang", "Bà Rịa - Vũng Tàu", "Bắc Giang", "Bắc Kạn", "Bạc Liêu", "Bắc Ninh", "Bến Tre", "Bình Định", "Bình Dương", "Bình Phước", "Bình Thuận", "Cà Mau", "Cần Thơ", "Cao Bằng", "Đà Nẵng", "Đắk Lắk", "Đắk Nông", "Điện Biên", "Đồng Nai", "Đồng Tháp", "Gia Lai", "Hà Giang", "Hà Nam", "Hà Nội", "Hà Tĩnh", "Hải Dương", "Hải Phòng", "Hậu Giang", "Hòa Bình", "Hưng Yên", "Khánh Hòa", "Kiên Giang", "Kon Tum", "Lai Châu", "Lâm Đồng", "Lạng Sơn", "Lào Cai", "Long An", "Nam Định", "Nghệ An", "Ninh Bình", "Ninh Thuận", "Phú Thọ", "Phú Yên", "Quảng Bình", "Quảng Nam", "Quảng Ngãi", "Quảng Ninh", "Quảng Trị", "Sóc Trăng", "Sơn La", "Tây Ninh", "Thái Bình", "Thái Nguyên", "Thanh Hóa", "Thừa Thiên Huế", "Tiền Giang", "TP Hồ Chí Minh", "Trà Vinh", "Tuyên Quang", "Vĩnh Long", "Vĩnh Phúc", "Yên Bái"];
                                    foreach ($cities as $city):
                                    ?>
                                        <option value="<?php echo $city; ?>" <?php echo $profile['noiSong'] == $city ? 'selected' : ''; ?>><?php echo $city; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <label>Sở thích</label>
                            <div class="interests-grid">
                                <?php 
                                $allInterests = ["Đọc sách", "Xem phim", "Nghe nhạc", "Du lịch", "Thể thao", "Nấu ăn", "Chụp ảnh", "Học ngoại ngữ", "Làm vườn", "Chơi game", "Thiền", "Vẽ", "Khiêu vũ", "Ca hát", "Tập gym", "Bơi lội", "Leo núi", "Cắm trại", "Mua sắm", "Thời trang", "Viết lách", "Thủ công mỹ nghệ", "Chơi nhạc cụ", "Xem thể thao", "Tình nguyện", "Sưu tầm", "Nghiên cứu khoa học", "Chơi cờ"];
                                foreach ($allInterests as $interest):
                                    $isActive = in_array($interest, $interests) ? 'active' : '';
                                ?>
                                    <button type="button" class="interest-tag <?php echo $isActive; ?>" data-interest="<?php echo $interest; ?>"><?php echo $interest; ?></button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-section">
                        <div class="form-group full-width">
                            <label for="description">Mô tả</label>
                            <textarea id="description" name="description" rows="5" maxlength="500" placeholder="Viết đôi nét về bản thân bạn..." required><?php echo htmlspecialchars($profile['moTa']); ?></textarea>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="form-actions">
                        <a href="../trangchu/index.php" class="btn-cancel">Hủy</a>
                        <button type="submit" class="btn-save">
                            Cập nhật Thông tin
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
            
            // Kiểm tra sở thích không được bỏ trống
            if (selectedInterests.length === 0) {
                showNotification('Vui lòng chọn ít nhất một sở thích!', 'error');
                return;
            }
            
            // Tạo FormData
            const formData = new FormData();
            
            // Thêm avatar nếu có thay đổi
            const avatarInput = document.getElementById('avatarInput');
            if (avatarInput.files && avatarInput.files.length > 0) {
                formData.append('avatar', avatarInput.files[0]);
            }
            
            formData.append('fullName', document.getElementById('fullName').value);
            formData.append('gender', document.getElementById('gender').value);
            formData.append('day', document.getElementById('day').value);
            formData.append('month', document.getElementById('month').value);
            formData.append('year', document.getElementById('year').value);
            formData.append('maritalStatus', document.getElementById('maritalStatus').value);
            formData.append('weight', document.getElementById('weight').value);
            formData.append('height', document.getElementById('height').value);
            formData.append('goal', document.getElementById('goal').value);
            formData.append('education', document.getElementById('education').value);
            formData.append('location', document.getElementById('location').value);
            formData.append('interests', selectedInterests.join(', '));
            formData.append('description', document.getElementById('description').value);
            
            // Hiển thị loading
            showNotification('Đang xử lý...', 'loading');
            
            // Gửi request
            fetch('../../controller/profile_update.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    setTimeout(() => {
                        window.location.href = '../trangchu/index.php';
                    }, 1500);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Có lỗi xảy ra, vui lòng thử lại!', 'error');
            });
        }
        
        // Hàm hiển thị thông báo
        function showNotification(message, type) {
            // Xóa notification cũ nếu có
            const oldNotif = document.querySelector('.custom-notification');
            if (oldNotif) oldNotif.remove();
            
            const notification = document.createElement('div');
            notification.className = 'custom-notification';
            
            let icon = '';
            let color = '';
            let showCloseButton = false;
            let autoCloseTime = 0;
            
            if (type === 'success') {
                icon = '<i class="fas fa-check-circle"></i>';
                color = '#28a745';
                showCloseButton = false;
                autoCloseTime = 1500; // Tự động đóng sau 1.5 giây
            } else if (type === 'error') {
                icon = '<i class="fas fa-exclamation-circle"></i>';
                color = '#dc3545';
                showCloseButton = true; // Hiển thị nút đóng cho lỗi
                autoCloseTime = 0; // Không tự động đóng
            } else if (type === 'loading') {
                icon = '<i class="fas fa-spinner fa-spin"></i>';
                color = '#5BC0DE';
                showCloseButton = false;
                autoCloseTime = 0; // Không tự động đóng
            }
            
            const closeButtonHTML = showCloseButton ? `
                <button onclick="this.closest('.custom-notification').remove()" style="
                    margin-top: 20px;
                    padding: 10px 30px;
                    background: ${color};
                    color: white;
                    border: none;
                    border-radius: 8px;
                    font-size: 14px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.3s ease;
                " onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                    Đóng
                </button>
            ` : '';
            
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
                    <div style="font-size: 48px; color: ${color}; margin-bottom: 15px;">${icon}</div>
                    <h3 style="margin: 0; color: #2C3E50;">${message}</h3>
                    ${closeButtonHTML}
                </div>
                <div onclick="${showCloseButton ? 'this.parentElement.remove()' : ''}" style="
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0,0,0,0.5);
                    z-index: 9999;
                    ${showCloseButton ? 'cursor: pointer;' : ''}
                "></div>
            `;
            document.body.appendChild(notification);
            
            // Tự động xóa nếu có thời gian
            if (autoCloseTime > 0) {
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, autoCloseTime);
            }
        }
    </script>
    <style>
        .btn-cancel {
            display: inline-block;
            padding: 12px 30px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-right: 10px;
            transition: all 0.3s ease;
        }
        .btn-cancel:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
    </style>
</body>
</html>
