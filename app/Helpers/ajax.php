<?php
/* Ho tro phan hoi mot phan (AJAX) cho cac luong loc khung gio, huy lich,
   dat lich, luu ho so. Controller van giu nguyen nhanh render HTML day du;
   nhanh AJAX chi la mot duong ra khac khi request mang header
   X-Requested-With: XMLHttpRequest. */

if (!function_exists('ajax_is_request')) {
    function ajax_is_request(): bool
    {
        // Cung dinh nghia voi $laAjax o index.php: co tham so ?ajax la du
        // (JS cua khu bac si gui ca tham so lan header).
        if (isset($_GET['ajax'])) {
            return true;
        }

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
    function ajax_json(array $payload, int $status = 200): void
    {
        if ($status < 100 || $status > 599) {
            $status = 200;
        }

        tra_json($payload, $status);
    }
}
