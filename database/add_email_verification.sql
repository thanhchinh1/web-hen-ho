-- Migration: Tạo bảng email_verifications
-- Lưu trữ mã OTP xác thực email

CREATE TABLE IF NOT EXISTS `email_verifications` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(100) NOT NULL COMMENT 'Email hoặc SĐT cần xác thực',
  `otp_code` VARCHAR(10) NOT NULL COMMENT 'Mã OTP (6 số)',
  `password_hash` VARCHAR(255) NOT NULL COMMENT 'Mật khẩu đã hash (lưu tạm)',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời gian tạo OTP',
  `expires_at` TIMESTAMP NOT NULL COMMENT 'Thời gian hết hạn OTP',
  `attempts` INT(2) NOT NULL DEFAULT 0 COMMENT 'Số lần nhập sai',
  `is_verified` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0: Chưa xác thực, 1: Đã xác thực',
  `verified_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Thời gian xác thực thành công',
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_otp_code` (`otp_code`),
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lưu OTP xác thực email';

-- Migration: Thêm cột email_verified vào bảng nguoidung
ALTER TABLE `nguoidung` 
ADD COLUMN `email_verified` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0: Chưa xác thực, 1: Đã xác thực email' 
AFTER `matKhau`;

-- Thêm index cho cột email_verified
ALTER TABLE `nguoidung` 
ADD KEY `idx_email_verified` (`email_verified`);

-- Cập nhật tất cả user cũ thành đã xác thực (để họ vẫn đăng nhập được)
UPDATE `nguoidung` SET `email_verified` = 1 WHERE `email_verified` = 0;
