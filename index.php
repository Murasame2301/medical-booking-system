<?php
/* Router chung: moi request deu di qua index.php?page=...
   Nhiem vu:
     1. Nap cau hinh va helper dung chung.
     2. Doc 'page', tra bang dinh tuyen.
     3. Chan tai khoan da bi khoa (kiem tra lai moi request).
     4. Kiem tra dang nhap + dung vai tro.
     5. Kiem tra CSRF cho moi POST.
     6. Goi controller, roi render view (kieu trang day du hoac theo khung). */

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

/* Chi cho phep ten trang don gian: chu thuong, so, gach ngang, toi da hai
   dau '/'. Chan ca chuoi co dau cham hoac duong dan tuyet doi. */
if (!preg_match('#^[a-z0-9-]+(/[a-z0-9-]+){0,2}$#', $trang) || !isset($danhSachTrang[$trang])) {
    http_response_code(404);
    require __DIR__ . '/app/Views/loi/404.php';
    exit;
}

$cauHinh = $danhSachTrang[$trang];
$laAjax = isset($_GET['ajax']) || ajax_is_request();
$vaiTroYeuCau = $cauHinh['quyen'] ?? null;

/* Tai khoan bi khoa giua phien: day ra ngay, khong doi het phien.
   Kiem tra o day, khong chi luc dang nhap, de quan tri khoa duoc ngay. */
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

/* Trang cong khai thi khong chan. Trang co quyen thi bat buoc dang nhap
   va dung vai tro. */
if ($vaiTroYeuCau !== null) {
    bat_buoc_dang_nhap($vaiTroYeuCau);
}

/* Trang dang nhap / dang ky: nguoi da dang nhap thi ve trang chu cua minh. */
if ($trang === 'dang-nhap' || $trang === 'dang-ky') {
    if (da_dang_nhap()) {
        chuyen_trang(trang_chu_theo_vai_tro(vai_tro_hien_tai()));
    }
}

if ($trang === 'dang-xuat') {
    dang_xuat();
}

/* CSRF: moi POST deu phai co token, tru cac trang auth (tu kiem tra rieng
   vi trang dang nhap co the chua co token trong phien). */
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

/* Trang bac si: nap them __nap.php de cac ham dung chung (VD: lop_muc trong
   sidebar, cac ham trong model) co mat truoc khi render khung. */
if (str_starts_with($trang, 'bac-si/')) {
    require_once __DIR__ . '/app/Controllers/BacSi/__nap.php';
}

$viewData = [];
$ham = $cauHinh['ham'];

/* Nhanh AJAX: route nao khai bao 'xu_ly' thi goi thang ham do va bat buoc
   ham tra JSON. Route khong khai bao thi chay tiep ham trang — cac controller
   benh nhan tu nhan biet AJAX qua ajax_is_request() va tu tra JSON. */
$hamXuLy = $cauHinh['xu_ly'] ?? null;
if ($laAjax && $hamXuLy !== null) {
    if (!is_callable($hamXuLy)) {
        tra_json(ket_qua(false, 'Trang này chưa hỗ trợ yêu cầu AJAX.'), 400);
    }
    $hamXuLy($conn);
    bat_buoc_ajax();
}

$viewData = $ham($conn);

/* Trang dang xuat khong co view. */
if (!isset($cauHinh['view'])) {
    exit;
}

$viewFile = __DIR__ . '/app/Views/' . $cauHinh['view'];

/* Kieu 1: view tu la mot trang HTML day du (benh nhan, auth). */
if (!isset($cauHinh['khung'])) {
    require $viewFile;
    exit;
}

/* Kieu 2: render trong khung cua vai tro. Khung nao co sidebar thi chen
   sidebar (bac si dung menu trai); khung quan tri chi co header/footer vi
   menu nam ngang tren header. */
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
