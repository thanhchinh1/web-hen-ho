-- Script sửa lỗi UNIQUE constraint cho bảng GhepDoi
-- Chạy script này trong MySQL Workbench hoặc phpMyAdmin

USE webhenho;

-- Bước 1: Xóa các bản ghi duplicate nếu có
DELETE g1 FROM GhepDoi g1
INNER JOIN GhepDoi g2 
WHERE g1.maGhepDoi > g2.maGhepDoi
AND (
    (g1.maNguoiA = g2.maNguoiA AND g1.maNguoiB = g2.maNguoiB)
    OR (g1.maNguoiA = g2.maNguoiB AND g1.maNguoiB = g2.maNguoiA)
);

-- Bước 2: Đảm bảo maNguoiA luôn nhỏ hơn maNguoiB
UPDATE GhepDoi
SET maNguoiA = maNguoiB, maNguoiB = maNguoiA
WHERE maNguoiA > maNguoiB;

-- Bước 3: Thêm UNIQUE index để ngăn duplicate
CREATE UNIQUE INDEX idx_unique_match 
ON GhepDoi(maNguoiA, maNguoiB, trangThaiGhepDoi);

-- Hoàn thành! Giờ không thể tạo duplicate match nữa.
