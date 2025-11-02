-- Thêm cột theo dõi trạng thái online
ALTER TABLE `NguoiDung` 
ADD COLUMN `lanHoatDongCuoi` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
AFTER `trangThaiNguoiDung`;

-- Cập nhật tất cả user hiện tại
UPDATE `NguoiDung` SET `lanHoatDongCuoi` = NOW();

-- Hoàn thành! Người dùng được coi là online nếu hoạt động trong vòng 5 phút gần đây.
