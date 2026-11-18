<?php

require_once __DIR__ . '/../Helpers/csrf.php';
require_once __DIR__ . '/../Helpers/HoSoValidator.php';
require_once __DIR__ . '/../Helpers/ajax.php';

class TaiKhoanController
{
    // Han che do tim email/BHYT/CCCD cua nguoi khac qua thong bao "da duoc su dung":
    // qua GIOI_HAN_TRUNG lan trung trong KHUNG_GIO_TRUNG giay thi tam chan doi cac truong nay.
    private const GIOI_HAN_TRUNG = 5;
    private const KHUNG_GIO_TRUNG = 900;

    private $nguoiDung;

    public function __construct($nguoiDung)
    {
        $this->nguoiDung = $nguoiDung;
    }

    /**
     * Trang "Thông tin tài khoản": thông tin tài khoản chỉ đọc
     * và hồ sơ bệnh nhân có thể chỉnh sửa.
     */
    public function hienThi()
    {
        $manguoidung = $this->batDauTrang('thong-tin-tai-khoan.php');
        $taiKhoan = $this->layTaiKhoanDangNhap($manguoidung);
        // [] = loi truy van tam thoi: chua xac minh duoc trang thai/vai tro, khong cho ghi.
        $taiKhoanLoi = $taiKhoan === [];

        $hoSoError = '';
        $hoSoFormValues = null;

        $flash = $this->docFlash('tai_khoan_flash');
        $message = (string) ($flash['message'] ?? '');
        $messageType = (string) ($flash['type'] ?? '');
        $moHoSo = !empty($flash['mo_ho_so']);

        if ($taiKhoanLoi && $message === '') {
            $message = 'Không thể tải thông tin tài khoản. Vui lòng thử lại.';
            $messageType = 'error';
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
            && $this->getString($_POST['hanh_dong'] ?? '') === 'cap_nhat_ho_so') {
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

                $this->flash('tai_khoan_flash', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.', 'error', true);
                $this->redirect('thong-tin-tai-khoan.php');
            }

            $values = $this->layGiaTriPost();
            $error = $taiKhoanLoi
                ? 'Không thể tải thông tin tài khoản nên chưa lưu được hồ sơ. Vui lòng thử lại.'
                : $this->xuLyCapNhatHoSo($manguoidung, $taiKhoan, $values);

            if ($isAjax) {
                if ($error !== '') {
                    ajax_json([
                        'ok' => false,
                        'message' => $error,
                    ]);
                }

                $this->traKetQuaHoSoAjax($manguoidung, $values);
            }

            if ($error !== '') {
                $hoSoError = $error;
                $hoSoFormValues = $values;
                $moHoSo = true;
            } else {
                // Luong khong co JS: giu nguyen POST/303 + flash.
                $this->flash('tai_khoan_flash', 'Đã cập nhật hồ sơ bệnh nhân.', 'success');
                $this->redirect('thong-tin-tai-khoan.php');
            }
        }

        try {
            $hoSo = $this->nguoiDung->layHoSo($manguoidung) ?? [];
        } catch (RuntimeException $exception) {
            $hoSo = [];
            if ($message === '') {
                $message = 'Không thể tải hồ sơ bệnh nhân. Vui lòng thử lại.';
                $messageType = 'error';
            }
        }

        if ($hoSoFormValues === null) {
            $hoSoFormValues = $this->dungGiaTriHoSo($taiKhoan, $hoSo);
        }

        return [
            'message' => $message,
            'messageType' => $messageType,
            'taiKhoan' => $this->boMatKhau($taiKhoan),
            'taiKhoanLoi' => $taiKhoanLoi,
            'hoSo' => $hoSo,
            'hoSoFormValues' => $hoSoFormValues,
            'hoSoError' => $hoSoError,
            'hoSoIncomplete' => !$this->duHoSo($hoSo),
            'moHoSo' => $moHoSo,
        ];
    }

