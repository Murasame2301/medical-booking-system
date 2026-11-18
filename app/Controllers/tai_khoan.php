<?php

// Trang "Thong tin tai khoan" va "Doi mat khau".

require_once __DIR__ . '/../Helpers/csrf.php';
require_once __DIR__ . '/../Helpers/ajax.php';
require_once __DIR__ . '/../Helpers/chung.php';
require_once __DIR__ . '/../Helpers/ho_so.php';
require_once __DIR__ . '/../Models/nguoi_dung.php';

// Han che do tim email/BHYT/CCCD cua nguoi khac qua thong bao "da duoc su dung":
// qua GIOI_HAN_TRUNG lan trung trong KHUNG_GIO_TRUNG giay thi tam chan doi cac truong nay.
define('TAI_KHOAN_GIOI_HAN_TRUNG', 5);
define('TAI_KHOAN_KHUNG_GIO_TRUNG', 900);

/**
 * Trang "Thông tin tài khoản": thông tin tài khoản chỉ đọc
 * và hồ sơ bệnh nhân có thể chỉnh sửa.
 */
function tai_khoan_hien_thi($conn)
{
    $manguoidung = tai_khoan_bat_dau_trang('thong-tin-tai-khoan.php');
    $taiKhoan = tai_khoan_lay_tai_khoan_dang_nhap($conn, $manguoidung);
    // [] = loi truy van tam thoi: chua xac minh duoc trang thai/vai tro, khong cho ghi.
    $taiKhoanLoi = $taiKhoan === [];

    $hoSoError = '';
    $hoSoFormValues = null;

    $flash = chung_doc_flash('tai_khoan_flash');
    $message = (string) ($flash['message'] ?? '');
    $messageType = (string) ($flash['type'] ?? '');
    $moHoSo = !empty($flash['mo_ho_so']);

    if ($taiKhoanLoi && $message === '') {
        $message = 'Không thể tải thông tin tài khoản. Vui lòng thử lại.';
        $messageType = 'error';
    }

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
        && chung_chuoi($_POST['hanh_dong'] ?? '') === 'cap_nhat_ho_so') {
        $isAjax = ajax_is_request();

        if (!csrf_validate()) {
            if ($isAjax) {
                // Tra token hien tai de nguoi dung bam luu lai ma khong mat du lieu da nhap.
                // An toan: header X-Requested-With khong gui duoc tu trang khac khi khong co
                // CORS, va trang khac cung khong doc duoc phan hoi nay.
                ajax_json([
                    'ok' => false,
                    'message' => 'Phiên làm việc đã được làm mới. Vui lòng bấm lưu lại.',
                    'csrfToken' => csrf_token(),
                ]);
            }

            tai_khoan_dat_flash('Phiên làm việc đã hết hạn. Vui lòng thử lại.', 'error', true);
            chung_chuyen_trang_sau_post('thong-tin-tai-khoan.php');
        }

        $values = tai_khoan_lay_gia_tri_post();
        $error = $taiKhoanLoi
            ? 'Không thể tải thông tin tài khoản nên chưa lưu được hồ sơ. Vui lòng thử lại.'
            : tai_khoan_xu_ly_cap_nhat_ho_so($conn, $manguoidung, $taiKhoan, $values);

        if ($isAjax) {
            if ($error !== '') {
                ajax_json([
                    'ok' => false,
                    'message' => $error,
                ]);
            }

            tai_khoan_tra_ket_qua_ho_so_ajax($conn, $manguoidung, $values);
        }

        if ($error !== '') {
            $hoSoError = $error;
            $hoSoFormValues = $values;
            $moHoSo = true;
        } else {
            // Luong khong co JS: giu nguyen POST/303 + flash.
            tai_khoan_dat_flash('Đã cập nhật hồ sơ bệnh nhân.', 'success');
            chung_chuyen_trang_sau_post('thong-tin-tai-khoan.php');
        }
    }

    try {
        $hoSo = nguoi_dung_lay_ho_so($conn, $manguoidung) ?? [];
    } catch (RuntimeException $exception) {
        $hoSo = [];
        if ($message === '') {
            $message = 'Không thể tải hồ sơ bệnh nhân. Vui lòng thử lại.';
            $messageType = 'error';
        }
    }

    if ($hoSoFormValues === null) {
        $hoSoFormValues = ho_so_gia_tri_form($taiKhoan, $hoSo);
    }

    return [
        'message' => $message,
        'messageType' => $messageType,
        'taiKhoan' => tai_khoan_bo_mat_khau($taiKhoan),
        'taiKhoanLoi' => $taiKhoanLoi,
        'hoSo' => $hoSo,
        'hoSoFormValues' => $hoSoFormValues,
        'hoSoError' => $hoSoError,
        'hoSoIncomplete' => !ho_so_da_du($hoSo),
        'moHoSo' => $moHoSo,
    ];
}

