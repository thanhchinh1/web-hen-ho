# Tính năng Chỉnh Vị Trí Ảnh Đại Diện

## Mô tả

Tính năng cho phép người dùng chỉnh sửa vị trí và zoom ảnh đại diện trước khi lưu trong các trang:

- **Thiết lập hồ sơ** (`views/hoso/thietlaphoso.php`)
- **Chỉnh sửa hồ sơ** (`views/hoso/chinhsua.php`)

## Tính năng chính

### 1. Avatar Editor Modal

- Giao diện modal popup hiện đại
- Hiển thị ảnh trong khung tròn (giống preview cuối cùng)
- Background gradient đẹp mắt

### 2. Điều khiển ảnh

- **Di chuyển**: Kéo thả ảnh bằng chuột hoặc chạm (mobile)
- **Zoom**: Sử dụng thanh trượt để phóng to/thu nhỏ (1x - 3x)
- Hỗ trợ cả desktop và mobile (touch events)

### 3. Xử lý ảnh

- Crop ảnh thành hình vuông 400x400px (độ phân giải cao)
- Chuyển đổi sang JPEG với chất lượng 95%
- Tối ưu kích thước file
- Preview ngay lập tức

### 4. Validation

- Kiểm tra kích thước file (tối đa 5MB)
- Kiểm tra định dạng file (jpg, jpeg, png, gif)
- Thông báo lỗi rõ ràng

## Files đã thay đổi

### Views

1. **views/hoso/thietlaphoso.php**

   - Thêm modal avatar editor
   - Cập nhật JavaScript với chức năng crop/position
   - Sử dụng canvas API để crop ảnh

2. **views/hoso/chinhsua.php**
   - Tương tự thietlaphoso.php
   - Tích hợp với form submission hiện có

### CSS

1. **public/css/thietlaphoso.css**

   - Styles cho modal
   - Styles cho controls (zoom slider)
   - Responsive design cho mobile

2. **public/css/chinhsua.css**
   - Tương tự thietlaphoso.css

### Controller

1. **controller/cProfile_setup.php**
   - Cải thiện xử lý extension cho ảnh đã crop

## Cách sử dụng

### Cho người dùng:

1. Click nút "Tải ảnh lên"
2. Chọn ảnh từ thiết bị
3. Modal editor tự động mở
4. Di chuyển ảnh bằng cách kéo thả
5. Điều chỉnh zoom bằng thanh trượt
6. Click "Áp dụng" để xác nhận
7. Preview ảnh đã chỉnh sẽ hiển thị
8. Lưu form như bình thường

### Cho developer:

```javascript
// Biến trạng thái
let croppedBlob = null; // Blob ảnh đã crop
let cropData = { scale: 1, x: 0, y: 0 }; // Dữ liệu crop

// Hàm chính
openAvatarEditor(event); // Mở modal
applyCrop(); // Áp dụng crop
closeAvatarEditor(); // Đóng modal
```

## Công nghệ sử dụng

- **Canvas API**: Crop và resize ảnh
- **Blob API**: Tạo file từ canvas
- **FileReader API**: Đọc file ảnh
- **Drag & Drop**: Di chuyển ảnh
- **Touch Events**: Hỗ trợ mobile
- **CSS Transform**: Zoom và translate

## Tương thích

- ✅ Desktop (Chrome, Firefox, Safari, Edge)
- ✅ Mobile (iOS Safari, Chrome Mobile)
- ✅ Tablet
- ✅ Responsive design

## Lưu ý kỹ thuật

- Ảnh được crop ở client-side (giảm tải server)
- Sử dụng JPEG quality 95% để cân bằng chất lượng và dung lượng
- Transform CSS được tối ưu cho performance
- Blob được gửi qua FormData với tên "avatar.jpg"

## Cải tiến trong tương lai

- [ ] Thêm rotation (xoay ảnh)
- [ ] Thêm filters (bộ lọc màu)
- [ ] Crop ratio khác nhau (1:1, 16:9, etc.)
- [ ] Undo/Redo
- [ ] Multiple file upload (chọn nhiều ảnh)
