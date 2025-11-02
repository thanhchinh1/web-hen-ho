<?php
require_once '../models/mSession.php';
require_once '../models/mProfile.php';
require_once '../models/mLike.php';
require_once '../models/mBlock.php';
require_once '../models/mUser.php';

Session::start();

header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!Session::isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
    exit;
}

// Chỉ chấp nhận POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$currentUserId = Session::getUserId();
$action = $_POST['action'] ?? '';

if ($action !== 'search') {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

// Lấy tham số tìm kiếm
$interests = [];
if (isset($_POST['interests']) && !empty($_POST['interests'])) {
    $decoded = json_decode($_POST['interests'], true);
    if (is_array($decoded)) {
        $interests = $decoded;
    }
}

$filters = [
    'gender' => $_POST['gender'] ?? '',
    'status' => $_POST['status'] ?? '',
    'purpose' => $_POST['purpose'] ?? '',
    'city' => $_POST['city'] ?? '',
    'interests' => $interests,
    'ageRange' => $_POST['ageRange'] ?? ''
];

try {
    $profileModel = new Profile();
    $likeModel = new Like();
    $blockModel = new Block();
    $userModel = new User();
    
    // Lấy danh sách người cần loại trừ
    $likedUserIds = $likeModel->getLikedUserIds($currentUserId);
    $whoLikedMeIds = $likeModel->getUserIdsWhoLikedMe($currentUserId);
    $blockedUserIds = $blockModel->getBlockedUserIds($currentUserId);
    $excludeIds = array_unique(array_merge([$currentUserId], $likedUserIds, $whoLikedMeIds, $blockedUserIds));
    
    // Tìm kiếm với filters
    $results = $profileModel->searchProfiles($filters, $excludeIds, 20);
    
    // Format kết quả
    $profiles = [];
    foreach ($results as $profile) {
        $age = $profileModel->calculateAge($profile['ngaySinh']);
        $avatarSrc = !empty($profile['avt']) ? $profile['avt'] : 'public/img/default-avatar.jpg';
        $isOnline = $userModel->isUserOnline($profile['maNguoiDung']);
        $lastActivity = $userModel->getLastActivity($profile['maNguoiDung']);
        
        // Tính thời gian hoạt động cuối
        $lastSeenText = '';
        if ($isOnline) {
            $lastSeenText = 'online';
        } elseif ($lastActivity && $lastActivity['minutesAgo'] !== null) {
            $minutes = $lastActivity['minutesAgo'];
            if ($minutes < 60) {
                $lastSeenText = $minutes . ' phút trước';
            } elseif ($minutes < 1440) {
                $lastSeenText = floor($minutes / 60) . ' giờ trước';
            } else {
                $lastSeenText = floor($minutes / 1440) . ' ngày trước';
            }
        }
        
        $profiles[] = [
            'id' => $profile['maNguoiDung'],
            'name' => $profile['ten'],
            'age' => $age,
            'gender' => $profile['gioiTinh'],
            'location' => $profile['noiSong'],
            'goal' => $profile['mucTieuPhatTrien'],
            'avatar' => $avatarSrc,
            'education' => $profile['hocVan'] ?? '',
            'status' => $profile['tinhTrangHonNhan'] ?? '',
            'isOnline' => $isOnline,
            'lastSeen' => $lastSeenText
        ];
    }
    
    echo json_encode([
        'success' => true,
        'count' => count($profiles),
        'profiles' => $profiles,
        'message' => count($profiles) > 0 ? 
            "Tìm thấy " . count($profiles) . " kết quả" : 
            "Không tìm thấy kết quả phù hợp"
    ]);
    
} catch (Exception $e) {
    error_log("Search error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra khi tìm kiếm!'
    ]);
}
?>
