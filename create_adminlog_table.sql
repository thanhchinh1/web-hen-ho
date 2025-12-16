-- Tạo bảng AdminLog để lưu lịch sử hoạt động của admin
CREATE TABLE IF NOT EXISTS `adminlog` (
  `maLog` int(11) NOT NULL AUTO_INCREMENT,
  `maAdmin` int(11) NOT NULL,
  `hanhDong` varchar(100) NOT NULL,
  `chiTiet` text DEFAULT NULL,
  `ipAddress` varchar(45) DEFAULT NULL,
  `thoiGian` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`maLog`),
  KEY `idx_admin` (`maAdmin`),
  KEY `idx_thoigian` (`thoiGian`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
