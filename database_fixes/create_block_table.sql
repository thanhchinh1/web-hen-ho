-- Tạo bảng ChanNguoiDung để quản lý block users
-- Chạy trong phpMyAdmin hoặc MySQL Workbench

USE webhenho;

-- Tạo bảng ChanNguoiDung
CREATE TABLE IF NOT EXISTS ChanNguoiDung (
    id INT AUTO_INCREMENT PRIMARY KEY,
    maNguoiChan INT NOT NULL,
    maNguoiBiChan INT NOT NULL,
    thoiDiemChan DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (maNguoiChan) REFERENCES NguoiDung(maNguoiDung) ON DELETE CASCADE,
    FOREIGN KEY (maNguoiBiChan) REFERENCES NguoiDung(maNguoiDung) ON DELETE CASCADE,
    UNIQUE KEY unique_block (maNguoiChan, maNguoiBiChan),
    INDEX idx_blocker (maNguoiChan),
    INDEX idx_blocked (maNguoiBiChan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Hoàn thành! Bảng ChanNguoiDung đã được tạo.
