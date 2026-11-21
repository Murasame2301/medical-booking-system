<?php

// Khu benh nhan: tim khung gio, chi tiet khung gio, dat lich, lich su dat kham.
// Moi ham tra ve mang $viewData cho view tuong ung.

require_once __DIR__ . '/../../Helpers/csrf.php';
require_once __DIR__ . '/../../Helpers/ajax.php';
require_once __DIR__ . '/../../Helpers/session.php';
require_once __DIR__ . '/../../Helpers/ho_so.php';
require_once __DIR__ . '/../../Models/lich_kham.php';

function benh_nhan_partial($name)
{
    return __DIR__ . '/../../Views/benh-nhan/partials/' . $name . '.php';
}

function benh_nhan_url($page, array $thamSo = [])
{
    $url = 'index.php?page=' . $page;
    if ($thamSo !== []) {
        $url .= '&' . http_build_query($thamSo);
    }

    return $url;
}

/**
 * Trang tim lich kham (page=dat-kham).
 */
function benh_nhan_dat_kham($conn)
{
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $ngay = chung_chuoi($_GET['ngay'] ?? '') ?: $tomorrow;
    $machuyenkhoa = chung_so_duong($_GET['machuyenkhoa'] ?? null);
    $mabacsi = chung_so_duong($_GET['mabacsi'] ?? null);
    $message = '';
    $messageType = '';

    $dateError = benh_nhan_kiem_tra_ngay($ngay);
    $specialties = [];
    $doctors = [];
    $slots = [];

    try {
        $specialties = lich_kham_lay_danh_sach_chuyen_khoa($conn);
        $doctors = lich_kham_lay_danh_sach_bac_si($conn, $machuyenkhoa);

        if ($dateError === '') {
            $slots = lich_kham_tim_khung_gio($conn, $ngay, $machuyenkhoa, $mabacsi);
        }
    } catch (RuntimeException $exception) {
        $message = 'Không thể tải lịch khám lúc này. Vui lòng thử lại.';
        $messageType = 'error';
    }

    if (ajax_is_request()) {
        $query = ['ngay' => $ngay];
        if ($machuyenkhoa !== null) {
            $query['machuyenkhoa'] = $machuyenkhoa;
        }
        if ($mabacsi !== null) {
            $query['mabacsi'] = $mabacsi;
        }

        ajax_json([
            'ok' => $dateError === '' && $messageType !== 'error',
            'ngay' => $ngay,
            'machuyenkhoa' => $machuyenkhoa,
            'mabacsi' => $mabacsi,
            'alertHtml' => render_fragment(benh_nhan_partial('search-alert'), [
                'dateError' => $dateError,
                'message' => $message,
                'messageType' => $messageType,
            ]),
            'resultsHtml' => render_fragment(benh_nhan_partial('slot-results'), [
                'ngay' => $ngay,
                'slots' => $slots,
            ]),
            'doctorOptionsHtml' => render_fragment(benh_nhan_partial('doctor-options'), [
                'doctors' => $doctors,
                'mabacsi' => $mabacsi,
            ]),
            'url' => benh_nhan_url('dat-kham', $query),
        ]);
    }

    return [
        'message' => $message,
        'messageType' => $messageType,
        'dateError' => $dateError,
        'ngay' => $ngay,
        'machuyenkhoa' => $machuyenkhoa,
        'mabacsi' => $mabacsi,
        'specialties' => $specialties,
        'doctors' => $doctors,
        'slots' => $slots,
        'isLoggedIn' => da_dang_nhap(),
    ];
}

/**
 * Trang lich su dat kham (page=lich-su-dat-kham), kem xu ly huy lich.
 */