    /**
     * Trang "Đổi mật khẩu".
     */
    public function doiMatKhau()
    {
        $manguoidung = $this->batDauTrang('doi-mat-khau.php');
        $taiKhoan = $this->layTaiKhoanDangNhap($manguoidung);

        $flash = $this->docFlash('doi_mat_khau_flash');
        $message = (string) ($flash['message'] ?? '');
        $messageType = (string) ($flash['type'] ?? '');

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            if (!csrf_validate()) {
                $this->flash('doi_mat_khau_flash', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.', 'error');
                $this->redirect('doi-mat-khau.php');
            }

            if ($taiKhoan === []) {
                // Khong doc duoc hash hien tai: dung bao "sai mat khau" khi loi nam o CSDL.
                $this->flash('doi_mat_khau_flash', 'Không thể tải thông tin tài khoản. Vui lòng thử lại.', 'error');
                $this->redirect('doi-mat-khau.php');
            }

            $this->xuLyDoiMatKhau($manguoidung, $taiKhoan);
        }

        return [
            'message' => $message,
            'messageType' => $messageType,
            'taiKhoan' => $this->boMatKhau($taiKhoan),
        ];
    }

    private function batDauTrang($urlQuayLai)
    {
        if (empty($_SESSION['is_logged_in']) || empty($_SESSION['manguoidung'])) {
            $_SESSION['after_login_url'] = $urlQuayLai;
            $this->chuyenTrang('dang-nhap.php', 'Phiên đăng nhập đã hết. Vui lòng đăng nhập lại.');
        }

        return $this->getInt($_SESSION['manguoidung'] ?? 0);
    }

    private function layTaiKhoanDangNhap($manguoidung)
    {
        $taiKhoan = null;
        $loiTruyVan = false;

        try {
            $taiKhoan = $this->nguoiDung->layTaiKhoan($manguoidung);
        } catch (RuntimeException $exception) {
            $loiTruyVan = true;
        }

        if ($taiKhoan === null) {
            if ($loiTruyVan) {
                // Lỗi truy vấn tạm thời: không đăng xuất; nơi gọi chặn ghi và báo lỗi.
                return [];
            }

            // Tài khoản không còn tồn tại: kết thúc phiên.
            $this->ketThucPhien();
        }

        if (($taiKhoan['trangthai'] ?? '') !== 'hoat_dong') {
            $this->ketThucPhien();
        }

        if ((int) ($taiKhoan['mavaitro'] ?? 0) !== 1) {
            // Trang này chỉ dành cho bệnh nhân.
            $this->chuyenTrang('xem-lich-kham.php', 'Trang này chỉ dành cho bệnh nhân.');
        }

        return $taiKhoan;
    }

    private function layGiaTriPost()
    {
        return [
            'hoten' => $this->getString($_POST['hoten'] ?? ''),
            'ngaysinh' => $this->getString($_POST['ngaysinh'] ?? ''),
            'gioitinh' => $this->getString($_POST['gioitinh'] ?? ''),
            'email' => $this->getString($_POST['email'] ?? ''),
            'diachi' => $this->getString($_POST['diachi'] ?? ''),
            'bhyt' => $this->getString($_POST['bhyt'] ?? ''),
            'cccd' => $this->getString($_POST['cccd'] ?? ''),
            'dantoc' => $this->getString($_POST['dantoc'] ?? ''),
        ];
    }

