-- Migration: Cập nhật hệ thống trạng thái tin nhắn đầy đủ
-- Date: 2025-12-24
-- Description: Thêm các trạng thái: sending, sent, delivered, seen, failed, recalled

-- 1. Thêm các cột mới vào bảng tinnhan
ALTER TABLE `tinnhan` 
ADD COLUMN IF NOT EXISTS `trangThai` ENUM('sending', 'sent', 'delivered', 'seen', 'failed', 'recalled') 
    DEFAULT 'sent' AFTER `noiDung`,
ADD COLUMN IF NOT EXISTS `thoiDiemNhan` DATETIME NULL AFTER `trangThai`,
ADD COLUMN IF NOT EXISTS `thoiDiemXem` DATETIME NULL AFTER `thoiDiemNhan`,
ADD COLUMN IF NOT EXISTS `thuHoiLuc` DATETIME NULL AFTER `thoiDiemXem`,
ADD COLUMN IF NOT EXISTS `loiGanNhat` VARCHAR(255) NULL AFTER `thuHoiLuc`;

-- 2. Cập nhật dữ liệu cũ: tất cả tin nhắn cũ được coi là 'seen'
UPDATE `tinnhan` SET `trangThai` = 'seen' WHERE `trangThai` IS NULL OR `trangThai` = '';

-- 3. Tạo bảng typing_status để theo dõi trạng thái "đang soạn"
CREATE TABLE IF NOT EXISTS `typing_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `maGhepDoi` int(11) NOT NULL,
  `maNguoiDung` int(11) NOT NULL,
  `isTyping` tinyint(1) DEFAULT 0,
  `lastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_typing` (`maGhepDoi`, `maNguoiDung`),
  KEY `idx_match` (`maGhepDoi`),
  KEY `idx_user` (`maNguoiDung`),
  FOREIGN KEY (`maGhepDoi`) REFERENCES `ghepdoi`(`maGhepDoi`) ON DELETE CASCADE,
  FOREIGN KEY (`maNguoiDung`) REFERENCES `nguoidung`(`maNguoiDung`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Thêm index cho performance
ALTER TABLE `tinnhan` 
ADD INDEX IF NOT EXISTS `idx_status` (`trangThai`),
ADD INDEX IF NOT EXISTS `idx_match_sender_status` (`maGhepDoi`, `maNguoiGui`, `trangThai`);

-- 5. Cập nhật thời gian xem cho tin nhắn cũ
UPDATE `tinnhan` 
SET `thoiDiemXem` = `thoiDiemGui` 
WHERE `trangThai` = 'seen' AND `thoiDiemXem` IS NULL;

COMMIT;
