<?php

// Trang dat kham: tim khung gio, chi tiet khung gio, dat lich va lich su dat kham.
// Tra ve mang du lieu cho view, giong het cau truc $viewData ma view dang doc.

require_once __DIR__ . '/../Helpers/csrf.php';
require_once __DIR__ . '/../Helpers/ajax.php';
require_once __DIR__ . '/../Helpers/chung.php';
require_once __DIR__ . '/../Helpers/ho_so.php';
require_once __DIR__ . '/../Models/lich_kham.php';

function lich_kham_partial($name)
{
    return __DIR__ . '/../Views/appointments/partials/' . $name . '.php';
}

/**
 * Trang tim lich kham (dat-kham.php).
 */
function lich_kham_hien_thi($conn)
{
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $ngay = chung_chuoi($_GET['ngay'] ?? '') ?: $tomorrow;
    $machuyenkhoa = chung_so_duong($_GET['machuyenkhoa'] ?? null);
    $mabacsi = chung_so_duong($_GET['mabacsi'] ?? null);
    $message = '';
    $messageType = '';

    $dateError = lich_kham_kiem_tra_ngay($ngay);
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
            'alertHtml' => render_fragment(lich_kham_partial('search-alert'), [
                'dateError' => $dateError,
                'message' => $message,
                'messageType' => $messageType,
            ]),
            'resultsHtml' => render_fragment(lich_kham_partial('slot-results'), [
                'ngay' => $ngay,
                'slots' => $slots,
            ]),
            'doctorOptionsHtml' => render_fragment(lich_kham_partial('doctor-options'), [
                'doctors' => $doctors,
                'mabacsi' => $mabacsi,
            ]),
            'url' => 'dat-kham.php?' . http_build_query($query),
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
        'isLoggedIn' => !empty($_SESSION['is_logged_in']),
    ];
}

/**
 * Trang lich su dat kham (xem-lich-kham.php), kem xu ly huy lich.
 */
function lich_kham_lich_su($conn)
{
    if (empty($_SESSION['is_logged_in']) || empty($_SESSION['manguoidung'])) {
        $_SESSION['after_login_url'] = 'xem-lich-kham.php';

        if (ajax_is_request()) {
            // fetch di theo 302 se nhan ve ca trang dang nhap; tra JSON de JS tu chuyen trang.
            ajax_json([
                'ok' => false,
                'redirect' => 'dang-nhap.php',
                'message' => 'Phiên đăng nhập đã hết. Vui lòng đăng nhập lại.',
            ]);
        }

        header('Location: dang-nhap.php');
        exit;
    }

    $manguoidung = (int) $_SESSION['manguoidung'];
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

            lich_kham_dat_flash_lich_su('Phiên làm việc đã hết hạn. Vui lòng thử lại.', 'error');
            lich_kham_chuyen_trang_lich_su();
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

            lich_kham_dat_flash_lich_su($loi, 'error', $lydoHuy, $malichkham);
            lich_kham_chuyen_trang_lich_su();
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
                $cardHtml = lich_kham_render_the_lich_su($conn, $malichkham, $manguoidung);
            }

            ajax_json([
                'ok' => $ketQua,
                'message' => $thongDiep,
                'messageType' => $loaiThongDiep,
                'malichkham' => $malichkham,
                'cardHtml' => $cardHtml,
            ]);
        }

        lich_kham_dat_flash_lich_su($thongDiep, $loaiThongDiep);
        lich_kham_chuyen_trang_lich_su();
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

    // Cung cong thuc voi WHERE cua lich_kham_huy_lich(): chi lich chua kham va chua toi gio moi huy duoc.
    foreach ($lichSu as $key => $row) {
        $lichSu[$key]['coTheHuy'] = in_array($row['trangthai'], ['cho_xac_nhan', 'da_xac_nhan'], true)
            && strtotime($row['thoigianbatdau']) >= $now;
    }

    return [
        'message' => $message,
        'messageType' => $messageType,
        'lichSu' => $lichSu,
        'lydoHuy' => (string) ($flash['lydo'] ?? ''),
        'malichkhamLoi' => isset($flash['malichkham']) ? (int) $flash['malichkham'] : null,
        'isLoggedIn' => !empty($_SESSION['is_logged_in']),
    ];
}

function lich_kham_dat_flash_lich_su($message, $type, $lydo = '', $malichkham = null)
{
    chung_dat_flash('lich_kham_flash', $message, $type, [
        'lydo' => $lydo,
        'malichkham' => $malichkham,
    ]);
}

