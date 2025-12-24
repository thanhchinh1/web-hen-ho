# 📊 CƠ CHẾ TÍNH ĐIỂM TƯƠNG THÍCH - DUYENHUB

## Tổng quan
Hệ thống DuyenHub sử dụng thuật toán tính điểm tương thích dựa trên **5 yếu tố chính** để đánh giá mức độ phù hợp giữa 2 người dùng. Điểm số dao động từ **0% đến 100%**.

---

## 🎯 Các yếu tố tính điểm (Tổng: 100 điểm)

### 1. Độ tuổi phù hợp - 20 điểm 🎂

Yếu tố độ tuổi được tính dựa trên chênh lệch tuổi giữa 2 người:

| Chênh lệch tuổi | Điểm số | Tỷ lệ |
|-----------------|---------|-------|
| ≤ 2 tuổi        | 20 điểm | 100%  |
| ≤ 5 tuổi        | 15 điểm | 75%   |
| ≤ 10 tuổi       | 10 điểm | 50%   |
| ≤ 15 tuổi       | 5 điểm  | 25%   |
| > 15 tuổi       | 0 điểm  | 0%    |

**Lý do:** Độ tuổi gần nhau thường có cùng quan điểm sống và giai đoạn phát triển tương đồng.

---

### 2. Học vấn - 15 điểm 🎓

Đánh giá sự tương đồng về trình độ học vấn:

| Điều kiện | Điểm số | Tỷ lệ |
|-----------|---------|-------|
| Cùng trình độ | 15 điểm | 100% |
| Khác trình độ | 0 điểm | 0% |

**Các trình độ:** Trung học phổ thông, Trung cấp, Cao đẳng, Đại học, Thạc sĩ, Tiến sĩ, Khác.

**Lý do:** Học vấn tương đương giúp 2 người có cùng tầm nhìn và cách tiếp cận vấn đề.

---

### 3. Mục tiêu phát triển - 20 điểm 🎯

Đánh giá sự phù hợp về mục đích sử dụng ứng dụng:

| Điều kiện | Điểm số | Tỷ lệ |
|-----------|---------|-------|
| Cùng mục tiêu | 20 điểm | 100% |
| Khác mục tiêu | 0 điểm | 0% |

**Các mục tiêu:** Tìm bạn bè, Hẹn hò không ràng buộc, Tìm mối quan hệ nghiêm túc, Kết hôn, Chưa chắc chắn.

**Lý do:** Mục tiêu chung là nền tảng quan trọng cho sự phát triển bền vững của mối quan hệ.

---

### 4. Nơi sống - 15 điểm 🏠

Đánh giá khoảng cách địa lý:

| Điều kiện | Điểm số | Tỷ lệ |
|-----------|---------|-------|
| Cùng thành phố | 15 điểm | 100% |
| Khác thành phố | 0 điểm | 0% |

**Lý do:** Sống gần nhau tạo điều kiện gặp gỡ và duy trì mối quan hệ dễ dàng hơn.

---

### 5. Sở thích chung - 30 điểm ❤️

Yếu tố quan trọng nhất, tính dựa trên số lượng sở thích chung:

| Số sở thích chung | Điểm số | Ghi chú |
|-------------------|---------|---------|
| Mỗi sở thích | +10 điểm | Tích lũy |
| Tối đa | 30 điểm | ≥ 3 sở thích |

**Ví dụ:**
- 1 sở thích chung → 10 điểm
- 2 sở thích chung → 20 điểm
- 3+ sở thích chung → 30 điểm

**Các sở thích:** Du lịch, Ẩm thực, Thể thao, Âm nhạc, Phim ảnh, Đọc sách, Nghệ thuật, Game, Thời trang, Công nghệ, Nấu ăn, Nhiếp ảnh.

**Lý do:** Sở thích chung tạo ra nhiều chủ đề trò chuyện và hoạt động cùng nhau.

---

## 🔢 Công thức tính

