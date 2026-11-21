<?php
//Router chung: mọi request đi qua index.php?page=...

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/Helpers/utils.php';
require_once __DIR__ . '/app/Helpers/csrf.php';
require_once __DIR__ . '/app/Helpers/ajax.php';
require_once __DIR__ . '/app/Helpers/session.php';
require_once __DIR__ . '/app/Helpers/db.php';
require_once __DIR__ . '/app/Models/nguoi_dung.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$danhSachTrang = require __DIR__ . '/config/routes.php';

$trang = (string) ($_GET['page'] ?? 'trang-chu');

//Chỉ cho tên trang đơn giản (chữ thường, số, gạch ngang) và phải có trong bảng định tuyến.
if (!preg_match('#^[a-z0-9-]+(/[a-z0-9-]+){0,2}$#', $trang) || !isset($danhSachTrang[$trang])) {
    http_response_code(404);
    require __DIR__ . '/app/Views/loi/404.php';
    exit;
}

$cauHinh = $danhSachTrang[$trang];
$laAjax = isset($_GET['ajax']) || ajax_is_request();
$vaiTroYeuCau = $cauHinh['quyen'] ?? null;

//Tài khoản bị khoá giữa phiên thì đẩy ra ngay (kiểm tra mọi request, không chỉ lúc đăng nhập).
if (da_dang_nhap()) {
    $nguoiDungHienTai = nguoi_dung_lay_tai_khoan($conn, ma_nguoi_dung_hien_tai());
    if ($nguoiDungHienTai === null || $nguoiDungHienTai['trangthai'] === TK_BI_KHOA) {
        $_SESSION = [];
        session_destroy();
        session_start();
        $_SESSION['auth_message'] = 'Tài khoản của bạn đã bị khoá. Vui lòng liên hệ quản trị viên.';

        if ($laAjax) {
            tra_json(ket_qua(false, $_SESSION['auth_message']), 401);
        }
        chuyen_trang('index.php?page=dang-nhap');
    }
}

//Trang công khai thì bỏ qua. Trang có quyền thì bắt buộc đăng nhập và đúng vai trò.
if ($vaiTroYeuCau !== null) {
    bat_buoc_dang_nhap($vaiTroYeuCau);
}

//Trang đăng nhập, đăng ký: ai đã đăng nhập thì về trang chủ của mình.
if ($trang === 'dang-nhap' || $trang === 'dang-ky') {
    if (da_dang_nhap()) {
        chuyen_trang(trang_chu_theo_vai_tro(vai_tro_hien_tai()));
    }
}

if ($trang === 'dang-xuat') {
    dang_xuat();
}

//CSRF: mọi POST phải có token, trừ trang đăng nhập, đăng ký (có thể chưa có token trong phiên).
$laTrangAuth = in_array($trang, ['dang-nhap', 'dang-ky'], true);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$laTrangAuth && !csrf_validate()) {
    if ($laAjax) {
        tra_json(ket_qua(false, 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.'), 419);
    }
    http_response_code(419);
    require __DIR__ . '/app/Views/loi/419.php';
    exit;
}

require_once __DIR__ . '/' . $cauHinh['controller'];

//Trang bác sĩ: nạp thêm __nap.php để có sẵn hàm dùng chung trước khi render.
if (str_starts_with($trang, 'bac-si/')) {
    require_once __DIR__ . '/app/Controllers/BacSi/__nap.php';
}

$viewData = [];
$ham = $cauHinh['ham'];

//Nhánh AJAX: route có 'xu_ly' thì gọi hàm đó và bắt buộc trả JSON.
$hamXuLy = $cauHinh['xu_ly'] ?? null;
if ($laAjax && $hamXuLy !== null) {
    if (!is_callable($hamXuLy)) {
        tra_json(ket_qua(false, 'Trang này chưa hỗ trợ yêu cầu AJAX.'), 400);
    }
    $hamXuLy($conn);
    bat_buoc_ajax();
}

$viewData = $ham($conn);

//Trang không có view thì dừng.
if (!isset($cauHinh['view'])) {
    exit;
}

$viewFile = __DIR__ . '/app/Views/' . $cauHinh['view'];

//Kiểu 1: view tự là một trang HTML đầy đủ (bệnh nhân, auth).
if (!isset($cauHinh['khung'])) {
    require $viewFile;
    exit;
}

//Kiểu 2: render trong khung của vai trò. Khung có sidebar thì chèn sidebar.
$tieuDe = $cauHinh['tieu_de'] ?? 'Phòng khám';
$jsTrang = $cauHinh['js'] ?? [];
$khung = $cauHinh['khung'];
$thuMucKhung = __DIR__ . '/app/Views/' . $khung . '/layout/';

require $thuMucKhung . 'header.php';

if (is_file($thuMucKhung . 'sidebar.php')) {
    require $thuMucKhung . 'sidebar.php';
}

require $viewFile;
require $thuMucKhung . 'footer.php';
