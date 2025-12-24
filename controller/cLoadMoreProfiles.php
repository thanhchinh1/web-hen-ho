<?php
require_once '../models/mSession.php';
require_once '../models/mProfile.php';
require_once '../models/mLike.php';
require_once '../models/mBlock.php';
require_once '../models/mMatch.php';
require_once '../models/mUser.php';

Session::start();

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
$count = intval($_POST['count'] ?? 1); // Số lượng hồ sơ cần lấy

// Giới hạn số lượng
if ($count < 1 || $count > 10) {
    $count = 1;
}

$profileModel = new Profile();
$likeModel = new Like();
$blockModel = new Block();
$matchModel = new MatchModel();
$userModel = new User();

// Lấy danh sách ID cần loại trừ
$likedUserIds = $likeModel->getLikedUserIds($currentUserId);
$whoLikedMeIds = $likeModel->getUserIdsWhoLikedMe($currentUserId);
$blockedUserIds = $blockModel->getBlockedUserIds($currentUserId);
$whoBlockedMeIds = $blockModel->getUserIdsWhoBlockedMe($currentUserId);
$myMatches = $matchModel->getMyMatches($currentUserId);
$matchedUserIds = array_map(function($match) {
    return $match['maNguoiDung'];
}, $myMatches);

// Kết hợp và thêm chính mình vào danh sách loại trừ (bao gồm cả người đã chặn và bị chặn)
$excludeIds = array_unique(array_merge(
    [$currentUserId], 
    $likedUserIds, 
    $whoLikedMeIds, 
    $blockedUserIds, 
    $whoBlockedMeIds, 
    $matchedUserIds
));

// Lấy thêm hồ sơ mới
$newProfiles = $profileModel->getAllProfiles($count, 0, $excludeIds);

if (empty($newProfiles)) {
    echo json_encode(['success' => true, 'profiles' => [], 'message' => 'Không còn hồ sơ mới']);
    exit;
}

// Format dữ liệu hồ sơ để trả về
$formattedProfiles = [];
foreach ($newProfiles as $profile) {
    $age = $profileModel->calculateAge($profile['ngaySinh']);
    $avatarSrc = !empty($profile['avt']) ? $profile['avt'] : 'public/img/default-avatar.jpg';
    $isOnline = $userModel->isUserOnline($profile['maNguoiDung']);
    $isInactive = $userModel->isUserInactive($profile['maNguoiDung']);
    $lastActivity = $userModel->getLastActivity($profile['maNguoiDung']);
    
    $lastSeenText = '';
    if ($isOnline) {
        $lastSeenText = '<p class="last-seen online"><i class="fas fa-circle"></i> Đang hoạt động</p>';
    } elseif ($isInactive) {
        $lastSeenText = '<p class="last-seen inactive"><i class="fas fa-circle"></i> Không hoạt động</p>';
    } elseif ($lastActivity && $lastActivity['minutesAgo'] !== null) {
        $minutes = $lastActivity['minutesAgo'];
        if ($minutes < 60) {
            $timeText = $minutes . ' phút trước';
        } elseif ($minutes < 1440) {
            $timeText = floor($minutes / 60) . ' giờ trước';
        } else {
            $timeText = floor($minutes / 1440) . ' ngày trước';
        }
        $lastSeenText = '<p class="last-seen"><i class="far fa-clock"></i> ' . $timeText . '</p>';
    }
    
    $formattedProfiles[] = [
        'maNguoiDung' => $profile['maNguoiDung'],
        'ten' => htmlspecialchars($profile['ten']),
        'tuoi' => $age,
        'noiSong' => htmlspecialchars($profile['noiSong']),
        'mucTieuPhatTrien' => htmlspecialchars($profile['mucTieuPhatTrien']),
        'avt' => $avatarSrc,
        'isOnline' => $isOnline,
        'isInactive' => $isInactive,
        'lastSeenText' => $lastSeenText
    ];
}

echo json_encode(['success' => true, 'profiles' => $formattedProfiles]);
?>
