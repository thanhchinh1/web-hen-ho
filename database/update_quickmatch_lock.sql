-- Thêm cột để khóa tài khoản khi đang xử lý ghép đôi
ALTER TABLE `timkiemghepdoi` 
ADD COLUMN `isLocked` TINYINT(1) DEFAULT 0 COMMENT 'Khóa tạm thời khi đang xử lý ghép đôi',
ADD COLUMN `lockedAt` DATETIME NULL COMMENT 'Thời điểm khóa';

-- Index để tăng tốc độ query
CREATE INDEX idx_searching_unlocked ON timkiemghepdoi(trangThai, isLocked, thoiDiemBatDau);