```php
// Tổng điểm thực tế
$score = $ageScore + $educationScore + $goalScore + $locationScore + $interestScore;

// Tổng điểm tối đa
$totalFactors = 100; // (20 + 15 + 20 + 15 + 30)

// Tính phần trăm
$percentage = ($score / $totalFactors) * 100;

// Làm tròn kết quả
$finalScore = round($percentage);
```

---

## 📝 Ví dụ cụ thể

### **Ví dụ 1: Cặp đôi có độ tương thích cao (90%)**

**Người A:**
- Tuổi: 25
- Học vấn: Đại học
- Mục tiêu: Tìm mối quan hệ nghiêm túc
- Nơi sống: Hà Nội
- Sở thích: Du lịch, Ẩm thực, Âm nhạc

**Người B:**
- Tuổi: 27
- Học vấn: Đại học
- Mục tiêu: Tìm mối quan hệ nghiêm túc
- Nơi sống: Hà Nội
- Sở thích: Du lịch, Ẩm thực, Phim ảnh

**Tính toán:**
- Độ tuổi: 27 - 25 = 2 tuổi → **20 điểm** ✅
- Học vấn: Đại học = Đại học → **15 điểm** ✅
- Mục tiêu: Nghiêm túc = Nghiêm túc → **20 điểm** ✅
- Nơi sống: Hà Nội = Hà Nội → **15 điểm** ✅
- Sở thích: 2 sở thích chung (Du lịch, Ẩm thực) → **20 điểm** ✅

**Tổng điểm: 90/100 = 90% phù hợp** 💕

---

### **Ví dụ 2: Cặp đôi có độ tương thích trung bình (55%)**

**Người C:**
- Tuổi: 23
- Học vấn: Đại học
- Mục tiêu: Hẹn hò không ràng buộc
- Nơi sống: Hà Nội
- Sở thích: Game, Công nghệ, Thể thao

**Người D:**
- Tuổi: 30
- Học vấn: Thạc sĩ
- Mục tiêu: Tìm mối quan hệ nghiêm túc
- Nơi sống: Hà Nội
- Sở thích: Du lịch, Thể thao, Đọc sách

**Tính toán:**
- Độ tuổi: 30 - 23 = 7 tuổi → **10 điểm** ⚠️
- Học vấn: Đại học ≠ Thạc sĩ → **0 điểm** ❌
- Mục tiêu: Khác nhau → **0 điểm** ❌
- Nơi sống: Hà Nội = Hà Nội → **15 điểm** ✅
- Sở thích: 1 sở thích chung (Thể thao) → **10 điểm** ⚠️

**Tổng điểm: 35/100 = 35% phù hợp** 😐

---

### **Ví dụ 3: Cặp đôi có độ tương thích thấp (25%)**

**Người E:**
- Tuổi: 22
- Học vấn: Cao đẳng
- Mục tiêu: Tìm bạn bè
- Nơi sống: TP.HCM
- Sở thích: Game, Công nghệ

**Người F:**
- Tuổi: 35
- Học vấn: Tiến sĩ
- Mục tiêu: Kết hôn
- Nơi sống: Đà Nẵng
- Sở thích: Nghệ thuật, Âm nhạc, Đọc sách

**Tính toán:**
- Độ tuổi: 35 - 22 = 13 tuổi → **5 điểm** ❌
- Học vấn: Cao đẳng ≠ Tiến sĩ → **0 điểm** ❌
- Mục tiêu: Khác nhau → **0 điểm** ❌
- Nơi sống: TP.HCM ≠ Đà Nẵng → **0 điểm** ❌
- Sở thích: 0 sở thích chung → **0 điểm** ❌

**Tổng điểm: 5/100 = 5% phù hợp** 💔

---

## 🎯 Ngưỡng ghép đôi

Hệ thống DuyenHub áp dụng các quy tắc sau để ghép đôi:

### **1. Quy tắc về giới tính** 👫

