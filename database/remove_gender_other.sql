-- Migration: Xóa giới tính "Khác" khỏi hệ thống
-- Date: 2025-12-23
-- Description: Cập nhật enum gioiTinh từ ('Nam','Nữ','Khac') thành ('Nam','Nữ')

-- Bước 1: Cập nhật các bản ghi có giới tính 'Khac' thành NULL hoặc một giá trị mặc định
-- (Nếu muốn set thành giá trị cụ thể, thay NULL bằng 'Nam' hoặc 'Nữ')
UPDATE hoso SET gioiTinh = NULL WHERE gioiTinh = 'Khac';

-- Bước 2: Thay đổi cấu trúc cột gioiTinh - xóa giá trị 'Khac' khỏi enum
ALTER TABLE hoso 
MODIFY COLUMN gioiTinh enum('Nam','Nữ') DEFAULT NULL;

-- Lưu ý: 
-- - Nếu có dữ liệu quan trọng với giới tính 'Khac', hãy backup trước khi chạy
-- - Có thể thay NULL bằng giá trị mặc định nếu cần (ví dụ: 'Nam' hoặc 'Nữ')
