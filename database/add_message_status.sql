-- Thêm cột trạng thái tin nhắn vào bảng tinnhan
ALTER TABLE tinnhan 
ADD COLUMN trangThai ENUM('sent', 'delivered', 'seen') DEFAULT 'sent' COMMENT 'Trạng thái tin nhắn: sent (đã gửi), delivered (đã nhận), seen (đã xem)',
ADD COLUMN thoiDiemNhan DATETIME NULL COMMENT 'Thời điểm người nhận nhận được tin nhắn',
ADD COLUMN thoiDiemXem DATETIME NULL COMMENT 'Thời điểm người nhận xem tin nhắn';

-- Tạo index cho truy vấn nhanh hơn
CREATE INDEX idx_message_status ON tinnhan(maGhepDoi, trangThai);
