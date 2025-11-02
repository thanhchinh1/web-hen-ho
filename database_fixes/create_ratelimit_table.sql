-- Tạo bảng RateLimitLog để track rate limiting
-- Chạy trong phpMyAdmin hoặc MySQL Workbench

USE webhenho;

-- Tạo bảng RateLimitLog
CREATE TABLE IF NOT EXISTS RateLimitLog (
    id INT AUTO_INCREMENT PRIMARY KEY,
    maNguoiDung INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    ipAddress VARCHAR(45) NOT NULL,
    thoiDiem DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_action (maNguoiDung, action, thoiDiem),
    INDEX idx_cleanup (thoiDiem)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Hoàn thành! Bảng RateLimitLog đã được tạo.
