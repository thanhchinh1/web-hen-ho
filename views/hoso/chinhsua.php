<?php    
require_once '../../models/mSession.php';
require_once '../../models/mProfile.php';

Session::start();

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

$currentUserEmail = Session::getUserEmail();
$currentUserId = Session::getUserId();

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
$interests = !empty($profile['soThich']) ? explode(', ', $profile['soThich']) : [];
$avatarSrc = !empty($profile['avt']) ? '../../' . htmlspecialchars($profile['avt']) : 'https://i.pravatar.cc/300';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa Hồ sơ - DuyenHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/chinhsua.css?v=<?php echo time(); ?>">
</head>
<body>
    <!-- Header -->
     <header class="main-header">
        <div class="header-container">
            <div class="header-left">
                <a href="../trangchu/index.php" class="logo">
                    <img src="../../public/img/logo.jpg" alt="DuyenHub Logo">
                    <span class="logo-text">DuyenHub</span>
                </a>
                <nav class="header-menu">
                    <a href="../trangchu/index.php" class="menu-item active">
                        <i class="fas fa-home"></i>
                        <span>Trang chủ</span>
                    </a>
                    <a href="../nhantin/chat.php" class="menu-item">
                        <i class="fas fa-comments"></i>
                        <span>Tin nhắn</span>
                    </a>
                    <a href="../timkiem/ghepdoinhanh.php" class="menu-item">
                        <i class="fas fa-search"></i>
                        <span>Tìm kiếm</span>
                    </a>
                    <a href="#" class="menu-item">
                        <i class="fas fa-question-circle"></i>
                        <span>Trợ giúp</span>
                    </a>
                </nav>
            </div>
            <div class="header-actions">
                <a href="../../controller/cLogout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Đăng xuất</span>
                </a>
            </div>
        </div>
    </header>

    <div class="profile-setup-wrapper">
        <button class="back-btn" onclick="window.history.back()" title="Quay lại">
            <i class="fas fa-arrow-left"></i>
        </button>
        
        <div class="profile-setup-container">
            <div class="profile-setup-header">
                <h1>Chỉnh sửa Hồ sơ</h1>
               
            </div>

            <!-- Avatar Upload Section -->
            <div class="avatar-section">
                <div class="avatar-preview" id="avatarPreview">
                    <img src="<?php echo $avatarSrc; ?>" alt="Avatar" id="avatarImage">
                </div>
                <button type="button" class="btn-upload-avatar" onclick="document.getElementById('avatarInput').click()">
                    <i class="fas fa-camera"></i>
                    Tải ảnh lên
                </button>
                <p class="avatar-hint">Kích thước tối đa: 5MB</p>
            </div>

            <!-- Profile Form -->
            <form id="profileForm" method="POST" action="../../controller/cProfile_update.php" enctype="multipart/form-data">
                <input type="file" id="avatarInput" name="avatar" accept="image/*" style="display: none;" onchange="previewAvatar(event)">
                
                <!-- Profile Details -->
                <div class="profile-details">
                    <!-- Thông tin cá nhân -->
                    <!-- Thông tin cá nhân -->
                    <div class="form-section">
                        <h2 class="section-title">Thông tin cá nhân</h2>
                        

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-user icon"></i>
                                    Tên đầy đủ
                                </label>
                                <input type="text" name="fullName" class="form-input" 
                                       value="<?php echo htmlspecialchars($profile['ten']); ?>" 
                                       placeholder="Nhập tên của bạn" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-venus-mars icon"></i>
                                    Giới tính
                                </label>
                                <select name="gender" class="form-select" required>
                                    <option value="">Chọn giới tính</option>
                                    <option value="Nam" <?php echo $profile['gioiTinh'] == 'Nam' ? 'selected' : ''; ?>>Nam</option>
                                    <option value="Nữ" <?php echo $profile['gioiTinh'] == 'Nữ' ? 'selected' : ''; ?>>Nữ</option>
                                    <option value="Khác" <?php echo $profile['gioiTinh'] == 'Khác' ? 'selected' : ''; ?>>Khác</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-calendar-alt icon"></i>
                                Ngày sinh
                            </label>
                            <div class="birthday-group">
                                <select name="day" class="birthday-select" required>
                                    <option value="">Ngày</option>
                                    <?php for($i = 1; $i <= 31; $i++): ?>
                                        <option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>" <?php echo $birthDay == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : ''; ?>><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                                <select name="month" class="birthday-select" required>
                                    <option value="">Tháng</option>
                                    <?php for($i = 1; $i <= 12; $i++): ?>
                                        <option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>" <?php echo $birthMonth == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : ''; ?>>Tháng <?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                                <select name="year" class="birthday-select" required>
                                    <option value="">Năm</option>
                                    <?php for($i = date('Y') - 18; $i >= 1950; $i--): ?>
                                        <option value="<?= $i ?>" <?php echo $birthYear == $i ? 'selected' : ''; ?>><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-heart icon"></i>
                                    Tình trạng hôn nhân
                                </label>
                                <select name="maritalStatus" class="form-select" required>
                                    <option value="">Chọn tình trạng</option>
                                    <option value="Độc thân" <?php echo $profile['tinhTrangHonNhan'] == 'Độc thân' ? 'selected' : ''; ?>>Độc thân</option>
                                    <option value="Đã ly hôn" <?php echo $profile['tinhTrangHonNhan'] == 'Đã ly hôn' ? 'selected' : ''; ?>>Đã ly hôn</option>
                                    <option value="Mẹ đơn thân" <?php echo $profile['tinhTrangHonNhan'] == 'Mẹ đơn thân' ? 'selected' : ''; ?>>Mẹ đơn thân</option>
                                    <option value="Cha đơn thân" <?php echo $profile['tinhTrangHonNhan'] == 'Cha đơn thân' ? 'selected' : ''; ?>>Cha đơn thân</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-weight icon"></i>
                                    Cân nặng (kg)
                                </label>
                                <input type="number" name="weight" class="form-input" 
                                       min="30" max="200" placeholder="VD: 65"
                                       value="<?php echo isset($profile['canNang']) ? $profile['canNang'] : ''; ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-ruler-vertical icon"></i>
                                    Chiều cao (cm)
                                </label>
                                <input type="number" name="height" class="form-input" 
                                       min="100" max="250" placeholder="VD: 170"
                                       value="<?php echo isset($profile['chieuCao']) ? $profile['chieuCao'] : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-map-marker-alt icon"></i>
                                    Thành phố
                                </label>
                                <select name="location" class="form-select" required>
                                    <option value="">Chọn thành phố</option>
                                    <?php 
                                    $cities = ["An Giang", "Bà Rịa - Vũng Tàu", "Bắc Giang", "Bắc Kạn", "Bạc Liêu", "Bắc Ninh", "Bến Tre", "Bình Định", "Bình Dương", "Bình Phước", "Bình Thuận", "Cà Mau", "Cần Thơ", "Cao Bằng", "Đà Nẵng", "Đắk Lắk", "Đắk Nông", "Điện Biên", "Đồng Nai", "Đồng Tháp", "Gia Lai", "Hà Giang", "Hà Nam", "Hà Nội", "Hà Tĩnh", "Hải Dương", "Hải Phòng", "Hậu Giang", "Hòa Bình", "Hưng Yên", "Khánh Hòa", "Kiên Giang", "Kon Tum", "Lai Châu", "Lâm Đồng", "Lạng Sơn", "Lào Cai", "Long An", "Nam Định", "Nghệ An", "Ninh Bình", "Ninh Thuận", "Phú Thọ", "Phú Yên", "Quảng Bình", "Quảng Nam", "Quảng Ngãi", "Quảng Ninh", "Quảng Trị", "Sóc Trăng", "Sơn La", "Tây Ninh", "Thái Bình", "Thái Nguyên", "Thanh Hóa", "Thừa Thiên Huế", "Tiền Giang", "TP Hồ Chí Minh", "Trà Vinh", "Tuyên Quang", "Vĩnh Long", "Vĩnh Phúc", "Yên Bái"];
                                    foreach ($cities as $city):
                                    ?>
                                        <option value="<?php echo $city; ?>" <?php echo $profile['noiSong'] == $city ? 'selected' : ''; ?>><?php echo $city; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Học vấn & Mục tiêu -->
                    <div class="form-section">
                        <h2 class="section-title">Học vấn & Mục tiêu</h2>
                       

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-graduation-cap icon"></i>
                                    Học vấn
                                </label>
                                <select name="education" class="form-select" required>
                                    <option value="">Chọn trình độ</option>
                                    <option value="Trung học" <?php echo $profile['hocVan'] == 'Trung học' ? 'selected' : ''; ?>>Trung học</option>
                                    <option value="Cao đẳng" <?php echo $profile['hocVan'] == 'Cao đẳng' ? 'selected' : ''; ?>>Cao đẳng</option>
                                    <option value="Đại học" <?php echo $profile['hocVan'] == 'Đại học' ? 'selected' : ''; ?>>Đại học</option>
                                    <option value="Thạc sĩ" <?php echo $profile['hocVan'] == 'Thạc sĩ' ? 'selected' : ''; ?>>Thạc sĩ</option>
                                    <option value="Tiến sĩ" <?php echo $profile['hocVan'] == 'Tiến sĩ' ? 'selected' : ''; ?>>Tiến sĩ</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-bullseye icon"></i>
                                    Mục tiêu
                                </label>
                                <select name="goal" class="form-select" required>
                                    <option value="">Chọn mục tiêu</option>
                                    <option value="Hẹn hò" <?php echo $profile['mucTieuPhatTrien'] == 'Hẹn hò' ? 'selected' : ''; ?>>Hẹn hò</option>
                                    <option value="Kết bạn" <?php echo $profile['mucTieuPhatTrien'] == 'Kết bạn' ? 'selected' : ''; ?>>Kết bạn</option>
                                    <option value="Kết hôn" <?php echo $profile['mucTieuPhatTrien'] == 'Kết hôn' ? 'selected' : ''; ?>>Kết hôn</option>
                                    <option value="Tìm hiểu" <?php echo $profile['mucTieuPhatTrien'] == 'Tìm hiểu' ? 'selected' : ''; ?>>Tìm hiểu</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Sở thích -->
                    <div class="form-section">
                        <h2 class="section-title">Sở thích</h2>
                        <div class="interests-grid">
                            <?php
                            $allInterests = [
                                "Đọc sách", "Xem phim", "Nghe nhạc", "Du lịch", "Thể thao", "Nấu ăn",
                                "Chụp ảnh", "Học ngoại ngữ", "Làm vườn", "Chơi game", "Thiền", "Vẽ",
                                "Khiêu vũ", "Ca hát", "Tập gym", "Bơi lội", "Leo núi", "Cắm trại",
                                "Mua sắm", "Thời trang", "Viết lách", "Thủ công mỹ nghệ", "Chơi nhạc cụ",
                                "Xem thể thao", "Tình nguyện", "Sưu tầm", "Nghiên cứu khoa học", "Chơi cờ"
                            ];
                            foreach ($allInterests as $interest):
                                $isSelected = in_array($interest, $interests);
                            ?>
                                <button type="button" class="interest-tag <?php echo $isSelected ? 'active' : ''; ?>" 
                                      data-interest="<?php echo $interest; ?>"
                                      onclick="toggleInterest(this)">
                                    <?php echo $interest; ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="interests" id="interestsInput" value="<?php echo isset($profile['soThich']) ? htmlspecialchars($profile['soThich']) : ''; ?>">
                    </div>

                    <!-- Giới thiệu bản thân -->
                    <div class="form-section">
                        <h2 class="section-title">Giới thiệu bản thân</h2>
                       

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-pen icon"></i>
                                Mô tả về bạn
                            </label>
                            <textarea name="bio" class="form-textarea" rows="5" 
                                      placeholder="Viết vài dòng về bản thân bạn..."><?php echo isset($profile['moTa']) ? htmlspecialchars($profile['moTa']) : ''; ?></textarea>
                        </div>
                    </div>

                    <!-- Submit buttons -->
                    <div class="form-actions">
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-save"></i>
                            Lưu thay đổi
                        </button>
                    </div>
                </div>
            </form>
        </div>
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

        // Toggle interest selection
        function toggleInterest(element) {
            element.classList.toggle('active');
            updateInterestsInput();
        }

        // Update hidden input with selected interests
        function updateInterestsInput() {
            const selectedInterests = [];
            document.querySelectorAll('.interest-tag.active').forEach(tag => {
                selectedInterests.push(tag.dataset.interest);
            });
            document.getElementById('interestsInput').value = selectedInterests.join(', ');
        }

        // Handle form submission
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Kiểm tra sở thích
            const selectedInterests = document.querySelectorAll('.interest-tag.active');
            if (selectedInterests.length === 0) {
                showNotification('Vui lòng chọn ít nhất một sở thích!', 'error');
                return;
            }

            // Tạo FormData
            const formData = new FormData(this);
            
            // Hiển thị loading
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';

            // Gửi request
            fetch('../../controller/cProfile_update.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    // Chuyển về trang hồ sơ sau 1.5 giây
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1500);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                showNotification('Có lỗi xảy ra, vui lòng thử lại!', 'error');
                console.error('Error:', error);
            });
        });

        // Hiển thị thông báo
        function showNotification(message, type) {
            const notification = document.createElement('div');
            const icon = type === 'success' ? '✓' : '✕';
            const color = type === 'success' ? '#4CAF50' : '#f44336';
            
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
                    <h3 style="margin: 0; color: #2C3E50; font-size: 18px;">${message}</h3>
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
            
            // Tự động xóa sau 3 giây
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    </script>
</body>
</html>