/**
 * Trang "Đổi mật khẩu".
 */
function tai_khoan_doi_mat_khau($conn)
{
    $manguoidung = tai_khoan_bat_dau_trang('doi-mat-khau.php');
    $taiKhoan = tai_khoan_lay_tai_khoan_dang_nhap($conn, $manguoidung);

    $flash = chung_doc_flash('doi_mat_khau_flash');
    $message = (string) ($flash['message'] ?? '');
    $messageType = (string) ($flash['type'] ?? '');

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        if (!csrf_validate()) {
            tai_khoan_dat_flash('Phiên làm việc đã hết hạn. Vui lòng thử lại.', 'error', false, 'doi_mat_khau_flash');
            chung_chuyen_trang_sau_post('doi-mat-khau.php');
        }

        if ($taiKhoan === []) {
            // Khong doc duoc hash hien tai: dung bao "sai mat khau" khi loi nam o CSDL.
            tai_khoan_dat_flash('Không thể tải thông tin tài khoản. Vui lòng thử lại.', 'error', false, 'doi_mat_khau_flash');
            chung_chuyen_trang_sau_post('doi-mat-khau.php');
        }

        tai_khoan_xu_ly_doi_mat_khau($conn, $manguoidung, $taiKhoan);
    }

    return [
        'message' => $message,
        'messageType' => $messageType,
        'taiKhoan' => tai_khoan_bo_mat_khau($taiKhoan),
    ];
}

function tai_khoan_bat_dau_trang($urlQuayLai)
{
    if (empty($_SESSION['is_logged_in']) || empty($_SESSION['manguoidung'])) {
        $_SESSION['after_login_url'] = $urlQuayLai;
        chung_chuyen_trang_bao_loi('dang-nhap.php', 'Phiên đăng nhập đã hết. Vui lòng đăng nhập lại.');
    }

    return chung_so_nguyen($_SESSION['manguoidung'] ?? 0);
}

function tai_khoan_lay_tai_khoan_dang_nhap($conn, $manguoidung)
{
    $taiKhoan = null;
    $loiTruyVan = false;

    try {
        $taiKhoan = nguoi_dung_lay_tai_khoan($conn, $manguoidung);
    } catch (RuntimeException $exception) {
        $loiTruyVan = true;
    }

    if ($taiKhoan === null) {
        if ($loiTruyVan) {
            // Lỗi truy vấn tạm thời: không đăng xuất; nơi gọi chặn ghi và báo lỗi.
            return [];
        }

        // Tài khoản không còn tồn tại: kết thúc phiên.
        chung_ket_thuc_phien();
    }

    if (($taiKhoan['trangthai'] ?? '') !== 'hoat_dong') {
        chung_ket_thuc_phien();
    }

    if ((int) ($taiKhoan['mavaitro'] ?? 0) !== 1) {
        // Trang này chỉ dành cho bệnh nhân.
        chung_chuyen_trang_bao_loi('xem-lich-kham.php', 'Trang này chỉ dành cho bệnh nhân.');
    }

    return $taiKhoan;
}

function tai_khoan_lay_gia_tri_post()
{
    return [
        'hoten' => chung_chuoi($_POST['hoten'] ?? ''),
        'ngaysinh' => chung_chuoi($_POST['ngaysinh'] ?? ''),
        'gioitinh' => chung_chuoi($_POST['gioitinh'] ?? ''),
        'email' => chung_chuoi($_POST['email'] ?? ''),
        'diachi' => chung_chuoi($_POST['diachi'] ?? ''),
        'bhyt' => chung_chuoi($_POST['bhyt'] ?? ''),
        'cccd' => chung_chuoi($_POST['cccd'] ?? ''),
        'dantoc' => chung_chuoi($_POST['dantoc'] ?? ''),
    ];
}

