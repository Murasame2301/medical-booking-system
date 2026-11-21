<?php
//Session đăng nhập, phân quyền và điều hướng dùng chung cho ba vai trò.

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/ajax.php';

if (!function_exists('da_dang_nhap')) {
    function da_dang_nhap(): bool
    {
        return !empty($_SESSION[SS_MA_NGUOI_DUNG]) && !empty($_SESSION[SS_VAI_TRO]);
    }
}

if (!function_exists('ma_nguoi_dung_hien_tai')) {
    function ma_nguoi_dung_hien_tai(): int
    {
        return (int) ($_SESSION[SS_MA_NGUOI_DUNG] ?? 0);
    }
}

if (!function_exists('vai_tro_hien_tai')) {
    function vai_tro_hien_tai(): int
    {
        return (int) ($_SESSION[SS_VAI_TRO] ?? 0);
    }
}

if (!function_exists('la_vai_tro')) {
    function la_vai_tro(int $vaiTro): bool
    {
        return da_dang_nhap() && vai_tro_hien_tai() === $vaiTro;
    }
}

if (!function_exists('ho_ten_hien_tai')) {
    function ho_ten_hien_tai(): string
    {
        return (string) ($_SESSION[SS_HO_TEN] ?? '');
    }
}

if (!function_exists('chuyen_trang')) {
    //Chuyển trang và dừng chương trình.
    function chuyen_trang(string $duongDan): void
    {
        header('Location: ' . $duongDan);
        exit;
    }
}

if (!function_exists('chuyen_trang_sau_post')) {
    //Chuyển trang sau form POST: 303 đổi thành GET để F5 không gửi lại.
    function chuyen_trang_sau_post(string $duongDan): void
    {
        header('Location: ' . $duongDan, true, 303);
        exit;
    }
}

if (!function_exists('mo_phien_dang_nhap')) {
    //Mở session cho một tài khoản vừa xác thực xong.
    function mo_phien_dang_nhap(array $nguoiDung): void
    {
        session_regenerate_id(true);
        csrf_rotate();
        $_SESSION[SS_MA_NGUOI_DUNG] = (int) $nguoiDung['manguoidung'];
        $_SESSION[SS_VAI_TRO] = (int) $nguoiDung['mavaitro'];
        $_SESSION[SS_HO_TEN] = (string) $nguoiDung['hoten'];
    }
}

if (!function_exists('dang_xuat')) {
    //Xoá toàn bộ session và về trang đăng nhập.
    function dang_xuat(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $thamSoCookie = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $thamSoCookie['path'],
                $thamSoCookie['domain'],
                $thamSoCookie['secure'],
                $thamSoCookie['httponly']
            );
        }

        session_destroy();
        chuyen_trang('index.php?page=dang-nhap');
    }
}

if (!function_exists('tra_loi_chua_dang_nhap')) {
    //Chưa đăng nhập hoặc hết session. Request AJAX nhận JSON thay vì bị chuyển trang.
    function tra_loi_chua_dang_nhap(string $thongDiep = 'Phiên đăng nhập đã hết hạn, vui lòng đăng nhập lại.'): void
    {
        if (ajax_is_request()) {
            tra_json(ket_qua(false, $thongDiep), 401);
        }

        $_SESSION['auth_message'] = 'Vui lòng đăng nhập để tiếp tục.';
        chuyen_trang('index.php?page=dang-nhap');
    }
}

if (!function_exists('tra_loi_khong_du_quyen')) {
    function tra_loi_khong_du_quyen(): void
    {
        if (ajax_is_request()) {
            tra_json(ket_qua(false, 'Bạn không có quyền thực hiện thao tác này.'), 403);
        }

        http_response_code(403);
        echo 'Bạn không có quyền truy cập trang này.';
        exit;
    }
}

if (!function_exists('bat_buoc_dang_nhap')) {
    //Bắt buộc đã đăng nhập và đúng vai trò (nếu có).
    //Trả về mã người dùng.
    function bat_buoc_dang_nhap(?int $vaiTro = null): int
    {
        if (!da_dang_nhap()) {
            tra_loi_chua_dang_nhap();
        }

        if ($vaiTro !== null && vai_tro_hien_tai() !== $vaiTro) {
            tra_loi_khong_du_quyen();
        }

        return ma_nguoi_dung_hien_tai();
    }
}

if (!function_exists('trang_chu_theo_vai_tro')) {
    function trang_chu_theo_vai_tro(int $vaiTro): string
    {
        return 'index.php?page=' . (TRANG_CHU_THEO_VAI_TRO[$vaiTro] ?? 'dang-nhap');
    }
}