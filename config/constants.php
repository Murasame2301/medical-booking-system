<?php
/* Hang so dung chung cho ca ba vai tro. */

/* Vai tro: khop bang VAI_TRO trong CSDL */
const VT_BENH_NHAN = 1;
const VT_BAC_SI    = 2;
const VT_QUAN_TRI  = 3;

const TEN_VAI_TRO = [
    VT_BENH_NHAN => 'Bệnh nhân',
    VT_BAC_SI    => 'Bác sĩ',
    VT_QUAN_TRI  => 'Quản trị viên',
];

/* Khoa session */
const SS_MA_NGUOI_DUNG = 'manguoidung';
const SS_VAI_TRO       = 'vai_tro';
const SS_HO_TEN        = 'ho_ten';

/* Trang dau tien cua moi vai tro sau khi dang nhap */
const TRANG_CHU_THEO_VAI_TRO = [
    VT_BENH_NHAN => 'trang-chu',
    VT_BAC_SI    => 'bac-si/tong-quan',
    VT_QUAN_TRI  => 'quan-tri/tong-quan',
];

/* Lich lam viec */
const LLV_CON_TRONG  = 'con_trong';
const LLV_DA_DAT     = 'da_dat';
const LLV_TAT_CA     = 'tat_ca';
const LLV_TRANG_THAI = [LLV_CON_TRONG, LLV_DA_DAT];

/* Lich kham */
const LK_TONG_LUOT    = 'tong_luot';
const LK_CHO_XAC_NHAN = 'cho_xac_nhan';
const LK_DA_XAC_NHAN  = 'da_xac_nhan';
const LK_DA_KHAM      = 'da_kham';
const LK_DA_HUY       = 'da_huy';
const LK_TRANG_THAI = [LK_CHO_XAC_NHAN, LK_DA_XAC_NHAN, LK_DA_KHAM, LK_DA_HUY];

/* Trang thai tai khoan */
const TK_HOAT_DONG = 'hoat_dong';
const TK_BI_KHOA   = 'bi_khoa';

/* Gio lam viec */
const GIO_DAU_NGAY      = '00:00';
const GIO_CUOI_NGAY     = '23:59';
const CUOI_NGAY_PHUT    = 23 * 60 + 59;
const SO_PHUT_TOI_THIEU = 5;
const SO_PHUT_TOI_DA    = 60;

/* Anh dai dien: thu muc that va duong dan web tuong ung */
const ANH_THU_MUC      = 'assets/uploads/avatar/';
const ANH_TOI_DA_BYTE  = 2 * 1024 * 1024;   // 2MB
const ANH_LOAI_HOP_LE  = ['image/jpeg', 'image/png', 'image/webp'];
const ANH_TOI_DA_DIEM  = 25000000;          // 25 megapixel