function lich_kham_chuyen_trang_lich_su()
{
    // 303: doi POST thanh GET de F5 khong gui lai yeu cau huy.
    chung_chuyen_trang_sau_post('xem-lich-kham.php');
}

/**
 * Doc lai dung the lich vua huy de JS thay outerHTML. Tra '' neu khong tim thay
 * (JS khi do chi hien thong bao, khong dung vao the).
 */
function lich_kham_render_the_lich_su($conn, $malichkham, $manguoidung)
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

        $row['coTheHuy'] = in_array($row['trangthai'], ['cho_xac_nhan', 'da_xac_nhan'], true)
            && strtotime($row['thoigianbatdau']) >= $now;

        return render_fragment(lich_kham_partial('history-card'), [
            'item' => $row,
            'isReopen' => false,
            'lydoHuy' => '',
        ]);
    }

    return '';
}

/**
 * Trang chi tiet khung gio (chi-tiet-lich-kham.php), kem xu ly dat lich.
 */
function lich_kham_chi_tiet($conn)
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

    if ($slot !== null && empty($_SESSION['is_logged_in'])) {
        $_SESSION['after_login_url'] = lich_kham_url_chi_tiet($malichlamviec);
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
            chung_chuyen_trang_sau_post(lich_kham_url_chi_tiet($malichlamviec));
        }

        if (empty($_SESSION['is_logged_in']) || empty($_SESSION['manguoidung'])) {
            $_SESSION['auth_message'] = 'Vui lòng đăng nhập để tiếp tục đặt lịch.';
            $_SESSION['after_login_url'] = lich_kham_url_chi_tiet($malichlamviec);

            if ($isAjax) {
                ajax_json([
                    'ok' => false,
                    'redirect' => 'dang-nhap.php',
                    'message' => 'Vui lòng đăng nhập để tiếp tục đặt lịch.',
                ]);
            }

            header('Location: dang-nhap.php');
            exit;
        }

        if ($malichlamviec === null || $slot === null) {
            $message = 'Khung giờ này không còn sẵn sàng để đặt.';
            $messageType = 'error';
        } elseif (strlen($lydo) > 255) {
            $message = 'Lý do khám không được dài quá 255 ký tự.';
            $messageType = 'error';
        } else {
            $manguoidung = (int) $_SESSION['manguoidung'];

            try {
                $patient = lich_kham_lay_ho_so_benh_nhan($conn, $manguoidung);
                $hasPatientProfile = ho_so_da_du($patient);

                if (!$hasPatientProfile) {
                    $_SESSION['after_profile_url'] = lich_kham_url_chi_tiet($malichlamviec);

                    if ($isAjax) {
                        ajax_json([
                            'ok' => false,
                            'redirect' => 'tao-ho-so.php',
                            'message' => 'Bạn cần hoàn thiện hồ sơ trước khi đặt lịch.',
                        ]);
                    }

                    header('Location: tao-ho-so.php');
                    exit;
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
                    ? render_fragment(lich_kham_partial('booking-completed'), [
                        'message' => $message,
                        'backUrl' => lich_kham_url_quay_lai($slot),
                    ])
                    : null,
            ]);
        }
    }

    if (!empty($_SESSION['is_logged_in']) && !empty($_SESSION['manguoidung']) && $patient === null) {
        try {
            $patient = lich_kham_lay_ho_so_benh_nhan($conn, (int) $_SESSION['manguoidung']);
            $hasPatientProfile = ho_so_da_du($patient);
            if (!$hasPatientProfile && $slot !== null) {
                $_SESSION['after_profile_url'] = lich_kham_url_chi_tiet($malichlamviec);
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
        'isLoggedIn' => !empty($_SESSION['is_logged_in']),
        'lydo' => $lydo,
        'bookingCompleted' => $bookingCompleted,
        'backUrl' => lich_kham_url_quay_lai($slot),
    ];
}

function lich_kham_url_quay_lai($slot)
{
    if (!is_array($slot) || empty($slot['thoigianbatdau'])) {
        return 'dat-kham.php';
    }

    return 'dat-kham.php?ngay=' . rawurlencode(date('Y-m-d', strtotime($slot['thoigianbatdau'])));
}

function lich_kham_url_chi_tiet($malichlamviec)
{
    if ($malichlamviec === null) {
        return 'chi-tiet-lich-kham.php';
    }

    return 'chi-tiet-lich-kham.php?malichlamviec=' . rawurlencode((string) $malichlamviec);
}

function lich_kham_kiem_tra_ngay($ngay)
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