    /**
     * Validate va ghi ho so. Khong redirect: hienThi() quyet dinh tra JSON hay POST/303.
     *
     * @return string Chuoi loi; rong nghia la da luu.
     */
    private function xuLyCapNhatHoSo($manguoidung, array $taiKhoan, array $values)
    {
        $error = HoSoValidator::validateHoTen($values['hoten']);
        if ($error === '') {
            $error = HoSoValidator::validateHoSo(
                $values['ngaysinh'],
                $values['gioitinh'],
                $values['email'],
                $values['diachi'],
                $values['bhyt']
            );
        }
        if ($error === '') {
            $error = HoSoValidator::validateTruongMoRong($values['cccd'], $values['dantoc']);
        }

        if ($error !== '') {
            return $error;
        }

        // Chi chan khi nguoi dung dang doi email/BHYT/CCCD: sua dia chi, ho ten... van luu duoc.
        if ($this->doiTruongDuyNhat($taiKhoan, $values) && $this->biChanDoTrung()) {
            return 'Bạn đã nhập thông tin trùng với tài khoản khác quá nhiều lần. '
                . 'Vui lòng thử lại sau 15 phút.';
        }

        try {
            $this->nguoiDung->capNhatHoSo(
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
                $this->ghiNhanTrung();

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
    private function traKetQuaHoSoAjax($manguoidung, array $values)
    {
        try {
            $hoSo = $this->nguoiDung->layHoSo($manguoidung) ?? $values;
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
                    'hoSoIncomplete' => !$this->duHoSo($hoSo),
                    'hoSoFormValues' => $this->dungGiaTriHoSo([], $hoSo),
                    'hoSoError' => '',
                    'moHoSo' => false,
                ]
            ),
        ]);
    }

