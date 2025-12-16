-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 03, 2025 lúc 01:33 AM
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
(1, 50, 'email', 'verified', NULL, 'Email đã được xác minh', '2025-12-01 10:30:00', '2025-12-01 10:35:00'),
(2, 50, 'phone', 'verified', NULL, 'Số điện thoại đã được xác minh', '2025-12-01 10:40:00', '2025-12-01 10:45:00'),
(3, 51, 'email', 'verified', NULL, 'Email đã được xác minh', '2025-11-28 09:00:00', '2025-11-28 09:10:00'),
(4, 52, 'email', 'verified', NULL, 'Email đã được xác minh', '2025-11-29 10:00:00', '2025-11-29 10:15:00'),
(5, 53, 'email', 'verified', NULL, 'Email đã được xác minh', '2025-11-30 11:00:00', '2025-11-30 11:20:00'),
(6, 54, 'email', 'pending', 'abc123xyz', NULL, '2025-12-01 08:00:00', NULL),
(7, 55, 'email', 'verified', NULL, 'Email đã được xác minh', '2025-12-02 09:00:00', '2025-12-02 09:10:00'),
(8, 56, 'email', 'verified', NULL, 'Email đã được xác minh', '2025-12-03 10:00:00', '2025-12-03 10:20:00'),
(9, 57, 'email', 'verified', NULL, 'Email đã được xác minh', '2025-12-02 14:20:00', '2025-12-02 14:30:00'),
(10, 57, 'profile', 'verified', NULL, 'Hồ sơ đã được xác minh', '2025-12-02 14:40:00', '2025-12-02 14:50:00'),
(11, 58, 'email', 'pending', 'def456uvw', NULL, '2025-12-04 11:00:00', NULL),
(12, 59, 'email', 'verified', NULL, 'Email đã được xác minh', '2025-12-05 12:00:00', '2025-12-05 12:15:00'),
(13, 60, 'email', 'verified', NULL, 'Email đã được xác minh', '2025-12-06 13:00:00', '2025-12-06 13:10:00'),
(14, 61, 'email', 'verified', NULL, 'Email đã được xác minh', '2025-12-03 09:15:00', '2025-12-03 09:25:00'),
(15, 61, 'phone', 'verified', NULL, 'Số điện thoại đã được xác minh', '2025-12-03 09:30:00', '2025-12-03 09:40:00'),
(16, 62, 'email', 'verified', NULL, 'Email đã được xác minh', '2025-12-07 14:00:00', '2025-12-07 14:15:00'),
(17, 63, 'email', 'pending', 'ghi789rst', NULL, '2025-12-08 15:00:00', NULL),
(18, 64, 'email', 'verified', NULL, 'Email đã được xác minh', '2025-12-09 16:00:00', '2025-12-09 16:20:00'),
(19, 65, 'email', 'verified', NULL, 'Email đã được xác minh', '2025-12-05 16:45:00', '2025-12-05 16:55:00'),
(20, 65, 'photo', 'verified', NULL, 'Ảnh đại diện đã được xác minh', '2025-12-05 17:00:00', '2025-12-05 17:10:00'),
(21, 66, 'email', 'verified', NULL, 'Email đã được xác minh', '2025-12-10 08:00:00', '2025-12-10 08:15:00'),
(22, 67, 'email', 'pending', 'jkl012mno', NULL, '2025-12-11 09:00:00', NULL),
(23, 68, 'email', 'verified', NULL, 'Email đã được xác minh', '2025-12-12 10:00:00', '2025-12-12 10:20:00'),
(24, 69, 'email', 'verified', NULL, 'Email đã được xác minh', '2025-12-07 11:30:00', '2025-12-07 11:45:00'),
(25, 70, 'email', 'rejected', NULL, 'Email không hợp lệ', '2025-12-13 11:00:00', '2025-12-13 12:00:00');

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
(1, 64, 'Quấy rối', 'Gửi tin nhắn quấy rối nhiều lần với nội dung không phù hợp', 1, 'warning', 1, '2025-12-10 15:00:00', NULL),
(2, 70, 'Giả mạo thông tin', 'Sử dụng ảnh đại diện không phải của mình', 2, 'warning', 1, '2025-12-12 16:00:00', NULL),
(3, 92, 'Lừa đảo', 'Yêu cầu chuyển tiền với lý do không chính đáng', 5, 'permanent_ban', 1, '2025-12-15 20:00:00', NULL),
(4, 80, 'Nội dung không phù hợp', 'Gửi nội dung có tính chất khiêu dâm', 3, 'temporary_ban', 1, '2025-12-13 18:00:00', '2025-12-20 18:00:00'),
(5, 72, 'Spam', 'Gửi tin nhắn quảng cáo liên tục', 8, 'warning', 1, '2025-12-13 19:00:00', NULL);

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
(1, 'WELCOME2025', 'Chào mừng thành viên mới 2025', 'percent', 20.00, 50000.00, 100, 15, '2025-01-01 00:00:00', '2025-12-31 23:59:59', 'active', 'new_user', '2025-01-01 00:00:00'),
(2, 'VIP50K', 'Giảm 50K cho gói VIP', 'fixed', 50000.00, NULL, 50, 8, '2025-12-01 00:00:00', '2025-12-31 23:59:59', 'active', 'all', '2025-12-01 00:00:00'),
(3, 'XMAS2025', 'Giáng sinh 2025 - Giảm 30%', 'percent', 30.00, 100000.00, 200, 25, '2025-12-20 00:00:00', '2025-12-26 23:59:59', 'active', 'all', '2025-12-15 00:00:00'),
(4, 'NEWYEAR30', 'Năm mới giảm 30K', 'fixed', 30000.00, NULL, 150, 0, '2025-12-28 00:00:00', '2026-01-05 23:59:59', 'active', 'all', '2025-12-16 00:00:00'),
(5, 'FLASHSALE', 'Flash Sale - Giảm 40%', 'percent', 40.00, 80000.00, 30, 12, '2025-12-15 00:00:00', '2025-12-17 23:59:59', 'active', 'vip_only', '2025-12-14 00:00:00'),
(6, 'OLDCODE', 'Mã cũ đã hết hạn', 'percent', 15.00, 30000.00, 50, 50, '2025-11-01 00:00:00', '2025-11-30 23:59:59', 'expired', 'all', '2025-11-01 00:00:00');

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
(1, 54, 'Không thể thanh toán gói VIP', 'Tôi đã thử thanh toán nhưng luôn báo lỗi. Vui lòng hỗ trợ.', 'payment', 'resolved', 1, 'Đã kiểm tra và sửa lỗi thanh toán. Bạn có thể thử lại.', '2025-12-10 10:00:00', '2025-12-10 14:00:00'),
(2, 58, 'Lỗi không tải được ảnh', 'Khi upload ảnh đại diện bị báo lỗi không hỗ trợ định dạng.', 'technical', 'resolved', 1, 'Hệ thống chỉ hỗ trợ JPG, PNG, GIF. Vui lòng thử lại với định dạng này.', '2025-12-11 11:00:00', '2025-12-11 15:00:00'),
(3, 62, 'Câu hỏi về tính năng ghép đôi nhanh', 'Tính năng ghép đôi nhanh hoạt động như thế nào?', 'general', 'resolved', 1, 'Ghép đôi nhanh sẽ tự động tìm người phù hợp với bạn dựa trên sở thích và thông tin hồ sơ.', '2025-12-12 12:00:00', '2025-12-12 16:00:00'),
(4, 66, 'Muốn hủy gói VIP', 'Tôi muốn hủy gói VIP và hoàn tiền.', 'payment', 'in_progress', 1, 'Chúng tôi đang xử lý yêu cầu của bạn. Vui lòng chờ 3-5 ngày làm việc.', '2025-12-13 13:00:00', '2025-12-13 17:00:00'),
(5, 70, 'Tài khoản bị khóa không rõ lý do', 'Tài khoản của tôi bị khóa mà không có thông báo. Vui lòng kiểm tra lại.', 'report', 'resolved', 1, 'Tài khoản bị khóa do vi phạm quy định về ảnh đại diện. Bạn đã được mở khóa sau khi cập nhật ảnh phù hợp.', '2025-12-14 14:00:00', '2025-12-14 18:00:00'),
(6, 74, 'Không nhận được email xác minh', 'Tôi đã đăng ký nhưng không nhận được email xác minh.', 'technical', 'pending', NULL, NULL, '2025-12-15 15:00:00', NULL),
(7, 78, 'Hỏi về chính sách bảo mật', 'Thông tin cá nhân của tôi có được bảo mật không?', 'general', 'pending', NULL, NULL, '2025-12-16 09:00:00', NULL),
(8, 82, 'Lỗi khi gửi tin nhắn', 'Tôi không thể gửi tin nhắn cho đối phương. Luôn báo lỗi.', 'technical', 'in_progress', 1, 'Chúng tôi đang kiểm tra vấn đề này. Vui lòng chờ trong ít phút.', '2025-12-16 10:00:00', '2025-12-16 10:30:00'),
(9, 86, 'Muốn xóa tài khoản', 'Tôi muốn xóa vĩnh viễn tài khoản của mình.', 'other', 'pending', NULL, NULL, '2025-12-15 16:00:00', NULL),
(10, 89, 'Câu hỏi về gói VIP', 'Gói VIP có những tính năng gì đặc biệt?', 'general', 'resolved', 1, 'Gói VIP cho phép bạn xem không giới hạn hồ sơ, ưu tiên ghép đôi, và nhiều tính năng đặc biệt khác.', '2025-12-14 17:00:00', '2025-12-14 19:00:00');

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
(1, 'Chào mừng đến với DuyenHub!', 'Cảm ơn bạn đã tham gia DuyenHub. Hãy hoàn thiện hồ sơ để tìm được người phù hợp nhất!', 'info', 'medium', 'all', NULL, 'sent', 1, '2025-12-01 00:00:00', '2025-11-30 23:00:00'),
(2, 'Bảo trì hệ thống định kỳ', 'Hệ thống sẽ bảo trì vào 3h sáng ngày 18/12/2025. Thời gian dự kiến 2 giờ.', 'maintenance', 'high', 'all', NULL, 'scheduled', 1, '2025-12-17 20:00:00', '2025-12-16 10:00:00'),
(3, 'Khuyến mãi Flash Sale - Giảm 40%', 'Flash Sale chỉ trong 3 ngày! Nâng cấp gói VIP ngay với giảm giá 40%. Mã: FLASHSALE', 'promotion', 'urgent', 'all', NULL, 'sent', 1, '2025-12-15 00:00:00', '2025-12-14 23:00:00'),
(4, 'Cảnh báo: Phát hiện tài khoản giả mạo', 'Chúng tôi phát hiện một số tài khoản giả mạo. Vui lòng cảnh giác và báo cáo nếu gặp hành vi đáng ngờ.', 'warning', 'high', 'all', NULL, 'sent', 1, '2025-12-10 10:00:00', '2025-12-10 09:00:00'),
(5, 'Tính năng mới: Ghép đôi nhanh', 'Giờ đây bạn có thể sử dụng tính năng Ghép đôi nhanh để tìm người phù hợp nhanh chóng hơn!', 'info', 'medium', 'all', NULL, 'sent', 1, '2025-12-05 00:00:00', '2025-12-04 23:00:00'),
(6, 'Ưu đãi đặc biệt cho thành viên VIP', 'Chỉ dành cho VIP: Giảm 50K cho lần gia hạn tiếp theo. Mã: VIP50K', 'promotion', 'medium', 'vip', NULL, 'sent', 1, '2025-12-01 00:00:00', '2025-11-30 22:00:00'),
(7, 'Chính sách bảo mật mới', 'Chúng tôi đã cập nhật chính sách bảo mật. Vui lòng xem chi tiết tại mục Cài đặt.', 'info', 'low', 'all', NULL, 'draft', 1, NULL, '2025-12-16 11:00:00');

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
(1, 52, 64, 'Gửi tin nhắn quấy rối nhiều lần, nội dung không phù hợp', 'harassment', 'resolved', 'DaXuLy', 1, 'Đã xử lý và cảnh cáo người dùng', '2025-12-10 15:00:00', '2025-12-10 12:00:00'),
(2, 56, 70, 'Sử dụng ảnh đại diện giả mạo', 'fake_profile', 'resolved', 'DaXuLy', 1, 'Đã yêu cầu đổi ảnh đại diện', '2025-12-12 16:00:00', '2025-12-12 13:00:00'),
(3, 60, 80, 'Nội dung tin nhắn không phù hợp, có tính chất khiêu dâm', 'inappropriate_content', 'reviewing', 'ChuaXuLy', NULL, NULL, NULL, '2025-12-13 14:00:00'),
(4, 68, 95, 'Spam tin nhắn liên tục', 'spam', 'pending', 'ChuaXuLy', NULL, NULL, NULL, '2025-12-14 15:00:00'),
(5, 75, 92, 'Hành vi lừa đảo, yêu cầu chuyển tiền', 'scam', 'resolved', 'DaXuLy', 1, 'Đã khóa tài khoản người dùng vi phạm', '2025-12-15 20:00:00', '2025-12-15 16:00:00'),
(6, 77, 58, 'Sử dụng ngôn ngữ thô tục, xúc phạm', 'harassment', 'pending', 'ChuaXuLy', NULL, NULL, NULL, '2025-12-09 11:00:00'),
(7, 82, 66, 'Thông tin hồ sơ không chính xác', 'fake_profile', 'rejected', 'DaXuLy', 1, 'Sau khi kiểm tra, thông tin hồ sơ là chính xác', '2025-12-11 10:00:00', '2025-12-11 08:00:00'),
(8, 85, 72, 'Gửi hình ảnh không phù hợp', 'inappropriate_content', 'reviewing', 'ChuaXuLy', NULL, NULL, NULL, '2025-12-13 17:00:00'),
(9, 88, 54, 'Quảng cáo dịch vụ bên ngoài', 'spam', 'pending', 'ChuaXuLy', NULL, NULL, NULL, '2025-12-14 18:00:00'),
(10, 91, 62, 'Hành vi quấy rối qua tin nhắn', 'harassment', 'pending', 'ChuaXuLy', NULL, NULL, NULL, '2025-12-15 19:00:00');

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
(1, 54, 64, '2025-12-10 11:30:00'),
(2, 66, 70, '2025-12-12 14:00:00'),
(3, 72, 80, '2025-12-13 16:00:00'),
(4, 83, 95, '2025-12-14 17:00:00'),
(5, 96, 92, '2025-12-15 18:00:00');

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
(1, 50, 52, '2025-12-10 10:00:00', 'matched'),
(2, 51, 53, '2025-12-11 14:20:00', 'matched'),
(3, 55, 56, '2025-12-12 09:15:00', 'matched'),
(4, 57, 61, '2025-12-13 16:00:00', 'matched'),
(5, 59, 77, '2025-12-14 11:30:00', 'matched'),
(6, 60, 68, '2025-12-15 08:00:00', 'matched'),
(7, 63, 67, '2025-12-10 15:00:00', 'matched'),
(8, 65, 90, '2025-12-11 10:00:00', 'matched'),
(9, 69, 88, '2025-12-12 13:00:00', 'matched'),
(10, 71, 75, '2025-12-13 07:00:00', 'matched'),
(11, 73, 79, '2025-12-14 19:00:00', 'matched'),
(12, 76, 85, '2025-12-15 14:00:00', 'matched'),
(13, 54, 64, '2025-12-10 11:00:00', 'unmatched'),
(14, 58, 62, '2025-12-11 12:00:00', 'matched'),
(15, 66, 70, '2025-12-12 13:00:00', 'blocked'),
(16, 72, 78, '2025-12-13 14:00:00', 'matched'),
(17, 74, 80, '2025-12-14 15:00:00', 'matched'),
(18, 81, 87, '2025-12-15 16:00:00', 'matched'),
(19, 82, 86, '2025-12-10 17:00:00', 'matched'),
(20, 83, 91, '2025-12-11 18:00:00', 'matched'),
(21, 89, 93, '2025-12-12 19:00:00', 'matched'),
(22, 92, 96, '2025-12-13 20:00:00', 'matched'),
(23, 94, 98, '2025-12-14 21:00:00', 'matched'),
(24, 95, 99, '2025-12-15 22:00:00', 'matched'),
(25, 97, 56, '2025-12-10 08:00:00', 'matched');

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
(1, 50, 'VIP', 'Active', '2026-01-01', '2025-12-01 10:30:00'),
(2, 51, 'Free', 'Active', NULL, '2025-11-28 09:00:00'),
(3, 52, 'Free', 'Active', NULL, '2025-11-29 10:00:00'),
(4, 53, 'Free', 'Active', NULL, '2025-11-30 11:00:00'),
(5, 54, 'Free', 'Active', NULL, '2025-12-01 08:00:00'),
(6, 55, 'Free', 'Active', NULL, '2025-12-02 09:00:00'),
(7, 56, 'Free', 'Active', NULL, '2025-12-03 10:00:00'),
(8, 57, 'VIP', 'Active', '2026-01-02', '2025-12-02 14:20:00'),
(9, 58, 'Free', 'Active', NULL, '2025-12-04 11:00:00'),
(10, 59, 'Free', 'Active', NULL, '2025-12-05 12:00:00'),
(11, 60, 'Free', 'Active', NULL, '2025-12-06 13:00:00'),
(12, 61, 'VIP', 'Active', '2026-01-03', '2025-12-03 09:15:00'),
(13, 62, 'Free', 'Active', NULL, '2025-12-07 14:00:00'),
(14, 63, 'Free', 'Active', NULL, '2025-12-08 15:00:00'),
(15, 64, 'Free', 'Active', NULL, '2025-12-09 16:00:00'),
(16, 65, 'VIP', 'Active', '2026-01-05', '2025-12-05 16:45:00'),
(17, 66, 'Free', 'Active', NULL, '2025-12-10 08:00:00'),
(18, 67, 'Free', 'Active', NULL, '2025-12-11 09:00:00'),
(19, 68, 'Free', 'Active', NULL, '2025-12-12 10:00:00'),
(20, 69, 'VIP', 'Active', '2026-01-07', '2025-12-07 11:30:00'),
(21, 70, 'Free', 'Active', NULL, '2025-12-13 11:00:00'),
(22, 71, 'Free', 'Active', NULL, '2025-12-14 12:00:00'),
(23, 72, 'Free', 'Active', NULL, '2025-11-20 13:00:00'),
(24, 73, 'Free', 'Active', NULL, '2025-11-21 14:00:00'),
(25, 74, 'Free', 'Active', NULL, '2025-11-22 15:00:00'),
(26, 75, 'VIP', 'Active', '2026-01-08', '2025-12-08 13:20:00'),
(27, 76, 'Free', 'Active', NULL, '2025-11-23 16:00:00'),
(28, 77, 'VIP', 'Active', '2026-01-09', '2025-12-09 15:10:00'),
(29, 78, 'Free', 'Active', NULL, '2025-11-24 08:00:00'),
(30, 79, 'Free', 'Active', NULL, '2025-11-25 09:00:00'),
(31, 80, 'Free', 'Active', NULL, '2025-11-26 10:00:00'),
(32, 81, 'Free', 'Active', NULL, '2025-11-27 11:00:00'),
(33, 82, 'Free', 'Active', NULL, '2025-11-28 12:00:00'),
(34, 83, 'Free', 'Active', NULL, '2025-11-29 13:00:00'),
(35, 84, 'VIP', 'Active', '2026-01-10', '2025-12-10 10:00:00'),
(36, 85, 'Free', 'Active', NULL, '2025-11-30 14:00:00'),
(37, 86, 'Free', 'Active', NULL, '2025-12-01 15:00:00'),
(38, 87, 'Free', 'Active', NULL, '2025-12-02 16:00:00'),
(39, 88, 'VIP', 'Active', '2026-01-11', '2025-12-11 12:30:00'),
(40, 89, 'Free', 'Active', NULL, '2025-12-03 08:00:00'),
(41, 90, 'Free', 'Active', NULL, '2025-12-04 09:00:00'),
(42, 91, 'VIP', 'Active', '2026-01-13', '2025-12-13 14:45:00'),
(43, 92, 'Free', 'Active', NULL, '2025-12-05 10:00:00'),
(44, 93, 'Free', 'Active', NULL, '2025-12-06 11:00:00'),
(45, 94, 'Free', 'Active', NULL, '2025-12-07 12:00:00'),
(46, 95, 'VIP', 'Active', '2026-01-14', '2025-12-14 16:20:00'),
(47, 96, 'Free', 'Active', NULL, '2025-12-08 13:00:00'),
(48, 97, 'Free', 'Active', NULL, '2025-12-09 14:00:00'),
(49, 98, 'VIP', 'Active', '2026-01-15', '2025-12-15 09:50:00'),
(50, 99, 'Free', 'Active', NULL, '2025-12-10 15:00:00');

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
(26, 50, 'Nguyễn Hoàng Khánh Duy', '2004-12-07', 'Nam', 'Độc thân', 80, 168, 'Kết hôn', 'Thạc sĩ', 'Bến Tre', 'Chơi game, Thiền, Tập gym, Chơi cờ', 'Mình là người thích rèn luyện thể chất và tinh thần, thường dành thời gian tập gym để giữ sức khỏe và thiền để cân bằng cuộc sống. Ngoài ra, mình cũng thích chơi game và chơi cờ để giải trí và rèn tư duy chiến lược.', 'public/uploads/avatars/avatar_50_1762128865.jpg'),
(27, 51, 'Huỳnh Văn Quân', '2004-10-08', 'Nam', 'Độc thân', 70, 175, 'Hẹn hò', 'Đại học', 'Đắk Lắk', 'Xem phim, Học ngoại ngữ, Chơi game, Tập gym, Thủ công mỹ nghệ, Chơi cờ', 'Thích tập gym, thiền để giữ tinh thần thoải mái. Rảnh thì chơi game hoặc đánh cờ cho vui – vừa giải trí vừa rèn óc chiến lược.', 'public/uploads/avatars/avatar_51_1762129015.jpg'),
(28, 52, 'Nguyễn Thanh Chúc', '2005-12-12', 'Nữ', 'Độc thân', 53, 167, 'Hẹn hò', 'Đại học', 'Long An', 'Đọc sách, Xem phim, Nghe nhạc, Du lịch, Nấu ăn', 'Mình là người vui vẻ, hòa đồng và luôn cố gắng học hỏi những điều mới. Trong cuộc sống, mình thích đọc sách, nghe nhạc và đi du lịch để khám phá những điều thú vị xung quanh. Mình luôn tin rằng, chỉ cần cố gắng mỗi ngày thì mọi ước mơ đều có thể trở thành hiện thực.', 'public/uploads/avatars/avatar_52_1762129285.jpg'),
(29, 53, 'Cao Như Ý', '2002-10-17', 'Nữ', 'Độc thân', 55, 166, 'Hẹn hò', 'Đại học', 'Khánh Hòa', 'Xem phim, Nghe nhạc, Thiền, Khiêu vũ, Viết lách, Thủ công mỹ nghệ, Chơi nhạc cụ', 'Mình là một cô gái năng động, thân thiện và luôn sẵn sàng giúp đỡ bạn bè. Sở thích của mình là đọc truyện, vẽ tranh và nghe nhạc. Mình ước mơ sau này sẽ trở thành [nghề nghiệp mơ ước], để có thể làm điều mình yêu thích và giúp ích cho người khác.', 'public/uploads/avatars/avatar_53_1762129480.jpg'),
(30, 54, 'Nguyễn Như Hoa', '2001-03-07', 'Khac', 'Độc thân', 60, 172, 'Tìm hiểu', 'Đại học', 'An Giang', 'Đọc sách, Xem phim, Chụp ảnh, Tập gym, Chơi nhạc cụ', 'Mình là người hiền lành, chu đáo và sống tình cảm. Mình yêu thích những điều giản dị như ngắm hoàng hôn, nghe nhạc nhẹ và trồng cây. Mình luôn cố gắng sống tích cực, giúp đỡ người khác và trân trọng từng khoảnh khắc trong cuộc sống.', 'public/uploads/avatars/avatar_54_1762129702.jpg'),
(31, 55, 'Trần Thanh Chính', '2007-07-14', 'Nam', 'Độc thân', 70, 170, 'Tìm hiểu', 'Đại học', 'Bình Dương', 'Đọc sách, Chụp ảnh, Khiêu vũ, Cắm trại, Thời trang', 'Mình là người vui vẻ, hòa đồng và luôn cố gắng hết mình trong học tập cũng như công việc. Mình thích thể thao, nghe nhạc và khám phá những điều mới mẻ trong cuộc sống. Mình tin rằng chỉ cần kiên trì thì điều gì cũng có thể làm được.', 'public/uploads/avatars/avatar_55_1762129998.jpg'),
(32, 56, 'Lê Minh Anh', '1998-05-15', 'Nữ', 'Độc thân', 52, 162, 'Hẹn hò', 'Đại học', 'Hà Nội', 'Du lịch, Chụp ảnh, Đọc sách, Nghe nhạc', 'Mình là người yêu thích khám phá và trải nghiệm những điều mới mẻ. Thích đi du lịch và chụp ảnh để lưu giữ khoảnh khắc đẹp trong cuộc sống.', NULL),
(33, 57, 'Phạm Thanh Trung', '1995-08-22', 'Nam', 'Độc thân', 75, 178, 'Kết hôn', 'Đại học', 'Hồ Chí Minh', 'Tập gym, Bóng đá, Chơi game, Xem phim', 'Mình là người năng động, thích thể thao đặc biệt là bóng đá. Tập gym thường xuyên để giữ dáng và sức khỏe. Hy vọng tìm được người phù hợp để cùng nhau xây dựng tương lai.', NULL),
(34, 58, 'Nguyễn Kim Chi', '2000-03-10', 'Nữ', 'Độc thân', 48, 160, 'Hẹn hò', 'Cao đẳng', 'Đà Nẵng', 'Nấu ăn, Xem phim, Nghe nhạc, Shopping', 'Mình là cô gái hiền lành, thích nấu ăn và chăm sóc gia đình. Thích những buổi chiều đi dạo biển và ngắm hoàng hôn.', NULL),
(35, 59, 'Trần Bảo Lộc', '1997-11-28', 'Nam', 'Độc thân', 68, 172, 'Tìm hiểu', 'Thạc sĩ', 'Cần Thơ', 'Đọc sách, Viết lách, Cà phê, Du lịch', 'Mình là người yêu thích văn học và nghệ thuật. Thích những buổi chiều ngồi quán cà phê đọc sách hoặc viết lách. Tìm kiếm một người cùng chí hướng.', NULL),
(36, 60, 'Hoàng Tuyết Nhi', '1999-07-05', 'Nữ', 'Độc thân', 50, 165, 'Hẹn hò', 'Đại học', 'Hải Phòng', 'Khiêu vũ, Yoga, Du lịch, Chụp ảnh', 'Mình là người yêu thích vận động và nghệ thuật. Thích khiêu vũ và yoga để giữ dáng. Mơ ước được đi du lịch khắp nơi trên thế giới.', NULL),
(37, 61, 'Võ Đức Mạnh', '1996-02-14', 'Nam', 'Độc thân', 82, 180, 'Kết hôn', 'Đại học', 'Bình Định', 'Võ thuật, Tập gym, Đọc sách, Nấu ăn', 'Mình là người tự tin, mạnh mẽ nhưng cũng rất tế nhị. Thích võ thuật và tập gym. Hy vọng tìm được nửa kia để cùng nhau xây dựng tổ ấm hạnh phúc.', NULL),
(38, 62, 'Đặng Phương Thảo', '2001-06-20', 'Nữ', 'Độc thân', 47, 158, 'Hẹn hò', 'Đại học', 'Quảng Nam', 'Vẽ tranh, Nghe nhạc, Làm vườn, Đọc sách', 'Mình là người yêu thích nghệ thuật và thiên nhiên. Thích vẽ tranh và chăm sóc cây cối. Tìm kiếm một người hiểu và chia sẻ đam mê của mình.', NULL),
(39, 63, 'Lý Hồng Phúc', '1994-09-08', 'Nam', 'Ly hôn', 78, 176, 'Tìm hiểu', 'Thạc sĩ', 'Vũng Tàu', 'Câu cá, Bơi lội, Du lịch, Nhiếp ảnh', 'Mình là người trưởng thành, đã trải qua nhiều thăng trầm trong cuộc sống. Thích những hoạt động ngoài trời như câu cá và bơi lội. Mong tìm được người đồng hành chân thành.', NULL),
(40, 64, 'Phan Thùy Linh', '1998-12-03', 'Nữ', 'Độc thân', 51, 164, 'Hẹn hò', 'Đại học', 'Ninh Bình', 'Piano, Đọc sách, Yoga, Du lịch', 'Mình là người yêu âm nhạc, đặc biệt là piano. Thích những giai điệu nhẹ nhàng và không gian yên tĩnh. Hy vọng tìm được người cùng yêu âm nhạc như mình.', NULL),
(41, 65, 'Ngô Quang Minh', '1997-04-18', 'Nam', 'Độc thân', 72, 174, 'Kết hôn', 'Đại học', 'Thái Nguyên', 'Bóng rổ, Game, Lập trình, Xem phim', 'Mình làm việc trong ngành công nghệ, thích lập trình và chơi game. Ngoài ra còn yêu thích bóng rổ. Tìm kiếm người phù hợp để cùng nhau phát triển.', NULL),
(42, 66, 'Bùi Lan Phương', '2002-01-25', 'Nữ', 'Độc thân', 49, 161, 'Hẹn hò', 'Cao đẳng', 'Nghệ An', 'Nấu ăn, Làm bánh, Shopping, Xem phim', 'Mình là cô gái đảm đang, thích nấu ăn và làm bánh. Mơ ước có một gia đình nhỏ ấm cúng. Tìm kiếm người chân thành và hiểu mình.', NULL),
(43, 67, 'Trịnh Hoài Dũng', '1995-10-30', 'Nam', 'Độc thân', 76, 177, 'Kết hôn', 'Đại học', 'Đồng Nai', 'Xe máy, Du lịch, Nhiếp ảnh, Cà phê', 'Mình là người yêu thích tự do và khám phá. Thích đi phượt bằng xe máy và khám phá những vùng đất mới. Hy vọng tìm được bạn đồng hành trên mọi nẻo đường.', NULL),
(44, 68, 'Lưu Mỹ Duyên', '1999-08-12', 'Nữ', 'Độc thân', 54, 168, 'Hẹn hò', 'Đại học', 'Bình Phước', 'Khiêu vũ, Thời trang, Làm đẹp, Du lịch', 'Mình là người yêu thích cái đẹp và nghệ thuật. Thích khiêu vũ và thời trang. Luôn muốn trở nên tốt hơn mỗi ngày. Tìm kiếm người đàn ông lịch sự và hiểu mình.', NULL),
(45, 69, 'Đinh Thanh Tùng', '1996-05-07', 'Nam', 'Độc thân', 71, 173, 'Tìm hiểu', 'Đại học', 'Lâm Đồng', 'Leo núi, Cắm trại, Nhiếp ảnh, Guitar', 'Mình là người yêu thiên nhiên và phiêu lưu. Thích leo núi và cắm trại dưới bầu trời đầy sao. Biết chơi guitar và thích hát. Mong tìm được người cùng đam mê.', NULL),
(46, 70, 'Vũ Hương Giang', '2000-11-19', 'Nữ', 'Độc thân', 52, 163, 'Hẹn hò', 'Đại học', 'Hà Tĩnh', 'Âm nhạc, Đọc sách, Yoga, Cà phê', 'Mình là người yêu âm nhạc và nghệ thuật. Thích những giai điệu nhẹ nhàng và không gian yên bình. Tìm kiếm một người cùng sở thích và hiểu mình.', NULL),
(47, 71, 'Dương Văn Hùng', '1994-03-22', 'Nam', 'Độc thân', 79, 179, 'Kết hôn', 'Thạc sĩ', 'Thanh Hóa', 'Bơi lội, Tập gym, Đọc sách, Du lịch', 'Mình là người có trách nhiệm và nghiêm túc với cuộc sống. Thích tập luyện thể thao và đọc sách để phát triển bản thân. Hy vọng tìm được người phù hợp để cùng xây dựng tương lai.', NULL),
(48, 72, 'Mai Thanh Hà', '1998-07-15', 'Nữ', 'Độc thân', 50, 165, 'Hẹn hò', 'Đại học', 'Bắc Ninh', 'Nấu ăn, Yoga, Du lịch, Chụp ảnh', 'Mình là cô gái hiền lành, yêu thích nấu ăn và chăm sóc gia đình. Thích yoga để giữ dáng và sức khỏe. Mong muốn tìm được người đàn ông chân thành.', NULL),
(49, 73, 'Lê Đức Thắng', '1997-09-28', 'Nam', 'Độc thân', 74, 175, 'Tìm hiểu', 'Đại học', 'Quảng Ninh', 'Bóng đá, Game, Xem phim, Cà phé', 'Mình là người vui vẻ, hòa đồng. Thích bóng đá và thường xuyên chơi với bạn bè vào cuối tuần. Tìm kiếm một người cùng sở thích để cùng nhau trải nghiệm cuộc sống.', NULL),
(50, 74, 'Nguyễn Khánh Nguyên', '2001-04-10', 'Nữ', 'Độc thân', 48, 159, 'Hẹn hò', 'Cao đẳng', 'Hưng Yên', 'Làm vườn, Nấu ăn, Đọc sách, Xem phim', 'Mình là người yêu thích thiên nhiên và sự giản dị. Thích trồng cây và chăm sóc khu vườn nhỏ của mình. Tìm kiếm người đàn ông hiền lành và chân thành.', NULL),
(51, 75, 'Hoàng Văn Long', '1995-12-05', 'Nam', 'Độc thân', 77, 181, 'Kết hôn', 'Đại học', 'Hải Dương', 'Bơi lội, Chạy bộ, Du lịch, Nhiếp ảnh', 'Mình là người yêu thể thao và sống năng động. Thích bơi lội và chạy bộ mỗi sáng. Hy vọng tìm được người phù hợp để cùng nhau xây dựng gia đình.', NULL),
(52, 76, 'Trương Thanh Tâm', '1999-02-17', 'Nữ', 'Độc thân', 51, 164, 'Hẹn hò', 'Đại học', 'Nam Định', 'Piano, Đọc sách, Vẽ tranh, Yoga', 'Mình là người yêu nghệ thuật và âm nhạc. Thích chơi piano và vẽ tranh trong thời gian rảnh. Tìm kiếm người cùng đam mê nghệ thuật như mình.', NULL),
(53, 77, 'Lê Minh Tuấn', '1996-06-21', 'Nam', 'Độc thân', 73, 176, 'Tìm hiểu', 'Thạc sĩ', 'Thái Bình', 'Lập trình, Đọc sách, Cà phê, Game', 'Mình làm việc trong lĩnh vực công nghệ thông tin. Thích lập trình và học hỏi công nghệ mới. Ngoài ra còn thích đọc sách và uống cà phê. Mong tìm được người hiểu mình.', NULL),
(54, 78, 'Phạm Ngọc Mai', '2000-09-14', 'Nữ', 'Độc thân', 49, 162, 'Hẹn hò', 'Đại học', 'Phú Thọ', 'Khiêu vũ, Yoga, Shopping, Du lịch', 'Mình là cô gái năng động, yêu thích khiêu vũ và yoga. Thích đi du lịch và khám phá những điều mới mẻ. Tìm kiếm người đàn ông lịch lãm và có trách nhiệm.', NULL),
(55, 79, 'Nguyễn Quốc Anh', '1997-01-08', 'Nam', 'Độc thân', 70, 172, 'Kết hôn', 'Đại học', 'Vĩnh Phúc', 'Bóng rổ, Chạy bộ, Xem phim, Du lịch', 'Mình là người yêu thể thao, đặc biệt là bóng rổ. Thích chạy bộ mỗi sáng để giữ sức khỏe. Hy vọng tìm được người phù hợp để cùng nhau xây dựng tương lai.', NULL),
(56, 80, 'Lê Bích Ngọc', '1998-10-25', 'Nữ', 'Độc thân', 50, 163, 'Hẹn hò', 'Đại học', 'Bắc Giang', 'Nấu ăn, Làm bánh, Đọc sách, Yoga', 'Mình là cô gái đảm đang, thích nấu ăn và làm bánh. Thích những buổi tối ấm cúng bên gia đình. Tìm kiếm người chân thành để cùng nhau xây dựng hạnh phúc.', NULL),
(57, 81, 'Đỗ Trường Giang', '1995-05-12', 'Nam', 'Độc thân', 75, 177, 'Tìm hiểu', 'Đại học', 'Bắc Kạn', 'Câu cá, Cắm trại, Du lịch, Nhiếp ảnh', 'Mình là người yêu thiên nhiên và sự tự do. Thích câu cá và cắm trại vào cuối tuần. Mong tìm được người đồng hành cùng sở thích.', NULL),
(58, 82, 'Vương Diễm Quyên', '2001-08-30', 'Nữ', 'Độc thân', 47, 160, 'Hẹn hò', 'Cao đẳng', 'Cao Bằng', 'Thời trang, Làm đẹp, Shopping, Xem phim', 'Mình là cô gái yêu thích thời trang và làm đẹp. Thích shopping và cập nhật xu hướng mới. Tìm kiếm người đàn ông phong cách và hiểu mình.', NULL),
(59, 83, 'Nguyễn Thanh Nhàn', '1999-03-16', 'Nữ', 'Độc thân', 52, 166, 'Hẹn hò', 'Đại học', 'Hà Giang', 'Đọc sách, Viết lách, Yoga, Cà phê', 'Mình là người yêu thích văn học và nghệ thuật. Thích viết lách và chia sẻ suy nghĩ của mình. Tìm kiếm người cùng đam mê văn chương.', NULL),
(60, 84, 'Phạm Hoàng Thành', '1996-11-09', 'Nam', 'Độc thân', 72, 174, 'Kết hôn', 'Thạc sĩ', 'Lạng Sơn', 'Võ thuật, Tập gym, Đọc sách, Du lịch', 'Mình là người tự tin và mạnh mẽ. Thích võ thuật và tập gym để rèn luyện sức khỏe. Hy vọng tìm được người phù hợp để cùng xây dựng gia đình hạnh phúc.', NULL),
(61, 85, 'Lý Phương Linh', '2000-06-23', 'Nữ', 'Độc thân', 51, 165, 'Hẹn hò', 'Đại học', 'Tuyên Quang', 'Piano, Vẽ tranh, Du lịch, Yoga', 'Mình là người yêu nghệ thuật, thích chơi piano và vẽ tranh. Thích những điều đẹp đẽ trong cuộc sống. Tìm kiếm người đồng điệu về tâm hồn.', NULL),
(62, 86, 'Trần Đức Hiếu', '1997-02-04', 'Nam', 'Độc thân', 69, 171, 'Tìm hiểu', 'Đại học', 'Yên Bái', 'Bóng đá, Game, Xem phim, Cà phê', 'Mình là người vui vẻ và hòa đồng. Thích bóng đá và thường xuyên theo dõi các trận đấu. Tìm kiếm người cùng sở thích để cùng nhau trải nghiệm.', NULL),
(63, 87, 'Hoàng Thanh Thủy', '1998-09-18', 'Nữ', 'Độc thân', 50, 164, 'Hẹn hò', 'Đại học', 'Sơn La', 'Nấu ăn, Yoga, Đọc sách, Du lịch', 'Mình là cô gái hiền lành và chu đáo. Thích nấu ăn và chăm sóc người thân. Mong muốn tìm được người đàn ông chân thành và có trách nhiệm.', NULL),
(64, 88, 'Lê Văn Sơn', '1995-04-27', 'Nam', 'Độc thân', 76, 178, 'Kết hôn', 'Đại học', 'Điện Biên', 'Leo núi, Cắm trại, Nhiếp ảnh, Guitar', 'Mình là người yêu thiên nhiên và phiêu lưu. Thích leo núi và cắm trại. Biết chơi guitar và thích ca hát. Hy vọng tìm được người đồng hành trên mọi nẻo đường.', NULL),
(65, 89, 'Đặng Ngọc Lan', '2001-07-11', 'Nữ', 'Độc thân', 48, 161, 'Hẹn hò', 'Cao đẳng', 'Lai Châu', 'Làm vườn, Nấu ăn, Xem phim, Đọc sách', 'Mình là người yêu thích sự giản dị và gần gũi với thiên nhiên. Thích trồng cây và chăm sóc vườn tược. Tìm kiếm người hiền lành và chân thành.', NULL),
(66, 90, 'Võ Minh Hiếu', '1998-01-02', 'Nam', 'Độc thân', 71, 173, 'Tìm hiểu', 'Đại học', 'Lào Cai', 'Lập trình, Đọc sách, Game, Cà phê', 'Mình làm việc trong ngành công nghệ, thích lập trình và học hỏi điều mới. Thích đọc sách và uống cà phê trong thời gian rảnh. Mong tìm được người hiểu mình.', NULL),
(67, 91, 'Phan Thanh Bình', '1996-10-14', 'Nam', 'Độc thân', 74, 176, 'Kết hôn', 'Thạc sĩ', 'Hòa Bình', 'Bơi lội, Chạy bộ, Du lịch, Nhiếp ảnh', 'Mình là người yêu thể thao và sống năng động. Thích bơi lội và chạy marathon. Hy vọng tìm được người phù hợp để cùng xây dựng tương lai.', NULL),
(68, 92, 'Bùi Huệ Trinh', '1999-05-26', 'Nữ', 'Độc thân', 49, 162, 'Hẹn hò', 'Đại học', 'Quảng Trị', 'Khiêu vũ, Yoga, Shopping, Du lịch', 'Mình là cô gái năng động, yêu thích khiêu vũ và yoga. Thích đi shopping và khám phá những điều mới. Tìm kiếm người đàn ông lịch lãm.', NULL),
(69, 93, 'Trần Anh Tuấn', '1997-08-08', 'Nam', 'Độc thân', 73, 175, 'Tìm hiểu', 'Đại học', 'Quảng Bình', 'Bóng rổ, Tập gym, Xem phim, Du lịch', 'Mình là người yêu thể thao, đặc biệt là bóng rổ. Thích tập gym và duy trì lối sống lành mạnh. Mong tìm được người đồng hành phù hợp.', NULL),
(70, 94, 'Lê Tuyết Mai', '2000-12-20', 'Nữ', 'Độc thân', 50, 165, 'Hẹn hò', 'Đại học', 'Thừa Thiên Huế', 'Piano, Đọc sách, Vẽ tranh, Yoga', 'Mình là người yêu nghệ thuật và âm nhạc. Thích chơi piano và vẽ tranh. Tìm kiếm người cùng đam mê nghệ thuật như mình.', NULL),
(71, 95, 'Nguyễn Quang Đại', '1995-03-15', 'Nam', 'Độc thân', 78, 180, 'Kết hôn', 'Đại học', 'Kon Tum', 'Võ thuật, Bơi lội, Du lịch, Nhiếp ảnh', 'Mình là người tự tin, mạnh mẽ. Thích võ thuật và bơi lội. Hy vọng tìm được người phù hợp để cùng xây dựng gia đình hạnh phúc.', NULL),
(72, 96, 'Võ Hoàng An', '1998-11-07', 'Nam', 'Độc thân', 70, 172, 'Tìm hiểu', 'Đại học', 'Gia Lai', 'Câu cá, Cắm trại, Chạy bộ, Cà phê', 'Mình là người yêu thiên nhiên và sự tự do. Thích câu cá và cắm trại vào cuối tuần. Mong tìm được người đồng hành cùng sở thích.', NULL),
(73, 97, 'Lê Kim Oanh', '2001-04-19', 'Nữ', 'Độc thân', 47, 159, 'Hẹn hò', 'Cao đẳng', 'Bình Thuận', 'Nấu ăn, Làm bánh, Xem phim, Shopping', 'Mình là cô gái đảm đang, thích nấu ăn và làm bánh. Mơ ước có một gia đình nhỏ ấm cúng. Tìm kiếm người chân thành.', NULL),
(74, 98, 'Trần Ngọc Hưng', '1996-07-31', 'Nam', 'Độc thân', 72, 174, 'Kết hôn', 'Thạc sĩ', 'Ninh Thuận', 'Bóng đá, Tập gym, Đọc sách, Du lịch', 'Mình là người có trách nhiệm và nghiêm túc. Thích bóng đá và tập gym. Hy vọng tìm được người phù hợp để cùng xây dựng tương lai.', NULL),
(75, 99, 'Phạm Minh Châu', '1999-09-23', 'Nữ', 'Độc thân', 51, 164, 'Hẹn hò', 'Đại học', 'Tây Ninh', 'Yoga, Đọc sách, Du lịch, Chụp ảnh', 'Mình là người yêu thích sự cân bằng trong cuộc sống. Thích yoga và đọc sách. Tìm kiếm người hiểu và chia sẻ đam mê của mình.', NULL);

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
(1, 'admin', '0192023a7bbd73250516f069df18b500', 'active', '2025-11-02 13:17:23', 'admin'),
(50, 'duy@gmail.com', '2ab8b5d8b006dbe088cb073d958a17d8', 'active', '2025-12-15 10:30:00', 'user'),
(51, 'quan@gmail.com', '62dc7dfc8c432b5c58bf6225b1c9cbd6', 'active', '2025-12-15 09:45:00', 'user'),
(52, 'chuc@gmail.com', '28556c135b1e05da2fd05c1fa5fbb052', 'active', '2025-12-14 20:15:00', 'user'),
(53, 'cao@gmail.com', '8d49a9bb6bd80078e946ca97e1660496', 'active', '2025-12-15 08:20:00', 'user'),
(54, 'hoa@gmail.com', '21fe9c40e5f65807e20cc6022004fec3', 'active', '2025-12-13 16:30:00', 'user'),
(55, 'chinh@gmail.com', '1285b5e87664e2a2d88ad1c18609c628', 'active', '2025-12-15 11:00:00', 'user'),
(56, 'minhanh@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-14 14:22:00', 'user'),
(57, 'thanhtrung@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-15 07:30:00', 'user'),
(58, 'kimchi@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-14 19:45:00', 'user'),
(59, 'baoloc@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-15 06:15:00', 'user'),
(60, 'tuyetnhi@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-14 22:30:00', 'user'),
(61, 'ducmanh@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-15 08:45:00', 'user'),
(62, 'phuongthao@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-14 17:20:00', 'user'),
(63, 'hongphuc@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-15 10:10:00', 'user'),
(64, 'thuylinh@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-13 21:50:00', 'user'),
(65, 'quangminh@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-15 09:30:00', 'user'),
(66, 'lanphuong@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-14 15:40:00', 'user'),
(67, 'hoaidung@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-15 07:55:00', 'user'),
(68, 'myduyen@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-14 20:25:00', 'user'),
(69, 'thanhtung@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-15 11:15:00', 'user'),
(70, 'huonggiang@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-14 18:30:00', 'user'),
(71, 'vanhung@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-15 06:45:00', 'user'),
(72, 'thanhha@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-14 23:00:00', 'user'),
(73, 'ducthang@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-15 08:20:00', 'user'),
(74, 'khanhnguyen@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-14 16:55:00', 'user'),
(75, 'hoanglong@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-15 10:40:00', 'user'),
(76, 'thanhtam@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-13 19:30:00', 'user'),
(77, 'minhtuan@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-15 09:10:00', 'user'),
(78, 'ngocmai@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-14 21:45:00', 'user'),
(79, 'quocanh@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-15 07:25:00', 'user'),
(80, 'bichngoc@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-14 14:50:00', 'user'),
(81, 'truonggiang@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-15 11:30:00', 'user'),
(82, 'diemquyen@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-14 17:15:00', 'user'),
(83, 'thanhnhan@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-15 06:30:00', 'user'),
(84, 'hoangthanh@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-14 22:10:00', 'user'),
(85, 'phuonglinh@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-15 08:55:00', 'user'),
(86, 'duchieu@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-14 19:20:00', 'user'),
(87, 'thanhthuy@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-15 10:25:00', 'user'),
(88, 'vanson@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-13 20:40:00', 'user'),
(89, 'ngoclan@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-15 09:05:00', 'user'),
(90, 'minhhieu@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-14 15:30:00', 'user'),
(91, 'thanhbinh@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-15 07:50:00', 'user'),
(92, 'huetrinh@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-14 21:15:00', 'user'),
(93, 'anhtuan@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-15 11:40:00', 'user'),
(94, 'tuyetmai@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-14 16:25:00', 'user'),
(95, 'quangdai@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-15 06:55:00', 'user'),
(96, 'hoangan@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-14 23:20:00', 'user'),
(97, 'kimoanh@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-15 08:35:00', 'user'),
(98, 'ngochung@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-14 18:45:00', 'user'),
(99, 'minhchau@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'active', '2025-12-15 10:50:00', 'user');

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
(1, 50, 199000.00, '2025-12-01 10:30:00'),
(2, 57, 199000.00, '2025-12-02 14:20:00'),
(3, 61, 199000.00, '2025-12-03 09:15:00'),
(4, 65, 199000.00, '2025-12-05 16:45:00'),
(5, 69, 199000.00, '2025-12-07 11:30:00'),
(6, 75, 199000.00, '2025-12-08 13:20:00'),
(7, 77, 199000.00, '2025-12-09 15:10:00'),
(8, 84, 199000.00, '2025-12-10 10:00:00'),
(9, 88, 199000.00, '2025-12-11 12:30:00'),
(10, 91, 199000.00, '2025-12-13 14:45:00'),
(11, 95, 199000.00, '2025-12-14 16:20:00'),
(12, 98, 199000.00, '2025-12-15 09:50:00');

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
(1, 50, 52, '2025-12-10 09:30:00'),
(2, 52, 50, '2025-12-10 09:45:00'),
(3, 51, 53, '2025-12-11 14:00:00'),
(4, 53, 51, '2025-12-11 14:15:00'),
(5, 55, 56, '2025-12-12 09:00:00'),
(6, 56, 55, '2025-12-12 09:10:00'),
(7, 57, 61, '2025-12-13 15:30:00'),
(8, 61, 57, '2025-12-13 15:50:00'),
(9, 59, 77, '2025-12-14 11:00:00'),
(10, 77, 59, '2025-12-14 11:20:00'),
(11, 60, 68, '2025-12-15 07:45:00'),
(12, 68, 60, '2025-12-15 07:55:00'),
(13, 63, 67, '2025-12-10 14:30:00'),
(14, 67, 63, '2025-12-10 14:45:00'),
(15, 65, 90, '2025-12-11 09:30:00'),
(16, 90, 65, '2025-12-11 09:50:00'),
(17, 69, 88, '2025-12-12 12:30:00'),
(18, 88, 69, '2025-12-12 12:45:00'),
(19, 71, 75, '2025-12-13 06:30:00'),
(20, 75, 71, '2025-12-13 06:45:00'),
(21, 73, 79, '2025-12-14 18:30:00'),
(22, 79, 73, '2025-12-14 18:45:00'),
(23, 76, 85, '2025-12-15 13:30:00'),
(24, 85, 76, '2025-12-15 13:45:00'),
(25, 54, 58, '2025-12-10 10:00:00'),
(26, 62, 66, '2025-12-11 15:00:00'),
(27, 70, 74, '2025-12-12 16:00:00'),
(28, 78, 82, '2025-12-13 17:00:00'),
(29, 86, 89, '2025-12-14 18:00:00'),
(30, 91, 95, '2025-12-15 19:00:00'),
(31, 92, 96, '2025-12-10 20:00:00'),
(32, 93, 97, '2025-12-11 21:00:00'),
(33, 94, 98, '2025-12-12 22:00:00'),
(34, 99, 56, '2025-12-13 08:00:00'),
(35, 64, 72, '2025-12-14 09:00:00'),
(36, 80, 84, '2025-12-15 10:00:00'),
(37, 81, 87, '2025-12-10 11:00:00'),
(38, 83, 91, '2025-12-11 12:00:00'),
(39, 89, 93, '2025-12-12 13:00:00'),
(40, 95, 99, '2025-12-13 14:00:00');

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
(1, '2025-12-09', 5, 8, 45, 1, '2025-12-09 23:59:00'),
(2, '2025-12-10', 8, 12, 67, 2, '2025-12-10 23:59:00'),
(3, '2025-12-11', 6, 10, 53, 1, '2025-12-11 23:59:00'),
(4, '2025-12-12', 7, 15, 72, 3, '2025-12-12 23:59:00'),
(5, '2025-12-13', 9, 11, 58, 2, '2025-12-13 23:59:00'),
(6, '2025-12-14', 10, 14, 81, 4, '2025-12-14 23:59:00'),
(7, '2025-12-15', 5, 9, 62, 2, '2025-12-15 23:59:00');

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

--
-- Đang đổ dữ liệu cho bảng `timkiemghepdoi`
--

INSERT INTO `timkiemghepdoi` (`maTimKiem`, `maNguoiDung`, `trangThai`, `thoiDiemBatDau`, `thoiDiemKetThuc`) VALUES
(1, 54, 'searching', '2025-12-16 10:00:00', NULL),
(2, 58, 'searching', '2025-12-16 10:05:00', NULL),
(3, 62, 'searching', '2025-12-16 10:10:00', NULL),
(4, 66, 'matched', '2025-12-15 14:00:00', '2025-12-15 14:15:00'),
(5, 70, 'matched', '2025-12-15 14:05:00', '2025-12-15 14:15:00'),
(6, 74, 'searching', '2025-12-16 09:30:00', NULL),
(7, 78, 'cancelled', '2025-12-15 11:00:00', '2025-12-15 11:20:00'),
(8, 82, 'searching', '2025-12-16 10:15:00', NULL),
(9, 86, 'matched', '2025-12-15 16:00:00', '2025-12-15 16:10:00'),
(10, 89, 'matched', '2025-12-15 16:05:00', '2025-12-15 16:10:00');

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
-- Đang đổ dữ liệu cho bảng `tinnhan`
--

INSERT INTO `tinnhan` (`maTinNhan`, `maGhepDoi`, `maNguoiGui`, `noiDung`, `thoiDiemGui`) VALUES
(1, 1, 50, 'Chào bạn! Rất vui được kết nối với bạn 😊', '2025-12-10 10:00:00'),
(2, 1, 52, 'Chào bạn! Mình cũng rất vui nè. Bạn sống ở đâu vậy?', '2025-12-10 10:05:00'),
(3, 1, 50, 'Mình ở Bến Tre. Còn bạn thì sao?', '2025-12-10 10:07:00'),
(4, 1, 52, 'Mình ở Long An. Gần nhau đấy 😄', '2025-12-10 10:10:00'),
(5, 2, 51, 'Hi! Bạn có thích chơi game không?', '2025-12-11 14:20:00'),
(6, 2, 53, 'Mình ít chơi game lắm, nhưng thích khiêu vũ và vẽ tranh 🎨', '2025-12-11 14:25:00'),
(7, 2, 51, 'Nghe hay đấy! Mình cũng thích nghệ thuật nhưng mình chơi cờ nhiều hơn', '2025-12-11 14:30:00'),
(8, 3, 55, 'Xin chào! Bạn có thích du lịch không?', '2025-12-12 09:15:00'),
(9, 3, 56, 'Chào bạn! Mình rất thích du lịch và chụp ảnh luôn', '2025-12-12 09:20:00'),
(10, 3, 55, 'Tuyệt vời! Mình cũng thích chụp ảnh. Bạn đã đi những đâu rồi?', '2025-12-12 09:25:00'),
(11, 4, 57, 'Hello! Bạn có tập gym không?', '2025-12-13 16:00:00'),
(12, 4, 61, 'Có đó! Mình tập gym và võ thuật luôn 💪', '2025-12-13 16:05:00'),
(13, 4, 57, 'Tuyệt quá! Mình cũng đang tập gym mỗi ngày', '2025-12-13 16:10:00'),
(14, 5, 59, 'Chào bạn! Mình thấy bạn thích đọc sách và viết lách nhỉ?', '2025-12-14 11:30:00'),
(15, 5, 77, 'Đúng rồi! Bạn cũng thích văn học à? 📚', '2025-12-14 11:35:00'),
(16, 5, 59, 'Ừ! Mình rất thích đọc sách. Bạn thích thể loại nào?', '2025-12-14 11:40:00'),
(17, 6, 60, 'Hi! Bạn có biết khiêu vũ không?', '2025-12-15 08:00:00'),
(18, 6, 68, 'Mình có học khiêu vũ và thời trang nữa 💃', '2025-12-15 08:05:00'),
(19, 7, 63, 'Chào bạn! Bạn thích làm gì vào cuối tuần?', '2025-12-10 15:00:00'),
(20, 7, 67, 'Mình thích đi phượt bằng xe máy và nhiếp ảnh', '2025-12-10 15:10:00'),
(21, 8, 65, 'Hello! Bạn làm nghề gì vậy?', '2025-12-11 10:00:00'),
(22, 8, 90, 'Mình làm trong ngành IT, lập trình viên 💻', '2025-12-11 10:05:00'),
(23, 9, 69, 'Chào bạn! Bạn có thích leo núi không?', '2025-12-12 13:00:00'),
(24, 9, 88, 'Có! Mình rất thích leo núi và cắm trại 🏕️', '2025-12-12 13:10:00'),
(25, 10, 71, 'Hi! Bạn có thích bơi lội không?', '2025-12-13 07:00:00'),
(26, 10, 75, 'Mình rất thích! Mình bơi mỗi sáng luôn 🏊', '2025-12-13 07:10:00'),
(27, 11, 73, 'Chào! Bạn có xem bóng đá không?', '2025-12-14 19:00:00'),
(28, 11, 79, 'Có! Mình thích chơi bóng rổ hơn nhưng cũng xem bóng đá', '2025-12-14 19:10:00'),
(29, 12, 76, 'Hi! Bạn có biết chơi piano không?', '2025-12-15 14:00:00'),
(30, 12, 85, 'Mình có! Bạn cũng thích piano à? 🎹', '2025-12-15 14:05:00');

-- --------------------------------------------------------

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
-- Chỉ mục cho bảng `thanhtoan`
--
ALTER TABLE `thanhtoan`
  ADD PRIMARY KEY (`maThanhToan`),
  ADD KEY `maNguoiThanhToan` (`maNguoiThanhToan`);

--
-- Chỉ mục cho bảng `thongbaoheothong`
--
ALTER TABLE `thongbaoheothong`
  ADD PRIMARY KEY (`maThongBao`),
  ADD KEY `maAdminTao` (`maAdminTao`),
  ADD KEY `idx_status` (`trangThai`,`thoiDiemGui`);

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
  MODIFY `maGhepDoi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT cho bảng `goidangky`
--
ALTER TABLE `goidangky`
  MODIFY `maGoiDangKy` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `hotro`
--
ALTER TABLE `hotro`
  MODIFY `maHoTro` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `hoso`
--
ALTER TABLE `hoso`
  MODIFY `maHoSo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT cho bảng `lichsuvipham`
--
ALTER TABLE `lichsuvipham`
  MODIFY `maViPham` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `magiamgia`
--
ALTER TABLE `magiamgia`
  MODIFY `maMaGiamGia` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `nguoidung`
--
ALTER TABLE `nguoidung`
  MODIFY `maNguoiDung` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT cho bảng `ratelimitlog`
--
ALTER TABLE `ratelimitlog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT cho bảng `thanhtoan`
--
ALTER TABLE `thanhtoan`
  MODIFY `maThanhToan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `thich`
--
ALTER TABLE `thich`
  MODIFY `maThich` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT cho bảng `thongkengay`
--
ALTER TABLE `thongkengay`
  MODIFY `maThongKe` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `thongbaoheothong`
--
ALTER TABLE `thongbaoheothong`
  MODIFY `maThongBao` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `timkiemghepdoi`
--
ALTER TABLE `timkiemghepdoi`
  MODIFY `maTimKiem` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `tinnhan`
--
ALTER TABLE `tinnhan`
  MODIFY `maTinNhan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT cho bảng `xacminhtaikhoan`
--
ALTER TABLE `xacminhtaikhoan`
  MODIFY `maXacMinh` int(11) NOT NULL AUTO_INCREMENT;

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
-- Các ràng buộc cho bảng `thongbaoheothong`
--
ALTER TABLE `thongbaoheothong`
  ADD CONSTRAINT `thongbaoheothong_ibfk_1` FOREIGN KEY (`maAdminTao`) REFERENCES `nguoidung` (`maNguoiDung`) ON DELETE SET NULL;

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

--
-- Các ràng buộc cho bảng `xacminhtaikhoan`
--
ALTER TABLE `xacminhtaikhoan`
  ADD CONSTRAINT `xacminhtaikhoan_ibfk_1` FOREIGN KEY (`maNguoiDung`) REFERENCES `nguoidung` (`maNguoiDung`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
