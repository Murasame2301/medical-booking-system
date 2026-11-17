<?php
require_once __DIR__ . '/../Core/khoitao.php';

// File này chỉ XỬ LÝ, không in HTML. Chỉ nhận dữ liệu gửi bằng POST.
if (isset($_SESSION['id'])) {
    chuyen_trang(trang_chu_theo_vai_tro($_SESSION['vaitro']));
}

$hoten = trim($_POST['hoten'] ?? '');
$ngaysinh = $_POST['ngaysinh'] ?? '';
$sdt = trim($_POST['sdt'] ?? '');
$email = trim($_POST['email'] ?? '');
$matkhau = $_POST['matkhau'] ?? '';
$nhaplai = $_POST['nhaplai'] ?? '';

// Bước 1: kiểm tra dữ liệu, gặp lỗi đầu tiên thì ghi vào $loi
$loi = "";
if ($hoten == "" || $ngaysinh == "" || $sdt == "") {
    $loi = "Vui lòng nhập đủ thông tin bắt buộc.";
} elseif (!preg_match('/^0[0-9]{9}$/', $sdt)) {
    $loi = "Số điện thoại phải gồm 10 chữ số, bắt đầu bằng số 0.";
} elseif ($email != "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $loi = "Email không hợp lệ.";
} elseif (strlen($matkhau) < 6) {
    $loi = "Mật khẩu phải từ 6 ký tự trở lên.";
} elseif ($matkhau != $nhaplai) {
    $loi = "Mật khẩu nhập lại không khớp.";
} elseif (tim_nguoi_dung($conn, $sdt)) {
    $loi = "Số điện thoại này đã được đăng ký.";
} elseif ($email != "" && email_da_dung($conn, $email)) {
    $loi = "Email này đã được sử dụng.";
}

// Có lỗi: nhớ dữ liệu đã gõ + lời nhắn, rồi quay về form
if ($loi != "") {
    luu_du_lieu_cu($_POST);
    dat_thong_bao('loi', $loi);
    chuyen_trang('../../dang_ky.php');
}

// Bước 2: dữ liệu hợp lệ thì lưu vào DB
$mk = password_hash($matkhau, PASSWORD_DEFAULT);
if (tao_benh_nhan($conn, $hoten, $sdt, $email, $mk, $ngaysinh)) {
    dat_thong_bao('ok', 'Đăng ký thành công, hãy đăng nhập.');
    chuyen_trang('../../dang_nhap.php');
}

luu_du_lieu_cu($_POST);
dat_thong_bao('loi', 'Có lỗi khi tạo tài khoản, vui lòng thử lại.');
chuyen_trang('../../dang_ky.php');