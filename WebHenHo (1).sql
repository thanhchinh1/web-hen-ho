-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 26, 2025 at 11:10 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.1.17


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `WebHenHo`
--

-- --------------------------------------------------------

--
-- Table structure for table `BaoCao`
--
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


CREATE TABLE `BaoCao` (
  `maBaoCao` int(11) NOT NULL,
  `maNguoiBaoCao` int(11) DEFAULT NULL,
  `maNguoiBiBaoCao` int(11) DEFAULT NULL,
  `lyDoBaoCao` text DEFAULT NULL,
  `trangThai` enum('ChuaXuLy','DaXuLy') DEFAULT 'ChuaXuLy',
  `thoiDiemBaoCao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `BaoCao`
--

-- --------------------------------------------------------

--
-- Table structure for table `GhepDoi`
--

CREATE TABLE `GhepDoi` (
  `maGhepDoi` int(11) NOT NULL,
  `maNguoiA` int(11) DEFAULT NULL,
  `maNguoiB` int(11) DEFAULT NULL,
  `thoiDiemGhepDoi` datetime DEFAULT current_timestamp(),
  `trangThaiGhepDoi` enum('matched','blocked','unmatched') DEFAULT 'matched'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `GhepDoi`
--


-- --------------------------------------------------------

--
-- Table structure for table `GoiDangKy`
--

CREATE TABLE `GoiDangKy` (
  `maGoiDangKy` int(11) NOT NULL,
  `maNguoiDung` int(11) DEFAULT NULL,
  `loaiGoi` enum('Free','VIP') DEFAULT 'Free',
  `trangThaiGoi` enum('Active','Expired') DEFAULT 'Active',
  `ngayHetHan` date DEFAULT NULL,
  `thoiDiemTao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `GoiDangKy`
--


-- --------------------------------------------------------

--
-- Table structure for table `HoSo`
--

CREATE TABLE `HoSo` (
  `maHoSo` int(11) NOT NULL,
  `maNguoiDung` int(11) DEFAULT NULL,
  `ten` varchar(100) DEFAULT NULL,
  `ngaySinh` date DEFAULT NULL,
  `gioiTinh` enum('Nam','Nu','Khac') DEFAULT NULL,
  `tinhTrangHonNhan` varchar(50) DEFAULT NULL,
  `canNang` float DEFAULT NULL,
  `chieuCao` float DEFAULT NULL,
  `mucTieuPhatTrien` text DEFAULT NULL,
  `hocVan` varchar(100) DEFAULT NULL,
  `noiSong` varchar(100) DEFAULT NULL,
  `soThich` text DEFAULT NULL,
  `moTa` text DEFAULT NULL,
  `avt` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `HoSo`
--



-- --------------------------------------------------------

--
-- Table structure for table `NguoiDung`
--

CREATE TABLE `NguoiDung` (
  `maNguoiDung` int(11) NOT NULL,
  `tenDangNhap` varchar(50) NOT NULL,
  `matKhau` varchar(255) NOT NULL,
  `trangThaiNguoiDung` enum('active','banned','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `NguoiDung`
--



-- --------------------------------------------------------

--
-- Table structure for table `ThanhToan`
--

CREATE TABLE `ThanhToan` (
  `maThanhToan` int(11) NOT NULL,
  `maNguoiThanhToan` int(11) DEFAULT NULL,
  `soTien` decimal(12,2) DEFAULT NULL,
  `thoiDiemThanhToan` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ThanhToan`
--



-- --------------------------------------------------------

--
-- Table structure for table `Thich`
--

CREATE TABLE `Thich` (
  `maThich` int(11) NOT NULL,
  `maNguoiThich` int(11) DEFAULT NULL,
  `maNguoiDuocThich` int(11) DEFAULT NULL,
  `thoiDiemThich` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Thich`
--



-- --------------------------------------------------------

--
-- Table structure for table `TinNhan`
--

CREATE TABLE `TinNhan` (
  `maTinNhan` int(11) NOT NULL,
  `maGhepDoi` int(11) DEFAULT NULL,
  `maNguoiGui` int(11) DEFAULT NULL,
  `noiDung` text NOT NULL,
  `thoiDiemGui` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--

--
-- Indexes for dumped tables
--

--
-- Indexes for table `BaoCao`
--
ALTER TABLE `BaoCao`
  ADD PRIMARY KEY (`maBaoCao`),
  ADD KEY `maNguoiBaoCao` (`maNguoiBaoCao`),
  ADD KEY `maNguoiBiBaoCao` (`maNguoiBiBaoCao`);

--
-- Indexes for table `GhepDoi`
--
ALTER TABLE `GhepDoi`
  ADD PRIMARY KEY (`maGhepDoi`),
  ADD KEY `maNguoiA` (`maNguoiA`),
  ADD KEY `maNguoiB` (`maNguoiB`);

--
-- Indexes for table `GoiDangKy`
--
ALTER TABLE `GoiDangKy`
  ADD PRIMARY KEY (`maGoiDangKy`),
  ADD KEY `maNguoiDung` (`maNguoiDung`);

--
-- Indexes for table `HoSo`
--
ALTER TABLE `HoSo`
  ADD PRIMARY KEY (`maHoSo`),
  ADD UNIQUE KEY `maNguoiDung` (`maNguoiDung`);

--
-- Indexes for table `NguoiDung`
--
ALTER TABLE `NguoiDung`
  ADD PRIMARY KEY (`maNguoiDung`),
  ADD UNIQUE KEY `tenDangNhap` (`tenDangNhap`);

--
-- Indexes for table `ThanhToan`
--
ALTER TABLE `ThanhToan`
  ADD PRIMARY KEY (`maThanhToan`),
  ADD KEY `maNguoiThanhToan` (`maNguoiThanhToan`);

--
-- Indexes for table `Thich`
--
ALTER TABLE `Thich`
  ADD PRIMARY KEY (`maThich`),
  ADD KEY `maNguoiThich` (`maNguoiThich`),
  ADD KEY `maNguoiDuocThich` (`maNguoiDuocThich`);

--
-- Indexes for table `TinNhan`
--
ALTER TABLE `TinNhan`
  ADD PRIMARY KEY (`maTinNhan`),
  ADD KEY `maGhepDoi` (`maGhepDoi`),
  ADD KEY `maNguoiGui` (`maNguoiGui`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `BaoCao`
--
ALTER TABLE `BaoCao`
  MODIFY `maBaoCao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `GhepDoi`
--
ALTER TABLE `GhepDoi`
  MODIFY `maGhepDoi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `GoiDangKy`
--
ALTER TABLE `GoiDangKy`
  MODIFY `maGoiDangKy` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `HoSo`
--
ALTER TABLE `HoSo`
  MODIFY `maHoSo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `NguoiDung`
--
ALTER TABLE `NguoiDung`
  MODIFY `maNguoiDung` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `ThanhToan`
--
ALTER TABLE `ThanhToan`
  MODIFY `maThanhToan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `Thich`
--
ALTER TABLE `Thich`
  MODIFY `maThich` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `TinNhan`
--
ALTER TABLE `TinNhan`
  MODIFY `maTinNhan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `BaoCao`
--
ALTER TABLE `BaoCao`
  ADD CONSTRAINT `baocao_ibfk_1` FOREIGN KEY (`maNguoiBaoCao`) REFERENCES `NguoiDung` (`maNguoiDung`),
  ADD CONSTRAINT `baocao_ibfk_2` FOREIGN KEY (`maNguoiBiBaoCao`) REFERENCES `NguoiDung` (`maNguoiDung`);

--
-- Constraints for table `GhepDoi`
--
ALTER TABLE `GhepDoi`
  ADD CONSTRAINT `ghepdoi_ibfk_1` FOREIGN KEY (`maNguoiA`) REFERENCES `NguoiDung` (`maNguoiDung`),
  ADD CONSTRAINT `ghepdoi_ibfk_2` FOREIGN KEY (`maNguoiB`) REFERENCES `NguoiDung` (`maNguoiDung`);

--
-- Constraints for table `GoiDangKy`
--
ALTER TABLE `GoiDangKy`
  ADD CONSTRAINT `goidangky_ibfk_1` FOREIGN KEY (`maNguoiDung`) REFERENCES `NguoiDung` (`maNguoiDung`);

--
-- Constraints for table `HoSo`
--
ALTER TABLE `HoSo`
  ADD CONSTRAINT `hoso_ibfk_1` FOREIGN KEY (`maNguoiDung`) REFERENCES `NguoiDung` (`maNguoiDung`) ON DELETE CASCADE;

--
-- Constraints for table `ThanhToan`
--
ALTER TABLE `ThanhToan`
  ADD CONSTRAINT `thanhtoan_ibfk_1` FOREIGN KEY (`maNguoiThanhToan`) REFERENCES `NguoiDung` (`maNguoiDung`);

--
-- Constraints for table `Thich`
--
ALTER TABLE `Thich`
  ADD CONSTRAINT `thich_ibfk_1` FOREIGN KEY (`maNguoiThich`) REFERENCES `NguoiDung` (`maNguoiDung`),
  ADD CONSTRAINT `thich_ibfk_2` FOREIGN KEY (`maNguoiDuocThich`) REFERENCES `NguoiDung` (`maNguoiDung`);

--
-- Constraints for table `TinNhan`
--
ALTER TABLE `TinNhan`
  ADD CONSTRAINT `tinnhan_ibfk_1` FOREIGN KEY (`maGhepDoi`) REFERENCES `GhepDoi` (`maGhepDoi`) ON DELETE CASCADE,
  ADD CONSTRAINT `tinnhan_ibfk_2` FOREIGN KEY (`maNguoiGui`) REFERENCES `NguoiDung` (`maNguoiDung`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
