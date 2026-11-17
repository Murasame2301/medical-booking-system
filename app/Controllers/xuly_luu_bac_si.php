<?php
require_once __DIR__ . '/../Core/khoitao.php';

bat_buoc_quan_tri('../../');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    chuyen_trang('../../qt_bac_si.php');
}

$id = (int)($_POST['id'] ?? 0);   // 0 = thêm mới
$d = [
    'hoten'     => trim($_POST['hoten'] ?? ''),
    'sdt'       => trim($_POST['sdt'] ?? ''),
    'email'     => trim($_POST['email'] ?? ''),
    'trinhdo'   => trim($_POST['trinhdo'] ?? ''),
    'gioithieu' => trim($_POST['gioithieu'] ?? ''),
];
$matkhau = $_POST['matkhau'] ?? '';

// Các ô tích gửi lên thành một mảng; ép từng phần tử về số nguyên và bỏ phần tử trùng
$chuyenkhoa = array_unique(array_map('intval', (array)($_POST['chuyenkhoa'] ?? [])));

// Khi lỗi thì quay về form (đúng chế độ thêm hoặc sửa)
$ve_form = '../../qt_bac_si_form.php' . ($id > 0 ? '?id=' . $id : '');

// Đang sửa thì bác sĩ đó phải tồn tại
if ($id > 0 && !lay_bac_si_chi_tiet($conn, $id)) {
    dat_thong_bao('loi', 'Không tìm thấy bác sĩ.');
    chuyen_trang('../../qt_bac_si.php');
}

$nguoi_cung_sdt = tim_nguoi_dung($conn, $d['sdt']);   // người đang dùng SĐT này (nếu có)

$loi = "";
if ($d['hoten'] == "" || $d['sdt'] == "") {
    $loi = "Vui lòng nhập họ tên và số điện thoại.";
} elseif (!sdt_hop_le($d['sdt'])) {
    $loi = "Số điện thoại phải gồm 10 chữ số, bắt đầu bằng số 0.";
} elseif ($nguoi_cung_sdt && $nguoi_cung_sdt['ID'] != $id) {
    $loi = "Số điện thoại này đã được người khác sử dụng.";
} elseif ($d['email'] != "" && !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
    $loi = "Email không hợp lệ.";
} elseif ($d['email'] != "" && email_da_dung($conn, $d['email'], $id)) {
    $loi = "Email này đã được người khác sử dụng.";
} elseif ($id == 0 && strlen($matkhau) < 6) {
    $loi = "Mật khẩu ban đầu phải từ 6 ký tự trở lên.";
} elseif ($id > 0 && $matkhau != "" && strlen($matkhau) < 6) {
    $loi = "Mật khẩu mới phải từ 6 ký tự trở lên.";
} elseif (count($chuyenkhoa) == 0) {
    $loi = "Vui lòng chọn ít nhất một chuyên khoa.";
} elseif (mb_strlen($d['trinhdo']) > 100) {
    $loi = "Trình độ tối đa 100 ký tự.";
}

if ($loi != "") {
    $du_lieu = $d;
    $du_lieu['chuyenkhoa'] = $chuyenkhoa;   // giữ cả các ô đã tích (mật khẩu thì không giữ)
    luu_du_lieu_cu($du_lieu);
    dat_thong_bao('loi', $loi);
    chuyen_trang($ve_form);
}

// Có nhập mật khẩu thì mã hóa; để trống (khi sửa) thì null = giữ mật khẩu cũ
$mk_ma_hoa = ($matkhau != "") ? password_hash($matkhau, PASSWORD_DEFAULT) : null;

if ($id == 0) {
    $thanhcong = tao_bac_si($conn, $d, $mk_ma_hoa, $chuyenkhoa);
    $loi_chung = 'Có lỗi khi thêm bác sĩ, vui lòng thử lại.';
    $loi_ok = 'Đã thêm bác sĩ.';
} else {
    $thanhcong = cap_nhat_bac_si($conn, $id, $d, $mk_ma_hoa, $chuyenkhoa);
    $loi_chung = 'Có lỗi khi lưu thông tin, vui lòng thử lại.';
    $loi_ok = 'Đã cập nhật thông tin bác sĩ.';
}

if (!$thanhcong) {
    $du_lieu = $d;
    $du_lieu['chuyenkhoa'] = $chuyenkhoa;
    luu_du_lieu_cu($du_lieu);
    dat_thong_bao('loi', $loi_chung);
    chuyen_trang($ve_form);
}

dat_thong_bao('ok', $loi_ok);
chuyen_trang('../../qt_bac_si.php');