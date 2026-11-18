<?php

// Xac thuc: dang nhap / dang ky / dang xuat.
// Mot trang dang nhap duy nhat cho ca ba vai tro; sau khi dang nhap, he
// thong re nhanh theo mavaitro. Dang nhap bang so dien thoai.

require_once __DIR__ . '/../Helpers/csrf.php';
require_once __DIR__ . '/../Helpers/utils.php';
require_once __DIR__ . '/../Helpers/session.php';
require_once __DIR__ . '/../Models/nguoi_dung.php';

/**
 * Trang dang nhap chung.
 *
 * @return array message, phonenumber
 */
function auth_dang_nhap($conn)
{
    if (da_dang_nhap()) {
        chuyen_trang(trang_chu_theo_vai_tro(vai_tro_hien_tai()));
    }

    $message = (string) ($_SESSION['auth_message'] ?? '');
    unset($_SESSION['auth_message']);
    $phonenumber = '';

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        if (!csrf_validate()) {
            $_SESSION['auth_message'] = 'Phiên làm việc đã hết hạn. Vui lòng thử lại.';
            chuyen_trang_sau_post('index.php?page=dang-nhap');
        }

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
                $user = nguoi_dung_tim_theo_sdt($conn, $phonenumber);

                if ($user && password_verify($rawPassword, $user['matkhau'])) {
                    if (($user['trangthai'] ?? '') === TK_BI_KHOA) {
                        $message = 'Tài khoản của bạn đã bị khoá. Vui lòng liên hệ quản trị viên.';
                    } else {
                        mo_phien_dang_nhap($user);

                        $sauDangNhap = $_SESSION['after_login_url'] ?? '';
                        unset($_SESSION['after_login_url'], $_SESSION['auth_message']);

                        // Chi nhan duong dan noi bo, tranh bi dung lam redirect mo.
                        if ($sauDangNhap !== '' && str_starts_with($sauDangNhap, 'index.php?page=')) {
                            chuyen_trang($sauDangNhap);
                        }

                        chuyen_trang(trang_chu_theo_vai_tro((int) $user['mavaitro']));
                    }
                } else {
                    $message = 'Số điện thoại hoặc mật khẩu không đúng.';
                }
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

/**
 * Trang dang ky tai khoan benh nhan.
 *
 * @return array message, fullname, phonenumber
 */
function auth_dang_ky($conn)
{
    if (da_dang_nhap()) {
        chuyen_trang(trang_chu_theo_vai_tro(vai_tro_hien_tai()));
    }

    $message = '';
    $fullname = '';
    $phonenumber = '';

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        if (!csrf_validate()) {
            $_SESSION['auth_message'] = 'Phiên làm việc đã hết hạn. Vui lòng thử lại.';
            chuyen_trang_sau_post('index.php?page=dang-ky');
        }

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

        $loi = '';
        if ($fullname === '' || $phonenumber === '' || $rawPassword === '' || $rawRepassword === '') {
            $loi = 'Vui lòng nhập đầy đủ thông tin.';
        } elseif (chung_do_dai($fullname) > 100) {
            $loi = 'Họ và tên không được dài quá 100 ký tự.';
        } elseif (!hop_le_sdt($phonenumber)) {
            $loi = 'Số điện thoại phải gồm 10 chữ số và bắt đầu bằng số 0.';
        } elseif ($rawPassword !== $rawRepassword) {
            $loi = 'Mật khẩu nhập lại chưa khớp.';
        } else {
            $loi = ho_so_kiem_tra_mat_khau_moi($rawPassword);
        }

        if ($loi !== '') {
            $message = $loi;
        } else {
            try {
                if (nguoi_dung_tim_theo_sdt($conn, $phonenumber) !== null) {
                    $message = 'Số điện thoại này đã được đăng ký.';
                } else {
                    nguoi_dung_tao_tai_khoan(
                        $conn,
                        $fullname,
                        $phonenumber,
                        password_hash($rawPassword, PASSWORD_DEFAULT)
                    );

                    // Dang ky xong dang nhap luon, de khong phai go lai.
                    $user = nguoi_dung_tim_theo_sdt($conn, $phonenumber);
                    if ($user !== null) {
                        mo_phien_dang_nhap($user);
                        chuyen_trang(trang_chu_theo_vai_tro(VT_BENH_NHAN));
                    }

                    $_SESSION['auth_message'] = 'Đăng ký thành công. Vui lòng đăng nhập.';
                    chuyen_trang_sau_post('index.php?page=dang-nhap');
                }
            } catch (RuntimeException $exception) {
                if ((int) $exception->getCode() === 1062) {
                    $message = 'Số điện thoại này đã được đăng ký.';
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

/**
 * Dang xuat: xoa phien roi ve trang dang nhap.
 */
function auth_dang_xuat($conn)
{
    dang_xuat();
}
