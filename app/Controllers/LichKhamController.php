<?php

class LichKhamController
{
    private $lichKham;

    public function __construct($lichKham)
    {
        $this->lichKham = $lichKham;
    }

    public function hienThi()
    {
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $ngay = $this->getString($_GET['ngay'] ?? '') ?: $tomorrow;
        $machuyenkhoa = $this->getPositiveInt($_GET['machuyenkhoa'] ?? null);
        $mabacsi = $this->getPositiveInt($_GET['mabacsi'] ?? null);
        $message = '';
        $messageType = '';

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $this->getString($_POST['hanh_dong'] ?? '') === 'dat_lich') {
            $ngay = $this->getString($_POST['ngay'] ?? '') ?: $ngay;
            $machuyenkhoa = $this->getPositiveInt($_POST['machuyenkhoa'] ?? null);
            $mabacsi = $this->getPositiveInt($_POST['mabacsi'] ?? null);
            $message = $this->xuLyDatLich($messageType, $ngay, $machuyenkhoa, $mabacsi);
        }

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

    private function xuLyDatLich(&$messageType, $ngay, $machuyenkhoa, $mabacsi)
    {
        if (empty($_SESSION['is_logged_in']) || empty($_SESSION['manguoidung'])) {
            $query = http_build_query([
                'ngay' => $ngay,
                'machuyenkhoa' => $machuyenkhoa,
                'mabacsi' => $mabacsi,
            ]);
            $_SESSION['auth_message'] = 'Vui lòng đăng nhập để tiếp tục đặt lịch.';
            $_SESSION['after_login_url'] = 'dat-kham.php?' . $query;
            header('Location: dang-nhap.php');
            exit;
        }

        $manguoidung = (int) $_SESSION['manguoidung'];
        $malichlamviec = $this->getPositiveInt($_POST['malichlamviec'] ?? null);
        $lydo = $this->getString($_POST['lydo'] ?? '');

        if ($malichlamviec === null) {
            $messageType = 'error';
            return 'Khung giờ khám không hợp lệ.';
        }

        if (strlen($lydo) > 255) {
            $messageType = 'error';
            return 'Lý do khám không được dài quá 255 ký tự.';
        }

        try {
            if (!$this->lichKham->coBenhNhan($manguoidung)) {
                header('Location: tao-ho-so.php');
                exit;
            }

            $this->lichKham->datLich($manguoidung, $malichlamviec, $lydo);
            $messageType = 'success';
            return 'Đặt lịch thành công. Lịch khám đang chờ xác nhận.';
        } catch (RuntimeException $exception) {
            $messageType = 'error';

            if ((int) $exception->getCode() === 409) {
                return 'Khung giờ này vừa được đặt bởi người khác. Vui lòng chọn khung giờ khác.';
            }

            if ((int) $exception->getCode() === 1062) {
                return 'Khung giờ này vừa được đặt bởi người khác. Vui lòng chọn khung giờ khác.';
            }

            return 'Không thể đặt lịch lúc này. Vui lòng thử lại.';
        }
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