**QUAN TRỌNG**: Hệ thống **CHỈ ghép đôi Nam với Nữ và ngược lại**.

```php
// Xác định giới tính đối lập
if ($userGender === 'Nam') {
    $targetGender = 'Nữ';
} elseif ($userGender === 'Nữ') {
    $targetGender = 'Nam';
}
```

**Quy tắc:**
- ✅ **Nam ↔️ Nữ**: Được phép ghép đôi
- ❌ **Nam ↔️ Nam**: KHÔNG được ghép đôi
- ❌ **Nữ ↔️ Nữ**: KHÔNG được ghép đôi
- ❌ **Giới tính "Khác"**: KHÔNG tham gia ghép đôi nhanh

**Lý do:** Ứng dụng tập trung vào việc kết nối các cặp đôi khác giới tính để phát triển mối quan hệ tình cảm truyền thống.

### **2. Ngưỡng điểm tối thiểu** 📊

```php
$minimumScore = 30; // 30%
```

**Quy tắc:**
- ✅ **Điểm ≥ 30%**: Được phép ghép đôi
- ❌ **Điểm < 30%**: Không đủ điều kiện ghép đôi

**Lý do:** Đảm bảo mỗi cặp đôi có ít nhất một số điểm chung cơ bản để có thể bắt đầu cuộc trò chuyện có ý nghĩa.

### **3. Ưu tiên độ phù hợp cao** 🏆

Khi có nhiều ứng viên phù hợp, hệ thống sẽ:
1. Tính điểm tương thích với TẤT CẢ ứng viên
2. Sắp xếp theo điểm số **GIẢM DẦN** (cao → thấp)
3. Chọn người có **điểm cao nhất** để ghép đôi

```php
// Sắp xếp theo điểm số giảm dần
usort($candidatesWithScores, function($a, $b) {
    return $b['score'] - $a['score'];
});

// Chọn người đầu tiên (điểm cao nhất)
$bestMatch = $candidatesWithScores[0];
```

**Lý do:** Tối ưu hóa khả năng thành công của mối quan hệ bằng cách ưu tiên các cặp đôi có độ tương thích cao nhất.

---

## 📂 Cấu trúc code

### **File chính:**

#### 1. `models/mMatching.php`
Chứa thuật toán tính điểm tương thích.

**Hàm chính:**
```php
public function calculateCompatibility($userId1, $userId2)
```
- **Input:** ID của 2 người dùng
- **Output:** Điểm tương thích (0-100%)
- **Mô tả:** Tính toán điểm dựa trên 5 yếu tố

```php
public function getCompatibilityReasons($userId1, $userId2)
```
- **Input:** ID của 2 người dùng
- **Output:** Mảng các lý do tương thích
- **Mô tả:** Trả về các điểm chung cụ thể để hiển thị cho người dùng

---

#### 2. `models/mQuickMatch.php`
Sử dụng thuật toán để tìm và ghép đôi.

**Hàm chính:**
```php
public function tryFindMatch($userId)
```
- **Input:** ID người dùng đang tìm kiếm
- **Output:** Thông tin người được ghép đôi hoặc false
- **Mô tả:** 
  - Lấy giới tính của user hiện tại
  - Xác định giới tính đối lập (Nam → Nữ, Nữ → Nam)
  - Lấy danh sách người đang tìm kiếm (cùng giới tính đối lập)
  - Lọc bỏ người đã chặn, đã ghép đôi
  - Tính điểm tương thích với TẤT CẢ ứng viên
  - Sắp xếp theo điểm số GIẢM DẦN
  - Chọn người có điểm CAO NHẤT (≥ 30%)
  - Tạo match nếu tìm thấy

---

#### 3. `views/timkiem/ghepdoinhanh.php`
Hiển thị kết quả ghép đôi cho người dùng.

**Hiển thị:**
```javascript
document.getElementById('compatibilityScore').textContent = 
    Math.round(data.score) + '% Phù hợp';
```

