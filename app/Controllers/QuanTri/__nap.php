<?php
//Nạp model, helper và các controller quản trị

require_once __DIR__ . '/../../Helpers/ho_so.php';
require_once __DIR__ . '/../../Helpers/avatar.php';
require_once __DIR__ . '/../../Models/quan_tri.php';

const QUAN_TRI_KHOA_FLASH = 'quan_tri_flash';
const QUAN_TRI_KHOA_FORM_CU = 'quan_tri_form_cu';

//Đặt thông báo hiện ở trang kế tiếp.
//$loai: 'ok' hoặc 'loi'.
function quan_tri_dat_flash($loai, $noiDung)
{
    $_SESSION[QUAN_TRI_KHOA_FLASH] = ['type' => $loai, 'message' => $noiDung];
}

//Đọc rồi xoá thông báo.
function quan_tri_lay_flash()
{
    $flash = $_SESSION[QUAN_TRI_KHOA_FLASH] ?? null;
    unset($_SESSION[QUAN_TRI_KHOA_FLASH]);

    return is_array($flash) ? $flash : null;
}

//Nhớ dữ liệu form đã gõ để điền lại khi lỗi. Không lưu mật khẩu.
function quan_tri_luu_form_cu($du_lieu)
{
    unset($du_lieu['matkhau']);
    $_SESSION[QUAN_TRI_KHOA_FORM_CU] = $du_lieu;
}

function quan_tri_lay_form_cu()
{
    $cu = $_SESSION[QUAN_TRI_KHOA_FORM_CU] ?? [];
    unset($_SESSION[QUAN_TRI_KHOA_FORM_CU]);

    return is_array($cu) ? $cu : [];
}

require_once __DIR__ . '/tong_quan.php';
require_once __DIR__ . '/nguoi_dung.php';
require_once __DIR__ . '/bac_si.php';
require_once __DIR__ . '/ajax.php';