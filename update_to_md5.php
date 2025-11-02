<?php
require_once 'models/mDbconnect.php';

// Kết nối database
$db = clsConnect::getInstance();
$conn = $db->connect();

echo "<h2>Cập nhật mật khẩu sang MD5</h2>";

// Cập nhật mật khẩu cho tài khoản admin trong bảng nguoidung
$username = 'admin';
$password = 'admin123';
$md5Password = md5($password);

echo "<h3>1. Cập nhật bảng nguoidung:</h3>";
echo "<p>Username: <strong>$username</strong></p>";
echo "<p>Password: <strong>$password</strong></p>";
echo "<p>MD5 Hash: <code>$md5Password</code></p>";

// Kiểm tra xem tài khoản có tồn tại không
$stmt = $conn->prepare("SELECT maNguoiDung FROM nguoidung WHERE tenDangNhap = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Cập nhật mật khẩu
    $stmt = $conn->prepare("UPDATE nguoidung SET matKhau = ?, role = 'admin', trangThaiNguoiDung = 'active' WHERE tenDangNhap = ?");
    $stmt->bind_param("ss", $md5Password, $username);
    
    if ($stmt->execute()) {
        echo "<div style='background:#d4edda; padding:15px; border:1px solid #c3e6cb; border-radius:5px; margin:20px 0;'>";
        echo "<strong style='color:#155724;'>✓ Cập nhật thành công!</strong>";
        echo "</div>";
    } else {
        echo "<p style='color:red;'>✗ Lỗi: " . $conn->error . "</p>";
    }
} else {
    // Tạo tài khoản mới
    echo "<p style='color:orange;'>Không tìm thấy tài khoản, đang tạo mới...</p>";
    
    $stmt = $conn->prepare("INSERT INTO nguoidung (tenDangNhap, matKhau, role, trangThaiNguoiDung) VALUES (?, ?, 'admin', 'active')");
    $stmt->bind_param("ss", $username, $md5Password);
    
    if ($stmt->execute()) {
        echo "<div style='background:#d4edda; padding:15px; border:1px solid #c3e6cb; border-radius:5px; margin:20px 0;'>";
        echo "<strong style='color:#155724;'>✓ Tạo tài khoản thành công!</strong>";
        echo "</div>";
    } else {
        echo "<p style='color:red;'>✗ Lỗi: " . $conn->error . "</p>";
    }
}

// Kiểm tra lại
echo "<h3>2. Kiểm tra lại:</h3>";
$stmt = $conn->prepare("SELECT maNguoiDung, tenDangNhap, matKhau, role, trangThaiNguoiDung FROM nguoidung WHERE tenDangNhap = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "<table border='1' cellpadding='10' style='border-collapse:collapse;'>";
    echo "<tr><td><strong>ID</strong></td><td>" . $user['maNguoiDung'] . "</td></tr>";
    echo "<tr><td><strong>Tên đăng nhập</strong></td><td>" . $user['tenDangNhap'] . "</td></tr>";
    echo "<tr><td><strong>Mật khẩu (MD5)</strong></td><td>" . $user['matKhau'] . "</td></tr>";
    echo "<tr><td><strong>Role</strong></td><td>" . $user['role'] . "</td></tr>";
    echo "<tr><td><strong>Trạng thái</strong></td><td>" . $user['trangThaiNguoiDung'] . "</td></tr>";
    echo "</table>";
    
    // Test login
    if (md5($password) === $user['matKhau']) {
        echo "<div style='background:#d4edda; padding:15px; border:1px solid #c3e6cb; border-radius:5px; margin:20px 0;'>";
        echo "<h3 style='color:#155724; margin:0;'>✓ TEST ĐĂNG NHẬP THÀNH CÔNG!</h3>";
        echo "<p style='margin:10px 0 0 0;'>Bạn có thể đăng nhập với:</p>";
        echo "<ul>";
        echo "<li><strong>Username:</strong> admin</li>";
        echo "<li><strong>Password:</strong> admin123</li>";
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<p style='color:red;'>✗ Mật khẩu không khớp!</p>";
    }
}

echo "<hr>";
echo "<a href='views/dangnhap/login.php' style='display:inline-block; padding:10px 20px; background:#007bff; color:white; text-decoration:none; border-radius:5px;'>Đăng nhập ngay</a>";
?>
