-- Tạo bảng lưu lịch sử giao dịch nâng cấp VIP
CREATE TABLE giaodich (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_name VARCHAR(255) NOT NULL,
    ma_chuyen_khoan VARCHAR(100) NOT NULL,
    ngay_giao_dich DATETIME DEFAULT NULL,
    trang_thai ENUM('cho_xac_nhan', 'da_xac_nhan', 'da_xoa') DEFAULT 'cho_xac_nhan',
    thoi_gian_tao DATETIME DEFAULT CURRENT_TIMESTAMP,
    thoi_gian_xac_nhan DATETIME DEFAULT NULL,
    thoi_gian_xoa DATETIME DEFAULT NULL,
    CONSTRAINT fk_giaodich_user FOREIGN KEY (user_id) REFERENCES nguoidung(maNguoiDung) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Index cho truy vấn nhanh
CREATE INDEX idx_giaodich_user_id ON giaodich(user_id);
CREATE INDEX idx_giaodich_ma_chuyen_khoan ON giaodich(ma_chuyen_khoan);
CREATE INDEX idx_giaodich_trang_thai ON giaodich(trang_thai);