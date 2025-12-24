-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th12 24, 2025 lúc 07:38 AM
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

--
-- Đang đổ dữ liệu cho bảng `baocao`
--

INSERT INTO `baocao` (`maBaoCao`, `maNguoiBaoCao`, `maNguoiBiBaoCao`, `lyDoBaoCao`, `loaiBaoCao`, `trangThaiAD`, `trangThai`, `maAdminXuLy`, `ghiChuAdmin`, `thoiDiemXuLy`, `thoiDiemBaoCao`) VALUES
(1, 101, 110, 'Người dùng này gửi tin nhắn spam liên tục', 'spam', 'resolved', 'DaXuLy', 1, 'Đã xác minh và cảnh cáo người dùng', '2025-12-15 14:30:00', '2025-12-15 10:30:00'),
(2, 115, 125, 'Hành vi quấy rối, gửi nội dung không phù hợp', 'harassment', 'resolved', 'DaXuLy', 1, 'Đã khóa tài khoản tạm thời 7 ngày', '2025-12-15 16:00:00', '2025-12-15 12:00:00'),
(3, 120, 130, 'Sử dụng ảnh đại diện giả mạo', 'fake_profile', 'reviewing', 'ChuaXuLy', NULL, NULL, NULL, '2025-12-16 08:15:00'),
(4, 125, 140, 'Ngôn ngữ thô tục, thiếu văn hóa', 'inappropriate_content', 'pending', 'ChuaXuLy', NULL, NULL, NULL, '2025-12-16 10:30:00'),
(5, 130, 145, 'Yêu cầu tiền bạc, lừa đảo', 'scam', 'reviewing', 'ChuaXuLy', 1, 'Đang xem xét chứng cứ', '2025-12-16 14:00:00', '2025-12-16 11:00:00'),
(6, 135, 148, 'Spam tin nhắn quảng cáo', 'spam', 'pending', 'ChuaXuLy', NULL, NULL, NULL, '2025-12-16 13:45:00'),
(7, 102, 112, 'Hành vi không phù hợp trong chat', 'harassment', 'rejected', 'DaXuLy', 1, 'Không đủ bằng chứng', '2025-12-15 18:00:00', '2025-12-15 15:30:00'),
(8, 108, 118, 'Ảnh đại diện không phù hợp', 'inappropriate_content', 'resolved', 'DaXuLy', 1, 'Đã yêu cầu người dùng thay đổi ảnh', '2025-12-16 09:00:00', '2025-12-16 07:00:00');

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

--
-- Đang đổ dữ liệu cho bảng `channguoidung`
--

INSERT INTO `channguoidung` (`id`, `maNguoiChan`, `maNguoiBiChan`, `thoiDiemChan`) VALUES
(1, 100, 110, '2025-12-15 10:00:00'),
(2, 105, 120, '2025-12-15 11:30:00'),
(3, 115, 130, '2025-12-15 14:00:00'),
(4, 125, 135, '2025-12-15 16:30:00'),
(5, 140, 145, '2025-12-16 09:00:00'),
(8, 154, 153, '2025-12-24 13:29:40');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `email_verifications`
--

CREATE TABLE `email_verifications` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL COMMENT 'Email hoặc SĐT cần xác thực',
  `otp_code` varchar(10) NOT NULL COMMENT 'Mã OTP (6 số)',
  `password_hash` varchar(255) NOT NULL COMMENT 'Mật khẩu đã hash (lưu tạm)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Thời gian tạo OTP',
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Thời gian hết hạn OTP',
  `attempts` int(2) NOT NULL DEFAULT 0 COMMENT 'Số lần nhập sai',
  `is_verified` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0: Chưa xác thực, 1: Đã xác thực',
  `verified_at` timestamp NULL DEFAULT NULL COMMENT 'Thời gian xác thực thành công'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lưu OTP xác thực email';

--
-- Đang đổ dữ liệu cho bảng `email_verifications`
--

INSERT INTO `email_verifications` (`id`, `email`, `otp_code`, `password_hash`, `created_at`, `expires_at`, `attempts`, `is_verified`, `verified_at`) VALUES
(5, 'tranthanhchinhpk1@gmail.com', '088953', 'e602eecc405484fbc5c4540bb8be2d4c', '2025-12-16 09:32:01', '2025-12-16 03:42:01', 0, 1, '2025-12-16 09:32:30'),
(6, 'khanhduy201420@gmail.com', '723568', 'e602eecc405484fbc5c4540bb8be2d4c', '2025-12-16 09:34:26', '2025-12-16 03:44:26', 0, 1, '2025-12-16 09:35:23'),
(7, 'khanhduy201420@gmail.com', '602318', 'forgot_password_103', '2025-12-16 09:41:46', '2025-12-16 03:51:46', 0, 1, '2025-12-16 09:42:07'),
(9, 'tranthanhchinhpk1@gmail.com', '250199', 'forgot_password_102', '2025-12-16 09:44:31', '2025-12-16 03:54:31', 1, 1, '2025-12-16 09:45:03'),
(10, 'tranthanhchinhpk1@gmail.com', '529141', 'forgot_password_102', '2025-12-16 09:47:06', '2025-12-16 03:57:06', 5, 0, NULL),
(11, 'chinhhmt1@gmail.com', '596435', 'e602eecc405484fbc5c4540bb8be2d4c', '2025-12-16 09:52:32', '2025-12-16 04:02:32', 0, 0, NULL),
(12, 'tranthanhchinhhmtsdfsf@gmail.com', '237656', 'e602eecc405484fbc5c4540bb8be2d4c', '2025-12-16 09:54:14', '2025-12-16 04:04:14', 0, 0, NULL),
(13, 'thinh98.tt2@gmail.com', '306711', '3664ac6376027580e9e5d6baca19c7b5', '2025-12-16 10:43:35', '2025-12-16 04:53:35', 0, 1, '2025-12-16 10:44:05'),
(14, 'thinh98.tt2@gmail.com', '126869', 'forgot_password_150', '2025-12-18 04:39:47', '2025-12-17 22:49:47', 0, 1, '2025-12-18 04:40:06'),
(15, 'khanhduy201420@gmail.com', '372643', '7d080f6a8d770a68fb28cadd3e7fe8f9', '2025-12-23 11:32:16', '2025-12-23 11:42:16', 0, 1, '2025-12-23 11:32:31'),
(16, 'khanhduypubgmb2004@gmail.com', '116699', '7d080f6a8d770a68fb28cadd3e7fe8f9', '2025-12-23 11:34:09', '2025-12-23 11:44:09', 0, 1, '2025-12-23 11:35:02'),
(17, 'kdzy2004@gmail.com', '581399', '7d080f6a8d770a68fb28cadd3e7fe8f9', '2025-12-23 14:47:58', '2025-12-23 14:57:58', 0, 1, '2025-12-23 14:48:42');

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

--
-- Đang đổ dữ liệu cho bảng `ghepdoi`
--