/**
 * Validate va ghi ho so. Khong redirect: tai_khoan_hien_thi() quyet dinh tra JSON hay POST/303.
 *
 * @return string Chuoi loi; rong nghia la da luu.
 */
function tai_khoan_xu_ly_cap_nhat_ho_so($conn, $manguoidung, array $taiKhoan, array $values)
{
    $error = ho_so_kiem_tra_ho_ten($values['hoten']);
    if ($error === '') {
        $error = ho_so_kiem_tra_ho_so(
            $values['ngaysinh'],
            $values['gioitinh'],
            $values['email'],
            $values['diachi'],
            $values['bhyt']
        );
    }
    if ($error === '') {
        $error = ho_so_kiem_tra_truong_mo_rong($values['cccd'], $values['dantoc']);
    }

    if ($error !== '') {
        return $error;
    }

    // Chi chan khi nguoi dung dang doi email/BHYT/CCCD: sua dia chi, ho ten... van luu duoc.
    if (tai_khoan_doi_truong_duy_nhat($taiKhoan, $values) && tai_khoan_bi_chan_do_trung()) {
        return 'Bạn đã nhập thông tin trùng với tài khoản khác quá nhiều lần. '
            . 'Vui lòng thử lại sau 15 phút.';
    }

    try {
        nguoi_dung_cap_nhat_ho_so(
            $conn,
            $manguoidung,
            $values['hoten'],
            $values['ngaysinh'],
            $values['gioitinh'],
            $values['email'],
            $values['diachi'],
            $values['bhyt'],
            $values['cccd'],
            $values['dantoc']
        );
    } catch (RuntimeException $exception) {
        if ((int) $exception->getCode() === 1062) {
            tai_khoan_ghi_nhan_trung();

            return 'Email, số BHYT hoặc số CCCD này đã được sử dụng cho tài khoản khác.';
        }

        return 'Không thể cập nhật hồ sơ. Vui lòng thử lại.';
    }

    return '';
}

/**
 * Tra ket qua luu ho so cho AJAX: the ho so render lai tu du lieu vua doc
 * trong CSDL, kem email moi de cap nhat the "Thong tin tai khoan".
 */
function tai_khoan_tra_ket_qua_ho_so_ajax($conn, $manguoidung, array $values)
{
    try {
        $hoSo = nguoi_dung_lay_ho_so($conn, $manguoidung) ?? $values;
    } catch (RuntimeException $exception) {
        $hoSo = $values;
    }

    ajax_json([
        'ok' => true,
        'message' => 'Đã cập nhật hồ sơ bệnh nhân.',
        'messageType' => 'success',
        'email' => (string) ($hoSo['email'] ?? ''),
        'cardHtml' => render_fragment(
            __DIR__ . '/../Views/account/partials/profile-card.php',
            [
                'hoSo' => $hoSo,
                'hoSoIncomplete' => !ho_so_da_du($hoSo),
                'hoSoFormValues' => ho_so_gia_tri_form([], $hoSo),
                'hoSoError' => '',
                'moHoSo' => false,
            ]
        ),
    ]);
}

