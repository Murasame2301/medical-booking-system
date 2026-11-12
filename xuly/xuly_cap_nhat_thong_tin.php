<?php
require_once __DIR__ . '/../includes/khoitao.php';

bat_buoc_dang_nhap('../');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    chuyen_trang('../thong_tin_ca_nhan.php');
}

$d = [
    'hoten'    => trim($_POST['hoten'] ?? ''),
    'ngaysinh' => $_POST['ngaysinh'] ?? '',
    'gioitinh' => $_POST['gioitinh'] ?? '',
    'email'    => trim($_POST['email'] ?? ''),
    'diachi'   => trim($_POST['diachi'] ?? ''),
    'cccd'     => trim($_POST['cccd'] ?? ''),
    'dantoc'   => trim($_POST['dantoc'] ?? ''),
    'bhyt'     => trim($_POST['bhyt'] ?? ''),
];

$loi = "";
if ($d['hoten'] == "") {
    $loi = "Họ tên không được để trống.";
} elseif ($d['ngaysinh'] != "" && (strtotime($d['ngaysinh']) === false || strtotime($d['ngaysinh']) > time())) {
    $loi = "Ngày sinh không hợp lệ.";
} elseif ($d['gioitinh'] != "" && !in_array($d['gioitinh'], ['nam', 'nu'])) {
    $loi = "Giới tính không hợp lệ.";
} elseif ($d['email'] != "" && !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
    $loi = "Email không hợp lệ.";
} elseif ($d['email'] != "" && email_da_dung($conn, $d['email'], $_SESSION['id'])) {
    $loi = "Email này đã được người khác sử dụng.";
} elseif (mb_strlen($d['diachi']) > 255) {
    $loi = "Địa chỉ tối đa 255 ký tự.";
} elseif ($d['cccd'] != "" && !preg_match('/^[0-9]{12}$/', $d['cccd'])) {
    $loi = "Số CCCD phải gồm đúng 12 chữ số.";
} elseif (mb_strlen($d['dantoc']) > 15) {
    $loi = "Dân tộc tối đa 15 ký tự.";
} elseif ($d['bhyt'] != "" && !preg_match('/^[A-Za-z0-9]{10,15}$/', $d['bhyt'])) {
    $loi = "Mã thẻ BHYT gồm 10 đến 15 chữ và số.";
} elseif ($d['cccd'] != "" && !preg_match('/^[0-9]{12}$/', $d['cccd'])) {
    $loi = "Số CCCD phải gồm đúng 12 chữ số.";
} elseif ($d['cccd'] != "" && da_co_nguoi_dung($conn, 'cccd', $d['cccd'], $_SESSION['id'])) {
    $loi = "Số CCCD này đã được người khác sử dụng.";
} elseif (mb_strlen($d['dantoc']) > 15) {
    $loi = "Dân tộc tối đa 15 ký tự.";
} elseif ($d['bhyt'] != "" && !preg_match('/^[A-Za-z0-9]{10,15}$/', $d['bhyt'])) {
    $loi = "Mã thẻ BHYT gồm 10 đến 15 chữ và số.";
} elseif ($d['bhyt'] != "" && da_co_nguoi_dung($conn, 'bhyt', $d['bhyt'], $_SESSION['id'])) {
    $loi = "Mã thẻ BHYT này đã được người khác sử dụng.";
}

if ($loi != "") {
    luu_du_lieu_cu($_POST);
    dat_thong_bao('loi', $loi);
    chuyen_trang('../thong_tin_ca_nhan.php');
}

if (!cap_nhat_thong_tin_ca_nhan($conn, $_SESSION['id'], $d)) {
    luu_du_lieu_cu($_POST);
    dat_thong_bao('loi', 'Có lỗi khi lưu thông tin, vui lòng thử lại.');
    chuyen_trang('../thong_tin_ca_nhan.php');
}

// Cập nhật luôn tên hiển thị trên header
$_SESSION['hoten'] = $d['hoten'];

dat_thong_bao('ok', 'Đã cập nhật thông tin cá nhân.');
chuyen_trang('../thong_tin_ca_nhan.php');