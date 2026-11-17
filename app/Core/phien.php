<?php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Chuyển sang trang khác rồi dừng chương trình
function chuyen_trang($duongdan) {
    header("Location: " . $duongdan);
    exit;
}

function bat_buoc_dang_nhap($goc = '') {
    global $conn;   // lấy biến kết nối DB đã tạo ở connect.php

    if (!isset($_SESSION['id'])) {
        chuyen_trang($goc . 'dang_nhap.php');
    }

    // Bị khóa khi đang đăng nhập: xóa thông tin đăng nhập, báo lý do, về trang đăng nhập
    if (tai_khoan_bi_khoa($conn, $_SESSION['id'])) {
        unset($_SESSION['id'], $_SESSION['hoten'], $_SESSION['vaitro']);
        dat_thong_bao('loi', 'Tài khoản của bạn đã bị khóa.');
        chuyen_trang($goc . 'dang_nhap.php');
    }
}

// Hàm chung: bắt đăng nhập, rồi bắt đúng vai trò. Sai vai trò thì về trang chủ.
function bat_buoc_vai_tro($vaitro, $goc = '') {
    bat_buoc_dang_nhap($goc);
    if ($_SESSION['vaitro'] != $vaitro) {
        chuyen_trang($goc . 'index.php');
    }
}

function bat_buoc_benh_nhan($goc = '') {
    bat_buoc_vai_tro('benh_nhan', $goc);
}

function bat_buoc_quan_tri($goc = '') {
    bat_buoc_vai_tro('quan_tri', $goc);
}

// Ghi một lời nhắn để hiện ở trang kế tiếp. $loai là 'loi' hoặc 'ok'
function dat_thong_bao($loai, $noidung) {
    $_SESSION['thong_bao'] = ['loai' => $loai, 'noidung' => $noidung];
}

// Nhớ lại dữ liệu người dùng đã gõ để điền lại vào form.
// Mật khẩu KHÔNG BAO GIỜ được lưu lại.
function luu_du_lieu_cu($mang) {
    unset($mang['matkhau'], $mang['nhaplai']);
    $_SESSION['du_lieu_cu'] = $mang;
}

// Lấy dữ liệu đã nhớ ra dùng, rồi xóa luôn để không dùng lại lần sau
function lay_du_lieu_cu() {
    $cu = $_SESSION['du_lieu_cu'] ?? [];
    unset($_SESSION['du_lieu_cu']);
    return $cu;
}

// Trang đầu tiên của mỗi vai trò sau khi đăng nhập
function trang_chu_theo_vai_tro($vaitro) {
    if ($vaitro == 'quan_tri') {
        return 'qt_trang_chu.php';
    }
    return 'index.php';  
}