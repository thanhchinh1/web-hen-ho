-- =====================================================
-- Script: Thêm CASCADE DELETE cho bảng TinNhan
-- Mục đích: Khi xóa ghép đôi → Tự động xóa tất cả tin nhắn
-- Ngày: 2025-11-02
-- =====================================================

USE WebHenHo;

-- Bước 1: Kiểm tra và xóa foreign key cũ (nếu có)
SET @fk_name = (
    SELECT CONSTRAINT_NAME 
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = 'WebHenHo' 
    AND TABLE_NAME = 'TinNhan' 
    AND COLUMN_NAME = 'maGhepDoi'
    LIMIT 1
);

SET @drop_fk = IF(@fk_name IS NOT NULL, 
    CONCAT('ALTER TABLE TinNhan DROP FOREIGN KEY ', @fk_name), 
    'SELECT "No FK to drop"');
    
PREPARE stmt FROM @drop_fk;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Bước 2: Thêm lại foreign key với CASCADE DELETE
ALTER TABLE TinNhan
ADD CONSTRAINT fk_tinnhan_ghepdoi
FOREIGN KEY (maGhepDoi) 
REFERENCES GhepDoi(maGhepDoi) 
ON DELETE CASCADE;

-- Giải thích:
-- ON DELETE CASCADE: Khi xóa record trong bảng GhepDoi
-- → Tự động xóa tất cả record liên quan trong bảng TinNhan

SELECT '✅ Đã thêm CASCADE DELETE cho bảng TinNhan!' AS result;