---

## 🔄 Quy trình ghép đôi

```
1. Người dùng bắt đầu tìm kiếm
   ↓
2. Hệ thống thêm vào hàng đợi (timkiemghepdoi)
   ↓
3. Sau 5 giây chờ, bắt đầu tìm người phù hợp
   ↓
4. Xác định giới tính đối lập (Nam → Nữ, Nữ → Nam)
   ↓
5. Lọc ứng viên:
   - Giới tính ĐỐI LẬP (chỉ Nam-Nữ)
   - Không bị chặn
   - Không đang khóa
   - Không phải là chính mình
   - Chưa từng ghép đôi trước đó
   ↓
6. Tính điểm tương thích với TẤT CẢ ứng viên
   ↓
7. Sắp xếp ứng viên theo điểm số GIẢM DẦN
   ↓
8. Chọn người có điểm CAO NHẤT (≥ 30%)
   ↓
9. Khóa 2 người để tránh conflict
   ↓
10. Tạo match và tin nhắn chào mừng
    ↓
11. Thông báo cho cả 2 người
```

---

## 💡 Ưu điểm của thuật toán

1. **Công bằng và cân bằng:**
   - Không có yếu tố nào chiếm quá nhiều hoặc quá ít
   - Tất cả các yếu tố đều được xem xét

2. **Linh hoạt:**
   - Cho phép ghép đôi với nhiều mức độ phù hợp
   - Không yêu cầu phải 100% hoàn hảo

3. **Thực tế:**
   - Dựa trên các yếu tố quan trọng trong mối quan hệ
   - Sở thích được ưu tiên cao (30%)

4. **Hiệu quả:**
   - Tính toán nhanh chóng
   - Có thể xử lý nhiều người dùng cùng lúc

5. **Dễ hiểu:**
   - Người dùng có thể biết tại sao họ được ghép với nhau
   - Hiển thị lý do tương thích cụ thể

---

## 🚀 Cải tiến trong tương lai

1. **Trọng số động:**
   - Cho phép người dùng tự chọn yếu tố quan trọng với họ
   - Điều chỉnh trọng số dựa trên feedback

2. **Machine Learning:**
   - Học từ các match thành công
   - Tối ưu hóa thuật toán theo thời gian

3. **Thêm yếu tố:**
   - Tính cách (MBTI)
   - Thu nhập
   - Chiều cao/ngoại hình
   - Tôn giáo

4. **Lịch sử match:**
   - Tránh ghép lại với người đã từng match
   - Ưu tiên người chưa từng ghép

5. **Thời gian hoạt động:**
   - Ưu tiên người đang online
   - Ghép với người có thời gian hoạt động tương tự

---

## 📊 Thống kê

### Phân bố điểm lý tưởng:
- **90-100%**: Rất phù hợp (Tất cả yếu tố gần như hoàn hảo)
- **70-89%**: Phù hợp cao (Hầu hết yếu tố phù hợp)
- **50-69%**: Phù hợp trung bình (Một số yếu tố phù hợp)
- **30-49%**: Phù hợp thấp (Ít yếu tố phù hợp)
- **< 30%**: Không phù hợp (Không đủ điều kiện ghép đôi)

### Tỷ lệ thành công dự kiến:
- **≥ 70%**: Tỷ lệ match thành công cao
- **50-69%**: Tỷ lệ match thành công trung bình
- **30-49%**: Tỷ lệ match thành công thấp

---

## 📞 Liên hệ & Đóng góp

Nếu bạn có ý tưởng cải tiến thuật toán hoặc phát hiện lỗi, vui lòng:
- Tạo issue trên GitHub
- Hoặc liên hệ với team phát triển

---

## 📄 Bản quyền

© 2025 DuyenHub - Dating Application
Thuật toán được phát triển bởi team DuyenHub

---

**Cập nhật lần cuối:** 24/12/2025
**Phiên bản:** 1.0
