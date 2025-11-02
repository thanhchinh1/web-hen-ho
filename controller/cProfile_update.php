<?php
require_once '../models/mSession.php';
require_once '../models/mProfile.php';

Session::start();

// Kiểm tra đăng nhập
if (!Session::isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
    exit;
}

$userId = Session::getUserId();
$profile = new Profile();

// Kiểm tra request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Validate các trường bắt buộc (không bắt buộc avatar khi chỉnh sửa)
$requiredFields = ['fullName', 'gender', 'day', 'month', 'year', 'maritalStatus', 
                   'goal', 'education', 'location'];

foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin!']);
        exit;
    }
}

// Xử lý upload avatar (không bắt buộc khi chỉnh sửa)
$avatarPath = null;
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../public/uploads/avatars/';
    
    // Tạo thư mục nếu chưa tồn tại
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $file = $_FILES['avatar'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    
    // Lấy extension
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    // Validate file
    if (!in_array($fileExt, $allowedExtensions)) {
        echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận file ảnh (jpg, jpeg, png, gif)!']);
        exit;
    }
    
    if ($fileSize > 5000000) { // 5MB
        echo json_encode(['success' => false, 'message' => 'Kích thước file không được vượt quá 5MB!']);
        exit;
    }
    
    // Xóa ảnh cũ nếu có
    $oldProfile = $profile->getProfile($userId);
    if ($oldProfile && !empty($oldProfile['avt'])) {
        $oldAvatarPath = __DIR__ . '/../' . $oldProfile['avt'];
        if (file_exists($oldAvatarPath) && is_file($oldAvatarPath)) {
            // Không xóa default avatar
            if (strpos($oldProfile['avt'], 'default-avatar') === false) {
                @unlink($oldAvatarPath); // @ để suppress warning nếu không xóa được
            }
        }
    }
    
    // Tạo tên file unique
    $newFileName = 'avatar_' . $userId . '_' . time() . '.' . $fileExt;
    $fileDestination = $uploadDir . $newFileName;
    
    if (move_uploaded_file($fileTmpName, $fileDestination)) {
        $avatarPath = 'public/uploads/avatars/' . $newFileName;
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi khi upload ảnh!']);
        exit;
    }
}

// Chuẩn bị dữ liệu
$ngaySinh = $_POST['year'] . '-' . $_POST['month'] . '-' . $_POST['day'];

// Xử lý sở thích
$soThich = isset($_POST['interests']) ? $_POST['interests'] : '';

// Kiểm tra sở thích không được bỏ trống
if (empty(trim($soThich))) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn ít nhất một sở thích!']);
    exit;
}

$data = [
    'ten' => trim($_POST['fullName']),
    'ngaySinh' => $ngaySinh,
    'gioiTinh' => $_POST['gender'],
    'tinhTrangHonNhan' => $_POST['maritalStatus'],
    'canNang' => isset($_POST['weight']) && !empty($_POST['weight']) ? floatval($_POST['weight']) : null,
    'chieuCao' => isset($_POST['height']) && !empty($_POST['height']) ? floatval($_POST['height']) : null,
    'mucTieuPhatTrien' => $_POST['goal'],
    'hocVan' => $_POST['education'],
    'noiSong' => $_POST['location'],
    'soThich' => $soThich,
    'moTa' => isset($_POST['bio']) && !empty(trim($_POST['bio'])) ? trim($_POST['bio']) : ''
];

// Kiểm tra xem đã có hồ sơ chưa
if (!$profile->hasProfile($userId)) {
    echo json_encode(['success' => false, 'message' => 'Bạn chưa có hồ sơ!']);
    exit;
}

// Cập nhật hồ sơ
if ($profile->updateProfile($userId, $data, $avatarPath)) {
    echo json_encode(['success' => true, 'message' => 'Cập nhật hồ sơ thành công!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại!']);
}
?>
