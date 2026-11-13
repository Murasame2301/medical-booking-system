<?php

class AuthController
{
    private $nguoiDung;

    public function __construct($nguoiDung)
    {
        $this->nguoiDung = $nguoiDung;
    }

    public function dangNhap()
    {
        if (!empty($_SESSION['is_logged_in'])) {
            header('Location: xem-lich-kham.php');
            exit;
        }

        $message = $_SESSION['auth_message'] ?? '';
        unset($_SESSION['auth_message']);
        $phonenumber = '';

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $phonenumber = isset($_POST['sdt']) && is_string($_POST['sdt'])
                ? trim($_POST['sdt'])
                : '';
            $rawPassword = isset($_POST['mat_khau']) && is_string($_POST['mat_khau'])
                ? $_POST['mat_khau']
                : '';

            if ($phonenumber === '' || $rawPassword === '') {
                $message = 'Vui lòng nhập đầy đủ thông tin.';
            } else {
                try {
                    $user = $this->nguoiDung->timTheoSoDienThoai($phonenumber);

                    if ($user && password_verify($rawPassword, $user['matkhau'])) {
                        session_regenerate_id(true);
                        $_SESSION['manguoidung'] = $user['manguoidung'];
                        $_SESSION['is_logged_in'] = true;

                        $afterLoginUrl = $_SESSION['after_login_url'] ?? 'xem-lich-kham.php';
                        unset($_SESSION['after_login_url'], $_SESSION['auth_message']);
                        header('Location: ' . $afterLoginUrl);
                        exit;
                    }

                    $message = 'Số điện thoại hoặc mật khẩu không đúng.';
                } catch (RuntimeException $exception) {
                    $message = 'Không thể kiểm tra thông tin đăng nhập. Vui lòng thử lại.';
                }
            }
        }

        return [
            'message' => $message,
            'phonenumber' => $phonenumber,
        ];
    }

    public function dangKy()
    {
        if (!empty($_SESSION['is_logged_in'])) {
            header('Location: xem-lich-kham.php');
            exit;
        }

        $message = '';
        $fullname = '';
        $phonenumber = '';

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $fullname = isset($_POST['hoten']) && is_string($_POST['hoten'])
                ? trim($_POST['hoten'])
                : '';
            $phonenumber = isset($_POST['sdt']) && is_string($_POST['sdt'])
                ? trim($_POST['sdt'])
                : '';
            $rawPassword = isset($_POST['mat_khau']) && is_string($_POST['mat_khau'])
                ? $_POST['mat_khau']
                : '';
            $rawRepassword = isset($_POST['nhap_lai_mat_khau']) && is_string($_POST['nhap_lai_mat_khau'])
                ? $_POST['nhap_lai_mat_khau']
                : '';

            if ($fullname === '' || $phonenumber === '' || $rawPassword === '' || $rawRepassword === '') {
                $message = 'Vui lòng nhập đầy đủ thông tin.';
            } elseif ($rawPassword !== $rawRepassword) {
                $message = 'Mật khẩu không khớp. Vui lòng kiểm tra lại.';
            } else {
                $password = password_hash($rawPassword, PASSWORD_DEFAULT);

                try {
                    $manguoidung = $this->nguoiDung->taoTaiKhoan(
                        $fullname,
                        $phonenumber,
                        $password
                    );

                    session_regenerate_id(true);
                    $_SESSION['manguoidung'] = $manguoidung;

                    header('Location: tao-ho-so.php');
                    exit;
                } catch (RuntimeException $exception) {
                    if ((int) $exception->getCode() === 1062) {
                        $message = 'Số điện thoại này đã có tài khoản. Vui lòng đăng nhập hoặc sử dụng số khác.';
                    } else {
                        $message = 'Không thể tạo tài khoản. Vui lòng thử lại.';
                    }
                }
            }
        }

        return [
            'message' => $message,
            'fullname' => $fullname,
            'phonenumber' => $phonenumber,
        ];
    }
}
