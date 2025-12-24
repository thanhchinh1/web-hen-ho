<?php
// Script tự động xoá giao dịch quá 24h chưa xác nhận
require_once '../models/mDbconnect.php';

$db = clsConnect::getInstance()->connect();
$sql = "UPDATE giaodich SET trang_thai = 'da_xoa', thoi_gian_xoa = NOW() WHERE trang_thai = 'cho_xac_nhan' AND thoi_gian_tao < (NOW() - INTERVAL 24 HOUR)";
$result = $db->query($sql);
if ($result) {
    echo "Đã xoá các giao dịch quá 24h chưa xác nhận.";
} else {
    echo "Lỗi: " . $db->error;
}
