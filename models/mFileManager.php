<?php
/**
 * File Manager Helper
 * Handle file uploads, deletion, validation
 */
class FileManager {
    private static $uploadDir = __DIR__ . '/../public/uploads/';
    
    /**
     * Upload avatar
     */
    public static function uploadAvatar($file, $userId, $oldAvatarPath = null) {
        $avatarDir = self::$uploadDir . 'avatars/';
        
        // Tạo thư mục nếu chưa có
        if (!is_dir($avatarDir)) {
            mkdir($avatarDir, 0777, true);
        }
        
        // Validate file
        $validation = self::validateImage($file);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => $validation['message']];
        }
        
        // Generate unique filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $newFileName = 'avatar_' . $userId . '_' . time() . '.' . $extension;
        $destination = $avatarDir . $newFileName;
        
        // Upload file
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $relativePath = 'public/uploads/avatars/' . $newFileName;
            
            // Xóa avatar cũ nếu có
            if ($oldAvatarPath) {
                self::deleteFile($oldAvatarPath);
            }
            
            return [
                'success' => true,
                'path' => $relativePath,
                'filename' => $newFileName
            ];
        }
        
        return ['success' => false, 'message' => 'Không thể upload file!'];
    }
    
    /**
     * Validate image file
     */
    public static function validateImage($file, $maxSize = 5242880) {
        // Check error
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'message' => 'Lỗi khi upload file!'];
        }
        
        // Check size (default 5MB)
        if ($file['size'] > $maxSize) {
            $maxSizeMB = $maxSize / 1048576;
            return ['valid' => false, 'message' => "Kích thước file không được vượt quá {$maxSizeMB}MB!"];
        }
        
        // Check extension
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $allowedExtensions)) {
            return ['valid' => false, 'message' => 'Chỉ chấp nhận file ảnh (jpg, jpeg, png, gif, webp)!'];
        }
        
        // Check MIME type
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedMimeTypes)) {
            return ['valid' => false, 'message' => 'File không phải là ảnh hợp lệ!'];
        }
        
        // Check image dimensions (optional)
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['valid' => false, 'message' => 'File không phải là ảnh!'];
        }
        
        list($width, $height) = $imageInfo;
        if ($width < 100 || $height < 100) {
            return ['valid' => false, 'message' => 'Ảnh phải có kích thước tối thiểu 100x100 pixels!'];
        }
        
        if ($width > 4000 || $height > 4000) {
            return ['valid' => false, 'message' => 'Ảnh không được vượt quá 4000x4000 pixels!'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Delete file
     */
    public static function deleteFile($relativePath) {
        if (empty($relativePath)) {
            return false;
        }
        
        // Convert relative path to absolute
        $absolutePath = __DIR__ . '/../' . $relativePath;
        
        // Check if file exists and delete
        if (file_exists($absolutePath)) {
            return unlink($absolutePath);
        }
        
        return false;
    }
    
    /**
     * Get file size in human readable format
     */
    public static function formatFileSize($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
    
    /**
     * Cleanup old files (orphaned files không có trong database)
     */
    public static function cleanupOrphanedAvatars() {
        require_once 'mDbconnect.php';
        
        $db = clsConnect::getInstance();
        $conn = $db->connect();
        
        // Get all avatar paths from database
        $stmt = $conn->prepare("SELECT DISTINCT avt FROM HoSo WHERE avt IS NOT NULL AND avt != ''");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $dbAvatars = [];
        while ($row = $result->fetch_assoc()) {
            $dbAvatars[] = basename($row['avt']);
        }
        
        // Get all files in avatars directory
        $avatarDir = self::$uploadDir . 'avatars/';
        $files = glob($avatarDir . '*');
        
        $deletedCount = 0;
        foreach ($files as $file) {
            $filename = basename($file);
            
            // Skip if in database
            if (in_array($filename, $dbAvatars)) {
                continue;
            }
            
            // Skip if file is less than 1 day old (might be in progress)
            if (time() - filemtime($file) < 86400) {
                continue;
            }
            
            // Delete orphaned file
            if (unlink($file)) {
                $deletedCount++;
            }
        }
        
        return $deletedCount;
    }
    
    /**
     * Resize image (optional - requires GD library)
     */
    public static function resizeImage($sourcePath, $destinationPath, $maxWidth = 800, $maxHeight = 800) {
        // Check if GD library is available
        if (!extension_loaded('gd')) {
            return false;
        }
        
        // Get image info
        $imageInfo = getimagesize($sourcePath);
        if ($imageInfo === false) {
            return false;
        }
        
        list($origWidth, $origHeight, $imageType) = $imageInfo;
        
        // Calculate new dimensions
        $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight);
        
        // If image is smaller than max, don't resize
        if ($ratio >= 1) {
            return copy($sourcePath, $destinationPath);
        }
        
        $newWidth = intval($origWidth * $ratio);
        $newHeight = intval($origHeight * $ratio);
        
        // Create image from source
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            default:
                return false;
        }
        
        // Create new image
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG and GIF
        if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_GIF) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
        }
        
        // Resize
        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
        
        // Save image
        $result = false;
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $result = imagejpeg($newImage, $destinationPath, 85);
                break;
            case IMAGETYPE_PNG:
                $result = imagepng($newImage, $destinationPath, 8);
                break;
            case IMAGETYPE_GIF:
                $result = imagegif($newImage, $destinationPath);
                break;
        }
        
        // Free memory
        imagedestroy($sourceImage);
        imagedestroy($newImage);
        
        return $result;
    }
}
?>