function benh_nhan_lich_su_dat_kham($conn)
{
    if (!la_vai_tro(VT_BENH_NHAN)) {
        $_SESSION['after_login_url'] = 'index.php?page=lich-su-dat-kham';

        if (ajax_is_request()) {
            ajax_json([
                'ok' => false,
                'redirect' => 'index.php?page=dang-nhap',
                'message' => 'Phiên đăng nhập đã hết. Vui lòng đăng nhập lại.',
            ]);
        }

        chuyen_trang('index.php?page=dang-nhap');
    }

    $manguoidung = ma_nguoi_dung_hien_tai();
    $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($requestMethod === 'POST'
        && chung_chuoi($_POST['hanh_dong'] ?? '') === 'huy_lich') {
        $isAjax = ajax_is_request();

        if (!csrf_validate()) {
            if ($isAjax) {
                ajax_json([
                    'ok' => false,
                    'reload' => true,
                    'message' => 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.',
                ]);
            }

            benh_nhan_dat_flash_lich_su('Phiên làm việc đã hết hạn. Vui lòng thử lại.', 'error');
            benh_nhan_chuyen_trang_lich_su();
        }

        $malichkham = chung_so_duong($_POST['malichkham'] ?? null);
        $lydoHuy = chung_chuoi($_POST['lydo_huy'] ?? '');
        $loi = '';

        if ($malichkham === null) {
            $loi = 'Lịch khám không hợp lệ.';
        } elseif ($lydoHuy === '') {
            $loi = 'Vui lòng nhập lý do huỷ.';
        } elseif (chung_do_dai($lydoHuy) > 255) {
            $loi = 'Lý do huỷ không được dài quá 255 ký tự.';
        }

        if ($loi !== '') {
            if ($isAjax) {
                ajax_json([
                    'ok' => false,
                    'message' => $loi,
                    'malichkham' => $malichkham,
                    'lydo' => $lydoHuy,
                ]);
            }

            benh_nhan_dat_flash_lich_su($loi, 'error', $lydoHuy, $malichkham);
            benh_nhan_chuyen_trang_lich_su();
        }

        $ketQua = false;
        $thongDiep = '';
        $loaiThongDiep = 'error';

        try {
            $ketQua = lich_kham_huy_lich($conn, $malichkham, $manguoidung, $lydoHuy);
            if ($ketQua) {
                $thongDiep = 'Đã huỷ lịch khám.';
                $loaiThongDiep = 'success';
            } else {
                $thongDiep = 'Không thể huỷ lịch này. Lịch có thể đã qua giờ khám hoặc đã được huỷ trước đó.';
            }
        } catch (RuntimeException $exception) {
            $thongDiep = 'Không thể huỷ lịch lúc này. Vui lòng thử lại.';
        }

        if ($isAjax) {
            $cardHtml = '';
            if ($ketQua) {
                $cardHtml = benh_nhan_render_the_lich_su($conn, $malichkham, $manguoidung);
            }

            ajax_json([
                'ok' => $ketQua,
                'message' => $thongDiep,
                'messageType' => $loaiThongDiep,
                'malichkham' => $malichkham,
                'cardHtml' => $cardHtml,
            ]);
        }

        benh_nhan_dat_flash_lich_su($thongDiep, $loaiThongDiep);
        benh_nhan_chuyen_trang_lich_su();
    }

    $flash = chung_doc_flash('lich_kham_flash');

    $message = (string) ($flash['message'] ?? '');
    $messageType = (string) ($flash['type'] ?? '');
    $lichSu = [];
    $now = time();

    try {
        $lichSu = lich_kham_lay_lich_su_dat_kham($conn, $manguoidung);
    } catch (RuntimeException $exception) {
        $message = $message !== '' ? $message : 'Không thể tải lịch sử đặt khám. Vui lòng thử lại.';
        $messageType = $messageType !== '' ? $messageType : 'error';
    }

    // Cung cong thuc voi dieu kien trong lich_kham_huy_lich().
    foreach ($lichSu as $key => $row) {
        $lichSu[$key]['coTheHuy'] = in_array($row['trangthai'], [LK_CHO_XAC_NHAN, LK_DA_XAC_NHAN], true)
            && strtotime($row['thoigianbatdau']) >= $now;
    }

    return [
        'message' => $message,
        'messageType' => $messageType,
        'lichSu' => $lichSu,
        'lydoHuy' => (string) ($flash['lydo'] ?? ''),
        'malichkhamLoi' => isset($flash['malichkham']) ? (int) $flash['malichkham'] : null,
        'isLoggedIn' => da_dang_nhap(),
    ];
}

function benh_nhan_dat_flash_lich_su($message, $type, $lydo = '', $malichkham = null)
{
    chung_dat_flash('lich_kham_flash', $message, $type, [
        'lydo' => $lydo,
        'malichkham' => $malichkham,
    ]);
}

