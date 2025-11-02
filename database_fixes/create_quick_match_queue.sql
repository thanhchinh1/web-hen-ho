-- Bảng hàng đợi ghép đôi nhanh
CREATE TABLE IF NOT EXISTS TimKiemGhepDoi (
    maTimKiem INT AUTO_INCREMENT PRIMARY KEY,
    maNguoiDung INT NOT NULL,
    trangThai ENUM('searching', 'matched', 'cancelled') DEFAULT 'searching',
    thoiDiemBatDau DATETIME DEFAULT CURRENT_TIMESTAMP,
    thoiDiemKetThuc DATETIME DEFAULT NULL,
    FOREIGN KEY (maNguoiDung) REFERENCES NguoiDung(maNguoiDung) ON DELETE CASCADE,
    INDEX idx_searching (trangThai, thoiDiemBatDau),
    INDEX idx_user (maNguoiDung)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
