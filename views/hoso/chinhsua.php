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
    <link rel="stylesheet" href="../../public/css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="page-wrapper">
        <!-- Header -->
        <header class="profile-header">
            <a href="../trangchu/index.php" class="logo">
                <img src="../../public/img/logo.jpg" alt="DuyenHub">
                <span class="logo-text">DuyenHub</span>
            </a>
        </header>

        <!-- Back button -->
        <div class="back-button-container">
            <button class="btn-back" onclick="window.location.href='../trangchu/index.php'">
                <i class="fas fa-arrow-left"></i>
            </button>
        </div>

        <!-- Profile Content -->
        <div class="profile-content">
            <!-- Profile Header Section -->
            <div class="profile-hero">
                <div class="profile-avatar-section">
                    <img src="<?php echo $avatarSrc; ?>" alt="Avatar" class="profile-avatar" id="avatarImage">
                    <h1 class="profile-name"><?php echo htmlspecialchars($profile['ten']); ?></h1>
                </div>
            </div>

            <!-- Profile Form -->
            <form id="profileForm" method="POST" action="../../controller/cProfile_update.php" enctype="multipart/form-data">
                <!-- Avatar Upload Section -->
                <div style="text-align: center; margin: 20px 0;">
                    <button type="button" class="btn-upload-avatar" onclick="document.getElementById('avatarInput').click()" style="
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        border: none;
                        padding: 12px 30px;
                        border-radius: 25px;
                        font-size: 16px;
                        font-weight: 600;
                        cursor: pointer;
                        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
                        transition: all 0.3s ease;
                    " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(102, 126, 234, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(102, 126, 234, 0.3)'">
                        <i class="fas fa-camera"></i>
                        Thay đổi ảnh đại diện
                    </button>
                    <input type="file" id="avatarInput" name="avatar" accept="image/*" style="display: none;" onchange="previewAvatar(event)">
                </div>

                <!-- Profile Details -->
                <div class="profile-details">
                    <!-- Thông tin cá nhân -->
                    <section class="detail-section">
                        <h2 class="section-title">Thông tin cá nhân</h2>
                        <div class="info-list">
                            <div class="info-item">
                                <i class="fas fa-user"></i>
                                <label class="info-label">Tên đầy đủ</label>
                                <input type="text" name="fullName" class="info-value" value="<?php echo htmlspecialchars($profile['ten']); ?>" required>
                            </div>
                            
                            <div class="info-item">
                                <i class="fas fa-venus-mars"></i>
                                <label class="info-label">Giới tính</label>
                                <select name="gender" class="info-value" required>
                                    <option value="Nam" <?php echo $profile['gioiTinh'] == 'Nam' ? 'selected' : ''; ?>>Nam</option>
                                    <option value="Nữ" <?php echo $profile['gioiTinh'] == 'Nữ' ? 'selected' : ''; ?>>Nữ</option>
                                    <option value="Khác" <?php echo $profile['gioiTinh'] == 'Khác' ? 'selected' : ''; ?>>Khác</option>
                                </select>
                            </div>
                            
                            <div class="info-item">
                                <i class="fas fa-calendar-alt"></i>
                                <label class="info-label">Ngày sinh</label>
                                <div style="display: flex; gap: 5px;">
                                    <select name="day" style="flex: 1; padding: 8px; border: 1px solid #e0e0e0; border-radius: 8px; background: white; font-size: 15px; font-weight: 600; color: #2c3e50;" required>
                                        <?php for($i = 1; $i <= 31; $i++): ?>
                                            <option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>" <?php echo $birthDay == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : ''; ?>><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <select name="month" style="flex: 1; padding: 8px; border: 1px solid #e0e0e0; border-radius: 8px; background: white; font-size: 15px; font-weight: 600; color: #2c3e50;" required>
                                        <?php for($i = 1; $i <= 12; $i++): ?>
                                            <option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>" <?php echo $birthMonth == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : ''; ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <select name="year" style="flex: 1; padding: 8px; border: 1px solid #e0e0e0; border-radius: 8px; background: white; font-size: 15px; font-weight: 600; color: #2c3e50;" required>
                                        <?php for($i = date('Y') - 18; $i >= 1950; $i--): ?>
                                            <option value="<?= $i ?>" <?php echo $birthYear == $i ? 'selected' : ''; ?>><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <i class="fas fa-heart"></i>
                                <label class="info-label">Tình trạng hôn nhân</label>
                                <select name="maritalStatus" class="info-value" required>
                                    <option value="Độc thân" <?php echo $profile['tinhTrangHonNhan'] == 'Độc thân' ? 'selected' : ''; ?>>Độc thân</option>
                                    <option value="Đã ly hôn" <?php echo $profile['tinhTrangHonNhan'] == 'Đã ly hôn' ? 'selected' : ''; ?>>Đã ly hôn</option>
                                    <option value="Mẹ đơn thân" <?php echo $profile['tinhTrangHonNhan'] == 'Mẹ đơn thân' ? 'selected' : ''; ?>>Mẹ đơn thân</option>
                                    <option value="Cha đơn thân" <?php echo $profile['tinhTrangHonNhan'] == 'Cha đơn thân' ? 'selected' : ''; ?>>Cha đơn thân</option>
                                </select>
                            </div>
                            
                            <div class="info-item">
                                <i class="fas fa-weight"></i>
                                <label class="info-label">Cân nặng (kg)</label>
                                <input type="number" name="weight" class="info-value" min="30" max="200" value="<?php echo isset($profile['canNang']) ? $profile['canNang'] : ''; ?>">
                            </div>
                            
                            <div class="info-item">
                                <i class="fas fa-ruler-vertical"></i>
                                <label class="info-label">Chiều cao (cm)</label>
                                <input type="number" name="height" class="info-value" min="100" max="250" value="<?php echo isset($profile['chieuCao']) ? $profile['chieuCao'] : ''; ?>">
                            </div>
                            
                            <div class="info-item" style="grid-column: 1 / -1;">
                                <i class="fas fa-map-marker-alt"></i>
                                <label class="info-label">Thành phố</label>
                                <select name="location" class="info-value" required>
                                    <?php 
                                    $cities = ["An Giang", "Bà Rịa - Vũng Tàu", "Bắc Giang", "Bắc Kạn", "Bạc Liêu", "Bắc Ninh", "Bến Tre", "Bình Định", "Bình Dương", "Bình Phước", "Bình Thuận", "Cà Mau", "Cần Thơ", "Cao Bằng", "Đà Nẵng", "Đắk Lắk", "Đắk Nông", "Điện Biên", "Đồng Nai", "Đồng Tháp", "Gia Lai", "Hà Giang", "Hà Nam", "Hà Nội", "Hà Tĩnh", "Hải Dương", "Hải Phòng", "Hậu Giang", "Hòa Bình", "Hưng Yên", "Khánh Hòa", "Kiên Giang", "Kon Tum", "Lai Châu", "Lâm Đồng", "Lạng Sơn", "Lào Cai", "Long An", "Nam Định", "Nghệ An", "Ninh Bình", "Ninh Thuận", "Phú Thọ", "Phú Yên", "Quảng Bình", "Quảng Nam", "Quảng Ngãi", "Quảng Ninh", "Quảng Trị", "Sóc Trăng", "Sơn La", "Tây Ninh", "Thái Bình", "Thái Nguyên", "Thanh Hóa", "Thừa Thiên Huế", "Tiền Giang", "TP Hồ Chí Minh", "Trà Vinh", "Tuyên Quang", "Vĩnh Long", "Vĩnh Phúc", "Yên Bái"];
                                    foreach ($cities as $city):
                                    ?>
                                        <option value="<?php echo $city; ?>" <?php echo $profile['noiSong'] == $city ? 'selected' : ''; ?>><?php echo $city; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </section>

                    <!-- Học vấn & Mục tiêu -->
                    <section class="detail-section">
                        <h2 class="section-title">Học vấn & Mục tiêu</h2>
                        <div class="info-list">
                            <div class="info-item">
                                <i class="fas fa-graduation-cap"></i>
                                <label class="info-label">Học vấn</label>
                                <select name="education" class="info-value" required>
                                    <option value="Trung học" <?php echo $profile['hocVan'] == 'Trung học' ? 'selected' : ''; ?>>Trung học</option>
                                    <option value="Cao đẳng" <?php echo $profile['hocVan'] == 'Cao đẳng' ? 'selected' : ''; ?>>Cao đẳng</option>
                                    <option value="Đại học" <?php echo $profile['hocVan'] == 'Đại học' ? 'selected' : ''; ?>>Đại học</option>
                                    <option value="Thạc sĩ" <?php echo $profile['hocVan'] == 'Thạc sĩ' ? 'selected' : ''; ?>>Thạc sĩ</option>
                                    <option value="Tiến sĩ" <?php echo $profile['hocVan'] == 'Tiến sĩ' ? 'selected' : ''; ?>>Tiến sĩ</option>
                                </select>
                            </div>
                            
                            <div class="info-item">
                                <i class="fas fa-bullseye"></i>
                                <label class="info-label">Mục tiêu</label>
                                <select name="goal" class="info-value" required>
                                    <option value="Hẹn hò" <?php echo $profile['mucTieuPhatTrien'] == 'Hẹn hò' ? 'selected' : ''; ?>>Hẹn hò</option>
                                    <option value="Kết bạn" <?php echo $profile['mucTieuPhatTrien'] == 'Kết bạn' ? 'selected' : ''; ?>>Kết bạn</option>
                                    <option value="Kết hôn" <?php echo $profile['mucTieuPhatTrien'] == 'Kết hôn' ? 'selected' : ''; ?>>Kết hôn</option>
                                    <option value="Tìm hiểu" <?php echo $profile['mucTieuPhatTrien'] == 'Tìm hiểu' ? 'selected' : ''; ?>>Tìm hiểu</option>
                                </select>
                            </div>
                        </div>
                    </section>

                    <!-- Sở thích -->
                    <section class="detail-section">
                        <h2 class="section-title">Sở thích</h2>
                        <p style="color: #666; font-size: 14px; margin-bottom: 15px;"><i class="fas fa-heart"></i> Chọn sở thích của bạn</p>
                        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
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
                    </section>

                    <!-- Giới thiệu bản thân -->
                    <section class="detail-section">
                        <h2 class="section-title">Giới thiệu bản thân</h2>
                        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                            <textarea name="bio" rows="5" style="
                                width: 100%;
                                padding: 15px;
                                border: 1px solid #e8eef3;
                                border-radius: 10px;
                                font-size: 15px;
                                font-family: inherit;
                                resize: vertical;
                                transition: all 0.3s ease;
                            " placeholder="Viết vài dòng về bản thân bạn..." 
                            onfocus="this.style.borderColor='#5BC0DE'; this.style.boxShadow='0 0 0 3px rgba(91, 192, 222, 0.1)'"
                            onblur="this.style.borderColor='#e8eef3'; this.style.boxShadow='none'"><?php echo isset($profile['moTa']) ? htmlspecialchars($profile['moTa']) : ''; ?></textarea>
                        </div>
                    </section>

                    <!-- Submit buttons -->
                    <div style="text-align: center; margin: 30px 0; display: flex; gap: 15px; justify-content: center;">
                        <button type="submit" style="
                            background: linear-gradient(135deg, #FF6B9D 0%, #FF8DB4 100%);
                            color: white;
                            border: none;
                            padding: 15px 40px;
                            border-radius: 25px;
                            font-size: 16px;
                            font-weight: 600;
                            cursor: pointer;
                            box-shadow: 0 4px 15px rgba(255, 107, 157, 0.3);
                            transition: all 0.3s ease;
                        " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(255, 107, 157, 0.4)'" 
                           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(255, 107, 157, 0.3)'">
                            <i class="fas fa-save"></i>
                            Lưu thay đổi
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <style>
        /* Override profile.css for more compact layout */
        .info-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 14px;
            background: #ffffff;
            border: 1px solid #e8eef3;
            border-radius: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 1px 4px rgba(0,0,0,0.05);
        }
        
        .info-item:hover {
            border-color: #5BC0DE;
            box-shadow: 0 3px 10px rgba(91,192,222,0.15);
            transform: translateY(-1px);
        }
        
        .info-item i {
            color: #5BC0DE;
            font-size: 18px;
            margin-bottom: 2px;
        }
        
        .info-label {
            font-weight: 500;
            color: #888;
            font-size: 12px;
        }
        
        .info-value {
            padding: 8px 10px;
            border: 1px solid #e8eef3;
            border-radius: 7px;
            font-size: 14px;
            font-weight: 600;
            color: #2c3e50;
            background: white;
            font-family: inherit;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .info-value:focus {
            outline: none;
            border-color: #5BC0DE;
            box-shadow: 0 0 0 2px rgba(91, 192, 222, 0.1);
        }
        
        /* Interest tags styling */
        .interest-tag {
            padding: 10px 20px;
            border: 2px solid #E0E0E0;
            border-radius: 20px;
            background: white;
            color: #666;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .interest-tag:hover {
            border-color: #5BC0DE;
            color: #5BC0DE;
        }

        .interest-tag.active {
            background: #5BC0DE;
            border-color: #5BC0DE;
            color: white;
        }
        
        @media (max-width: 768px) {
            .info-list {
                grid-template-columns: 1fr;
            }
        }
    </style>

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
