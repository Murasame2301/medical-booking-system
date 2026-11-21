<?php

// Trang Thông tin tài khoản và đổi mật khẩu

require_once __DIR__ . '/../../Helpers/csrf.php';
require_once __DIR__ . '/../../Helpers/ajax.php';
require_once __DIR__ . '/../../Helpers/utils.php';
require_once __DIR__ . '/../../Helpers/session.php';
require_once __DIR__ . '/../../Helpers/ho_so.php';
require_once __DIR__ . '/../../Models/nguoi_dung.php';

const TAI_KHOAN_GIOI_HAN_TRUNG = 5;
const TAI_KHOAN_KHUNG_GIO_TRUNG = 900;

function benh_nhan_thong_tin_tai_khoan($conn)
{
    $manguoidung = benh_nhan_bat_dau_trang('index.php?page=thong-tin-tai-khoan');
    $taiKhoan = benh_nhan_lay_tai_khoan_dang_nhap($conn, $manguoidung);
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
        && chung_chuoi($_POST['hanh_dong'] ?? '') === 'cap_nhat_ho_so'
    ) {
        $isAjax = ajax_is_request();

        if (!csrf_validate()) {
            if ($isAjax) {
                // Trả token hiện tại để người dùng bấm lưu lại mà không mất dữ liệu đã nhập
                ajax_json([
                    'ok' => false,
                    'message' => 'Phiên làm việc đã được làm mới. Vui lòng bấm lưu lại.',
                    'csrfToken' => csrf_token(),
                ]);
            }

            benh_nhan_dat_flash('Phiên làm việc đã hết hạn. Vui lòng thử lại.', 'error', true);
            chuyen_trang_sau_post('index.php?page=thong-tin-tai-khoan');
        }

        $values = benh_nhan_lay_gia_tri_post();
        $error = $taiKhoanLoi
            ? 'Không thể tải thông tin tài khoản nên chưa lưu được hồ sơ. Vui lòng thử lại.'
            : benh_nhan_xu_ly_cap_nhat_ho_so($conn, $manguoidung, $taiKhoan, $values);

        if ($isAjax) {
            if ($error !== '') {
                ajax_json([
                    'ok' => false,
                    'message' => $error,
                ]);
            }

            benh_nhan_tra_ket_qua_ho_so_ajax($conn, $manguoidung, $values);
        }

        if ($error !== '') {
            $hoSoError = $error;
            $hoSoFormValues = $values;
            $moHoSo = true;
        } else {
            benh_nhan_dat_flash('Đã cập nhật hồ sơ bệnh nhân.', 'success');
            chuyen_trang_sau_post('index.php?page=thong-tin-tai-khoan');
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
        'taiKhoan' => benh_nhan_bo_mat_khau($taiKhoan),
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
function benh_nhan_doi_mat_khau($conn)
{
    $manguoidung = benh_nhan_bat_dau_trang('index.php?page=doi-mat-khau');
    $taiKhoan = benh_nhan_lay_tai_khoan_dang_nhap($conn, $manguoidung);

    $flash = chung_doc_flash('doi_mat_khau_flash');
    $message = (string) ($flash['message'] ?? '');
    $messageType = (string) ($flash['type'] ?? '');

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        if (!csrf_validate()) {
            benh_nhan_dat_flash('Phiên làm việc đã hết hạn. Vui lòng thử lại.', 'error', false, 'doi_mat_khau_flash');
            chuyen_trang_sau_post('index.php?page=doi-mat-khau');
        }

        if ($taiKhoan === []) {
            // Khong doc duoc hash hien tai: dung bao "sai mat khau" khi loi nam o CSDL.
            benh_nhan_dat_flash('Không thể tải thông tin tài khoản. Vui lòng thử lại.', 'error', false, 'doi_mat_khau_flash');
            chuyen_trang_sau_post('index.php?page=doi-mat-khau');
        }

        benh_nhan_xu_ly_doi_mat_khau($conn, $manguoidung, $taiKhoan);
    }

    return [
        'message' => $message,
        'messageType' => $messageType,
        'taiKhoan' => benh_nhan_bo_mat_khau($taiKhoan),
    ];
}

function benh_nhan_bat_dau_trang($urlQuayLai)
{
    if (!la_vai_tro(VT_BENH_NHAN)) {
        $_SESSION['after_login_url'] = $urlQuayLai;
        tra_loi_chua_dang_nhap('Phiên đăng nhập đã hết. Vui lòng đăng nhập lại.');
    }

    return ma_nguoi_dung_hien_tai();
}

function benh_nhan_lay_tai_khoan_dang_nhap($conn, $manguoidung)
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
        dang_xuat();
    }

    if (($taiKhoan['trangthai'] ?? '') !== TK_HOAT_DONG) {
        dang_xuat();
    }

    return $taiKhoan;
}