INSERT INTO `ghepdoi` (`maGhepDoi`, `maNguoiA`, `maNguoiB`, `thoiDiemGhepDoi`, `trangThaiGhepDoi`) VALUES
(1, 100, 101, '2025-12-14 09:00:00', 'matched'),
(2, 102, 103, '2025-12-14 10:45:00', 'matched'),
(3, 104, 105, '2025-12-14 11:50:00', 'matched'),
(4, 106, 107, '2025-12-14 13:30:00', 'matched'),
(5, 108, 109, '2025-12-14 14:45:00', 'matched'),
(6, 110, 111, '2025-12-14 15:50:00', 'matched'),
(7, 112, 113, '2025-12-14 17:00:00', 'matched'),
(8, 114, 115, '2025-12-15 08:45:00', 'matched'),
(9, 116, 117, '2025-12-15 10:00:00', 'matched'),
(10, 118, 119, '2025-12-15 11:45:00', 'matched'),
(11, 120, 121, '2025-12-15 13:30:00', 'matched'),
(12, 122, 123, '2025-12-15 14:50:00', 'matched'),
(13, 124, 125, '2025-12-15 16:00:00', 'matched'),
(14, 126, 127, '2025-12-15 17:15:00', 'matched'),
(15, 128, 129, '2025-12-16 08:30:00', 'matched'),
(16, 130, 131, '2025-12-16 09:45:00', 'matched'),
(17, 132, 133, '2025-12-16 11:00:00', 'matched'),
(18, 134, 135, '2025-12-16 12:15:00', 'matched'),
(19, 136, 137, '2025-12-16 13:30:00', 'matched'),
(20, 138, 139, '2025-12-16 14:45:00', 'matched'),
(21, 140, 141, '2025-12-16 16:00:00', 'matched'),
(22, 142, 143, '2025-12-16 17:15:00', 'matched'),
(23, 144, 145, '2025-12-16 18:30:00', 'matched'),
(24, 146, 147, '2025-12-16 19:45:00', 'matched'),
(25, 148, 149, '2025-12-16 21:00:00', 'matched'),
(32, 152, 154, '2025-12-24 13:29:59', 'matched');

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
(1, 100, 'VIP', 'Active', '2026-01-16', '2025-12-16 08:00:00'),
(2, 101, 'Free', 'Active', NULL, '2025-12-16 08:15:00'),
(3, 102, 'VIP', 'Active', '2026-01-16', '2025-12-16 08:30:00'),
(4, 103, 'Free', 'Active', NULL, '2025-12-16 08:45:00'),
(5, 104, 'VIP', 'Active', '2026-01-16', '2025-12-16 09:00:00'),
(6, 105, 'Free', 'Active', NULL, '2025-12-16 09:15:00'),
(7, 106, 'Free', 'Active', NULL, '2025-12-16 09:30:00'),
(8, 107, 'Free', 'Active', NULL, '2025-12-16 09:45:00'),
(9, 108, 'VIP', 'Active', '2026-01-16', '2025-12-16 10:00:00'),
(10, 109, 'Free', 'Active', NULL, '2025-12-16 10:15:00'),
(11, 110, 'Free', 'Active', NULL, '2025-12-16 10:30:00'),
(12, 111, 'Free', 'Active', NULL, '2025-12-16 10:45:00'),
(13, 112, 'VIP', 'Active', '2026-01-16', '2025-12-16 11:00:00'),
(14, 113, 'Free', 'Active', NULL, '2025-12-16 11:15:00'),
(15, 114, 'Free', 'Active', NULL, '2025-12-16 11:30:00'),
(16, 115, 'Free', 'Active', NULL, '2025-12-16 11:45:00'),
(17, 116, 'VIP', 'Active', '2026-01-16', '2025-12-16 12:00:00'),
(18, 117, 'Free', 'Active', NULL, '2025-12-16 12:15:00'),
(19, 118, 'Free', 'Active', NULL, '2025-12-16 12:30:00'),
(20, 119, 'Free', 'Active', NULL, '2025-12-16 12:45:00'),
(21, 120, 'VIP', 'Active', '2026-01-16', '2025-12-16 13:00:00'),
(22, 121, 'Free', 'Active', NULL, '2025-12-16 13:15:00'),
(23, 122, 'Free', 'Active', NULL, '2025-12-16 13:30:00'),
(24, 123, 'Free', 'Active', NULL, '2025-12-16 13:45:00'),
(25, 124, 'VIP', 'Active', '2026-01-16', '2025-12-16 14:00:00'),
(26, 125, 'Free', 'Active', NULL, '2025-12-16 14:15:00'),
(27, 126, 'Free', 'Active', NULL, '2025-12-16 14:30:00'),
(28, 127, 'Free', 'Active', NULL, '2025-12-16 14:45:00'),
(29, 128, 'Free', 'Active', NULL, '2025-12-16 15:00:00'),
(30, 129, 'Free', 'Active', NULL, '2025-12-16 15:15:00'),
(31, 130, 'VIP', 'Active', '2026-01-16', '2025-12-16 15:30:00'),
(32, 131, 'Free', 'Active', NULL, '2025-12-16 15:45:00'),
(33, 132, 'Free', 'Active', NULL, '2025-12-16 16:00:00'),
(34, 133, 'Free', 'Active', NULL, '2025-12-16 16:15:00'),
(35, 134, 'VIP', 'Active', '2026-01-16', '2025-12-16 16:30:00'),
(36, 135, 'Free', 'Active', NULL, '2025-12-16 16:45:00'),
(37, 136, 'Free', 'Active', NULL, '2025-12-16 17:00:00'),
(38, 137, 'Free', 'Active', NULL, '2025-12-16 17:15:00'),
(39, 138, 'VIP', 'Active', '2026-01-16', '2025-12-16 17:30:00'),
(40, 139, 'Free', 'Active', NULL, '2025-12-16 17:45:00'),
(41, 140, 'Free', 'Active', NULL, '2025-12-16 18:00:00'),
(42, 141, 'Free', 'Active', NULL, '2025-12-16 18:15:00'),
(43, 142, 'VIP', 'Active', '2026-01-16', '2025-12-16 18:30:00'),
(44, 143, 'Free', 'Active', NULL, '2025-12-16 18:45:00'),
(45, 144, 'Free', 'Active', NULL, '2025-12-16 19:00:00'),
(46, 145, 'Free', 'Active', NULL, '2025-12-16 19:15:00'),
(47, 146, 'Free', 'Active', NULL, '2025-12-16 19:30:00'),
(48, 147, 'Free', 'Active', NULL, '2025-12-16 19:45:00'),
(49, 148, 'Free', 'Active', NULL, '2025-12-16 20:00:00'),
(50, 149, 'Free', 'Active', NULL, '2025-12-16 20:15:00'),
(51, 152, 'VIP', 'Active', '2026-01-23', '2025-12-23 21:47:24'),
(52, 154, 'VIP', 'Active', '2026-01-23', '2025-12-23 21:49:23'),
(53, 153, 'VIP', 'Active', '2026-01-23', '2025-12-23 21:49:43');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hoso`
--

CREATE TABLE `hoso` (
  `maHoSo` int(11) NOT NULL,
  `maNguoiDung` int(11) DEFAULT NULL,
  `ten` varchar(100) DEFAULT NULL,
  `ngaySinh` date DEFAULT NULL,
  `gioiTinh` enum('Nam','Nữ') DEFAULT NULL,
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
(76, 100, 'Nguyễn Tuấn Anh', '1994-05-12', 'Nam', 'Độc thân', 72, 175, 'Kết hôn', 'Đại học', 'Hà Nội', 'Bóng đá, Đọc sách, Du lịch, Chạy bộ', 'Mình là người năng động, thích thể thao đặc biệt là bóng đá. Công việc ổn định, muốn tìm người phù hợp để cùng nhau xây dựng tương lai.', 'public/uploads/avatars/man_01.jpg'),
(77, 101, 'Trần Minh Thư', '1997-08-23', 'Nữ', 'Độc thân', 51, 163, 'Hẹn hò', 'Đại học', 'Hồ Chí Minh', 'Yoga, Đọc sách, Nấu ăn, Xem phim', 'Mình là cô gái hiền lành, yêu thích yoga và đọc sách. Thích những buổi tối ấm cúng bên gia đình. Tìm kiếm người chân thành.', 'public/uploads/avatars/woman_01.jpg'),
(78, 102, 'Lê Hoàng Nam', '1995-03-15', 'Nam', 'Độc thân', 78, 180, 'Tìm hiểu', 'Thạc sĩ', 'Đà Nẵng', 'Bơi lội, Tập gym, Nhiếp ảnh, Du lịch', 'Mình làm việc trong lĩnh vực công nghệ, thích bơi lội và tập gym. Thích đi du lịch khám phá những vùng đất mới.', 'public/uploads/avatars/man_02.jpg'),
(79, 103, 'Phạm Thùy Linh', '1999-11-08', 'Nữ', 'Độc thân', 49, 161, 'Hẹn hò', 'Đại học', 'Cần Thơ', 'Piano, Vẽ tranh, Yoga, Cà phê', 'Mình yêu âm nhạc và nghệ thuật. Thích chơi piano và vẽ tranh trong thời gian rảnh. Mong tìm được người cùng đam mê nghệ thuật.', 'public/uploads/avatars/woman_02.jpg'),
(80, 104, 'Võ Quang Minh', '1993-07-20', 'Nam', 'Độc thân', 75, 177, 'Kết hôn', 'Đại học', 'Hải Phòng', 'Lập trình, Đọc sách, Game, Chạy bộ', 'Mình là lập trình viên, thích công nghệ và đọc sách. Tìm kiếm người phù hợp để cùng nhau phát triển và xây dựng gia đình.', 'public/uploads/avatars/man_03.jpg'),
(81, 105, 'Hoàng Lan Phương', '1998-02-14', 'Nữ', 'Độc thân', 52, 164, 'Hẹn hò', 'Cao đẳng', 'Bình Dương', 'Nấu ăn, Làm bánh, Shopping, Xem phim', 'Mình là cô gái đảm đang, thích nấu ăn và làm bánh. Mơ ước có một gia đình nhỏ ấm cúng và hạnh phúc.', 'public/uploads/avatars/woman_03.jpg'),
(82, 106, 'Đoàn Đức Thành', '1996-09-30', 'Nam', 'Độc thân', 70, 173, 'Tìm hiểu', 'Đại học', 'Đồng Nai', 'Xe máy, Du lịch, Nhiếp ảnh, Cà phê', 'Mình thích đi phượt bằng xe máy và khám phá những vùng đất mới. Yêu thích nhiếp ảnh và lưu giữ khoảnh khắc đẹp.', 'public/uploads/avatars/man_04.jpg'),
(83, 107, 'Bùi Ngọc Diệp', '2000-06-18', 'Nữ', 'Độc thân', 48, 160, 'Hẹn hò', 'Đại học', 'Long An', 'Khiêu vũ, Yoga, Thời trang, Du lịch', 'Mình yêu thích khiêu vũ và yoga. Thích thời trang và luôn muốn trở nên tốt hơn mỗi ngày.', 'public/uploads/avatars/woman_04.jpg'),
(84, 108, 'Đặng Văn Hùng', '1992-12-05', 'Nam', 'Độc thân', 80, 182, 'Kết hôn', 'Thạc sĩ', 'Vũng Tàu', 'Võ thuật, Bơi lội, Du lịch, Đọc sách', 'Mình là người mạnh mẽ, tự tin. Thích võ thuật và bơi lội. Hy vọng tìm được người phù hợp để cùng xây dựng gia đình.', 'public/uploads/avatars/man_05.jpg'),
(85, 109, 'Lý Hoàng Yến', '1997-04-22', 'Nữ', 'Độc thân', 50, 165, 'Hẹn hò', 'Đại học', 'Nha Trang', 'Bơi lội, Chụp ảnh, Du lịch, Nghe nhạc', 'Mình sống gần biển nên rất thích bơi lội và chụp ảnh biển. Yêu thích du lịch và khám phá những điều mới mẻ.', 'public/uploads/avatars/woman_05.jpg'),
(86, 110, 'Trần Thanh Tùng', '1995-10-11', 'Nam', 'Độc thân', 73, 176, 'Tìm hiểu', 'Đại học', 'Huế', 'Leo núi, Cắm trại, Guitar, Nhiếp ảnh', 'Mình yêu thiên nhiên và phiêu lưu. Thích leo núi và cắm trại. Biết chơi guitar và thích ca hát.', 'public/uploads/avatars/man_06.jpg'),
(87, 111, 'Phan Mỹ Duyên', '1998-07-28', 'Nữ', 'Độc thân', 53, 167, 'Hẹn hò', 'Đại học', 'Quy Nhơn', 'Vẽ tranh, Đọc sách, Yoga, Cà phê', 'Mình yêu nghệ thuật, thích vẽ tranh và đọc sách. Tìm kiếm người hiểu và chia sẻ đam mê của mình.', 'public/uploads/avatars/woman_06.jpg'),
(88, 112, 'Nguyễn Đức Mạnh', '1994-01-17', 'Nam', 'Độc thân', 77, 179, 'Kết hôn', 'Đại học', 'Vinh', 'Bóng rổ, Tập gym, Xem phim, Du lịch', 'Mình yêu thể thao, đặc biệt là bóng rổ. Tập gym thường xuyên để giữ sức khỏe. Mong tìm được người đồng hành.', 'public/uploads/avatars/man_07.jpg'),
(89, 113, 'Võ Thùy Linh', '1999-05-09', 'Nữ', 'Độc thân', 51, 162, 'Hẹn hò', 'Cao đẳng', 'Buôn Ma Thuột', 'Nấu ăn, Làm vườn, Xem phim, Đọc sách', 'Mình yêu thích sự giản dị và gần gũi với thiên nhiên. Thích trồng cây và chăm sóc vườn tược.', 'public/uploads/avatars/woman_07.jpg'),
(90, 114, 'Lê Văn Sơn', '1993-11-25', 'Nam', 'Độc thân', 71, 174, 'Tìm hiểu', 'Thạc sĩ', 'Pleiku', 'Câu cá, Cắm trại, Đọc sách, Cà phê', 'Mình yêu thiên nhiên, thích câu cá và cắm trại vào cuối tuần. Thích những điều giản dị trong cuộc sống.', 'public/uploads/avatars/man_08.jpg'),
(91, 115, 'Phạm Kim Chi', '1997-08-14', 'Nữ', 'Độc thân', 49, 161, 'Hẹn hò', 'Đại học', 'Rạch Giá', 'Piano, Nghe nhạc, Du lịch, Yoga', 'Mình yêu âm nhạc, đặc biệt là piano. Thích những giai điệu nhẹ nhàng và không gian yên tĩnh.', 'public/uploads/avatars/woman_08.jpg'),
(92, 116, 'Trương Đức Hiếu', '1996-03-06', 'Nam', 'Độc thân', 74, 178, 'Kết hôn', 'Đại học', 'Phan Thiết', 'Lướt sóng, Bơi lội, Du lịch, Nhiếp ảnh', 'Mình sống gần biển nên rất thích lướt sóng và bơi lội. Hy vọng tìm được người phù hợp để cùng xây dựng tương lai.', 'public/uploads/avatars/man_09.jpg'),
(93, 117, 'Ngô Thanh Hương', '1998-12-20', 'Nữ', 'Độc thân', 52, 165, 'Hẹn hò', 'Đại học', 'Mỹ Tho', 'Nấu ăn, Yoga, Đọc sách, Chụp ảnh', 'Mình là cô gái hiền lành, thích nấu ăn và chăm sóc gia đình. Yêu thích yoga để giữ dáng và sức khỏe.', 'public/uploads/avatars/woman_09.jpg'),
(94, 118, 'Đinh Quốc Bảo', '1995-06-30', 'Nam', 'Độc thân', 76, 177, 'Tìm hiểu', 'Đại học', 'Sóc Trăng', 'Bóng đá, Game, Xem phim, Cà phê', 'Mình vui vẻ, hòa đồng. Thích bóng đá và thường xuyên theo dõi các trận đấu. Mong tìm được người cùng sở thích.', 'public/uploads/avatars/man_10.jpg'),
(95, 119, 'Bùi Ngọc Lan', '2000-09-08', 'Nữ', 'Độc thân', 48, 159, 'Hẹn hò', 'Cao đẳng', 'Bạc Liêu', 'Khiêu vũ, Thời trang, Shopping, Du lịch', 'Mình yêu thích thời trang và khiêu vũ. Thích đi shopping và cập nhật xu hướng mới. Tìm kiếm người đàn ông lịch lãm.', 'public/uploads/avatars/woman_10.jpg'),
(96, 120, 'Đặng Văn Phong', '1994-02-19', 'Nam', 'Độc thân', 79, 181, 'Kết hôn', 'Thạc sĩ', 'Cà Mau', 'Võ thuật, Tập gym, Đọc sách, Du lịch', 'Mình là người tự tin, mạnh mẽ. Thích võ thuật và tập gym để rèn luyện sức khỏe. Mong tìm được người phù hợp.', 'public/uploads/avatars/man_11.jpg'),
(97, 121, 'Lê Thùy Trang', '1997-11-13', 'Nữ', 'Độc thân', 50, 164, 'Hẹn hò', 'Đại học', 'Trà Vinh', 'Vẽ tranh, Nghe nhạc, Yoga, Đọc sách', 'Mình yêu nghệ thuật, thích vẽ tranh và nghe nhạc. Tìm kiếm người hiểu và chia sẻ đam mê của mình.', 'public/uploads/avatars/woman_11.jpg'),
(98, 122, 'Trần Hoàng Anh', '1996-07-04', 'Nam', 'Độc thân', 72, 175, 'Tìm hiểu', 'Đại học', 'Bến Tre', 'Bơi lội, Câu cá, Du lịch, Cà phê', 'Mình thích những hoạt động ngoài trời như bơi lội và câu cá. Yêu thích du lịch và khám phá thiên nhiên.', 'public/uploads/avatars/man_12.jpg'),
(99, 123, 'Phạm Ngọc Mai', '1998-04-26', 'Nữ', 'Độc thân', 51, 163, 'Hẹn hò', 'Đại học', 'Vĩnh Long', 'Piano, Đọc sách, Du lịch, Yoga', 'Mình yêu âm nhạc, thích chơi piano và đọc sách. Mong tìm được người cùng yêu âm nhạc như mình.', 'public/uploads/avatars/woman_12.jpg'),
(100, 124, 'Vũ Văn Anh Tuấn', '1995-01-29', 'Nam', 'Độc thân', 75, 178, 'Kết hôn', 'Đại học', 'Đồng Tháp', 'Bóng rổ, Chạy bộ, Xem phim, Du lịch', 'Mình yêu thể thao, thích bóng rổ và chạy bộ. Tìm kiếm người phù hợp để cùng nhau xây dựng tương lai.', 'public/uploads/avatars/man_13.jpg'),
(101, 125, 'Hoàng Minh Hạnh', '1999-10-17', 'Nữ', 'Độc thân', 49, 162, 'Hẹn hò', 'Cao đẳng', 'An Giang', 'Nấu ăn, Làm bánh, Xem phim, Shopping', 'Mình là cô gái đảm đang, thích nấu ăn và làm bánh. Mơ ước có một gia đình nhỏ ấm cúng.', 'public/uploads/avatars/woman_13.jpg'),
(102, 126, 'Nguyễn Quang Đạt', '1993-08-12', 'Nam', 'Độc thân', 81, 183, 'Kết hôn', 'Thạc sĩ', 'Kiên Giang', 'Võ thuật, Bơi lội, Du lịch, Nhiếp ảnh', 'Mình mạnh mẽ, tự tin. Thích võ thuật và bơi lội. Hy vọng tìm được người phù hợp để cùng xây dựng gia đình.', 'public/uploads/avatars/man_14.jpg'),
(103, 127, 'Trần Hương Ly', '1998-05-21', 'Nữ', 'Độc thân', 52, 166, 'Hẹn hò', 'Đại học', 'Hậu Giang', 'Yoga, Đọc sách, Chụp ảnh, Du lịch', 'Mình yêu thích yoga và đọc sách. Thích đi du lịch và chụp ảnh để lưu giữ khoảnh khắc đẹp.', 'public/uploads/avatars/woman_14.jpg'),
(104, 128, 'Lê Đức Thiện', '1994-12-03', 'Nam', 'Độc thân', 73, 176, 'Tìm hiểu', 'Đại học', 'Sở Trăng', 'Leo núi, Cắm trại, Guitar, Nhiếp ảnh', 'Mình yêu thiên nhiên và phiêu lưu. Thích leo núi và cắm trại. Biết chơi guitar và thích hát.', 'public/uploads/avatars/man_15.jpg'),
(105, 129, 'Phạm Thanh Vân', '1997-09-16', 'Nữ', 'Độc thân', 50, 164, 'Hẹn hò', 'Đại học', 'Bạc Liêu', 'Khiêu vũ, Yoga, Thời trang, Cà phê', 'Mình yêu thích khiêu vũ và yoga. Thích thời trang và luôn muốn trở nên tốt hơn mỗi ngày.', 'public/uploads/avatars/woman_15.jpg'),
(106, 130, 'Võ Minh Khôi', '1996-06-07', 'Nam', 'Độc thân', 78, 180, 'Kết hôn', 'Đại học', 'Cà Mau', 'Bóng đá, Tập gym, Xem phim, Du lịch', 'Mình năng động, thích bóng đá và tập gym. Mong tìm được người phù hợp để cùng nhau xây dựng tương lai.', 'public/uploads/avatars/man_16.jpg'),
(107, 131, 'Bùi Kim Anh', '2000-03-24', 'Nữ', 'Độc thân', 48, 160, 'Hẹn hò', 'Cao đẳng', 'Trà Vinh', 'Nấu ăn, Làm vườn, Xem phim, Đọc sách', 'Mình yêu thích sự giản dị. Thích nấu ăn và chăm sóc vườn tược. Tìm kiếm người hiền lành.', 'public/uploads/avatars/woman_16.jpg'),
(108, 132, 'Đặng Thanh Long', '1995-11-28', 'Nam', 'Độc thân', 74, 177, 'Tìm hiểu', 'Thạc sĩ', 'Bến Tre', 'Lập trình, Đọc sách, Game, Cà phê', 'Mình làm trong lĩnh vực IT, thích lập trình và học công nghệ mới. Thích đọc sách và uống cà phê.', 'public/uploads/avatars/man_17.jpg'),
(109, 133, 'Nguyễn Phương Thảo', '1998-08-10', 'Nữ', 'Độc thân', 51, 163, 'Hẹn hò', 'Đại học', 'Vĩnh Long', 'Vẽ tranh, Piano, Yoga, Du lịch', 'Mình yêu nghệ thuật, thích vẽ tranh và chơi piano. Tìm kiếm người cùng đam mê nghệ thuật.', 'public/uploads/avatars/woman_17.jpg'),
(110, 134, 'Trần Minh Quân', '1994-04-18', 'Nam', 'Độc thân', 76, 179, 'Kết hôn', 'Đại học', 'Đồng Tháp', 'Bơi lội, Chạy bộ, Du lịch, Nhiếp ảnh', 'Mình yêu thể thao, thích bơi lội và chạy marathon. Mong tìm được người phù hợp để cùng xây dựng gia đình.', 'public/uploads/avatars/man_18.jpg'),
(111, 135, 'Lê Thị Kim Oanh', '1999-12-05', 'Nữ', 'Độc thân', 49, 161, 'Hẹn hò', 'Đại học', 'An Giang', 'Piano, Đọc sách, Yoga, Cà phê', 'Mình yêu âm nhạc và nghệ thuật. Thích chơi piano và đọc sách. Tìm kiếm người hiểu mình.', 'public/uploads/avatars/woman_18.jpg'),
(112, 136, 'Phạm Hoàng Hiếu', '1995-07-22', 'Nam', 'Độc thân', 72, 175, 'Tìm hiểu', 'Đại học', 'Kiên Giang', 'Câu cá, Cắm trại, Du lịch, Cà phê', 'Mình yêu thiên nhiên, thích câu cá và cắm trại. Mong tìm được người đồng hành cùng sở thích.', 'public/uploads/avatars/man_19.jpg'),
(113, 137, 'Võ Thanh Trúc', '1997-02-28', 'Nữ', 'Độc thân', 50, 165, 'Hẹn hò', 'Đại học', 'Hậu Giang', 'Khiêu vũ, Yoga, Thời trang, Du lịch', 'Mình yêu thích khiêu vũ và yoga. Thích đi du lịch và khám phá những điều mới mẻ.', 'public/uploads/avatars/woman_19.jpg'),
(114, 138, 'Hoàng Đức Hải', '1993-10-14', 'Nam', 'Độc thân', 80, 182, 'Kết hôn', 'Thạc sĩ', 'Sóc Trăng', 'Võ thuật, Tập gym, Đọc sách, Du lịch', 'Mình tự tin, mạnh mẽ. Thích võ thuật và tập gym. Hy vọng tìm được người phù hợp để cùng xây dựng gia đình.', 'public/uploads/avatars/man_20.jpg'),
(115, 139, 'Nguyễn Thùy Hiền', '1998-06-09', 'Nữ', 'Độc thân', 52, 164, 'Hẹn hò', 'Đại học', 'Bạc Liêu', 'Nấu ăn, Yoga, Đọc sách, Chụp ảnh', 'Mình là cô gái hiền lành, thích nấu ăn và yoga. Yêu thích những điều giản dị trong cuộc sống.', 'public/uploads/avatars/woman_20.jpg'),
(116, 140, 'Trần Quang Trung', '1996-01-31', 'Nam', 'Độc thân', 75, 178, 'Tìm hiểu', 'Đại học', 'Cà Mau', 'Bóng rổ, Game, Xem phim, Du lịch', 'Mình yêu thể thao, thích bóng rổ và game. Tìm kiếm người cùng sở thích để cùng trải nghiệm.', 'public/uploads/avatars/man_21.jpg'),
(117, 141, 'Lê Hoài Phương', '1999-09-23', 'Nữ', 'Độc thân', 48, 162, 'Hẹn hò', 'Cao đẳng', 'Trà Vinh', 'Vẽ tranh, Nghe nhạc, Làm vườn, Đọc sách', 'Mình yêu nghệ thuật, thích vẽ tranh và nghe nhạc. Thích trồng cây và chăm sóc vườn tược.', 'public/uploads/avatars/woman_21.jpg'),
(118, 142, 'Phạm Thanh Lâm', '1994-05-16', 'Nam', 'Độc thân', 77, 179, 'Kết hôn', 'Đại học', 'Bến Tre', 'Leo núi, Cắm trại, Nhiếp ảnh, Guitar', 'Mình yêu thiên nhiên và phiêu lưu. Thích leo núi và chơi guitar. Mong tìm được người đồng hành.', 'public/uploads/avatars/man_22.jpg'),
(119, 143, 'Vũ Hoàng Linh', '1997-12-07', 'Nữ', 'Độc thân', 51, 165, 'Hẹn hò', 'Đại học', 'Vĩnh Long', 'Piano, Yoga, Du lịch, Chụp ảnh', 'Mình yêu âm nhạc, thích chơi piano và yoga. Thích đi du lịch và chụp ảnh những cảnh đẹp.', 'public/uploads/avatars/woman_22.jpg'),
(120, 144, 'Bùi Minh Tâm', '1995-08-29', 'Nam', 'Độc thân', 73, 176, 'Tìm hiểu', 'Thạc sĩ', 'Đồng Tháp', 'Lập trình, Đọc sách, Cà phê, Game', 'Mình làm trong ngành IT, thích lập trình và công nghệ. Thích đọc sách và cà phê. Mong tìm người hiểu mình.', 'public/uploads/avatars/man_23.jpg'),
(121, 145, 'Đặng Thanh Toàn', '1996-03-20', 'Nam', 'Độc thân', 79, 181, 'Kết hôn', 'Đại học', 'An Giang', 'Bơi lội, Tập gym, Du lịch, Nhiếp ảnh', 'Mình năng động, thích bơi lội và tập gym. Hy vọng tìm được người phù hợp để cùng xây dựng tương lai.', 'public/uploads/avatars/man_24.jpg'),
(122, 146, 'Nguyễn Thị Hằng', '1998-11-11', 'Nữ', 'Độc thân', 50, 163, 'Hẹn hò', 'Đại học', 'Kiên Giang', 'Nấu ăn, Làm bánh, Yoga, Xem phim', 'Mình đảm đang, thích nấu ăn và làm bánh. Thích yoga để giữ dáng. Tìm kiếm người chân thành.', 'public/uploads/avatars/woman_23.jpg'),
(123, 147, 'Trần Hoàng Vũ', '1993-07-04', 'Nam', 'Độc thân', 76, 178, 'Kết hôn', 'Thạc sĩ', 'Hậu Giang', 'Võ thuật, Bóng đá, Đọc sách, Du lịch', 'Mình tự tin, mạnh mẽ. Thích võ thuật và bóng đá. Mong tìm được người phù hợp để cùng xây dựng gia đình.', 'public/uploads/avatars/man_25.jpg'),
(124, 148, 'Lê Thanh Bình', '1997-04-26', 'Nam', 'Độc thân', 71, 174, 'Tìm hiểu', 'Đại học', 'Sóc Trăng', 'Chạy bộ, Câu cá, Du lịch, Cà phê', 'Mình thích những hoạt động ngoài trời như chạy bộ và câu cá. Yêu thích du lịch và khám phá.', 'public/uploads/avatars/man_26.jpg'),
(125, 149, 'Phạm Ngọc Ánh', '1999-01-19', 'Nữ', 'Độc thân', 49, 161, 'Hẹn hò', 'Đại học', 'Bạc Liêu', 'Khiêu vũ, Thời trang, Shopping, Du lịch', 'Mình yêu thích khiêu vũ và thời trang. Thích đi shopping và cập nhật xu hướng mới.', 'public/uploads/avatars/woman_24.jpg'),
(126, 150, 'Nguyễn Hoàng Khánh Duy', '2002-08-17', 'Nam', 'Độc thân', 80, 178, 'Kết bạn', 'Cao đẳng', 'Đà Nẵng', 'Thể thao, Nấu ăn, Chụp ảnh, Học ngoại ngữ', 'hihhi', 'public/uploads/avatars/avatar_150_1766032874.jpg'),
(127, 152, 'Khánh Duy', '2000-01-17', 'Nam', 'Độc thân', 30, 100, 'Kết bạn', 'Đại học', 'Bến Tre', 'Đọc sách, Xem phim, Nghe nhạc', 'haha', 'public/uploads/avatars/avatar_152_1766489596.jpg'),
(128, 153, 'Thịnh', '1991-11-17', 'Nữ', 'Độc thân', 30, 100, 'Kết bạn', 'Cao đẳng', 'Cà Mau', 'Đọc sách, Xem phim, Nghe nhạc', 'haha', 'public/uploads/avatars/avatar_153_1766489749.jpg'),
(129, 154, 'Ly', '1991-11-18', 'Nam', 'Độc thân', 31, 100, 'Tìm hiểu', 'Trung học', 'Đồng Nai', 'Đọc sách, Xem phim, Nghe nhạc', 'gaga', 'public/uploads/avatars/avatar_154_1766501356.jpg');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hotro`
--

