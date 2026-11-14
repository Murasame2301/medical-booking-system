<?php
/* Cau hinh vai tro */
const VT_BENH_NHAN = 1;
const VT_BAC_SI    = 2;
const VT_QUAN_TRI  = 3;

/* KHOA SESSION */
const SS_MA_NGUOI_DUNG = 'ma_nguoi_dung';
const SS_VAI_TRO       = 'vai_tro';

/* BO LOC CHUNG */
const TAT_CA = 'tat_ca';

/* LICH LAM VIEC */
const LLV_CON_TRONG = 'con_trong';
const LLV_DA_DAT    = 'da_dat';
const LLV_TAT_CA    = TAT_CA;
const LLV_TRANG_THAI = [LLV_CON_TRONG, LLV_DA_DAT];

/* LICH KHAM */
const LK_TONG_LUOT    = 'tong_luot';
const LK_CHO_XAC_NHAN = 'cho_xac_nhan';
const LK_DA_XAC_NHAN  = 'da_xac_nhan';
const LK_DA_KHAM      = 'da_kham';
const LK_DA_HUY       = 'da_huy';
const LK_TRANG_THAI = [LK_CHO_XAC_NHAN, LK_DA_XAC_NHAN, LK_DA_KHAM, LK_DA_HUY];

/* THOI GIAN */
const GIO_DAU_NGAY      = '00:00';
const GIO_CUOI_NGAY     = '23:59';
const CUOI_NGAY_PHUT    = 23 * 60 + 59;
const SO_PHUT_TOI_THIEU = 5;
const SO_PHUT_TOI_DA    = 60;