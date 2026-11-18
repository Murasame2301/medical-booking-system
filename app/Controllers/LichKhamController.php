<?php

require_once __DIR__ . '/../Helpers/csrf.php';
require_once __DIR__ . '/../Helpers/ajax.php';

class LichKhamController
{
    private $lichKham;

    public function __construct($lichKham)
    {
        $this->lichKham = $lichKham;
    }

    private function partial($name)
    {
        return __DIR__ . '/../Views/appointments/partials/' . $name . '.php';
    }

    public function hienThi()
    {
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $ngay = $this->getString($_GET['ngay'] ?? '') ?: $tomorrow;
        $machuyenkhoa = $this->getPositiveInt($_GET['machuyenkhoa'] ?? null);
        $mabacsi = $this->getPositiveInt($_GET['mabacsi'] ?? null);
        $message = '';
        $messageType = '';

        $dateError = $this->validateDate($ngay);
        $specialties = [];
        $doctors = [];
        $slots = [];

        try {
            $specialties = $this->lichKham->layDanhSachChuyenKhoa();
            $doctors = $this->lichKham->layDanhSachBacSi($machuyenkhoa);

            if ($dateError === '') {
                $slots = $this->lichKham->timKhungGio($ngay, $machuyenkhoa, $mabacsi);
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
                'alertHtml' => render_fragment($this->partial('search-alert'), [
                    'dateError' => $dateError,
                    'message' => $message,
                    'messageType' => $messageType,
                ]),
                'resultsHtml' => render_fragment($this->partial('slot-results'), [
                    'ngay' => $ngay,
                    'slots' => $slots,
                ]),
                'doctorOptionsHtml' => render_fragment($this->partial('doctor-options'), [
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

    public function hienThiLichSu()
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
            && $this->getString($_POST['hanh_dong'] ?? '') === 'huy_lich') {
            $isAjax = ajax_is_request();

            if (!csrf_validate()) {
                if ($isAjax) {
                    ajax_json([
                        'ok' => false,
                        'reload' => true,
                        'message' => 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.',
                    ]);
                }

                $this->flashLichSu('Phiên làm việc đã hết hạn. Vui lòng thử lại.', 'error');
                $this->redirectLichSu();
            }

            $malichkham = $this->getPositiveInt($_POST['malichkham'] ?? null);
            $lydoHuy = $this->getString($_POST['lydo_huy'] ?? '');
            $loi = '';

            if ($malichkham === null) {
                $loi = 'Lịch khám không hợp lệ.';
            } elseif ($lydoHuy === '') {
                $loi = 'Vui lòng nhập lý do huỷ.';
            } elseif ($this->mbLength($lydoHuy) > 255) {
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

                $this->flashLichSu($loi, 'error', $lydoHuy, $malichkham);
                $this->redirectLichSu();
            }

            $ketQua = false;
            $thongDiep = '';
            $loaiThongDiep = 'error';

            try {
                $ketQua = $this->lichKham->huyLich($malichkham, $manguoidung, $lydoHuy);
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
                    $cardHtml = $this->renderTheLichSu($malichkham, $manguoidung);
                }

                ajax_json([
                    'ok' => $ketQua,
                    'message' => $thongDiep,
                    'messageType' => $loaiThongDiep,
                    'malichkham' => $malichkham,
                    'cardHtml' => $cardHtml,
                ]);
            }

            $this->flashLichSu($thongDiep, $loaiThongDiep);
            $this->redirectLichSu();
        }

        $flash = $_SESSION['lich_kham_flash'] ?? [];
        unset($_SESSION['lich_kham_flash']);

        $message = (string) ($flash['message'] ?? '');
        $messageType = (string) ($flash['type'] ?? '');
        $lichSu = [];
        $now = time();

        try {
            $lichSu = $this->lichKham->layLichSuDatKham($manguoidung);
        } catch (RuntimeException $exception) {
            $message = $message !== '' ? $message : 'Không thể tải lịch sử đặt khám. Vui lòng thử lại.';
            $messageType = $messageType !== '' ? $messageType : 'error';
        }

        // Cung cong thuc voi WHERE cua huyLich(): chi lich chua kham va chua toi gio moi huy duoc.
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

    private function flashLichSu($message, $type, $lydo = '', $malichkham = null)
    {
        $_SESSION['lich_kham_flash'] = [
            'message' => $message,
            'type' => $type,
            'lydo' => $lydo,
            'malichkham' => $malichkham,
        ];
    }

    private function redirectLichSu()
    {
        // 303: doi POST thanh GET de F5 khong gui lai yeu cau huy.
        header('Location: xem-lich-kham.php', true, 303);
        exit;
    }

    /**
     * Doc lai dung the lich vua huy de JS thay outerHTML. Tra '' neu khong tim thay
     * (JS khi do chi hien thong bao, khong dung vao the).
     */
    private function renderTheLichSu($malichkham, $manguoidung)
    {
        try {
            $lichSu = $this->lichKham->layLichSuDatKham($manguoidung);
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

            return render_fragment($this->partial('history-card'), [
                'item' => $row,
                'isReopen' => false,
                'lydoHuy' => '',
            ]);
        }

        return '';
    }

    private function mbLength($value)
    {
        return function_exists('mb_strlen')
            ? mb_strlen((string) $value, 'UTF-8')
            : strlen((string) $value);
    }

    public function chiTiet()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $isAjax = $requestMethod === 'POST' && ajax_is_request();
        $rawSlotId = $requestMethod === 'POST'
            ? ($_POST['malichlamviec'] ?? null)
            : ($_GET['malichlamviec'] ?? null);
        $malichlamviec = $this->getPositiveInt($rawSlotId);
        $message = '';
        $messageType = '';

        $csrfFlash = $_SESSION['csrf_flash'] ?? '';
        unset($_SESSION['csrf_flash']);
        if (is_string($csrfFlash) && $csrfFlash !== '') {
            $message = $csrfFlash;
            $messageType = 'error';
        }

        $lydo = $_SERVER['REQUEST_METHOD'] === 'POST'
            ? $this->getString($_POST['lydo'] ?? '')
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
                $slot = $this->lichKham->layChiTietKhungGio($malichlamviec);
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
            $_SESSION['after_login_url'] = $this->buildDetailUrl($malichlamviec);
        }

        if ($requestMethod === 'POST'
            && $this->getString($_POST['hanh_dong'] ?? '') === 'dat_lich') {
            if (!csrf_validate()) {
                if ($isAjax) {
                    ajax_json([
                        'ok' => false,
                        'reload' => true,
                        'message' => 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.',
                    ]);
                }

                $_SESSION['csrf_flash'] = 'Phiên làm việc đã hết hạn. Vui lòng thử lại.';
                header('Location: ' . $this->buildDetailUrl($malichlamviec), true, 303);
                exit;
            }

            if (empty($_SESSION['is_logged_in']) || empty($_SESSION['manguoidung'])) {
                $_SESSION['auth_message'] = 'Vui lòng đăng nhập để tiếp tục đặt lịch.';
                $_SESSION['after_login_url'] = $this->buildDetailUrl($malichlamviec);

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
                    $patient = $this->lichKham->layHoSoBenhNhan($manguoidung);
                    $hasPatientProfile = $this->coDuHoSo($patient);

                    if (!$hasPatientProfile) {
                        $_SESSION['after_profile_url'] = $this->buildDetailUrl($malichlamviec);

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

                    $this->lichKham->datLich($manguoidung, $malichlamviec, $lydo);
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
                        ? render_fragment($this->partial('booking-completed'), [
                            'message' => $message,
                            'backUrl' => $this->buildBackUrl($slot),
                        ])
                        : null,
                ]);
            }
        }

        if (!empty($_SESSION['is_logged_in']) && !empty($_SESSION['manguoidung']) && $patient === null) {
            try {
                $patient = $this->lichKham->layHoSoBenhNhan((int) $_SESSION['manguoidung']);
                $hasPatientProfile = $this->coDuHoSo($patient);
                if (!$hasPatientProfile && $slot !== null) {
                    $_SESSION['after_profile_url'] = $this->buildDetailUrl($malichlamviec);
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
            'backUrl' => $this->buildBackUrl($slot),
        ];
    }

    private function buildBackUrl($slot)
    {
        if (!is_array($slot) || empty($slot['thoigianbatdau'])) {
            return 'dat-kham.php';
        }

        return 'dat-kham.php?ngay=' . rawurlencode(date('Y-m-d', strtotime($slot['thoigianbatdau'])));
    }

    private function coDuHoSo($patient)
    {
        if (!is_array($patient)) {
            return false;
        }

        return !empty($patient['ngaysinh'])
            && !empty($patient['gioitinh'])
            && trim((string) ($patient['diachi'] ?? '')) !== ''
            && trim((string) ($patient['bhyt'] ?? '')) !== '';
    }

    private function buildDetailUrl($malichlamviec)
    {
        if ($malichlamviec === null) {
            return 'chi-tiet-lich-kham.php';
        }

        return 'chi-tiet-lich-kham.php?malichlamviec=' . rawurlencode((string) $malichlamviec);
    }

    private function validateDate($ngay)
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

    private function getString($value)
    {
        return is_string($value) ? trim($value) : '';
    }

    private function getPositiveInt($value)
    {
        if (is_int($value) && $value > 0) {
            return $value;
        }

        if (is_string($value) && ctype_digit($value) && (int) $value > 0) {
            return (int) $value;
        }

        return null;
    }
}