CREATE TABLE `hotro` (
  `maHoTro` int(11) NOT NULL,
  `maNguoiDung` int(11) NOT NULL,
  `tieuDe` varchar(255) NOT NULL,
  `noiDung` text NOT NULL,
  `loai` enum('general','payment','technical','report','other') DEFAULT 'general',
  `trangThai` enum('pending','in_progress','resolved','closed') DEFAULT 'pending',
  `maAdminPhuTrach` int(11) DEFAULT NULL,
  `phanHoi` text DEFAULT NULL,
  `thoiDiemTao` datetime DEFAULT current_timestamp(),
  `thoiDiemCapNhat` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `hotro`
--

INSERT INTO `hotro` (`maHoTro`, `maNguoiDung`, `tieuDe`, `noiDung`, `loai`, `trangThai`, `maAdminPhuTrach`, `phanHoi`, `thoiDiemTao`, `thoiDiemCapNhat`) VALUES
(1, 100, 'Không thể đăng nhập', 'Tôi không thể đăng nhập vào tài khoản. Hệ thống báo lỗi mật khẩu sai nhưng tôi chắc chắn đã nhập đúng.', 'technical', 'resolved', 1, 'Đã reset mật khẩu và gửi link qua email. Vấn đề đã được giải quyết.', '2025-12-14 09:00:00', '2025-12-14 10:30:00'),
(2, 105, 'Thanh toán VIP không thành công', 'Tôi đã thanh toán gói VIP nhưng chưa được nâng cấp tài khoản.', 'payment', 'resolved', 1, 'Đã kiểm tra và kích hoạt gói VIP cho tài khoản của bạn.', '2025-12-14 14:00:00', '2025-12-14 15:00:00'),
(3, 110, 'Hỏi về chính sách hoàn tiền', 'Tôi muốn biết chính sách hoàn tiền khi hủy gói VIP như thế nào?', 'general', 'resolved', 1, 'Theo chính sách của chúng tôi, không hoàn tiền cho gói đã kích hoạt. Chi tiết đã được gửi qua email.', '2025-12-15 10:00:00', '2025-12-15 11:00:00'),
(4, 115, 'Báo cáo lỗi hiển thị', 'Ảnh đại diện không hiển thị đúng trên điện thoại của tôi.', 'technical', 'in_progress', 1, 'Đang kiểm tra vấn đề. Vui lòng thử xóa cache và đăng nhập lại.', '2025-12-15 16:30:00', '2025-12-15 17:00:00'),
(5, 120, 'Làm sao để xóa tài khoản?', 'Tôi muốn xóa tài khoản của mình. Hướng dẫn tôi với.', 'general', 'resolved', 1, 'Đã hướng dẫn các bước xóa tài khoản qua email. Lưu ý dữ liệu sẽ bị xóa vĩnh viễn.', '2025-12-16 08:00:00', '2025-12-16 09:00:00'),
(6, 125, 'Không nhận được thông báo', 'Tôi không nhận được thông báo khi có người nhắn tin.', 'technical', 'pending', NULL, NULL, '2025-12-16 11:00:00', NULL),
(7, 130, 'Tài khoản bị khóa', 'Tại sao tài khoản của tôi bị khóa? Tôi không vi phạm gì cả.', 'report', 'in_progress', 1, 'Đang xem xét lại hồ sơ. Sẽ phản hồi trong 24h.', '2025-12-16 13:00:00', '2025-12-16 14:00:00'),
(8, 135, 'Hỏi về tính năng VIP', 'Gói VIP có những quyền lợi gì? Có thể dùng mã giảm giá không?', 'general', 'pending', NULL, NULL, '2025-12-16 15:30:00', NULL),
(9, 140, 'Lỗi không gửi được tin nhắn', 'Hệ thống báo lỗi khi tôi gửi tin nhắn. Vui lòng kiểm tra giúp.', 'technical', 'pending', NULL, NULL, '2025-12-16 17:00:00', NULL),
(10, 145, 'Đề xuất tính năng mới', 'Tôi nghĩ nên thêm tính năng video call để người dùng có thể nói chuyện trực tiếp.', 'other', 'closed', 1, 'Cảm ơn góp ý. Chúng tôi sẽ xem xét trong các phiên bản tiếp theo.', '2025-12-16 18:30:00', '2025-12-16 19:00:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lichsuvipham`
--

CREATE TABLE `lichsuvipham` (
  `maViPham` int(11) NOT NULL,
  `maNguoiDung` int(11) NOT NULL,
  `loaiViPham` varchar(100) NOT NULL,
  `moTa` text DEFAULT NULL,
  `maBaoCao` int(11) DEFAULT NULL,
  `hanhDong` enum('warning','temporary_ban','permanent_ban','content_removal') NOT NULL,
  `maAdminXuLy` int(11) DEFAULT NULL,
  `thoiDiemViPham` datetime DEFAULT current_timestamp(),
  `thoiDiemHetHan` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `lichsuvipham`
--

INSERT INTO `lichsuvipham` (`maViPham`, `maNguoiDung`, `loaiViPham`, `moTa`, `maBaoCao`, `hanhDong`, `maAdminXuLy`, `thoiDiemViPham`, `thoiDiemHetHan`) VALUES
(1, 110, 'Spam', 'Gửi tin nhắn spam liên tục cho nhiều người dùng', 1, 'warning', 1, '2025-12-15 14:30:00', NULL),
(2, 125, 'Quấy rối', 'Hành vi quấy rối, gửi nội dung không phù hợp', 2, 'temporary_ban', 1, '2025-12-15 16:00:00', '2025-12-22 16:00:00'),
(3, 140, 'Ngôn ngữ thô tục', 'Sử dụng ngôn ngữ thô tục, thiếu văn hóa trong chat', 4, 'warning', 1, '2025-12-16 14:00:00', NULL),
(4, 118, 'Ảnh không phù hợp', 'Sử dụng ảnh đại diện không phù hợp với quy định', 8, 'content_removal', 1, '2025-12-16 09:00:00', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `magiamgia`
--

CREATE TABLE `magiamgia` (
  `maMaGiamGia` int(11) NOT NULL,
  `maCoupon` varchar(50) NOT NULL,
  `tenChuongTrinh` varchar(255) NOT NULL,
  `loaiGiam` enum('percent','fixed') DEFAULT 'percent',
  `giaTriGiam` decimal(10,2) NOT NULL,
  `giaTriToiDa` decimal(10,2) DEFAULT NULL,
  `soLuongToiDa` int(11) DEFAULT NULL,
  `soLuongDaSuDung` int(11) DEFAULT 0,
  `ngayBatDau` datetime NOT NULL,
  `ngayKetThuc` datetime NOT NULL,
  `trangThai` enum('active','inactive','expired') DEFAULT 'active',
  `apDungCho` enum('all','new_user','vip_only') DEFAULT 'all',
  `thoiDiemTao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `magiamgia`
--

INSERT INTO `magiamgia` (`maMaGiamGia`, `maCoupon`, `tenChuongTrinh`, `loaiGiam`, `giaTriGiam`, `giaTriToiDa`, `soLuongToiDa`, `soLuongDaSuDung`, `ngayBatDau`, `ngayKetThuc`, `trangThai`, `apDungCho`, `thoiDiemTao`) VALUES
(1, 'WELCOME2025', 'Chào mừng năm mới 2025', 'percent', 50.00, 100000.00, 100, 35, '2025-01-01 00:00:00', '2025-01-31 23:59:59', 'active', 'new_user', '2024-12-20 10:00:00'),
(2, 'VIP50OFF', 'Giảm 50% gói VIP', 'percent', 50.00, 150000.00, 50, 12, '2025-12-01 00:00:00', '2025-12-31 23:59:59', 'active', 'all', '2025-11-25 08:00:00'),
(3, 'NEWYEAR100K', 'Giảm 100K đầu năm', 'fixed', 100000.00, NULL, 200, 87, '2025-01-01 00:00:00', '2025-01-15 23:59:59', 'active', 'all', '2024-12-28 15:00:00'),
(4, 'VIPONLY30', 'Ưu đãi 30% VIP cũ', 'percent', 30.00, 80000.00, 30, 18, '2025-12-10 00:00:00', '2025-12-25 23:59:59', 'active', 'vip_only', '2025-12-05 10:00:00'),
(5, 'SUMMER2024', 'Khuyến mãi hè 2024', 'percent', 40.00, 120000.00, 150, 150, '2024-06-01 00:00:00', '2024-08-31 23:59:59', 'expired', 'all', '2024-05-20 09:00:00'),
(6, 'FLASH50K', 'Flash sale giảm 50K', 'fixed', 50000.00, NULL, 100, 73, '2025-12-15 00:00:00', '2025-12-20 23:59:59', 'active', 'all', '2025-12-14 12:00:00'),
(7, 'LOYALTY20', 'Ưu đãi khách hàng thân thiết', 'percent', 20.00, 60000.00, 80, 45, '2025-12-01 00:00:00', '2025-12-31 23:59:59', 'active', 'vip_only', '2025-11-28 14:00:00'),
(8, 'TRIAL7DAY', 'Dùng thử 7 ngày miễn phí', 'percent', 100.00, NULL, 500, 234, '2025-01-01 00:00:00', '2025-03-31 23:59:59', 'active', 'new_user', '2024-12-30 11:00:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nguoidung`
--

CREATE TABLE `nguoidung` (
  `maNguoiDung` int(11) NOT NULL,
  `tenDangNhap` varchar(50) NOT NULL,
  `matKhau` varchar(255) NOT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0: Chưa xác thực, 1: Đã xác thực email',
  `trangThaiNguoiDung` enum('active','banned','inactive') DEFAULT 'active',
  `lanHoatDongCuoi` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `role` varchar(255) NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `nguoidung`
--

INSERT INTO `nguoidung` (`maNguoiDung`, `tenDangNhap`, `matKhau`, `email_verified`, `trangThaiNguoiDung`, `lanHoatDongCuoi`, `role`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500', 1, 'active', '2025-12-16 17:40:31', 'admin'),
(100, 'nguyentuananh@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 08:00:00', 'user'),
(101, 'tranminhthu@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 08:15:00', 'user'),
(102, 'lehoangnam@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 08:30:00', 'user'),
(103, 'phamthuylinh@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 08:45:00', 'user'),
(104, 'voquangminh@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 09:00:00', 'user'),
(105, 'hoanglanphuong@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 09:15:00', 'user'),
(106, 'doanducthanh@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 09:30:00', 'user'),
(107, 'buingocdiep@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 09:45:00', 'user'),
(108, 'dangvanhung@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 10:00:00', 'user'),
(109, 'lyhoangyen@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 10:15:00', 'user'),
(110, 'tranthanhtung@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 10:30:00', 'user'),
(111, 'phanmyduyen@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 10:45:00', 'user'),
(112, 'nguyenducmanh@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 11:00:00', 'user'),
(113, 'vothuylinh@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 11:15:00', 'user'),
(114, 'levanson@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 11:30:00', 'user'),
(115, 'phamkimchi@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 11:45:00', 'user'),
(116, 'truongduchieu@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 12:00:00', 'user'),
(117, 'ngothanhhuong@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 12:15:00', 'user'),
(118, 'dinhquocbao@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 12:30:00', 'user'),
(119, 'buingoclan@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 12:45:00', 'user'),
(120, 'dangvanphong@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 13:00:00', 'user'),
(121, 'lethuytrang@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 13:15:00', 'user'),
(122, 'tranhoanganh@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 13:30:00', 'user'),
(123, 'phamngocmai@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 13:45:00', 'user'),
(124, 'vuvananhtuan@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 14:00:00', 'user'),
(125, 'hoangminhhanh@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 14:15:00', 'user'),
(126, 'nguyenquangdat@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 14:30:00', 'user'),
(127, 'tranhuongly@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 14:45:00', 'user'),
(128, 'leducthien@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 15:00:00', 'user'),
(129, 'phamthanhvan@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 15:15:00', 'user'),
(130, 'vominhkhoi@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 15:30:00', 'user'),
(131, 'buikimanh@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 15:45:00', 'user'),
(132, 'dangthanhlong@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 16:00:00', 'user'),
(133, 'nguyenphuongthao@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 16:15:00', 'user'),
(134, 'tranminhquan@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 16:30:00', 'user'),
(135, 'lethikimoanh@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 16:45:00', 'user'),
(136, 'phamhoanghieu@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 17:00:00', 'user'),
(137, 'vothanhtruc@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 17:15:00', 'user'),
(138, 'hoangduchai@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 17:30:00', 'user'),
(139, 'nguyenthuyhien@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 17:45:00', 'user'),
(140, 'tranquangtrung@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 18:00:00', 'user'),
(141, 'lehoaiphuong@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 18:15:00', 'user'),
(142, 'phamthanhlam@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 18:30:00', 'user'),
(143, 'vuhoanglinh@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 18:45:00', 'user'),
(144, 'buiminhtam@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 19:00:00', 'user'),
(145, 'dangthanhtoan@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 19:15:00', 'user'),
(146, 'nguyenthihang@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 19:30:00', 'user'),
(147, 'tranhoangvu@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 19:45:00', 'user'),
(148, 'lethanhbinh@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 20:00:00', 'user'),
(149, 'phamngocanh@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 'active', '2025-12-16 20:15:00', 'user'),
(150, 'thinh98.tt2@gmail.com', '3f8614840e0a04b99135d857500865d6', 1, 'active', '2025-12-18 12:07:42', 'user'),
(151, 'test', 'cc03e747a6afbbcbf8be7668acfebee5', 0, 'active', '2025-12-18 11:36:16', 'user'),
(152, 'khanhduy201420@gmail.com', '7d080f6a8d770a68fb28cadd3e7fe8f9', 1, 'active', '2025-12-24 13:37:53', 'user'),
(153, 'khanhduypubgmb2004@gmail.com', '7d080f6a8d770a68fb28cadd3e7fe8f9', 1, 'active', '2025-12-24 13:37:33', 'user'),
(154, 'kdzy2004@gmail.com', '7d080f6a8d770a68fb28cadd3e7fe8f9', 1, 'active', '2025-12-24 13:38:20', 'user');

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
(1, 100, 'login', '192.168.1.10', '2025-12-16 08:00:00'),
(2, 101, 'message', '192.168.1.11', '2025-12-16 09:15:00'),
(3, 102, 'like', '192.168.1.12', '2025-12-16 10:15:00'),
(4, 110, 'message', '192.168.1.20', '2025-12-15 10:30:00'),
(5, 110, 'message', '192.168.1.20', '2025-12-15 10:31:00'),
(6, 110, 'message', '192.168.1.20', '2025-12-15 10:32:00'),
(7, 115, 'login', '192.168.1.25', '2025-12-16 08:30:00'),
(8, 120, 'search', '192.168.1.30', '2025-12-16 13:00:00'),
(9, 125, 'message', '192.168.1.35', '2025-12-15 12:00:00'),
(10, 130, 'like', '192.168.1.40', '2025-12-16 09:45:00'),
(11, 135, 'search', '192.168.1.45', '2025-12-16 19:30:00'),
(12, 140, 'message', '192.168.1.50', '2025-12-16 16:15:00'),
(13, 145, 'login', '192.168.1.55', '2025-12-16 19:00:00'),
(14, 148, 'like', '192.168.1.58', '2025-12-16 20:30:00'),
(15, 149, 'search', '192.168.1.59', '2025-12-16 18:00:00'),
(50, 150, 'like_action', '::1', '2025-12-16 17:57:01'),
(51, 150, 'like_action', '::1', '2025-12-16 17:57:06'),
(52, 150, 'like_action', '::1', '2025-12-16 17:57:10'),
(53, 150, 'like_action', '::1', '2025-12-16 17:57:15'),
(54, 150, 'like_action', '::1', '2025-12-16 17:57:22'),
(55, 154, 'send_message', '::1', '2025-12-24 13:03:50'),
(56, 154, 'send_message', '::1', '2025-12-24 13:05:50'),
(57, 152, 'send_message', '::1', '2025-12-24 13:06:02'),
(58, 152, 'send_message', '::1', '2025-12-24 13:06:20'),
(59, 154, 'send_message', '::1', '2025-12-24 13:07:23'),
(60, 154, 'send_message', '::1', '2025-12-24 13:07:32'),
(61, 154, 'send_message', '::1', '2025-12-24 13:07:38'),
(62, 152, 'send_message', '::1', '2025-12-24 13:11:26'),
(63, 152, 'send_message', '::1', '2025-12-24 13:11:32'),
(64, 152, 'send_message', '::1', '2025-12-24 13:11:37'),
(65, 152, 'send_message', '::1', '2025-12-24 13:11:46'),
(66, 154, 'send_message', '::1', '2025-12-24 13:14:52'),
(67, 154, 'send_message', '::1', '2025-12-24 13:15:00'),
(68, 154, 'send_message', '::1', '2025-12-24 13:15:03'),
(69, 152, 'send_message', '::1', '2025-12-24 13:15:36'),
(70, 153, 'send_message', '::1', '2025-12-24 13:15:43'),
(71, 154, 'send_message', '::1', '2025-12-24 13:30:45'),
(72, 152, 'send_message', '::1', '2025-12-24 13:30:54'),
(73, 154, 'send_message', '::1', '2025-12-24 13:31:05'),
(74, 154, 'send_message', '::1', '2025-12-24 13:31:41'),
(75, 154, 'send_message', '::1', '2025-12-24 13:33:54');

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
(1, 100, 199000.00, '2025-12-16 08:00:00'),
(2, 102, 199000.00, '2025-12-16 08:30:00'),
(3, 104, 199000.00, '2025-12-16 09:00:00'),
(4, 108, 199000.00, '2025-12-16 10:00:00'),
(5, 112, 199000.00, '2025-12-16 11:00:00'),
(6, 116, 199000.00, '2025-12-16 12:00:00'),
(7, 120, 199000.00, '2025-12-16 13:00:00'),
(8, 124, 199000.00, '2025-12-16 14:00:00'),
(9, 130, 199000.00, '2025-12-16 15:30:00'),
(10, 134, 199000.00, '2025-12-16 16:30:00'),
(11, 138, 199000.00, '2025-12-16 17:30:00'),
(12, 142, 199000.00, '2025-12-16 18:30:00'),
(13, 152, 99000.00, '2025-12-23 21:47:24'),
(14, 154, 99000.00, '2025-12-23 21:49:23'),
(15, 153, 99000.00, '2025-12-23 21:49:43');

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

--
-- Đang đổ dữ liệu cho bảng `thich`
--

INSERT INTO `thich` (`maThich`, `maNguoiThich`, `maNguoiDuocThich`, `thoiDiemThich`) VALUES
(1, 100, 101, '2025-12-14 08:30:00'),
(2, 101, 100, '2025-12-14 09:00:00'),
(3, 102, 103, '2025-12-14 10:15:00'),
(4, 103, 102, '2025-12-14 10:45:00'),
(5, 104, 105, '2025-12-14 11:20:00'),
(6, 105, 104, '2025-12-14 11:50:00'),
(7, 106, 107, '2025-12-14 13:00:00'),
(8, 107, 106, '2025-12-14 13:30:00'),
(9, 108, 109, '2025-12-14 14:15:00'),
(10, 109, 108, '2025-12-14 14:45:00'),
(11, 110, 111, '2025-12-14 15:20:00'),
(12, 111, 110, '2025-12-14 15:50:00'),
(13, 112, 113, '2025-12-14 16:30:00'),
(14, 113, 112, '2025-12-14 17:00:00'),
(15, 114, 115, '2025-12-15 08:15:00'),
(16, 115, 114, '2025-12-15 08:45:00'),
(17, 116, 117, '2025-12-15 09:30:00'),
(18, 117, 116, '2025-12-15 10:00:00'),
(19, 118, 119, '2025-12-15 11:15:00'),
(20, 119, 118, '2025-12-15 11:45:00'),
(21, 120, 121, '2025-12-15 13:00:00'),
(22, 121, 120, '2025-12-15 13:30:00'),
(23, 122, 123, '2025-12-15 14:20:00'),
(24, 123, 122, '2025-12-15 14:50:00'),
(25, 124, 125, '2025-12-15 15:30:00'),
(26, 125, 124, '2025-12-15 16:00:00'),
(27, 126, 127, '2025-12-15 16:45:00'),
(28, 127, 126, '2025-12-15 17:15:00'),
(29, 128, 129, '2025-12-16 08:00:00'),
(30, 129, 128, '2025-12-16 08:30:00'),
(31, 130, 131, '2025-12-16 09:15:00'),
(32, 131, 130, '2025-12-16 09:45:00'),
(33, 132, 133, '2025-12-16 10:30:00'),
(34, 133, 132, '2025-12-16 11:00:00'),
(35, 134, 135, '2025-12-16 11:45:00'),
(36, 135, 134, '2025-12-16 12:15:00'),
(37, 136, 137, '2025-12-16 13:00:00'),
(38, 137, 136, '2025-12-16 13:30:00'),
(39, 138, 139, '2025-12-16 14:15:00'),
(40, 139, 138, '2025-12-16 14:45:00'),
(41, 140, 141, '2025-12-16 15:30:00'),
(42, 141, 140, '2025-12-16 16:00:00'),
(43, 142, 143, '2025-12-16 16:45:00'),
(44, 143, 142, '2025-12-16 17:15:00'),
(45, 144, 145, '2025-12-16 18:00:00'),
(46, 145, 144, '2025-12-16 18:30:00'),
(47, 146, 147, '2025-12-16 19:15:00'),
(48, 147, 146, '2025-12-16 19:45:00'),
(49, 148, 149, '2025-12-16 20:30:00'),
(50, 149, 148, '2025-12-16 21:00:00'),
(101, 150, 121, '2025-12-16 17:57:01'),
(102, 150, 135, '2025-12-16 17:57:06'),
(103, 150, 133, '2025-12-16 17:57:10'),
(104, 150, 131, '2025-12-16 17:57:15'),
(105, 150, 125, '2025-12-16 17:57:22'),
(141, 152, 109, '2025-12-24 12:46:46'),
(142, 154, 140, '2025-12-24 13:04:09'),
(143, 154, 143, '2025-12-24 13:04:10'),
(146, 154, 152, '2025-12-24 13:29:48'),
(147, 152, 154, '2025-12-24 13:29:59');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thongbaoheothong`
--

CREATE TABLE `thongbaoheothong` (
  `maThongBao` int(11) NOT NULL,
  `tieuDe` varchar(255) NOT NULL,
  `noiDung` text NOT NULL,
  `loai` enum('info','warning','promotion','maintenance') DEFAULT 'info',
  `doUuTien` enum('low','medium','high','urgent') DEFAULT 'medium',
  `guiToi` enum('all','vip','specific') DEFAULT 'all',
  `maNguoiDungCuThe` text DEFAULT NULL,
  `trangThai` enum('draft','scheduled','sent') DEFAULT 'draft',
  `maAdminTao` int(11) DEFAULT NULL,
  `thoiDiemGui` datetime DEFAULT NULL,
  `thoiDiemTao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `thongbaoheothong`
--

INSERT INTO `thongbaoheothong` (`maThongBao`, `tieuDe`, `noiDung`, `loai`, `doUuTien`, `guiToi`, `maNguoiDungCuThe`, `trangThai`, `maAdminTao`, `thoiDiemGui`, `thoiDiemTao`) VALUES
(1, 'Chào mừng năm mới 2025', 'Chúc mừng năm mới! Hệ thống tặng bạn mã giảm giá 50% cho gói VIP. Mã: WELCOME2025', 'promotion', 'high', 'all', NULL, 'sent', 1, '2025-01-01 00:00:00', '2024-12-30 10:00:00'),
(2, 'Bảo trì hệ thống', 'Hệ thống sẽ bảo trì vào ngày 20/12/2025 từ 02:00 - 04:00 sáng. Vui lòng sắp xếp thời gian phù hợp.', 'maintenance', 'urgent', 'all', NULL, 'sent', 1, '2025-12-18 09:00:00', '2025-12-17 15:00:00'),
(3, 'Cập nhật chính sách mới', 'Chúng tôi đã cập nhật chính sách bảo mật và điều khoản sử dụng. Vui lòng đọc kỹ tại mục Cài đặt.', 'info', 'medium', 'all', NULL, 'sent', 1, '2025-12-10 10:00:00', '2025-12-09 14:00:00'),
(4, 'Ưu đãi dành riêng cho VIP', 'Bạn là thành viên VIP! Nhận ngay mã giảm giá 30% cho lần gia hạn tiếp theo. Mã: VIPONLY30', 'promotion', 'high', 'vip', NULL, 'sent', 1, '2025-12-10 00:00:00', '2025-12-09 10:00:00'),
(5, 'Tính năng mới: Video Call', 'Chúng tôi vừa ra mắt tính năng Video Call! Hãy thử ngay để kết nối gần hơn với người bạn đời.', 'info', 'medium', 'all', NULL, 'sent', 1, '2025-12-05 08:00:00', '2025-12-04 16:00:00'),
(6, 'Flash Sale - Giảm 50K', 'Flash Sale trong 24h! Giảm ngay 50K cho gói VIP. Nhanh tay đăng ký. Mã: FLASH50K', 'promotion', 'urgent', 'all', NULL, 'sent', 1, '2025-12-15 00:00:00', '2025-12-14 18:00:00'),
(7, 'Cảnh báo an toàn', 'Lưu ý: Không chia sẻ thông tin cá nhân, tài khoản ngân hàng với người lạ. Báo cáo ngay nếu gặp hành vi lừa đảo.', 'warning', 'high', 'all', NULL, 'sent', 1, '2025-12-01 09:00:00', '2025-11-30 14:00:00'),
(8, 'Thông báo cho người dùng mới', 'Chào mừng bạn đến với ứng dụng hẹn hò! Hãy hoàn thiện hồ sơ để tăng cơ hội ghép đôi.', 'info', 'medium', 'all', NULL, 'scheduled', 1, '2025-12-20 10:00:00', '2025-12-16 15:00:00');

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

--
-- Đang đổ dữ liệu cho bảng `thongkengay`
--

INSERT INTO `thongkengay` (`maThongKe`, `ngay`, `soNguoiDungMoi`, `soGhepDoiMoi`, `soTinNhan`, `soBaoCao`, `ngayTao`) VALUES
(1, '2025-12-14', 15, 7, 45, 2, '2025-12-14 23:59:00'),
(2, '2025-12-15', 18, 8, 52, 3, '2025-12-15 23:59:00'),
(3, '2025-12-16', 17, 10, 48, 3, '2025-12-16 23:59:00'),
(4, '2025-12-13', 12, 5, 38, 1, '2025-12-13 23:59:00'),
(5, '2025-12-12', 14, 6, 42, 0, '2025-12-12 23:59:00'),
(6, '2025-12-11', 10, 4, 35, 2, '2025-12-11 23:59:00'),
(7, '2025-12-10', 16, 7, 50, 1, '2025-12-10 23:59:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `timkiemghepdoi`
--

CREATE TABLE `timkiemghepdoi` (
  `maTimKiem` int(11) NOT NULL,
  `maNguoiDung` int(11) NOT NULL,
  `trangThai` enum('searching','matched','cancelled') DEFAULT 'searching',
  `thoiDiemBatDau` datetime DEFAULT current_timestamp(),
  `thoiDiemKetThuc` datetime DEFAULT NULL,
  `isLocked` tinyint(1) DEFAULT 0 COMMENT 'Khóa tạm thời khi đang xử lý ghép đôi',
  `lockedAt` datetime DEFAULT NULL COMMENT 'Thời điểm khóa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `timkiemghepdoi`
--

INSERT INTO `timkiemghepdoi` (`maTimKiem`, `maNguoiDung`, `trangThai`, `thoiDiemBatDau`, `thoiDiemKetThuc`, `isLocked`, `lockedAt`) VALUES
(1, 100, 'matched', '2025-12-14 08:30:00', '2025-12-14 09:00:00', 0, NULL),
(2, 101, 'matched', '2025-12-14 08:35:00', '2025-12-14 09:00:00', 0, NULL),
(3, 102, 'matched', '2025-12-14 10:00:00', '2025-12-14 10:45:00', 0, NULL),
(4, 103, 'matched', '2025-12-14 10:10:00', '2025-12-14 10:45:00', 0, NULL),
(5, 104, 'matched', '2025-12-14 11:00:00', '2025-12-14 11:50:00', 0, NULL),
(6, 105, 'matched', '2025-12-14 11:15:00', '2025-12-14 11:50:00', 0, NULL),
(7, 106, 'matched', '2025-12-14 12:45:00', '2025-12-14 13:30:00', 0, NULL),
(8, 107, 'matched', '2025-12-14 13:00:00', '2025-12-14 13:30:00', 0, NULL),
(9, 108, 'matched', '2025-12-14 14:00:00', '2025-12-14 14:45:00', 0, NULL),
(10, 109, 'matched', '2025-12-14 14:15:00', '2025-12-14 14:45:00', 0, NULL),
(11, 110, 'searching', '2025-12-16 20:00:00', NULL, 0, NULL),
(12, 111, 'searching', '2025-12-16 20:05:00', NULL, 0, NULL),
(13, 112, 'cancelled', '2025-12-15 10:00:00', '2025-12-15 10:30:00', 0, NULL),
(14, 114, 'matched', '2025-12-15 08:00:00', '2025-12-15 08:45:00', 0, NULL),
(15, 115, 'matched', '2025-12-15 08:10:00', '2025-12-15 08:45:00', 0, NULL),
(16, 116, 'matched', '2025-12-15 09:15:00', '2025-12-15 10:00:00', 0, NULL),
(17, 117, 'matched', '2025-12-15 09:30:00', '2025-12-15 10:00:00', 0, NULL),
(18, 118, 'matched', '2025-12-15 11:00:00', '2025-12-15 11:45:00', 0, NULL),
(19, 119, 'matched', '2025-12-15 11:15:00', '2025-12-15 11:45:00', 0, NULL),
(20, 120, 'matched', '2025-12-15 13:00:00', '2025-12-15 13:30:00', 0, NULL),
(21, 121, 'matched', '2025-12-15 13:10:00', '2025-12-15 13:30:00', 0, NULL),
(22, 135, 'searching', '2025-12-16 19:30:00', NULL, 0, NULL),
(23, 140, 'searching', '2025-12-16 19:45:00', NULL, 0, NULL),
(24, 145, 'searching', '2025-12-16 20:10:00', NULL, 0, NULL),
(25, 149, 'cancelled', '2025-12-16 18:00:00', '2025-12-16 18:30:00', 0, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tinnhan`
--

CREATE TABLE `tinnhan` (
  `maTinNhan` int(11) NOT NULL,
  `maGhepDoi` int(11) DEFAULT NULL,
  `maNguoiGui` int(11) DEFAULT NULL,
  `noiDung` text NOT NULL,
  `thoiDiemGui` datetime DEFAULT current_timestamp(),
  `trangThai` enum('sent','delivered','seen') DEFAULT 'sent' COMMENT 'Trạng thái tin nhắn: sent (đã gửi), delivered (đã nhận), seen (đã xem)',
  `thoiDiemNhan` datetime DEFAULT NULL COMMENT 'Thời điểm người nhận nhận được tin nhắn',
  `thoiDiemXem` datetime DEFAULT NULL COMMENT 'Thời điểm người nhận xem tin nhắn'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `tinnhan`
--

INSERT INTO `tinnhan` (`maTinNhan`, `maGhepDoi`, `maNguoiGui`, `noiDung`, `thoiDiemGui`, `trangThai`, `thoiDiemNhan`, `thoiDiemXem`) VALUES
(1, 1, 100, 'Xin chào! Rất vui được làm quen với bạn.', '2025-12-14 09:15:00', 'sent', NULL, NULL),
(2, 1, 101, 'Chào bạn! Mình cũng rất vui được gặp bạn.', '2025-12-14 09:20:00', 'sent', NULL, NULL),
(3, 1, 100, 'Bạn thích làm gì vào cuối tuần?', '2025-12-14 09:25:00', 'sent', NULL, NULL),
(4, 1, 101, 'Mình thường đi du lịch hoặc đọc sách. Bạn thì sao?', '2025-12-14 09:30:00', 'sent', NULL, NULL),
(5, 2, 102, 'Hi! Chào bạn nhé.', '2025-12-14 11:00:00', 'sent', NULL, NULL),
(6, 2, 103, 'Chào bạn! Bạn sống ở đâu vậy?', '2025-12-14 11:05:00', 'sent', NULL, NULL),
(7, 2, 102, 'Mình ở Đà Nẵng. Còn bạn?', '2025-12-14 11:10:00', 'sent', NULL, NULL),
(8, 2, 103, 'Mình ở Cần Thơ. Mình thích nghệ thuật lắm.', '2025-12-14 11:15:00', 'sent', NULL, NULL),
(9, 3, 104, 'Chào bạn! Mình thấy profile bạn rất thú vị.', '2025-12-14 12:00:00', 'sent', NULL, NULL),
(10, 3, 105, 'Cảm ơn bạn! Bạn làm nghề gì vậy?', '2025-12-14 12:05:00', 'sent', NULL, NULL),
(11, 3, 104, 'Mình là lập trình viên. Bạn thích nấu ăn à?', '2025-12-14 12:10:00', 'sent', NULL, NULL),
(12, 3, 105, 'Đúng rồi! Mình rất thích làm bánh.', '2025-12-14 12:15:00', 'sent', NULL, NULL),
(13, 4, 106, 'Xin chào! Bạn có thích đi phượt không?', '2025-12-14 13:45:00', 'sent', NULL, NULL),
(14, 4, 107, 'Chào bạn! Có chứ, mình rất thích.', '2025-12-14 13:50:00', 'sent', NULL, NULL),
(15, 4, 106, 'Tuyệt vời! Chúng ta có thể đi cùng nhau.', '2025-12-14 13:55:00', 'sent', NULL, NULL),
(16, 5, 108, 'Hi! Mình thấy bạn thích võ thuật.', '2025-12-14 15:00:00', 'sent', NULL, NULL),
(17, 5, 109, 'Ừ, mình tập võ thuật được 5 năm rồi.', '2025-12-14 15:05:00', 'sent', NULL, NULL),
(18, 6, 110, 'Chào bạn! Bạn có thích leo núi không?', '2025-12-14 16:00:00', 'sent', NULL, NULL),
(19, 6, 111, 'Có nhé! Mình thích leo núi lắm.', '2025-12-14 16:05:00', 'sent', NULL, NULL),
(20, 7, 112, 'Hi! Bạn chơi thể thao gì?', '2025-12-14 17:15:00', 'sent', NULL, NULL),
(21, 7, 113, 'Mình thích bóng rổ và tập gym.', '2025-12-14 17:20:00', 'sent', NULL, NULL),
(22, 8, 114, 'Chào bạn! Bạn làm công việc gì?', '2025-12-15 09:00:00', 'sent', NULL, NULL),
(23, 8, 115, 'Mình là giáo viên. Bạn thì sao?', '2025-12-15 09:05:00', 'sent', NULL, NULL),
(24, 9, 116, 'Hi! Bạn có thích biển không?', '2025-12-15 10:15:00', 'sent', NULL, NULL),
(25, 9, 117, 'Mình sống gần biển nên rất thích.', '2025-12-15 10:20:00', 'sent', NULL, NULL),
(26, 10, 118, 'Chào bạn! Mình thích sở thích của bạn.', '2025-12-15 12:00:00', 'sent', NULL, NULL),
(27, 10, 119, 'Cảm ơn! Bạn cũng có sở thích hay đấy.', '2025-12-15 12:05:00', 'sent', NULL, NULL),
(28, 11, 120, 'Hi! Bạn thích âm nhạc gì?', '2025-12-15 13:45:00', 'sent', NULL, NULL),
(29, 11, 121, 'Mình thích nhạc chill và piano.', '2025-12-15 13:50:00', 'sent', NULL, NULL),
(30, 12, 122, 'Chào bạn! Cuối tuần này bạn có rảnh không?', '2025-12-15 15:00:00', 'sent', NULL, NULL),
(31, 12, 123, 'Có chứ! Bạn muốn đi đâu?', '2025-12-15 15:05:00', 'sent', NULL, NULL),
(32, 13, 124, 'Hi! Bạn thích chơi thể thao nào?', '2025-12-15 16:15:00', 'sent', NULL, NULL),
(33, 13, 125, 'Mình thích yoga và chạy bộ.', '2025-12-15 16:20:00', 'sent', NULL, NULL),
(34, 14, 126, 'Chào bạn! Mình thấy bạn rất thú vị.', '2025-12-15 17:30:00', 'sent', NULL, NULL),
(35, 14, 127, 'Cảm ơn bạn! Mình cũng vậy.', '2025-12-15 17:35:00', 'sent', NULL, NULL),
(36, 15, 128, 'Hi! Bạn làm nghề gì vậy?', '2025-12-16 08:45:00', 'sent', NULL, NULL),
(37, 15, 129, 'Mình làm trong ngành IT.', '2025-12-16 08:50:00', 'sent', NULL, NULL),
(38, 16, 130, 'Chào bạn! Bạn thích ăn gì?', '2025-12-16 10:00:00', 'sent', NULL, NULL),
(39, 16, 131, 'Mình thích ăn hải sản.', '2025-12-16 10:05:00', 'sent', NULL, NULL),
(40, 17, 132, 'Hi! Bạn có thích lập trình không?', '2025-12-16 11:15:00', 'sent', NULL, NULL),
(41, 17, 133, 'Có chứ! Mình cũng làm IT.', '2025-12-16 11:20:00', 'sent', NULL, NULL),
(42, 18, 134, 'Chào bạn! Bạn thích du lịch đâu?', '2025-12-16 12:30:00', 'sent', NULL, NULL),
(43, 18, 135, 'Mình thích đi núi và biển.', '2025-12-16 12:35:00', 'sent', NULL, NULL),
(44, 19, 136, 'Hi! Bạn có sở thích gì đặc biệt?', '2025-12-16 13:45:00', 'sent', NULL, NULL),
(45, 19, 137, 'Mình thích yoga và thiền.', '2025-12-16 13:50:00', 'sent', NULL, NULL),
(46, 20, 138, 'Chào bạn! Rất vui được gặp bạn.', '2025-12-16 15:00:00', 'sent', NULL, NULL),
(47, 20, 139, 'Mình cũng vậy!', '2025-12-16 15:05:00', 'sent', NULL, NULL),
(48, 21, 140, 'Hi! Bạn chơi game gì?', '2025-12-16 16:15:00', 'sent', NULL, NULL),
(49, 21, 141, 'Mình chơi game mobile và PC.', '2025-12-16 16:20:00', 'sent', NULL, NULL),
(50, 22, 142, 'Chào bạn! Bạn thích đi leo núi không?', '2025-12-16 17:30:00', 'sent', NULL, NULL),
(69, 32, 154, 'hi', '2025-12-24 13:30:45', 'seen', NULL, '2025-12-24 13:30:53'),
(70, 32, 152, 'hi', '2025-12-24 13:30:54', 'seen', '2025-12-24 13:30:56', '2025-12-24 13:30:56'),
(71, 32, 154, 'hi', '2025-12-24 13:31:05', 'seen', NULL, '2025-12-24 13:31:08'),
(72, 32, 154, 'hi', '2025-12-24 13:31:41', 'seen', NULL, '2025-12-24 13:33:32'),
(73, 32, 154, 'hii', '2025-12-24 13:33:54', 'sent', NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `xacminhtaikhoan`
--

CREATE TABLE `xacminhtaikhoan` (
  `maXacMinh` int(11) NOT NULL,
  `maNguoiDung` int(11) NOT NULL,
  `loaiXacMinh` enum('email','phone','profile','photo') NOT NULL,
  `trangThai` enum('pending','verified','rejected') DEFAULT 'pending',
  `maXacMinh_token` varchar(255) DEFAULT NULL,
  `ghiChu` text DEFAULT NULL,
  `thoiDiemTao` datetime DEFAULT current_timestamp(),
  `thoiDiemXacMinh` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `xacminhtaikhoan`
--

INSERT INTO `xacminhtaikhoan` (`maXacMinh`, `maNguoiDung`, `loaiXacMinh`, `trangThai`, `maXacMinh_token`, `ghiChu`, `thoiDiemTao`, `thoiDiemXacMinh`) VALUES
(1, 100, 'email', 'verified', 'e4f3a8b9c2d1', 'Email đã được xác minh thành công', '2025-12-16 07:50:00', '2025-12-16 08:00:00'),
(2, 101, 'email', 'verified', 'd7e2f1a5c8b3', 'Email đã được xác minh thành công', '2025-12-16 08:05:00', '2025-12-16 08:15:00'),
(3, 102, 'email', 'verified', 'a3b8c9d2e1f7', 'Email đã được xác minh thành công', '2025-12-16 08:20:00', '2025-12-16 08:30:00'),
(4, 110, 'profile', 'verified', 'f8e7d6c5b4a3', 'Hồ sơ đã được xác minh', '2025-12-14 15:00:00', '2025-12-14 16:00:00'),
(5, 115, 'photo', 'verified', 'c9d8e7f6a5b4', 'Ảnh đại diện đã được xác minh', '2025-12-15 11:00:00', '2025-12-15 12:00:00'),
(6, 120, 'email', 'verified', 'b4c5d6e7f8a9', 'Email đã được xác minh thành công', '2025-12-16 12:50:00', '2025-12-16 13:00:00'),
(7, 125, 'profile', 'pending', 'a1b2c3d4e5f6', 'Đang chờ xác minh hồ sơ', '2025-12-16 14:00:00', NULL),
(8, 130, 'email', 'verified', 'd4e5f6a7b8c9', 'Email đã được xác minh thành công', '2025-12-16 15:20:00', '2025-12-16 15:30:00'),
(9, 135, 'photo', 'pending', 'e5f6a7b8c9d1', 'Đang chờ xác minh ảnh đại diện', '2025-12-16 16:30:00', NULL),
(10, 140, 'profile', 'rejected', 'f6a7b8c9d1e2', 'Hồ sơ không đủ thông tin', '2025-12-16 17:50:00', '2025-12-16 18:00:00');

--
-- Chỉ mục cho các bảng đã đổ
--

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
-- Chỉ mục cho bảng `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_otp_code` (`otp_code`),
  ADD KEY `idx_expires_at` (`expires_at`);

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
-- Chỉ mục cho bảng `hotro`
--
ALTER TABLE `hotro`
  ADD PRIMARY KEY (`maHoTro`),
  ADD KEY `maNguoiDung` (`maNguoiDung`),
  ADD KEY `maAdminPhuTrach` (`maAdminPhuTrach`),
  ADD KEY `idx_status` (`trangThai`);

--
-- Chỉ mục cho bảng `lichsuvipham`
--
ALTER TABLE `lichsuvipham`
  ADD PRIMARY KEY (`maViPham`),
  ADD KEY `maNguoiDung` (`maNguoiDung`),
  ADD KEY `maAdminXuLy` (`maAdminXuLy`),
  ADD KEY `maBaoCao` (`maBaoCao`);

--
-- Chỉ mục cho bảng `magiamgia`
--
ALTER TABLE `magiamgia`
  ADD PRIMARY KEY (`maMaGiamGia`),
  ADD UNIQUE KEY `maCoupon` (`maCoupon`),
  ADD KEY `idx_active` (`trangThai`,`ngayBatDau`,`ngayKetThuc`);

--
-- Chỉ mục cho bảng `nguoidung`
--
ALTER TABLE `nguoidung`
  ADD PRIMARY KEY (`maNguoiDung`),
  ADD UNIQUE KEY `tenDangNhap` (`tenDangNhap`),
  ADD KEY `idx_email_verified` (`email_verified`);

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
-- Chỉ mục cho bảng `thongbaoheothong`
--
ALTER TABLE `thongbaoheothong`
  ADD PRIMARY KEY (`maThongBao`),
  ADD KEY `maAdminTao` (`maAdminTao`),
  ADD KEY `idx_status` (`trangThai`,`thoiDiemGui`);

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
  ADD KEY `idx_user` (`maNguoiDung`),
  ADD KEY `idx_searching_unlocked` (`trangThai`,`isLocked`,`thoiDiemBatDau`);

--
-- Chỉ mục cho bảng `tinnhan`
--
ALTER TABLE `tinnhan`
  ADD PRIMARY KEY (`maTinNhan`),
  ADD KEY `maGhepDoi` (`maGhepDoi`),
  ADD KEY `maNguoiGui` (`maNguoiGui`),
  ADD KEY `idx_message_status` (`maGhepDoi`,`trangThai`);

--
-- Chỉ mục cho bảng `xacminhtaikhoan`
--
ALTER TABLE `xacminhtaikhoan`
  ADD PRIMARY KEY (`maXacMinh`),
  ADD KEY `maNguoiDung` (`maNguoiDung`),
  ADD KEY `idx_status` (`trangThai`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `baocao`
--
ALTER TABLE `baocao`
  MODIFY `maBaoCao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `channguoidung`
--
ALTER TABLE `channguoidung`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `email_verifications`
--
ALTER TABLE `email_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT cho bảng `ghepdoi`
--
ALTER TABLE `ghepdoi`
  MODIFY `maGhepDoi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT cho bảng `goidangky`
--
ALTER TABLE `goidangky`
  MODIFY `maGoiDangKy` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT cho bảng `hoso`
--
ALTER TABLE `hoso`
  MODIFY `maHoSo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT cho bảng `hotro`
--
ALTER TABLE `hotro`
  MODIFY `maHoTro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `lichsuvipham`
--
ALTER TABLE `lichsuvipham`
  MODIFY `maViPham` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `magiamgia`
--
ALTER TABLE `magiamgia`
  MODIFY `maMaGiamGia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `nguoidung`
--
ALTER TABLE `nguoidung`
  MODIFY `maNguoiDung` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=155;

--
-- AUTO_INCREMENT cho bảng `ratelimitlog`
--
ALTER TABLE `ratelimitlog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT cho bảng `thanhtoan`
--
ALTER TABLE `thanhtoan`
  MODIFY `maThanhToan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT cho bảng `thich`
--
ALTER TABLE `thich`
  MODIFY `maThich` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT cho bảng `thongbaoheothong`
--
ALTER TABLE `thongbaoheothong`
  MODIFY `maThongBao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `thongkengay`
--
ALTER TABLE `thongkengay`
  MODIFY `maThongKe` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `timkiemghepdoi`
--
ALTER TABLE `timkiemghepdoi`
  MODIFY `maTimKiem` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT cho bảng `tinnhan`
--
ALTER TABLE `tinnhan`
  MODIFY `maTinNhan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT cho bảng `xacminhtaikhoan`
--
ALTER TABLE `xacminhtaikhoan`
  MODIFY `maXacMinh` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `baocao`
--
ALTER TABLE `baocao`
  ADD CONSTRAINT `baocao_ibfk_1` FOREIGN KEY (`maNguoiBaoCao`) REFERENCES `nguoidung` (`maNguoiDung`),
  ADD CONSTRAINT `baocao_ibfk_2` FOREIGN KEY (`maNguoiBiBaoCao`) REFERENCES `nguoidung` (`maNguoiDung`),
  ADD CONSTRAINT `baocao_ibfk_3` FOREIGN KEY (`maAdminXuLy`) REFERENCES `nguoidung` (`maNguoiDung`) ON DELETE SET NULL;

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
-- Các ràng buộc cho bảng `hotro`
--
ALTER TABLE `hotro`
  ADD CONSTRAINT `hotro_ibfk_1` FOREIGN KEY (`maNguoiDung`) REFERENCES `nguoidung` (`maNguoiDung`) ON DELETE CASCADE,
  ADD CONSTRAINT `hotro_ibfk_2` FOREIGN KEY (`maAdminPhuTrach`) REFERENCES `nguoidung` (`maNguoiDung`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `lichsuvipham`
--
ALTER TABLE `lichsuvipham`
  ADD CONSTRAINT `lichsuvipham_ibfk_1` FOREIGN KEY (`maNguoiDung`) REFERENCES `nguoidung` (`maNguoiDung`) ON DELETE CASCADE,
  ADD CONSTRAINT `lichsuvipham_ibfk_2` FOREIGN KEY (`maAdminXuLy`) REFERENCES `nguoidung` (`maNguoiDung`) ON DELETE SET NULL,
  ADD CONSTRAINT `lichsuvipham_ibfk_3` FOREIGN KEY (`maBaoCao`) REFERENCES `baocao` (`maBaoCao`) ON DELETE SET NULL;

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
-- Các ràng buộc cho bảng `thongbaoheothong`
--
ALTER TABLE `thongbaoheothong`
  ADD CONSTRAINT `thongbaoheothong_ibfk_1` FOREIGN KEY (`maAdminTao`) REFERENCES `nguoidung` (`maNguoiDung`) ON DELETE SET NULL;

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

--
-- Các ràng buộc cho bảng `xacminhtaikhoan`
--
ALTER TABLE `xacminhtaikhoan`
  ADD CONSTRAINT `xacminhtaikhoan_ibfk_1` FOREIGN KEY (`maNguoiDung`) REFERENCES `nguoidung` (`maNguoiDung`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
