-- Script thêm cột loaiBaoCao vào bảng BaoCao
-- Chạy script này trong MySQL Workbench hoặc phpMyAdmin

-- Kiểm tra và thêm cột loaiBaoCao nếu chưa có
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'BaoCao' 
  AND COLUMN_NAME = 'loaiBaoCao';

SET @query = IF(@col_exists = 0, 
  'ALTER TABLE BaoCao ADD COLUMN loaiBaoCao VARCHAR(50) DEFAULT ''other'' AFTER lyDoBaoCao', 
  'SELECT "Column loaiBaoCao already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Cập nhật index để tối ưu query
SET @idx_exists = 0;
SELECT COUNT(*) INTO @idx_exists 
FROM INFORMATION_SCHEMA.STATISTICS 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'BaoCao' 
  AND INDEX_NAME = 'idx_report_user';

SET @query = IF(@idx_exists = 0, 
  'CREATE INDEX idx_report_user ON BaoCao(maNguoiBiBaoCao, thoiDiemBaoCao)', 
  'SELECT "Index idx_report_user already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_exists = 0;
SELECT COUNT(*) INTO @idx_exists 
FROM INFORMATION_SCHEMA.STATISTICS 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'BaoCao' 
  AND INDEX_NAME = 'idx_report_type';

SET @query = IF(@idx_exists = 0, 
  'CREATE INDEX idx_report_type ON BaoCao(loaiBaoCao)', 
  'SELECT "Index idx_report_type already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
