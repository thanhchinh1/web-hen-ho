<?php
require_once '../../models/mSession.php';

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
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thiết lập Hồ sơ Cá nhân - Kết Nối Yêu Thương</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/thietlaphoso.css?v=<?php echo uniqid(); ?>">
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="header-container">
            <a href="../trangchu/index.php" class="logo">
                <img src="../../public/img/logo.jpg" alt="DuyenHub Logo">
                <span class="logo-text">DuyenHub</span>
            </a>
            <nav class="header-nav">
                <a href="#" class="nav-link" onclick="confirmLogout(event)">
                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                </a>
            </nav>
        </div>
    </header>

    <div class="profile-setup-wrapper">
        <div class="profile-setup-container">
            <div class="profile-setup-header">
                <h1>Thiết lập Hồ sơ Cá nhân</h1>
                <p>Cập nhật thông tin của bạn để chúng tôi có thể cung cấp trải nghiệm tốt hơn</p>
            </div>

                <!-- Avatar Upload Section -->
                <div class="avatar-section">
                    <div class="avatar-preview" id="avatarPreview">
                        <img src="" alt="" id="avatarImage">
                        <div class="avatar-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                    <button type="button" class="btn-upload-avatar" onclick="document.getElementById('avatarInput').click()">
                        <i class="fas fa-camera"></i>
                        Tải ảnh lên
                    </button>
                    <input type="file" id="avatarInput" accept="image/*" style="display: none;" onchange="openAvatarEditor(event)">
                    <p class="avatar-hint">Kích thước tối đa: 5MB</p>
                </div>

                <!-- Avatar Editor Modal -->
                <div id="avatarEditorModal" class="avatar-editor-modal" style="display: none;">
                    <div class="avatar-editor-content">
                        <div class="avatar-editor-header">
                            <h3>Chỉnh vị trí ảnh đại diện</h3>
                            <button type="button" class="close-modal" onclick="closeAvatarEditor()">&times;</button>
                        </div>
                        <div class="avatar-editor-body">
                            <div class="avatar-crop-container" id="avatarCropContainer">
                                <img src="" alt="" id="avatarCropImage">
                            </div>
                            <div class="avatar-controls">
                                <div class="control-group">
                                    <label><i class="fas fa-search-minus"></i> Zoom</label>
                                    <input type="range" id="zoomSlider" min="1" max="3" step="0.1" value="1">
                                </div>
                            </div>
                        </div>
                        <div class="avatar-editor-footer">
                            <button type="button" class="btn-cancel" onclick="closeAvatarEditor()">Hủy</button>
                            <button type="button" class="btn-apply" onclick="applyCrop()">Áp dụng</button>
                        </div>
                    </div>
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

                            <div class="form-group">
                                <label for="maritalStatus">Tình trạng hôn nhân</label>
                                <select id="maritalStatus" name="maritalStatus" required>
                                    <option value="">Chọn tình trạng</option>
                                    <option value="Độc thân">Độc thân</option>
                                    <option value="Đã ly hôn">Đã ly hôn</option>
                                    <option value="Mẹ đơn thân">Mẹ đơn thân</option>
                                    <option value="Cha đơn thân">Cha đơn thân</option>
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
                                    <option value="Kết bạn">Kết bạn</option>
                                    <option value="Tìm hiểu">Tìm hiểu</option> 
                                    <option value="Hẹn hò">Hẹn hò</option>  
                                    <option value="Kết hôn">Kết hôn</option>  
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

                        <div class="form-row">
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
                                    <button type="button" class="interest-tag" data-interest="Khiêu vũ">Khiêu vũ</button>
                                    <button type="button" class="interest-tag" data-interest="Ca hát">Ca hát</button>
                                    <button type="button" class="interest-tag" data-interest="Tập gym">Tập gym</button>
                                    <button type="button" class="interest-tag" data-interest="Bơi lội">Bơi lội</button>
                                    <button type="button" class="interest-tag" data-interest="Leo núi">Leo núi</button>
                                    <button type="button" class="interest-tag" data-interest="Cắm trại">Cắm trại</button>
                                    <button type="button" class="interest-tag" data-interest="Mua sắm">Mua sắm</button>
                                    <button type="button" class="interest-tag" data-interest="Thời trang">Thời trang</button>
                                    <button type="button" class="interest-tag" data-interest="Viết lách">Viết lách</button>
                                    <button type="button" class="interest-tag" data-interest="Thủ công mỹ nghệ">Thủ công mỹ nghệ</button>
                                    <button type="button" class="interest-tag" data-interest="Chơi nhạc cụ">Chơi nhạc cụ</button>
                                    <button type="button" class="interest-tag" data-interest="Xem thể thao">Xem thể thao</button>
                                    <button type="button" class="interest-tag" data-interest="Tình nguyện">Tình nguyện</button>
                                    <button type="button" class="interest-tag" data-interest="Sưu tầm">Sưu tầm</button>
                                    <button type="button" class="interest-tag" data-interest="Nghiên cứu khoa học">Nghiên cứu khoa học</button>
                                    <button type="button" class="interest-tag" data-interest="Chơi cờ">Chơi cờ</button>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="description">Mô tả</label>
                                <textarea id="description" name="description" rows="5" maxlength="500" placeholder="Viết đôi nét về bản thân bạn..." required></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="form-actions">
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-check-circle"></i> Lưu Thông tin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Avatar editor state
        let currentFile = null;
        let cropData = {
            scale: 1,
            x: 0,
            y: 0
        };
        let isDragging = false;
        let startPos = { x: 0, y: 0 };
        let croppedBlob = null;

        // Go back function
        function goBack() {
            if (confirm('Bạn có chắc muốn quay lại? Các thay đổi chưa lưu sẽ bị mất.')) {
                window.location.href = '../trangchu/index.php';
            }
        }

        // Open avatar editor modal
        function openAvatarEditor(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Validate file size
            if (file.size > 5000000) {
                showNotification('Kích thước file không được vượt quá 5MB!', 'error');
                event.target.value = '';
                return;
            }

            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                showNotification('Chỉ chấp nhận file ảnh (jpg, jpeg, png, gif)!', 'error');
                event.target.value = '';
                return;
            }

            currentFile = file;
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('avatarCropImage');
                img.src = e.target.result;
                
                // Reset crop data
                cropData = { scale: 1, x: 0, y: 0 };
                document.getElementById('zoomSlider').value = 1;
                
                // Show modal
                document.getElementById('avatarEditorModal').style.display = 'flex';
                
                // Initialize drag handlers
                setTimeout(() => initializeDragHandlers(), 100);
            };
            reader.readAsDataURL(file);
        }

        // Initialize drag handlers for image positioning
        function initializeDragHandlers() {
            const container = document.getElementById('avatarCropContainer');
            const img = document.getElementById('avatarCropImage');

            img.style.transform = `scale(${cropData.scale}) translate(${cropData.x}px, ${cropData.y}px)`;

            container.addEventListener('mousedown', startDrag);
            container.addEventListener('touchstart', startDrag);
            document.addEventListener('mousemove', drag);
            document.addEventListener('touchmove', drag);
            document.addEventListener('mouseup', endDrag);
            document.addEventListener('touchend', endDrag);

            // Zoom slider
            document.getElementById('zoomSlider').addEventListener('input', function(e) {
                cropData.scale = parseFloat(e.target.value);
                updateImageTransform();
            });
        }

        function startDrag(e) {
            e.preventDefault();
            isDragging = true;
            const clientX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
            const clientY = e.type.includes('touch') ? e.touches[0].clientY : e.clientY;
            startPos = { x: clientX - cropData.x, y: clientY - cropData.y };
        }

        function drag(e) {
            if (!isDragging) return;
            e.preventDefault();
            const clientX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
            const clientY = e.type.includes('touch') ? e.touches[0].clientY : e.clientY;
            cropData.x = clientX - startPos.x;
            cropData.y = clientY - startPos.y;
            updateImageTransform();
        }

        function endDrag() {
            isDragging = false;
        }

        function updateImageTransform() {
            const img = document.getElementById('avatarCropImage');
            img.style.transform = `scale(${cropData.scale}) translate(${cropData.x}px, ${cropData.y}px)`;
        }

        // Close avatar editor
        function closeAvatarEditor() {
            // Cảnh báo nếu có file được chọn nhưng chưa crop
            if (currentFile && !croppedBlob) {
                const confirmClose = confirm('Bạn chưa nhấn "Áp dụng" để lưu ảnh đã chỉnh. Bạn có chắc muốn hủy?');
                if (!confirmClose) {
                    return; // Không đóng modal
                }
            }
            
            document.getElementById('avatarEditorModal').style.display = 'none';
            document.getElementById('avatarInput').value = '';
            currentFile = null;
            // Không reset croppedBlob vì người dùng có thể đã crop rồi
        }

        // Apply crop and set preview
        async function applyCrop() {
            const img = document.getElementById('avatarCropImage');
            const container = document.getElementById('avatarCropContainer');
            
            // Create canvas to crop image
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            
            // Set canvas size to container size (circular preview size)
            const size = 400; // High resolution for better quality
            canvas.width = size;
            canvas.height = size;
            
            // Calculate source dimensions
            const rect = container.getBoundingClientRect();
            const imgRect = img.getBoundingClientRect();
            
            // Draw image with transformations
            const sourceX = (rect.left - imgRect.left) * (img.naturalWidth / imgRect.width);
            const sourceY = (rect.top - imgRect.top) * (img.naturalHeight / imgRect.height);
            const sourceWidth = rect.width * (img.naturalWidth / imgRect.width);
            const sourceHeight = rect.height * (img.naturalHeight / imgRect.height);
            
            ctx.drawImage(
                img,
                sourceX, sourceY, sourceWidth, sourceHeight,
                0, 0, size, size
            );
            
            // Convert to blob
            canvas.toBlob(function(blob) {
                croppedBlob = blob;
                
                // Update preview
                const previewImg = document.getElementById('avatarImage');
                previewImg.src = URL.createObjectURL(blob);
                
                // Close modal
                closeAvatarEditor();
                
                showNotification('Đã cập nhật ảnh đại diện!', 'success');
            }, 'image/jpeg', 0.95);
        }

        // Preview avatar before upload (old function - now handled by editor)
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
            
            // Kiểm tra avatar đã upload và crop chưa
            if (!croppedBlob) {
                showNotification('Vui lòng tải ảnh lên và nhấn nút "Áp dụng" trong cửa sổ chỉnh ảnh!', 'error');
                // Scroll to avatar section
                document.querySelector('.avatar-section').scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            
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
            // Use cropped image instead of original
            formData.append('avatar', croppedBlob, 'avatar.jpg');
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
            fetch('../../controller/cProfile_setup.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    setTimeout(() => {
                        // Sử dụng redirect từ server nếu có, nếu không thì mặc định về trang chủ
                        const redirectUrl = data.redirect || '../trangchu/index.php';
                        window.location.href = redirectUrl;
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
    <script>
    function confirmLogout(e) {
        e.preventDefault();
        if (confirm('Bạn có chắc chắn muốn đăng xuất không?')) {
            window.location.href = '../../controller/cLogout.php';
        }
    }
    </script>
</body>
</html>
