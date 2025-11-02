-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 02, 2025 at 06:49 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `webhenho`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `maAdmin` int(11) NOT NULL,
  `tenDangNhap` varchar(50) NOT NULL,
  `matKhau` varchar(255) NOT NULL,
  `hoTen` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `soDienThoai` varchar(20) DEFAULT NULL,
  `vaiTro` enum('super_admin','moderator','support') DEFAULT 'moderator',
  `trangThai` enum('active','inactive') DEFAULT 'active',
  `lanDangNhapCuoi` datetime DEFAULT NULL,
  `ngayTao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `adminlog`
--

CREATE TABLE `adminlog` (
  `maLog` int(11) NOT NULL,
  `maAdmin` int(11) DEFAULT NULL,
  `hanhDong` varchar(255) DEFAULT NULL,
  `chiTiet` text DEFAULT NULL,
  `ipAddress` varchar(50) DEFAULT NULL,
  `thoiGian` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `baocao`
--

CREATE TABLE `baocao` (
  `maBaoCao` int(11) NOT NULL,
  `maNguoiBaoCao` int(11) DEFAULT NULL,
  `maNguoiBiBaoCao` int(11) DEFAULT NULL,
  `lyDoBaoCao` text DEFAULT NULL,
  `loaiBaoCao` varchar(50) DEFAULT 'other',
  `trangThaiAD` enum('pending','reviewing','resolved','rejected') DEFAULT 'pending',
  `trangThai` enum('ChuaXuLy','DaXuLy') DEFAULT 'ChuaXuLy',
  `maAdminXuLy` int(11) DEFAULT NULL,
  `ghiChuAdmin` text DEFAULT NULL,
  `thoiDiemXuLy` datetime DEFAULT NULL,
  `thoiDiemBaoCao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `channguoidung`
--

