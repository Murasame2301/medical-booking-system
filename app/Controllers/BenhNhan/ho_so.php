<?php

// Trang ho so benh nhan (page=tao-ho-so).

require_once __DIR__ . '/../../Helpers/csrf.php';
require_once __DIR__ . '/../../Helpers/utils.php';
require_once __DIR__ . '/../../Helpers/session.php';
require_once __DIR__ . '/../../Helpers/ho_so.php';
require_once __DIR__ . '/../../Models/nguoi_dung.php';

/**
 * @return array Du lieu cho view benh-nhan/tao-ho-so.php
 */
function benh_nhan_tao_ho_so($conn)
{
    $manguoidung = chung_so_duong($_SESSION[SS_MA_NGUOI_DUNG] ?? null);

    if ($manguoidung === null) {
        chuyen_trang('index.php?page=dang-nhap');
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

        // Form nay khong gui hoten; chi cap nhat khi co gui.
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

                // Ten hien tren menu lay tu session; cap nhat lai sau khi luu.
                $_SESSION[SS_HO_TEN] = $hoten;

                $sauHoSo = $_SESSION['after_profile_url'] ?? 'index.php?page=dat-kham';
                unset($_SESSION['after_profile_url']);
                chuyen_trang_sau_post($sauHoSo);
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
