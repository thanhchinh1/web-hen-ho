-- Migration: Thêm cột ngayTao vào bảng NguoiDung
-- File: add_created_date_to_users.sql
-- Mục đích: Theo dõi ngày tạo tài khoản để có thống kê chính xác hơn

-- Kiểm tra và thêm cột ngayTao vào bảng NguoiDung
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'NguoiDung' 
  AND COLUMN_NAME = 'ngayTao';

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `NguoiDung` ADD COLUMN `ngayTao` DATETIME DEFAULT CURRENT_TIMESTAMP AFTER `trangThaiNguoiDung`',
    'SELECT "Column ngayTao already exists in NguoiDung" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Cập nhật ngayTao cho các user hiện có (set về 30 ngày trước)
UPDATE `NguoiDung` 
SET `ngayTao` = DATE_SUB(NOW(), INTERVAL 30 DAY)
WHERE `ngayTao` IS NULL;

-- Sau khi chạy migration này, cập nhật lại mAdmin.php:
-- Đổi dòng:
--   $stats['newUsersToday'] = 0;
-- Thành:
--   $result = $this->conn->query("
--       SELECT COUNT(*) as total FROM NguoiDung 
--       WHERE DATE(ngayTao) = CURDATE()
--   ");
--   $stats['newUsersToday'] = $result->fetch_assoc()['total'] ?? 0;

SELECT 'Migration completed successfully!' AS message;
