<?php

require_once __DIR__ . '/../Helpers/csrf.php';
require_once __DIR__ . '/../Helpers/HoSoValidator.php';

class HoSoController
{
    private $nguoiDung;

    public function __construct($nguoiDung)
    {
        $this->nguoiDung = $nguoiDung;
    }

    public function hienThi()
    {
        $manguoidung = $this->getPositiveInt($_SESSION['manguoidung'] ?? null);

        if ($manguoidung === null) {
            header('Location: dang-nhap.php');
            exit;
        }

        $message = '';
        $userFound = false;
        $hoten = '';
        $ngaysinh = '';
        $gioitinh = '';
        $email = '';
        $diachi = '';
        $bhyt = '';
        $cccd = '';
        $dantoc = '';

        try {
            $hoSo = $this->nguoiDung->layHoSo($manguoidung);

            if ($hoSo === null) {
                $message = 'Không tìm thấy thông tin bệnh nhân. Vui lòng đăng nhập lại.';
            } else {
                $userFound = true;
                $hoten = (string) ($hoSo['hoten'] ?? '');
                $ngaysinh = (string) ($hoSo['ngaysinh'] ?? '');
                $gioitinh = (string) ($hoSo['gioitinh'] ?? '');
                $email = (string) ($hoSo['email'] ?? '');
                $diachi = (string) ($hoSo['diachi'] ?? '');
                $bhyt = (string) ($hoSo['bhyt'] ?? '');
                $cccd = (string) ($hoSo['cccd'] ?? '');
                $dantoc = (string) ($hoSo['dantoc'] ?? '');
            }
        } catch (RuntimeException $exception) {
            $message = $exception->getMessage() . ' Vui lòng thử lại.';
        }

        $isPost = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';

        if ($isPost && $userFound && !csrf_validate()) {
            $isPost = false;
            $message = 'Phiên làm việc đã hết hạn. Vui lòng thử lại.';
        }

        if ($isPost && $userFound) {
            $ngaysinh = $this->getString($_POST['ngaysinh'] ?? '');
            $gioitinh = $this->getString($_POST['gioitinh'] ?? '');
            $email = $this->getString($_POST['email'] ?? '');
            $diachi = $this->getString($_POST['diachi'] ?? '');
            $bhyt = $this->getString($_POST['bhyt'] ?? '');
            $cccd = $this->getString($_POST['cccd'] ?? '');
            $dantoc = $this->getString($_POST['dantoc'] ?? '');

            // Form tao-ho-so.php khong gui hoten; chi cap nhat khi co gui.
            $postedHoten = $this->getString($_POST['hoten'] ?? '');
            if ($postedHoten !== '') {
                $hoten = $postedHoten;
            }

            $validationError = HoSoValidator::validateHoSo($ngaysinh, $gioitinh, $email, $diachi, $bhyt);

            if ($validationError === '') {
                $validationError = HoSoValidator::validateTruongMoRong($cccd, $dantoc);
            }

            if ($validationError !== '') {
                $message = $validationError;
            } else {
                try {
                    $this->nguoiDung->capNhatHoSo(
                        $manguoidung,
                        $hoten,
                        $ngaysinh,
                        $gioitinh,
                        $email,
                        $diachi,
                        $bhyt,
                        $cccd,
                        $dantoc
                    );

                    $_SESSION['is_logged_in'] = true;

                    $afterProfileUrl = $_SESSION['after_profile_url'] ?? 'dat-kham.php';
                    unset($_SESSION['after_profile_url']);
                    header('Location: ' . $afterProfileUrl);
                    exit;
                } catch (RuntimeException $exception) {
                    if ((int) $exception->getCode() === 1062) {
                        $message = 'Email, số BHYT hoặc số CCCD này đã được sử dụng cho tài khoản khác.';
                    } else {
                        $message = $exception->getMessage() . ' Vui lòng thử lại.';
                    }
                }
            }
        }

        return [
            'message' => $message,
            'userFound' => $userFound,
            'hoten' => $hoten,
            'ngaysinh' => $ngaysinh,
            'gioitinh' => $gioitinh,
            'email' => $email,
            'diachi' => $diachi,
            'bhyt' => $bhyt,
            'cccd' => $cccd,
            'dantoc' => $dantoc,
        ];
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
