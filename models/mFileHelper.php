<?php
/**
 * File Helper Class
 * Quản lý upload, delete, validate files
 */
class FileHelper {
    
    /**
     * Validate image file
     */
    public static function validateImage($file, $maxSize = 5000000) {
        $errors = [];
        
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'errors' => ['Lỗi upload file!']];
        }
        
        // Check extension
        $fileName = $file['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($fileExt, $allowedExtensions)) {
            $errors[] = 'Chỉ chấp nhận file ảnh: ' . implode(', ', $allowedExtensions);
        }
        
        // Check size
        if ($file['size'] > $maxSize) {
            $sizeMB = $maxSize / 1000000;
            $errors[] = "Kích thước file không được vượt quá {$sizeMB}MB!";
        }
        
        // Check if it's actually an image
        $mimeType = mime_content_type($file['tmp_name']);
        $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($mimeType, $allowedMimeTypes)) {
            $errors[] = 'File không phải là ảnh hợp lệ!';
        }
        
        // Check image dimensions (optional)
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            $errors[] = 'File bị lỗi hoặc không phải ảnh!';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'extension' => $fileExt ?? null,
            'size' => $file['size'] ?? 0,
            'mime' => $mimeType ?? null
        ];
    }
    
    /**
     * Upload avatar và xóa avatar cũ
     */
    public static function uploadAvatar($file, $userId, $oldAvatarPath = null) {
        // Validate
        $validation = self::validateImage($file);
        if (!$validation['valid']) {
            return ['success' => false, 'errors' => $validation['errors']];
        }
        
        // Setup upload directory
        $uploadDir = __DIR__ . '/../public/uploads/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Xóa avatar cũ nếu có
        if ($oldAvatarPath) {
            self::deleteFile($oldAvatarPath);
        }
        
        // Generate unique filename
        $extension = $validation['extension'];
        $newFileName = 'avatar_' . $userId . '_' . time() . '_' . uniqid() . '.' . $extension;
        $fileDestination = $uploadDir . $newFileName;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $fileDestination)) {
            return [
                'success' => true,
                'path' => 'public/uploads/avatars/' . $newFileName,
                'filename' => $newFileName
            ];
        }
        
        return ['success' => false, 'errors' => ['Lỗi khi lưu file!']];
    }
    
    /**
     * Delete file an toàn
     */
    public static function deleteFile($relativePath) {
        if (empty($relativePath)) {
            return false;
        }
        
        // Không xóa default avatar
        if (strpos($relativePath, 'default-avatar') !== false) {
            return false;
        }
        
        $fullPath = __DIR__ . '/../' . $relativePath;
        
        // Check file exists và nằm trong thư mục cho phép
        if (file_exists($fullPath) && is_file($fullPath)) {
            // Security: Đảm bảo file nằm trong thư mục uploads
            $realPath = realpath($fullPath);
            $uploadsPath = realpath(__DIR__ . '/../public/uploads/');
            
            if ($realPath && $uploadsPath && strpos($realPath, $uploadsPath) === 0) {
                return @unlink($realPath);
            }
        }
        
        return false;
    }
    
    /**
     * Cleanup old unused avatars
     * Chạy định kỳ để xóa file không được dùng
     */
    public static function cleanupOldAvatars($daysOld = 30) {
        require_once __DIR__ . '/mDbconnect.php';
        
        $db = clsConnect::getInstance();
        $conn = $db->connect();
        
        // Lấy danh sách avatars đang được dùng
        $stmt = $conn->prepare("SELECT DISTINCT avt FROM HoSo WHERE avt IS NOT NULL");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $usedAvatars = [];
        while ($row = $result->fetch_assoc()) {
            $filename = basename($row['avt']);
            $usedAvatars[] = $filename;
        }
        
        // Scan thư mục avatars
        $avatarDir = __DIR__ . '/../public/uploads/avatars/';
        $files = glob($avatarDir . '*');
        
        $deletedCount = 0;
        $cutoffTime = time() - ($daysOld * 24 * 60 * 60);
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $filename = basename($file);
                
                // Bỏ qua default avatar
                if (strpos($filename, 'default') !== false) {
                    continue;
                }
                
                // Kiểm tra file có đang được dùng không
                if (!in_array($filename, $usedAvatars)) {
                    // Kiểm tra file cũ hơn X ngày
                    if (filemtime($file) < $cutoffTime) {
                        if (@unlink($file)) {
                            $deletedCount++;
                        }
                    }
                }
            }
        }
        
        return $deletedCount;
    }
    
    /**
     * Get file size in human readable format
     */
    public static function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
?>