CREATE TABLE `channguoidung` (
  `id` int(11) NOT NULL,
  `maNguoiChan` int(11) NOT NULL,
  `maNguoiBiChan` int(11) NOT NULL,
  `thoiDiemChan` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ghepdoi`
--

CREATE TABLE `ghepdoi` (
  `maGhepDoi` int(11) NOT NULL,
  `maNguoiA` int(11) DEFAULT NULL,
  `maNguoiB` int(11) DEFAULT NULL,
  `thoiDiemGhepDoi` datetime DEFAULT current_timestamp(),
  `trangThaiGhepDoi` enum('matched','blocked','unmatched') DEFAULT 'matched'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `goidangky`
--

CREATE TABLE `goidangky` (
  `maGoiDangKy` int(11) NOT NULL,
  `maNguoiDung` int(11) DEFAULT NULL,
  `loaiGoi` enum('Free','VIP') DEFAULT 'Free',
  `trangThaiGoi` enum('Active','Expired') DEFAULT 'Active',
  `ngayHetHan` date DEFAULT NULL,
  `thoiDiemTao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hoso`
--

CREATE TABLE `hoso` (
  `maHoSo` int(11) NOT NULL,
  `maNguoiDung` int(11) DEFAULT NULL,
  `ten` varchar(100) DEFAULT NULL,
  `ngaySinh` date DEFAULT NULL,
  `gioiTinh` enum('Nam','Nữ','Khac') DEFAULT NULL,
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

-- --------------------------------------------------------

--
-- Table structure for table `nguoidung`
--

CREATE TABLE `nguoidung` (
  `maNguoiDung` int(11) NOT NULL,
  `tenDangNhap` varchar(50) NOT NULL,
  `matKhau` varchar(255) NOT NULL,
  `trangThaiNguoiDung` enum('active','banned','inactive') DEFAULT 'active',
  `lanHoatDongCuoi` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `role` varchar(255) NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ratelimitlog`
--

CREATE TABLE `ratelimitlog` (
  `id` int(11) NOT NULL,
  `maNguoiDung` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `ipAddress` varchar(45) NOT NULL,
  `thoiDiem` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `thanhtoan`
--

CREATE TABLE `thanhtoan` (
  `maThanhToan` int(11) NOT NULL,
  `maNguoiThanhToan` int(11) DEFAULT NULL,
  `soTien` decimal(12,2) DEFAULT NULL,
  `thoiDiemThanhToan` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `thich`
--

CREATE TABLE `thich` (
  `maThich` int(11) NOT NULL,
  `maNguoiThich` int(11) DEFAULT NULL,
  `maNguoiDuocThich` int(11) DEFAULT NULL,
  `thoiDiemThich` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `thongkengay`
--

CREATE TABLE `thongkengay` (
  `maThongKe` int(11) NOT NULL,
  `ngay` date NOT NULL,
  `soNguoiDungMoi` int(11) DEFAULT 0,
  `soGhepDoiMoi` int(11) DEFAULT 0,
  `soTinNhan` int(11) DEFAULT 0,
  `soBaoCao` int(11) DEFAULT 0,
  `ngayTao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `timkiemghepdoi`
--

CREATE TABLE `timkiemghepdoi` (
  `maTimKiem` int(11) NOT NULL,
  `maNguoiDung` int(11) NOT NULL,
  `trangThai` enum('searching','matched','cancelled') DEFAULT 'searching',
  `thoiDiemBatDau` datetime DEFAULT current_timestamp(),
  `thoiDiemKetThuc` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tinnhan`
--

CREATE TABLE `tinnhan` (
  `maTinNhan` int(11) NOT NULL,
  `maGhepDoi` int(11) DEFAULT NULL,
  `maNguoiGui` int(11) DEFAULT NULL,
  `noiDung` text NOT NULL,
  `thoiDiemGui` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`maAdmin`),
  ADD UNIQUE KEY `tenDangNhap` (`tenDangNhap`),
  ADD KEY `idx_username` (`tenDangNhap`),
  ADD KEY `idx_role` (`vaiTro`);

--
-- Indexes for table `adminlog`
--
ALTER TABLE `adminlog`
  ADD PRIMARY KEY (`maLog`),
  ADD KEY `idx_admin` (`maAdmin`),
  ADD KEY `idx_time` (`thoiGian`);

--
-- Indexes for table `baocao`
--
ALTER TABLE `baocao`
  ADD PRIMARY KEY (`maBaoCao`),
  ADD KEY `maNguoiBaoCao` (`maNguoiBaoCao`),
  ADD KEY `maNguoiBiBaoCao` (`maNguoiBiBaoCao`),
  ADD KEY `idx_report_user` (`maNguoiBiBaoCao`,`thoiDiemBaoCao`),
  ADD KEY `idx_report_type` (`loaiBaoCao`),
  ADD KEY `fk_admin_report` (`maAdminXuLy`),
  ADD KEY `idx_report_status` (`trangThai`,`thoiDiemBaoCao`);

--
-- Indexes for table `channguoidung`
--
ALTER TABLE `channguoidung`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_block` (`maNguoiChan`,`maNguoiBiChan`),
  ADD KEY `idx_blocker` (`maNguoiChan`),
  ADD KEY `idx_blocked` (`maNguoiBiChan`);

--
-- Indexes for table `ghepdoi`
--
ALTER TABLE `ghepdoi`
  ADD PRIMARY KEY (`maGhepDoi`),
  ADD UNIQUE KEY `idx_unique_match` (`maNguoiA`,`maNguoiB`,`trangThaiGhepDoi`),
  ADD KEY `maNguoiA` (`maNguoiA`),
  ADD KEY `maNguoiB` (`maNguoiB`);

--
-- Indexes for table `goidangky`
--
ALTER TABLE `goidangky`
  ADD PRIMARY KEY (`maGoiDangKy`),
  ADD KEY `maNguoiDung` (`maNguoiDung`);

--
-- Indexes for table `hoso`
--
ALTER TABLE `hoso`
  ADD PRIMARY KEY (`maHoSo`),
  ADD UNIQUE KEY `maNguoiDung` (`maNguoiDung`);

--
-- Indexes for table `nguoidung`
--
ALTER TABLE `nguoidung`
  ADD PRIMARY KEY (`maNguoiDung`),
  ADD UNIQUE KEY `tenDangNhap` (`tenDangNhap`);

--
-- Indexes for table `ratelimitlog`
--
ALTER TABLE `ratelimitlog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_action` (`maNguoiDung`,`action`,`thoiDiem`),
  ADD KEY `idx_cleanup` (`thoiDiem`);

--
-- Indexes for table `thanhtoan`
--
ALTER TABLE `thanhtoan`
  ADD PRIMARY KEY (`maThanhToan`),
  ADD KEY `maNguoiThanhToan` (`maNguoiThanhToan`);

--
-- Indexes for table `thich`
--
ALTER TABLE `thich`
  ADD PRIMARY KEY (`maThich`),
  ADD KEY `maNguoiThich` (`maNguoiThich`),
  ADD KEY `maNguoiDuocThich` (`maNguoiDuocThich`);

--
-- Indexes for table `thongkengay`
--
ALTER TABLE `thongkengay`
  ADD PRIMARY KEY (`maThongKe`),
  ADD UNIQUE KEY `ngay` (`ngay`),
  ADD KEY `idx_date` (`ngay`);

--
-- Indexes for table `timkiemghepdoi`
--
ALTER TABLE `timkiemghepdoi`
  ADD PRIMARY KEY (`maTimKiem`),
  ADD KEY `idx_searching` (`trangThai`,`thoiDiemBatDau`),
  ADD KEY `idx_user` (`maNguoiDung`);

--
-- Indexes for table `tinnhan`
--
ALTER TABLE `tinnhan`
  ADD PRIMARY KEY (`maTinNhan`),
  ADD KEY `maGhepDoi` (`maGhepDoi`),
  ADD KEY `maNguoiGui` (`maNguoiGui`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `maAdmin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `adminlog`
--
ALTER TABLE `adminlog`
  MODIFY `maLog` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `baocao`
--
ALTER TABLE `baocao`
  MODIFY `maBaoCao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `channguoidung`
--
ALTER TABLE `channguoidung`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ghepdoi`
--
ALTER TABLE `ghepdoi`
  MODIFY `maGhepDoi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `goidangky`
--
ALTER TABLE `goidangky`
  MODIFY `maGoiDangKy` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `hoso`
--
ALTER TABLE `hoso`
  MODIFY `maHoSo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `nguoidung`
--
ALTER TABLE `nguoidung`
  MODIFY `maNguoiDung` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `ratelimitlog`
--
ALTER TABLE `ratelimitlog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `thanhtoan`
--
ALTER TABLE `thanhtoan`
  MODIFY `maThanhToan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `thich`
--
ALTER TABLE `thich`
  MODIFY `maThich` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `thongkengay`
--
ALTER TABLE `thongkengay`
  MODIFY `maThongKe` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `timkiemghepdoi`
--
ALTER TABLE `timkiemghepdoi`
  MODIFY `maTimKiem` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tinnhan`
--
ALTER TABLE `tinnhan`
  MODIFY `maTinNhan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `adminlog`
--
ALTER TABLE `adminlog`
  ADD CONSTRAINT `adminlog_ibfk_1` FOREIGN KEY (`maAdmin`) REFERENCES `admin` (`maAdmin`) ON DELETE SET NULL;

--
-- Constraints for table `baocao`
--
ALTER TABLE `baocao`
  ADD CONSTRAINT `baocao_ibfk_1` FOREIGN KEY (`maNguoiBaoCao`) REFERENCES `nguoidung` (`maNguoiDung`),
  ADD CONSTRAINT `baocao_ibfk_2` FOREIGN KEY (`maNguoiBiBaoCao`) REFERENCES `nguoidung` (`maNguoiDung`),
  ADD CONSTRAINT `fk_admin_report` FOREIGN KEY (`maAdminXuLy`) REFERENCES `admin` (`maAdmin`) ON DELETE SET NULL;

--
-- Constraints for table `channguoidung`
--
ALTER TABLE `channguoidung`
  ADD CONSTRAINT `channguoidung_ibfk_1` FOREIGN KEY (`maNguoiChan`) REFERENCES `nguoidung` (`maNguoiDung`) ON DELETE CASCADE,
  ADD CONSTRAINT `channguoidung_ibfk_2` FOREIGN KEY (`maNguoiBiChan`) REFERENCES `nguoidung` (`maNguoiDung`) ON DELETE CASCADE;

--
-- Constraints for table `ghepdoi`
--
ALTER TABLE `ghepdoi`
  ADD CONSTRAINT `ghepdoi_ibfk_1` FOREIGN KEY (`maNguoiA`) REFERENCES `nguoidung` (`maNguoiDung`),
  ADD CONSTRAINT `ghepdoi_ibfk_2` FOREIGN KEY (`maNguoiB`) REFERENCES `nguoidung` (`maNguoiDung`);

--
-- Constraints for table `goidangky`
--
ALTER TABLE `goidangky`
  ADD CONSTRAINT `goidangky_ibfk_1` FOREIGN KEY (`maNguoiDung`) REFERENCES `nguoidung` (`maNguoiDung`);

--
-- Constraints for table `hoso`
--
ALTER TABLE `hoso`
  ADD CONSTRAINT `hoso_ibfk_1` FOREIGN KEY (`maNguoiDung`) REFERENCES `nguoidung` (`maNguoiDung`) ON DELETE CASCADE;

--
-- Constraints for table `thanhtoan`
--
ALTER TABLE `thanhtoan`
  ADD CONSTRAINT `thanhtoan_ibfk_1` FOREIGN KEY (`maNguoiThanhToan`) REFERENCES `nguoidung` (`maNguoiDung`);

--
-- Constraints for table `thich`
--
ALTER TABLE `thich`
  ADD CONSTRAINT `thich_ibfk_1` FOREIGN KEY (`maNguoiThich`) REFERENCES `nguoidung` (`maNguoiDung`),
  ADD CONSTRAINT `thich_ibfk_2` FOREIGN KEY (`maNguoiDuocThich`) REFERENCES `nguoidung` (`maNguoiDung`);

--
-- Constraints for table `timkiemghepdoi`
--
ALTER TABLE `timkiemghepdoi`
  ADD CONSTRAINT `timkiemghepdoi_ibfk_1` FOREIGN KEY (`maNguoiDung`) REFERENCES `nguoidung` (`maNguoiDung`) ON DELETE CASCADE;

--
-- Constraints for table `tinnhan`
--
ALTER TABLE `tinnhan`
  ADD CONSTRAINT `fk_tinnhan_ghepdoi` FOREIGN KEY (`maGhepDoi`) REFERENCES `ghepdoi` (`maGhepDoi`) ON DELETE CASCADE,
  ADD CONSTRAINT `tinnhan_ibfk_2` FOREIGN KEY (`maNguoiGui`) REFERENCES `nguoidung` (`maNguoiDung`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
