<?php
/* Chong CSRF cho form POST va request AJAX.
   Token gan voi session. Form gui qua o an csrf_token; fetch() gui qua
   header X-CSRF-Token (xem assets/js/bac-si/api.js). */

if (!defined('CSRF_SESSION_KEY')) {
    define('CSRF_SESSION_KEY', 'csrf_token');
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION[CSRF_SESSION_KEY]) || !is_string($_SESSION[CSRF_SESSION_KEY])) {
            $_SESSION[CSRF_SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[CSRF_SESSION_KEY];
    }
}

if (!function_exists('csrf_rotate')) {
    /**
     * Sinh lai token: goi sau session_regenerate_id() hoac sau khi doi quyen.
     */
    function csrf_rotate(): string
    {
        $_SESSION[CSRF_SESSION_KEY] = bin2hex(random_bytes(32));

        return $_SESSION[CSRF_SESSION_KEY];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Hidden input de nhung vao form POST.
     */
    function csrf_field(): string
    {
        return '<input type="hidden" name="csrf_token" value="'
            . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (!function_exists('csrf_gia_tri_gui_len')) {
    /**
     * Token client gui len: uu tien o an trong form, sau do den header cho AJAX.
     */
    function csrf_gia_tri_gui_len(): string
    {
        $tuForm = $_POST['csrf_token'] ?? null;
        if (is_string($tuForm) && $tuForm !== '') {
            return $tuForm;
        }

        $tuHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        return is_string($tuHeader) ? $tuHeader : '';
    }
}

if (!function_exists('csrf_validate')) {
    /**
     * So sanh token client gui voi token session (constant-time).
     */
    function csrf_validate(): bool
    {
        $expected = $_SESSION[CSRF_SESSION_KEY] ?? '';
        $submitted = csrf_gia_tri_gui_len();

        if (!is_string($expected) || $expected === '' || $submitted === '') {
            return false;
        }

        return hash_equals($expected, $submitted);
    }
}
