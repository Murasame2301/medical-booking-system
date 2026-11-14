<?php
require_once __DIR__ . '/chung/khoi_tao.php';

$trang_hien_tai = (string)($_GET['trang'] ?? '');
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && !in_array($trang_hien_tai, ['dangnhap', 'dangky'], true)) { // đổi 'dangky' cho đúng tên trang đăng ký
  kiem_tra_csrf();
}

// ... phần còn lại của index.php (xác định $trang, nạp trang con...)