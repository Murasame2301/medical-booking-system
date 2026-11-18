<?php

// Chong CSRF cho form POST: token gan voi session, khong phu thuoc framework.
// Cac entry point require_once file nay truoc khi xu ly POST.

if (!defined('CSRF_SESSION_KEY')) {
    define('CSRF_SESSION_KEY', 'csrf_token');
}

if (!function_exists('csrf_token')) {
    /**
     * Sinh (mot lan) va tra ve token cua session hien tai.
     */
    function csrf_token()
    {
        if (empty($_SESSION[CSRF_SESSION_KEY]) || !is_string($_SESSION[CSRF_SESSION_KEY])) {
            $_SESSION[CSRF_SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[CSRF_SESSION_KEY];
    }

    /**
     * Sinh lai token: goi sau session_regenerate_id() hoac khi doi thong tin dang nhap.
     */
    function csrf_rotate()
    {
        $_SESSION[CSRF_SESSION_KEY] = bin2hex(random_bytes(32));

        return $_SESSION[CSRF_SESSION_KEY];
    }

    /**
     * Tra ve hidden input de nhung vao form POST.
     */
    function csrf_field($form = '')
    {
        $attr = $form !== ''
            ? ' data-csrf-form="' . htmlspecialchars($form, ENT_QUOTES, 'UTF-8') . '"'
            : '';

        return '<input type="hidden" name="csrf_token" value="'
            . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '"' . $attr . '>';
    }

    /**
     * So sanh token POST voi token session (constant-time).
     */
    function csrf_validate($submitted = null)
    {
        $expected = $_SESSION[CSRF_SESSION_KEY] ?? '';
        $submitted = $submitted ?? ($_POST['csrf_token'] ?? '');

        if (!is_string($expected) || !is_string($submitted) || $expected === '' || $submitted === '') {
            return false;
        }

        return hash_equals($expected, $submitted);
    }
}