function benh_nhan_lay_gia_tri_post()
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
 * Validate va ghi ho so. Khong redirect: noi goi quyet dinh tra JSON hay POST/303.
 *
 * @return string Chuoi loi; rong nghia la da luu.
 */
function benh_nhan_xu_ly_cap_nhat_ho_so($conn, $manguoidung, array $taiKhoan, array $values)
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
    if (benh_nhan_doi_truong_duy_nhat($taiKhoan, $values) && benh_nhan_bi_chan_do_trung()) {
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
            benh_nhan_ghi_nhan_trung();

            return 'Email, số BHYT hoặc số CCCD này đã được sử dụng cho tài khoản khác.';
        }

        return 'Không thể cập nhật hồ sơ. Vui lòng thử lại.';
    }

    $_SESSION[SS_HO_TEN] = $values['hoten'];

    return '';
}

/**
 * Tra ket qua luu ho so cho AJAX: the ho so render lai tu du lieu vua doc
 * trong CSDL, kem email moi de cap nhat the "Thong tin tai khoan".
 */
function benh_nhan_tra_ket_qua_ho_so_ajax($conn, $manguoidung, array $values)
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
            __DIR__ . '/../../Views/benh-nhan/partials/profile-card.php',
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

function benh_nhan_xu_ly_doi_mat_khau($conn, $manguoidung, $taiKhoan)
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
        benh_nhan_dat_flash('Vui lòng nhập đầy đủ thông tin mật khẩu.', 'error', false, 'doi_mat_khau_flash');
        chuyen_trang_sau_post('index.php?page=doi-mat-khau');
    }

    if (!password_verify($matKhauHienTai, (string) ($taiKhoan['matkhau'] ?? ''))) {
        benh_nhan_dat_flash('Mật khẩu hiện tại không đúng.', 'error', false, 'doi_mat_khau_flash');
        chuyen_trang_sau_post('index.php?page=doi-mat-khau');
    }

    if ($matKhauMoi === $matKhauHienTai) {
        benh_nhan_dat_flash('Mật khẩu mới phải khác mật khẩu hiện tại.', 'error', false, 'doi_mat_khau_flash');
        chuyen_trang_sau_post('index.php?page=doi-mat-khau');
    }

    if ($matKhauMoi !== $nhapLai) {
        benh_nhan_dat_flash(
            'Mật khẩu nhập lại chưa khớp. Vui lòng kiểm tra lại.',
            'error',
            false,
            'doi_mat_khau_flash'
        );
        chuyen_trang_sau_post('index.php?page=doi-mat-khau');
    }

    $error = ho_so_kiem_tra_mat_khau_moi($matKhauMoi);
    if ($error !== '') {
        benh_nhan_dat_flash($error, 'error', false, 'doi_mat_khau_flash');
        chuyen_trang_sau_post('index.php?page=doi-mat-khau');
    }

    try {
        nguoi_dung_cap_nhat_mat_khau($conn, $manguoidung, password_hash($matKhauMoi, PASSWORD_DEFAULT));
    } catch (RuntimeException $exception) {
        benh_nhan_dat_flash('Không thể đổi mật khẩu. Vui lòng thử lại.', 'error', false, 'doi_mat_khau_flash');
        chuyen_trang_sau_post('index.php?page=doi-mat-khau');
    }

    session_regenerate_id(true);
    csrf_rotate();
    benh_nhan_dat_flash('Đã đổi mật khẩu thành công.', 'success', false, 'doi_mat_khau_flash');
    chuyen_trang_sau_post('index.php?page=doi-mat-khau');
}

/**
 * Nguoi dung co dang doi mot trong cac truong UNIQUE (email, BHYT, CCCD) khong.
 */
function benh_nhan_doi_truong_duy_nhat(array $taiKhoan, array $values)
{
    $email = strtolower(trim((string) ($taiKhoan['email'] ?? '')));

    return strtolower($values['email']) !== $email
        || $values['bhyt'] !== trim((string) ($taiKhoan['bhyt'] ?? ''))
        || $values['cccd'] !== trim((string) ($taiKhoan['cccd'] ?? ''));
}

function benh_nhan_bi_chan_do_trung()
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

function benh_nhan_ghi_nhan_trung()
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
function benh_nhan_bo_mat_khau($taiKhoan)
{
    if (is_array($taiKhoan)) {
        unset($taiKhoan['matkhau']);
    }

    return $taiKhoan;
}

function benh_nhan_dat_flash($message, $type, $moHoSo = false, $key = 'tai_khoan_flash')
{
    chung_dat_flash($key, $message, $type, ['mo_ho_so' => $moHoSo]);
}
