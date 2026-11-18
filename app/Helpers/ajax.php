<?php

// Ho tro phan hoi mot phan (AJAX) cho cac luong: loc khung gio, huy lich,
// dat lich, luu ho so. Moi controller van giu nguyen nhanh render HTML day du;
// nhanh AJAX chi la mot duong ra khac khi request mang header X-Requested-With.

if (!function_exists('ajax_is_request')) {
    function ajax_is_request()
    {
        $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';

        if (!is_string($requestedWith)) {
            return false;
        }

        return strtolower(trim($requestedWith)) === 'xmlhttprequest';
    }
}

if (!function_exists('ajax_json')) {
    /**
     * Gui JSON va ket thuc request. Khong bao gio tra HTML o nhanh nay,
     * de JS khong vo tinh chen ca trang vao mot vung nho.
     */
    function ajax_json(array $payload, $status = 200)
    {
        $status = (int) $status;
        if ($status < 100 || $status > 599) {
            $status = 200;
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        echo json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
        );
        exit;
    }
}

if (!function_exists('render_fragment')) {
    /**
     * Render mot view/partial thanh chuoi. Dung de tra markup da escape va
     * format san tu PHP thay vi viet lai cac closure format bang JS.
     */
    function render_fragment($__file, array $__vars = [])
    {
        extract($__vars, EXTR_SKIP);

        ob_start();
        require $__file;

        return (string) ob_get_clean();
    }
}