function tai_khoan_xu_ly_doi_mat_khau($conn, $manguoidung, $taiKhoan)
{
    $matKhauHienTai = isset($_POST['mat_khau_hien_tai']) && is_string($_POST['mat_khau_hien_tai'])
        ? $_POST['mat_khau_hien_tai']
        : '';
    $matKhauMoi = isset($_POST['mat_khau_moi']) && is_string($_POST['mat_khau_moi'])
        ? $_POST['mat_khau_moi']
        : '';
    $nhapLai = isset($_POST['nhap_lai_mat_khau']) && is_string($_POST['nhap_lai_mat_khau'])
        ? $_POST['nhap_lai_mat_khau']
        : '';

    if ($matKhauHienTai === '' || $matKhauMoi === '' || $nhapLai === '') {
        tai_khoan_dat_flash('Vui lòng nhập đầy đủ thông tin mật khẩu.', 'error', false, 'doi_mat_khau_flash');
        chung_chuyen_trang_sau_post('doi-mat-khau.php');
    }

    if (!password_verify($matKhauHienTai, (string) ($taiKhoan['matkhau'] ?? ''))) {
        tai_khoan_dat_flash('Mật khẩu hiện tại không đúng.', 'error', false, 'doi_mat_khau_flash');
        chung_chuyen_trang_sau_post('doi-mat-khau.php');
    }

    if ($matKhauMoi === $matKhauHienTai) {
        tai_khoan_dat_flash('Mật khẩu mới phải khác mật khẩu hiện tại.', 'error', false, 'doi_mat_khau_flash');
        chung_chuyen_trang_sau_post('doi-mat-khau.php');
    }

    if ($matKhauMoi !== $nhapLai) {
        tai_khoan_dat_flash(
            'Mật khẩu nhập lại chưa khớp. Vui lòng kiểm tra lại.',
            'error',
            false,
            'doi_mat_khau_flash'
        );
        chung_chuyen_trang_sau_post('doi-mat-khau.php');
    }

    $error = ho_so_kiem_tra_mat_khau_moi($matKhauMoi);
    if ($error !== '') {
        tai_khoan_dat_flash($error, 'error', false, 'doi_mat_khau_flash');
        chung_chuyen_trang_sau_post('doi-mat-khau.php');
    }

    try {
        nguoi_dung_cap_nhat_mat_khau($conn, $manguoidung, password_hash($matKhauMoi, PASSWORD_DEFAULT));
    } catch (RuntimeException $exception) {
        tai_khoan_dat_flash('Không thể đổi mật khẩu. Vui lòng thử lại.', 'error', false, 'doi_mat_khau_flash');
        chung_chuyen_trang_sau_post('doi-mat-khau.php');
    }

    session_regenerate_id(true);
    csrf_rotate();
    tai_khoan_dat_flash('Đã đổi mật khẩu thành công.', 'success', false, 'doi_mat_khau_flash');
    chung_chuyen_trang_sau_post('doi-mat-khau.php');
}

/**
 * Nguoi dung co dang doi mot trong cac truong UNIQUE (email, BHYT, CCCD) khong.
 */
function tai_khoan_doi_truong_duy_nhat(array $taiKhoan, array $values)
{
    $email = strtolower(trim((string) ($taiKhoan['email'] ?? '')));

    return strtolower($values['email']) !== $email
        || $values['bhyt'] !== trim((string) ($taiKhoan['bhyt'] ?? ''))
        || $values['cccd'] !== trim((string) ($taiKhoan['cccd'] ?? ''));
}

function tai_khoan_bi_chan_do_trung()
{
    $dem = $_SESSION['ho_so_trung'] ?? null;
    if (!is_array($dem)) {
        return false;
    }

    if (time() - (int) ($dem['tu'] ?? 0) > TAI_KHOAN_KHUNG_GIO_TRUNG) {
        unset($_SESSION['ho_so_trung']);

        return false;
    }

    return (int) ($dem['lan'] ?? 0) >= TAI_KHOAN_GIOI_HAN_TRUNG;
}

function tai_khoan_ghi_nhan_trung()
{
    $dem = $_SESSION['ho_so_trung'] ?? null;
    if (!is_array($dem) || time() - (int) ($dem['tu'] ?? 0) > TAI_KHOAN_KHUNG_GIO_TRUNG) {
        $dem = ['lan' => 0, 'tu' => time()];
    }

    $dem['lan'] = (int) ($dem['lan'] ?? 0) + 1;
    $_SESSION['ho_so_trung'] = $dem;
}

/**
 * View khong can hash mat khau.
 */
function tai_khoan_bo_mat_khau($taiKhoan)
{
    if (is_array($taiKhoan)) {
        unset($taiKhoan['matkhau']);
    }

    return $taiKhoan;
}

function tai_khoan_dat_flash($message, $type, $moHoSo = false, $key = 'tai_khoan_flash')
{
    chung_dat_flash($key, $message, $type, ['mo_ho_so' => $moHoSo]);
}
