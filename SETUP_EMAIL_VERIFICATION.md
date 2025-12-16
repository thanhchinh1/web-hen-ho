# 📧 HƯỚNG DẪN CÀI ĐẶT XÁC THỰC EMAIL

## ✅ Hoàn thành

Hệ thống xác thực email bằng PHPMailer + Gmail đã được cài đặt thành công!

---

## 🔧 CÁC BƯỚC CẤU HÌNH

### 1️⃣ Chạy Migration Database

Chạy file SQL để tạo bảng và cập nhật cấu trúc database:

```sql
-- File: database/add_email_verification.sql
```

**Cách chạy:**

- Mở phpMyAdmin
- Chọn database `webhenho` (hoặc tên database của bạn)
- Vào tab SQL
- Copy nội dung file `database/add_email_verification.sql` và chạy

**Hoặc dùng command line:**

```bash
mysql -u root -p webhenho < database/add_email_verification.sql
```

---

### 2️⃣ Cấu hình Gmail SMTP

#### Bước 1: Bật xác thực 2 bước (2FA) cho Gmail

1. Truy cập: https://myaccount.google.com/security
2. Tìm "2-Step Verification" (Xác minh 2 bước)
3. Bật tính năng này

#### Bước 2: Tạo App Password (Mật khẩu ứng dụng)

1. Truy cập: https://myaccount.google.com/apppasswords
2. Chọn app: "Mail" hoặc "Other" (tùy chọn)
3. Chọn device: "Other" và nhập tên (ví dụ: "DuyenHub Web")
4. Click "Generate"
5. **Copy mã 16 ký tự** (dạng: xxxx xxxx xxxx xxxx)

#### Bước 3: Cập nhật cấu hình

Mở file `models/mEmailConfig.php` và sửa:

```php
// ⚠️ THAY ĐỔI 2 DÒNG NÀY
const SMTP_USERNAME = 'your-email@gmail.com';        // Email Gmail của bạn
const SMTP_PASSWORD = 'your-app-password-here';      // App Password (16 ký tự, bỏ khoảng trắng)
```

**Ví dụ:**

```php
const SMTP_USERNAME = 'duyen hub@gmail.com';
const SMTP_PASSWORD = 'abcd efgh ijkl mnop';  // Bỏ khoảng trắng thành: abcdefghijklmnop
```

---

## 📋 LUỒNG HOẠT ĐỘNG

### Đăng ký mới:

1. User điền form đăng ký → Submit
2. Hệ thống tạo mã OTP 6 số
3. Gửi OTP qua email (PHPMailer + Gmail SMTP)
4. User nhập OTP trên trang verify-email.php
5. Xác thực thành công → Tạo tài khoản → Gửi email chào mừng
6. Redirect về trang đăng nhập

### Đăng nhập:

1. User nhập email/password
2. Hệ thống kiểm tra `email_verified = 1`
3. Nếu chưa xác thực → Thông báo lỗi
4. Nếu đã xác thực → Cho đăng nhập

---

## 📁 CẤU TRÚC FILES MỚI

```
models/
  ├── mEmailConfig.php          ✅ Cấu hình SMTP Gmail
  ├── mEmailService.php         ✅ Gửi email OTP & Welcome
  └── mEmailVerification.php    ✅ Quản lý OTP (tạo, verify, resend)

controller/
  ├── cRegister.php             ✅ Đã update: Gửi OTP thay vì đăng ký trực tiếp
  ├── cVerifyEmail.php          ✅ Xác thực OTP và tạo tài khoản
  └── cResendOTP.php            ✅ Gửi lại mã OTP

views/dangky/
  └── verify-email.php          ✅ Giao diện nhập OTP (6 số)

database/
  └── add_email_verification.sql ✅ Migration SQL
```

---

## 🧪 KIỂM TRA

### Test gửi email:

```php
// Tạo file test-email.php trong thư mục gốc
<?php
require_once 'models/mEmailService.php';
require_once 'models/mEmailConfig.php';

$emailService = new EmailService();
$result = $emailService->sendOTPEmail('email-test@gmail.com', '123456', 10);

if ($result) {
    echo "✅ Gửi email thành công!";
} else {
    echo "❌ Lỗi gửi email. Kiểm tra cấu hình!";
}
?>
```

---

## ⚙️ CẤU HÌNH NÂNG CAO

File `models/mEmailConfig.php`:

```php
const OTP_LENGTH = 6;           // Độ dài mã OTP (mặc định: 6)
const OTP_EXPIRE_MINUTES = 10;  // Thời gian hết hạn OTP (phút)
const OTP_MAX_ATTEMPTS = 5;     // Số lần nhập sai tối đa
```

---

## 🚨 XỬ LÝ LỖI THƯỜNG GẶP

### Lỗi: "SMTP connect() failed"

- Kiểm tra App Password đã đúng chưa
- Kiểm tra Gmail có bật 2FA chưa
- Kiểm tra firewall/antivirus có chặn port 587 không

### Lỗi: "Email chưa được cấu hình"

- Mở `models/mEmailConfig.php`
- Sửa dòng `SMTP_USERNAME` và `SMTP_PASSWORD`

### Lỗi: "Table doesn't exist"

- Chưa chạy migration SQL
- Chạy file `database/add_email_verification.sql`

### Email không đến

- Kiểm tra spam/junk folder
- Đợi 1-2 phút (Gmail đôi khi delay)
- Thử gửi lại OTP

---

## 🔐 BẢO MẬT

✅ Mã OTP có thời hạn 10 phút
✅ Giới hạn 5 lần nhập sai
✅ OTP lưu trong database được mã hóa
✅ Tự động xóa OTP cũ khi tạo mới
✅ Email chào mừng sau khi verify thành công

---

## 📞 HỖ TRỢ

Nếu gặp vấn đề, kiểm tra:

1. File log: `logs/` (nếu có)
2. PHP error log
3. Gmail inbox (email test)
4. Database: Bảng `email_verifications` và cột `email_verified` trong `nguoidung`

---

## 🎯 TÍNH NĂNG

✅ Xác thực email bằng OTP 6 số
✅ Gửi email đẹp với HTML template
✅ Countdown timer 10 phút
✅ Gửi lại OTP (có cooldown 60 giây)
✅ Auto-focus và paste OTP
✅ Giới hạn số lần nhập sai
✅ Email chào mừng sau khi xác thực
✅ Tích hợp với flow đăng ký hiện tại
✅ 100% MIỄN PHÍ với Gmail

---

**Chúc bạn triển khai thành công! 🎉**
