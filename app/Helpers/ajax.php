<?php
//Hỗ trợ phản hồi AJAX cho các luồng lọc khung giờ, huỷ lịch, đặt lịch, lưu hồ sơ.

if (!function_exists('ajax_is_request')) {
    function ajax_is_request(): bool
    {
        //Cùng định nghĩa với $laAjax ở index.php: có tham số ?ajax là đủ.
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
    //Gửi JSON và kết thúc request. Không trả HTML ở nhánh này.
    function ajax_json(array $payload, int $status = 200): void
    {
        if ($status < 100 || $status > 599) {
            $status = 200;
        }

        tra_json($payload, $status);
    }
}
