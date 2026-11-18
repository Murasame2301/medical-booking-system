<?php

// Trang ho so benh nhan (tao-ho-so.php).

require_once __DIR__ . '/../Helpers/csrf.php';
require_once __DIR__ . '/../Helpers/ho_so.php';
require_once __DIR__ . '/../Helpers/chung.php';
require_once __DIR__ . '/../Models/nguoi_dung.php';

/**
 * @return array Du lieu cho view profile/edit.php
 */
function ho_so_hien_thi($conn)
{
    $manguoidung = chung_so_duong($_SESSION['manguoidung'] ?? null);

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
        $hoSo = nguoi_dung_lay_ho_so($conn, $manguoidung);

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
        $ngaysinh = chung_chuoi($_POST['ngaysinh'] ?? '');
        $gioitinh = chung_chuoi($_POST['gioitinh'] ?? '');
        $email = chung_chuoi($_POST['email'] ?? '');
        $diachi = chung_chuoi($_POST['diachi'] ?? '');
        $bhyt = chung_chuoi($_POST['bhyt'] ?? '');
        $cccd = chung_chuoi($_POST['cccd'] ?? '');
        $dantoc = chung_chuoi($_POST['dantoc'] ?? '');

        // Form tao-ho-so.php khong gui hoten; chi cap nhat khi co gui.
        $postedHoten = chung_chuoi($_POST['hoten'] ?? '');
        if ($postedHoten !== '') {
            $hoten = $postedHoten;
        }

        $validationError = ho_so_kiem_tra_ho_so($ngaysinh, $gioitinh, $email, $diachi, $bhyt);

        if ($validationError === '') {
            $validationError = ho_so_kiem_tra_truong_mo_rong($cccd, $dantoc);
        }

        if ($validationError !== '') {
            $message = $validationError;
        } else {
            try {
                nguoi_dung_cap_nhat_ho_so(
                    $conn,
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
