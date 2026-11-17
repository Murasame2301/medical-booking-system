<?php
require_once __DIR__ . '/../Core/khoitao.php';

bat_buoc_quan_tri('../../');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    chuyen_trang('../../qt_nguoi_dung.php');
}

$id = (int)($_POST['id'] ?? 0);
$hanhdong = $_POST['hanhdong'] ?? '';   // 'khoa' hoặc 'mo'

// Quay về đúng danh sách đang xem (giữ nguyên ô tìm kiếm và bộ lọc).
// http_build_query tự đổi mảng thành chuỗi dạng "tukhoa=abc&vaitro=2" và mã hóa ký tự đặc biệt.
$ve = '../../qt_nguoi_dung.php?' . http_build_query([
    'tukhoa' => $_POST['tukhoa'] ?? '',
    'vaitro' => (int)($_POST['vaitro'] ?? 0),
]);

$vaitro = lay_vai_tro_cua($conn, $id);

if ($vaitro === null) {
    dat_thong_bao('loi', 'Không tìm thấy người dùng.');
    chuyen_trang($ve);
}
if ($hanhdong != 'khoa' && $hanhdong != 'mo') {
    dat_thong_bao('loi', 'Thao tác không hợp lệ.');
    chuyen_trang($ve);
}
// Chỉ chặn khi KHÓA: không khóa chính mình, không khóa quản trị viên
if ($hanhdong == 'khoa' && ($id == $_SESSION['id'] || $vaitro == 'quan_tri')) {
    dat_thong_bao('loi', 'Không thể khóa tài khoản quản trị viên.');
    chuyen_trang($ve);
}

$trangthai = ($hanhdong == 'khoa') ? 'bi_khoa' : 'hoat_dong';
dat_trang_thai_tai_khoan($conn, $id, $trangthai);

dat_thong_bao('ok', $hanhdong == 'khoa' ? 'Đã khóa tài khoản.' : 'Đã mở khóa tài khoản.');
chuyen_trang($ve);