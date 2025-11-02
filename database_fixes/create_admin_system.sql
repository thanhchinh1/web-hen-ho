-- Script tạo hệ thống Admin
-- Chạy script này trong MySQL Workbench hoặc phpMyAdmin

-- Bảng Admin
CREATE TABLE IF NOT EXISTS `Admin` (
  `maAdmin` int(11) NOT NULL AUTO_INCREMENT,
  `tenDangNhap` varchar(50) NOT NULL UNIQUE,
  `matKhau` varchar(255) NOT NULL,
  `hoTen` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `soDienThoai` varchar(20) DEFAULT NULL,
  `vaiTro` ENUM('super_admin', 'moderator', 'support') DEFAULT 'moderator',
  `trangThai` ENUM('active', 'inactive') DEFAULT 'active',
  `lanDangNhapCuoi` datetime DEFAULT NULL,
  `ngayTao` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`maAdmin`),
  KEY `idx_username` (`tenDangNhap`),
  KEY `idx_role` (`vaiTro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm admin mặc định (username: admin, password: admin123)
-- Password được hash bằng password_hash() của PHP
INSERT INTO `Admin` (`tenDangNhap`, `matKhau`, `hoTen`, `email`, `vaiTro`) 
VALUES ('admin', '$2y$10$3euPcmQFCiblsZeEQBYSnu3fWfXjHpjCx/0BV6RWJ8lx8K7VV.Z9e', 'Super Admin', 'admin@webhenho.com', 'super_admin')
ON DUPLICATE KEY UPDATE matKhau = VALUES(matKhau);

-- Bảng AdminLog (Lịch sử hoạt động admin)
CREATE TABLE IF NOT EXISTS `AdminLog` (
  `maLog` int(11) NOT NULL AUTO_INCREMENT,
  `maAdmin` int(11) DEFAULT NULL,
  `hanhDong` varchar(255) DEFAULT NULL,
  `chiTiet` text DEFAULT NULL,
  `ipAddress` varchar(50) DEFAULT NULL,
  `thoiGian` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`maLog`),
  KEY `idx_admin` (`maAdmin`),
  KEY `idx_time` (`thoiGian`),
  FOREIGN KEY (`maAdmin`) REFERENCES `Admin`(`maAdmin`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cập nhật bảng BaoCao thêm admin xử lý
-- Kiểm tra cột trangThai đã tồn tại chưa
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'BaoCao' 
  AND COLUMN_NAME = 'trangThai';

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `BaoCao` ADD COLUMN `trangThai` ENUM(''pending'', ''reviewing'', ''resolved'', ''rejected'') DEFAULT ''pending'' AFTER loaiBaoCao', 
    'SELECT "Column trangThai already exists" AS msg');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Thêm các cột admin
ALTER TABLE `BaoCao`
ADD COLUMN IF NOT EXISTS `maAdminXuLy` int(11) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `ghiChuAdmin` text DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `thoiDiemXuLy` datetime DEFAULT NULL;

-- Thêm foreign key (nếu chưa có)
SET @fk_exists = 0;
SELECT COUNT(*) INTO @fk_exists
FROM information_schema.TABLE_CONSTRAINTS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'BaoCao'
  AND CONSTRAINT_NAME = 'fk_admin_report';

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE `BaoCao` ADD CONSTRAINT `fk_admin_report` FOREIGN KEY (`maAdminXuLy`) REFERENCES `Admin`(`maAdmin`) ON DELETE SET NULL',
    'SELECT "Foreign key fk_admin_report already exists" AS msg');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Index tối ưu (nếu chưa có)
SET @idx_exists = 0;
SELECT COUNT(*) INTO @idx_exists
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'BaoCao'
  AND INDEX_NAME = 'idx_report_status';

SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_report_status ON BaoCao(trangThai, thoiDiemBaoCao)',
    'SELECT "Index idx_report_status already exists" AS msg');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Bảng thống kê admin (tùy chọn)
CREATE TABLE IF NOT EXISTS `ThongKeNgay` (
  `maThongKe` int(11) NOT NULL AUTO_INCREMENT,
  `ngay` date NOT NULL UNIQUE,
  `soNguoiDungMoi` int(11) DEFAULT 0,
  `soGhepDoiMoi` int(11) DEFAULT 0,
  `soTinNhan` int(11) DEFAULT 0,
  `soBaoCao` int(11) DEFAULT 0,
  `ngayTao` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`maThongKe`),
  KEY `idx_date` (`ngay`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
