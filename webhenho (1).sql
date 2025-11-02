-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 02, 2025 lúc 12:36 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `webhenho`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `admin`
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
-- Cấu trúc bảng cho bảng `adminlog`
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
-- Cấu trúc bảng cho bảng `baocao`
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
-- Cấu trúc bảng cho bảng `channguoidung`
--

CREATE TABLE `channguoidung` (
  `id` int(11) NOT NULL,
  `maNguoiChan` int(11) NOT NULL,
  `maNguoiBiChan` int(11) NOT NULL,
  `thoiDiemChan` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ghepdoi`
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
-- Cấu trúc bảng cho bảng `goidangky`
--

CREATE TABLE `goidangky` (
  `maGoiDangKy` int(11) NOT NULL,
  `maNguoiDung` int(11) DEFAULT NULL,
  `loaiGoi` enum('Free','VIP') DEFAULT 'Free',
  `trangThaiGoi` enum('Active','Expired') DEFAULT 'Active',
  `ngayHetHan` date DEFAULT NULL,
  `thoiDiemTao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `goidangky`
--

INSERT INTO `goidangky` (`maGoiDangKy`, `maNguoiDung`, `loaiGoi`, `trangThaiGoi`, `ngayHetHan`, `thoiDiemTao`) VALUES
(8, 49, 'VIP', 'Active', '2025-12-02', '2025-11-02 16:47:47'),
(9, 48, 'VIP', 'Active', '2025-12-02', '2025-11-02 17:19:38');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hoso`
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

--
-- Đang đổ dữ liệu cho bảng `hoso`
--

INSERT INTO `hoso` (`maHoSo`, `maNguoiDung`, `ten`, `ngaySinh`, `gioiTinh`, `tinhTrangHonNhan`, `canNang`, `chieuCao`, `mucTieuPhatTrien`, `hocVan`, `noiSong`, `soThich`, `moTa`, `avt`) VALUES
(26, 48, 'Thịnh', '1995-01-01', 'Nam', 'Độc thân', 30, 100, 'Kết hôn', 'Đại học', 'Hà Nội', 'Đọc sách, Nghe nhạc, Du lịch', 'hahaha', 'public/uploads/avatars/avatar_48_1762080723.jpg'),
(27, 49, 'heo', '1996-02-02', 'Nữ', 'Độc thân', 39, 100, 'Kết hôn', 'Đại học', 'Hà Nội', 'Đọc sách, Nghe nhạc, Du lịch', 'fff', 'public/uploads/avatars/avatar_49_1762080818.jpg');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nguoidung`
--

CREATE TABLE `nguoidung` (
  `maNguoiDung` int(11) NOT NULL,
  `tenDangNhap` varchar(50) NOT NULL,
  `matKhau` varchar(255) NOT NULL,
  `trangThaiNguoiDung` enum('active','banned','inactive') DEFAULT 'active',
  `lanHoatDongCuoi` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `role` varchar(255) NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `nguoidung`
--

INSERT INTO `nguoidung` (`maNguoiDung`, `tenDangNhap`, `matKhau`, `trangThaiNguoiDung`, `lanHoatDongCuoi`, `role`) VALUES
(47, 'admin', '0192023a7bbd73250516f069df18b500', 'active', '2025-11-02 16:20:48', 'admin'),
(48, 'example@gmail.com', '7d080f6a8d770a68fb28cadd3e7fe8f9', 'active', NULL, 'user'),
(49, 'example1@gmail.com', '7d080f6a8d770a68fb28cadd3e7fe8f9', 'active', NULL, 'user');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ratelimitlog`
--

CREATE TABLE `ratelimitlog` (
  `id` int(11) NOT NULL,
  `maNguoiDung` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `ipAddress` varchar(45) NOT NULL,
  `thoiDiem` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `ratelimitlog`
--

INSERT INTO `ratelimitlog` (`id`, `maNguoiDung`, `action`, `ipAddress`, `thoiDiem`) VALUES
(48, 49, 'like_action', '::1', '2025-11-02 18:02:36'),
(49, 48, 'send_message', '::1', '2025-11-02 18:03:22'),
(50, 48, 'send_message', '::1', '2025-11-02 18:03:33'),
(51, 49, 'send_message', '::1', '2025-11-02 18:03:35'),
(52, 49, 'send_message', '::1', '2025-11-02 18:13:10');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thanhtoan`
--

CREATE TABLE `thanhtoan` (
  `maThanhToan` int(11) NOT NULL,
  `maNguoiThanhToan` int(11) DEFAULT NULL,
  `soTien` decimal(12,2) DEFAULT NULL,
  `thoiDiemThanhToan` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `thanhtoan`
--

INSERT INTO `thanhtoan` (`maThanhToan`, `maNguoiThanhToan`, `soTien`, `thoiDiemThanhToan`) VALUES
(6, 49, 99000.00, '2025-11-02 16:47:47'),
(7, 48, 99000.00, '2025-11-02 17:19:38');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thich`
--

CREATE TABLE `thich` (
  `maThich` int(11) NOT NULL,
  `maNguoiThich` int(11) DEFAULT NULL,
  `maNguoiDuocThich` int(11) DEFAULT NULL,
  `thoiDiemThich` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thongkengay`
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
-- Cấu trúc bảng cho bảng `timkiemghepdoi`
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
-- Cấu trúc bảng cho bảng `tinnhan`
--

CREATE TABLE `tinnhan` (
  `maTinNhan` int(11) NOT NULL,
  `maGhepDoi` int(11) DEFAULT NULL,
  `maNguoiGui` int(11) DEFAULT NULL,
  `noiDung` text NOT NULL,
  `thoiDiemGui` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`maAdmin`),
  ADD UNIQUE KEY `tenDangNhap` (`tenDangNhap`),
  ADD KEY `idx_username` (`tenDangNhap`),
  ADD KEY `idx_role` (`vaiTro`);

--
-- Chỉ mục cho bảng `adminlog`
--
ALTER TABLE `adminlog`
  ADD PRIMARY KEY (`maLog`),
  ADD KEY `idx_admin` (`maAdmin`),
  ADD KEY `idx_time` (`thoiGian`);

--
-- Chỉ mục cho bảng `baocao`
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
-- Chỉ mục cho bảng `channguoidung`
--
ALTER TABLE `channguoidung`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_block` (`maNguoiChan`,`maNguoiBiChan`),
  ADD KEY `idx_blocker` (`maNguoiChan`),
  ADD KEY `idx_blocked` (`maNguoiBiChan`);

--
-- Chỉ mục cho bảng `ghepdoi`
--
ALTER TABLE `ghepdoi`
  ADD PRIMARY KEY (`maGhepDoi`),
  ADD UNIQUE KEY `idx_unique_match` (`maNguoiA`,`maNguoiB`,`trangThaiGhepDoi`),
  ADD KEY `maNguoiA` (`maNguoiA`),
  ADD KEY `maNguoiB` (`maNguoiB`);

--
-- Chỉ mục cho bảng `goidangky`
--
ALTER TABLE `goidangky`
  ADD PRIMARY KEY (`maGoiDangKy`),
  ADD KEY `maNguoiDung` (`maNguoiDung`);

--
-- Chỉ mục cho bảng `hoso`
--
ALTER TABLE `hoso`
  ADD PRIMARY KEY (`maHoSo`),
  ADD UNIQUE KEY `maNguoiDung` (`maNguoiDung`);

--
-- Chỉ mục cho bảng `nguoidung`
--
ALTER TABLE `nguoidung`
  ADD PRIMARY KEY (`maNguoiDung`),
  ADD UNIQUE KEY `tenDangNhap` (`tenDangNhap`);

--
-- Chỉ mục cho bảng `ratelimitlog`
--
ALTER TABLE `ratelimitlog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_action` (`maNguoiDung`,`action`,`thoiDiem`),
  ADD KEY `idx_cleanup` (`thoiDiem`);

--
-- Chỉ mục cho bảng `thanhtoan`
--
ALTER TABLE `thanhtoan`
  ADD PRIMARY KEY (`maThanhToan`),
  ADD KEY `maNguoiThanhToan` (`maNguoiThanhToan`);

--
-- Chỉ mục cho bảng `thich`
--
ALTER TABLE `thich`
  ADD PRIMARY KEY (`maThich`),
  ADD KEY `maNguoiThich` (`maNguoiThich`),
  ADD KEY `maNguoiDuocThich` (`maNguoiDuocThich`);

--
-- Chỉ mục cho bảng `thongkengay`
--
ALTER TABLE `thongkengay`
  ADD PRIMARY KEY (`maThongKe`),
  ADD UNIQUE KEY `ngay` (`ngay`),
  ADD KEY `idx_date` (`ngay`);

--
-- Chỉ mục cho bảng `timkiemghepdoi`
--
ALTER TABLE `timkiemghepdoi`
  ADD PRIMARY KEY (`maTimKiem`),
  ADD KEY `idx_searching` (`trangThai`,`thoiDiemBatDau`),
  ADD KEY `idx_user` (`maNguoiDung`);

--
-- Chỉ mục cho bảng `tinnhan`
--
ALTER TABLE `tinnhan`
  ADD PRIMARY KEY (`maTinNhan`),
  ADD KEY `maGhepDoi` (`maGhepDoi`),
  ADD KEY `maNguoiGui` (`maNguoiGui`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `admin`
--
ALTER TABLE `admin`
  MODIFY `maAdmin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `adminlog`
--
ALTER TABLE `adminlog`
  MODIFY `maLog` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT cho bảng `baocao`
--
ALTER TABLE `baocao`
  MODIFY `maBaoCao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `channguoidung`
--
ALTER TABLE `channguoidung`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `ghepdoi`
--
ALTER TABLE `ghepdoi`
  MODIFY `maGhepDoi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT cho bảng `goidangky`
--
ALTER TABLE `goidangky`
  MODIFY `maGoiDangKy` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `hoso`
--
ALTER TABLE `hoso`
  MODIFY `maHoSo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT cho bảng `nguoidung`
--
ALTER TABLE `nguoidung`
  MODIFY `maNguoiDung` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT cho bảng `ratelimitlog`
--
ALTER TABLE `ratelimitlog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT cho bảng `thanhtoan`
--
ALTER TABLE `thanhtoan`
  MODIFY `maThanhToan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `thich`
--
ALTER TABLE `thich`
  MODIFY `maThich` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT cho bảng `thongkengay`
--
ALTER TABLE `thongkengay`
  MODIFY `maThongKe` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `timkiemghepdoi`
--
ALTER TABLE `timkiemghepdoi`
  MODIFY `maTimKiem` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT cho bảng `tinnhan`
--
ALTER TABLE `tinnhan`
  MODIFY `maTinNhan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `adminlog`
--
ALTER TABLE `adminlog`
  ADD CONSTRAINT `adminlog_ibfk_1` FOREIGN KEY (`maAdmin`) REFERENCES `admin` (`maAdmin`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `baocao`
--
ALTER TABLE `baocao`
  ADD CONSTRAINT `baocao_ibfk_1` FOREIGN KEY (`maNguoiBaoCao`) REFERENCES `nguoidung` (`maNguoiDung`),
  ADD CONSTRAINT `baocao_ibfk_2` FOREIGN KEY (`maNguoiBiBaoCao`) REFERENCES `nguoidung` (`maNguoiDung`),
  ADD CONSTRAINT `fk_admin_report` FOREIGN KEY (`maAdminXuLy`) REFERENCES `admin` (`maAdmin`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `channguoidung`
--
ALTER TABLE `channguoidung`
  ADD CONSTRAINT `channguoidung_ibfk_1` FOREIGN KEY (`maNguoiChan`) REFERENCES `nguoidung` (`maNguoiDung`) ON DELETE CASCADE,
  ADD CONSTRAINT `channguoidung_ibfk_2` FOREIGN KEY (`maNguoiBiChan`) REFERENCES `nguoidung` (`maNguoiDung`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `ghepdoi`
--
ALTER TABLE `ghepdoi`
  ADD CONSTRAINT `ghepdoi_ibfk_1` FOREIGN KEY (`maNguoiA`) REFERENCES `nguoidung` (`maNguoiDung`),
  ADD CONSTRAINT `ghepdoi_ibfk_2` FOREIGN KEY (`maNguoiB`) REFERENCES `nguoidung` (`maNguoiDung`);

--
-- Các ràng buộc cho bảng `goidangky`
--
ALTER TABLE `goidangky`
  ADD CONSTRAINT `goidangky_ibfk_1` FOREIGN KEY (`maNguoiDung`) REFERENCES `nguoidung` (`maNguoiDung`);

--
-- Các ràng buộc cho bảng `hoso`
--
ALTER TABLE `hoso`
  ADD CONSTRAINT `hoso_ibfk_1` FOREIGN KEY (`maNguoiDung`) REFERENCES `nguoidung` (`maNguoiDung`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `thanhtoan`
--
ALTER TABLE `thanhtoan`
  ADD CONSTRAINT `thanhtoan_ibfk_1` FOREIGN KEY (`maNguoiThanhToan`) REFERENCES `nguoidung` (`maNguoiDung`);

--
-- Các ràng buộc cho bảng `thich`
--
ALTER TABLE `thich`
  ADD CONSTRAINT `thich_ibfk_1` FOREIGN KEY (`maNguoiThich`) REFERENCES `nguoidung` (`maNguoiDung`),
  ADD CONSTRAINT `thich_ibfk_2` FOREIGN KEY (`maNguoiDuocThich`) REFERENCES `nguoidung` (`maNguoiDung`);

--
-- Các ràng buộc cho bảng `timkiemghepdoi`
--
ALTER TABLE `timkiemghepdoi`
  ADD CONSTRAINT `timkiemghepdoi_ibfk_1` FOREIGN KEY (`maNguoiDung`) REFERENCES `nguoidung` (`maNguoiDung`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `tinnhan`
--
ALTER TABLE `tinnhan`
  ADD CONSTRAINT `fk_tinnhan_ghepdoi` FOREIGN KEY (`maGhepDoi`) REFERENCES `ghepdoi` (`maGhepDoi`) ON DELETE CASCADE,
  ADD CONSTRAINT `tinnhan_ibfk_2` FOREIGN KEY (`maNguoiGui`) REFERENCES `nguoidung` (`maNguoiDung`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