    private function xuLyDoiMatKhau($manguoidung, $taiKhoan)
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
            $this->flash('doi_mat_khau_flash', 'Vui lòng nhập đầy đủ thông tin mật khẩu.', 'error');
            $this->redirect('doi-mat-khau.php');
        }

        if (!password_verify($matKhauHienTai, (string) ($taiKhoan['matkhau'] ?? ''))) {
            $this->flash('doi_mat_khau_flash', 'Mật khẩu hiện tại không đúng.', 'error');
            $this->redirect('doi-mat-khau.php');
        }

        if ($matKhauMoi === $matKhauHienTai) {
            $this->flash('doi_mat_khau_flash', 'Mật khẩu mới phải khác mật khẩu hiện tại.', 'error');
            $this->redirect('doi-mat-khau.php');
        }

        if ($matKhauMoi !== $nhapLai) {
            $this->flash(
                'doi_mat_khau_flash',
                'Mật khẩu nhập lại chưa khớp. Vui lòng kiểm tra lại.',
                'error'
            );
            $this->redirect('doi-mat-khau.php');
        }

        $error = HoSoValidator::validateMatKhauMoi($matKhauMoi);
        if ($error !== '') {
            $this->flash('doi_mat_khau_flash', $error, 'error');
            $this->redirect('doi-mat-khau.php');
        }

        try {
            $this->nguoiDung->capNhatMatKhau($manguoidung, password_hash($matKhauMoi, PASSWORD_DEFAULT));
        } catch (RuntimeException $exception) {
            $this->flash('doi_mat_khau_flash', 'Không thể đổi mật khẩu. Vui lòng thử lại.', 'error');
            $this->redirect('doi-mat-khau.php');
        }

        session_regenerate_id(true);
        csrf_rotate();
        $this->flash(
            'doi_mat_khau_flash',
            'Đã đổi mật khẩu thành công.',
            'success'
        );
        $this->redirect('doi-mat-khau.php');
    }

    /**
     * Gia tri dien vao form ho so. Uu tien du lieu ho so vua doc tu CSDL (layHoSo da tra du
     * 8 truong); $taiKhoan chi bu cho truong nao thieu.
     */
    private function dungGiaTriHoSo($taiKhoan, $hoSo)
    {
        $nguon = (is_array($hoSo) ? $hoSo : []) + (is_array($taiKhoan) ? $taiKhoan : []);

        return [
            'hoten' => (string) ($nguon['hoten'] ?? ''),
            'ngaysinh' => (string) ($nguon['ngaysinh'] ?? ''),
            'gioitinh' => (string) ($nguon['gioitinh'] ?? ''),
            'email' => (string) ($nguon['email'] ?? ''),
            'diachi' => (string) ($nguon['diachi'] ?? ''),
            'bhyt' => (string) ($nguon['bhyt'] ?? ''),
            'cccd' => (string) ($nguon['cccd'] ?? ''),
            'dantoc' => (string) ($nguon['dantoc'] ?? ''),
        ];
    }

    private function duHoSo($hoSo)
    {
        if (!is_array($hoSo)) {
            return false;
        }

        return !empty($hoSo['ngaysinh'])
            && !empty($hoSo['gioitinh'])
            && trim((string) ($hoSo['diachi'] ?? '')) !== ''
            && trim((string) ($hoSo['bhyt'] ?? '')) !== '';
    }

    /**
     * Nguoi dung co dang doi mot trong cac truong UNIQUE (email, BHYT, CCCD) khong.
     */
    private function doiTruongDuyNhat(array $taiKhoan, array $values)
    {
        $email = strtolower(trim((string) ($taiKhoan['email'] ?? '')));

        return strtolower($values['email']) !== $email
            || $values['bhyt'] !== trim((string) ($taiKhoan['bhyt'] ?? ''))
            || $values['cccd'] !== trim((string) ($taiKhoan['cccd'] ?? ''));
    }

    private function biChanDoTrung()
    {
        $dem = $_SESSION['ho_so_trung'] ?? null;
        if (!is_array($dem)) {
            return false;
        }

        if (time() - (int) ($dem['tu'] ?? 0) > self::KHUNG_GIO_TRUNG) {
            unset($_SESSION['ho_so_trung']);

            return false;
        }

        return (int) ($dem['lan'] ?? 0) >= self::GIOI_HAN_TRUNG;
    }

    private function ghiNhanTrung()
    {
        $dem = $_SESSION['ho_so_trung'] ?? null;
        if (!is_array($dem) || time() - (int) ($dem['tu'] ?? 0) > self::KHUNG_GIO_TRUNG) {
            $dem = ['lan' => 0, 'tu' => time()];
        }

        $dem['lan'] = (int) ($dem['lan'] ?? 0) + 1;
        $_SESSION['ho_so_trung'] = $dem;
    }

    /**
     * View khong can hash mat khau.
     */
    private function boMatKhau($taiKhoan)
    {
        if (is_array($taiKhoan)) {
            unset($taiKhoan['matkhau']);
        }

        return $taiKhoan;
    }

    private function flash($key, $message, $type, $moHoSo = false)
    {
        $_SESSION[$key] = [
            'message' => $message,
            'type' => $type,
            'mo_ho_so' => $moHoSo,
        ];
    }

    private function docFlash($key)
    {
        $flash = $_SESSION[$key] ?? [];
        unset($_SESSION[$key]);

        return is_array($flash) ? $flash : [];
    }

    private function redirect($url)
    {
        // 303: POST thành GET để F5 không gửi lại yêu cầu.
        header('Location: ' . $url, true, 303);
        exit;
    }

    /**
     * Chuyen trang khi chua dang nhap / het phien / sai vai tro. Request AJAX nhan JSON
     * "redirect" thay vi 302, vi fetch se di theo 302 va nhan ve ca trang HTML.
     */
    private function chuyenTrang($url, $message)
    {
        if (ajax_is_request()) {
            ajax_json([
                'ok' => false,
                'redirect' => $url,
                'message' => $message,
            ]);
        }

        header('Location: ' . $url);
        exit;
    }

    private function ketThucPhien()
    {
        $_SESSION = [];
        session_destroy();
        $this->chuyenTrang('dang-nhap.php', 'Phiên đăng nhập đã kết thúc. Vui lòng đăng nhập lại.');
    }

    private function getString($value)
    {
        return is_string($value) ? trim($value) : '';
    }

    private function getInt($value)
    {
        if (is_int($value) && $value > 0) {
            return $value;
        }

        if (is_string($value) && ctype_digit($value) && (int) $value > 0) {
            return (int) $value;
        }

        return 0;
    }
}