function benh_nhan_chuyen_trang_lich_su()
{
    // 303: doi POST thanh GET de F5 khong gui lai yeu cau huy.
    chuyen_trang_sau_post('index.php?page=lich-su-dat-kham');
}

/**
 * Doc lai dung the lich vua huy de JS thay outerHTML. Tra '' neu khong tim thay.
 */
function benh_nhan_render_the_lich_su($conn, $malichkham, $manguoidung)
{
    try {
        $lichSu = lich_kham_lay_lich_su_dat_kham($conn, $manguoidung);
    } catch (RuntimeException $exception) {
        return '';
    }

    $now = time();
    foreach ($lichSu as $row) {
        if ((int) $row['malichkham'] !== (int) $malichkham) {
            continue;
        }

        $row['coTheHuy'] = in_array($row['trangthai'], [LK_CHO_XAC_NHAN, LK_DA_XAC_NHAN], true)
            && strtotime($row['thoigianbatdau']) >= $now;

        return render_fragment(benh_nhan_partial('history-card'), [
            'item' => $row,
            'isReopen' => false,
            'lydoHuy' => '',
        ]);
    }

    return '';
}

/**
 * Trang chi tiet khung gio (page=chi-tiet-lich-kham), kem xu ly dat lich.
 */
function benh_nhan_chi_tiet_lich_kham($conn)
{
    $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $isAjax = $requestMethod === 'POST' && ajax_is_request();
    $rawSlotId = $requestMethod === 'POST'
        ? ($_POST['malichlamviec'] ?? null)
        : ($_GET['malichlamviec'] ?? null);
    $malichlamviec = chung_so_duong($rawSlotId);
    $message = '';
    $messageType = '';

    $csrfFlash = $_SESSION['csrf_flash'] ?? '';
    unset($_SESSION['csrf_flash']);
    if (is_string($csrfFlash) && $csrfFlash !== '') {
        $message = $csrfFlash;
        $messageType = 'error';
    }

    $lydo = $_SERVER['REQUEST_METHOD'] === 'POST'
        ? chung_chuoi($_POST['lydo'] ?? '')
        : '';
    $slot = null;
    $patient = null;
    $hasPatientProfile = false;
    $bookingCompleted = false;

    if ($malichlamviec === null) {
        $message = 'Khung giờ khám không hợp lệ.';
        $messageType = 'error';
    } else {
        try {
            $slot = lich_kham_lay_chi_tiet_khung_gio($conn, $malichlamviec);
            if ($slot === null) {
                $message = 'Khung giờ này không còn trống hoặc đã qua thời gian khám.';
                $messageType = 'error';
            }
        } catch (RuntimeException $exception) {
            $message = 'Không thể tải thông tin khung giờ. Vui lòng thử lại.';
            $messageType = 'error';
        }
    }

    if ($slot !== null && !da_dang_nhap()) {
        $_SESSION['after_login_url'] = benh_nhan_url_chi_tiet($malichlamviec);
    }

    if ($requestMethod === 'POST'
        && chung_chuoi($_POST['hanh_dong'] ?? '') === 'dat_lich') {
        if (!csrf_validate()) {
            if ($isAjax) {
                ajax_json([
                    'ok' => false,
                    'reload' => true,
                    'message' => 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.',
                ]);
            }

            $_SESSION['csrf_flash'] = 'Phiên làm việc đã hết hạn. Vui lòng thử lại.';
            chuyen_trang_sau_post(benh_nhan_url_chi_tiet($malichlamviec));
        }

        if (!la_vai_tro(VT_BENH_NHAN)) {
            $_SESSION['auth_message'] = 'Vui lòng đăng nhập để tiếp tục đặt lịch.';
            $_SESSION['after_login_url'] = benh_nhan_url_chi_tiet($malichlamviec);

            if ($isAjax) {
                ajax_json([
                    'ok' => false,
                    'redirect' => 'index.php?page=dang-nhap',
                    'message' => 'Vui lòng đăng nhập để tiếp tục đặt lịch.',
                ]);
            }

            chuyen_trang('index.php?page=dang-nhap');
        }

        if ($malichlamviec === null || $slot === null) {
            $message = 'Khung giờ này không còn sẵn sàng để đặt.';
            $messageType = 'error';
        } elseif (strlen($lydo) > 255) {
            $message = 'Lý do khám không được dài quá 255 ký tự.';
            $messageType = 'error';
        } else {
            $manguoidung = ma_nguoi_dung_hien_tai();

            try {
                $patient = lich_kham_lay_ho_so_benh_nhan($conn, $manguoidung);
                $hasPatientProfile = ho_so_da_du($patient);

                if (!$hasPatientProfile) {
                    $_SESSION['after_profile_url'] = benh_nhan_url_chi_tiet($malichlamviec);

                    if ($isAjax) {
                        ajax_json([
                            'ok' => false,
                            'redirect' => 'index.php?page=tao-ho-so',
                            'message' => 'Bạn cần hoàn thiện hồ sơ trước khi đặt lịch.',
                        ]);
                    }

                    chuyen_trang('index.php?page=tao-ho-so');
                }

                lich_kham_dat_lich($conn, $manguoidung, $malichlamviec, $lydo);
                $message = 'Đặt lịch thành công. Lịch khám đang chờ xác nhận.';
                $messageType = 'success';
                $bookingCompleted = true;
            } catch (RuntimeException $exception) {
                $message = 'Không thể đặt lịch lúc này. Vui lòng thử lại.';
                $messageType = 'error';

                if ((int) $exception->getCode() === 409 || (int) $exception->getCode() === 1062) {
                    $message = 'Khung giờ này vừa được đặt bởi người khác. Vui lòng chọn khung giờ khác.';
                }
            }
        }

        // Nhanh AJAX: tra JSON de JS cap nhat panel ma khong mat noi dung da go.
        if ($isAjax) {
            ajax_json([
                'ok' => $bookingCompleted,
                'message' => $message,
                'messageType' => $messageType ?: 'error',
                'panelHtml' => $bookingCompleted
                    ? render_fragment(benh_nhan_partial('booking-completed'), [
                        'message' => $message,
                        'backUrl' => benh_nhan_url_quay_lai($slot),
                    ])
                    : null,
            ]);
        }
    }

    if (la_vai_tro(VT_BENH_NHAN) && $patient === null) {
        try {
            $patient = lich_kham_lay_ho_so_benh_nhan($conn, ma_nguoi_dung_hien_tai());
            $hasPatientProfile = ho_so_da_du($patient);
            if (!$hasPatientProfile && $slot !== null) {
                $_SESSION['after_profile_url'] = benh_nhan_url_chi_tiet($malichlamviec);
            }
        } catch (RuntimeException $exception) {
            $message = $message !== '' ? $message : 'Không thể tải hồ sơ bệnh nhân. Vui lòng thử lại.';
            $messageType = $messageType !== '' ? $messageType : 'error';
        }
    }

    return [
        'message' => $message,
        'messageType' => $messageType,
        'malichlamviec' => $malichlamviec,
        'slot' => $slot,
        'patient' => $patient,
        'hasPatientProfile' => $hasPatientProfile,
        'isLoggedIn' => da_dang_nhap(),
        'lydo' => $lydo,
        'bookingCompleted' => $bookingCompleted,
        'backUrl' => benh_nhan_url_quay_lai($slot),
    ];
}

function benh_nhan_url_quay_lai($slot)
{
    if (!is_array($slot) || empty($slot['thoigianbatdau'])) {
        return 'index.php?page=dat-kham';
    }

    return benh_nhan_url('dat-kham', ['ngay' => date('Y-m-d', strtotime($slot['thoigianbatdau']))]);
}

function benh_nhan_url_chi_tiet($malichlamviec)
{
    if ($malichlamviec === null) {
        return 'index.php?page=chi-tiet-lich-kham';
    }

    return benh_nhan_url('chi-tiet-lich-kham', ['malichlamviec' => (string) $malichlamviec]);
}

function benh_nhan_kiem_tra_ngay($ngay)
{
    $date = DateTime::createFromFormat('!Y-m-d', $ngay);
    if (!$date || $date->format('Y-m-d') !== $ngay) {
        return 'Ngày khám không hợp lệ.';
    }

    if ($ngay < date('Y-m-d')) {
        return 'Vui lòng chọn ngày khám từ hôm nay trở đi.';
    }

    return '';
}
