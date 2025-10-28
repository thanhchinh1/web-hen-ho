<?php
session_start();
require_once '../models/mProfile.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
    exit;
}

$userId = $_SESSION['user_id'];
$profile = new Profile();

// Kiểm tra request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Validate các trường bắt buộc
$requiredFields = ['fullName', 'gender', 'day', 'month', 'year', 'maritalStatus', 
                   'weight', 'height', 'goal', 'education', 'location', 'description'];

foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin!']);
        exit;
    }
}

// Kiểm tra upload avatar (bắt buộc khi thiết lập lần đầu)
if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng tải lên ảnh đại diện!']);
    exit;
}

// Xử lý upload avatar
$avatarPath = null;
$uploadDir = '../public/uploads/avatars/';

// Tạo thư mục nếu chưa tồn tại
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$file = $_FILES['avatar'];
$fileName = $file['name'];
$fileTmpName = $file['tmp_name'];
$fileSize = $file['size'];
$fileError = $file['error'];

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

// Tạo tên file unique
$newFileName = 'avatar_' . $userId . '_' . time() . '.' . $fileExt;
$fileDestination = $uploadDir . $newFileName;

if (move_uploaded_file($fileTmpName, $fileDestination)) {
    $avatarPath = 'public/uploads/avatars/' . $newFileName;
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi khi upload ảnh!']);
    exit;
}

// Chuẩn bị dữ liệu
$ngaySinh = $_POST['year'] . '-' . $_POST['month'] . '-' . $_POST['day'];

// Xử lý sở thích (nếu có)
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
    'canNang' => floatval($_POST['weight']),
    'chieuCao' => floatval($_POST['height']),
    'mucTieuPhatTrien' => $_POST['goal'],
    'hocVan' => $_POST['education'],
    'noiSong' => $_POST['location'],
    'soThich' => $soThich,
    'moTa' => trim($_POST['description'])
];

// Kiểm tra xem đã có hồ sơ chưa
if ($profile->hasProfile($userId)) {
    echo json_encode(['success' => false, 'message' => 'Bạn đã có hồ sơ rồi!']);
    exit;
}

// Tạo hồ sơ mới
if ($profile->createProfile($userId, $data, $avatarPath)) {
    echo json_encode(['success' => true, 'message' => 'Thiết lập hồ sơ thành công!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại!']);
}
?>
