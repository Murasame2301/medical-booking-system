<?php

// Cac ham dung chung cho controller: chuyen kieu du lieu, chuyen trang, flash,
// ket thuc phien. Truoc day cac ham nay nam rai rac trong tung controller duoi
// dang private method; gio gom ve mot cho de khong lap lai.

/**
 * Chuoi da trim, hoac '' neu gia tri khong phai chuoi.
 */
function chung_chuoi($value)
{
    return is_string($value) ? trim($value) : '';
}

/**
 * So nguyen duong; null neu khong hop le.
 */
function chung_so_duong($value)
{
    if (is_int($value) && $value > 0) {
        return $value;
    }

    if (is_string($value) && ctype_digit($value) && (int) $value > 0) {
        return (int) $value;
    }

    return null;
}

/**
 * So nguyen duong; 0 neu khong hop le.
 */
function chung_so_nguyen($value)
{
    $so = chung_so_duong($value);

    return $so === null ? 0 : $so;
}

/**
 * Do dai tinh theo ky tu (khong phai byte) cho khop VARCHAR(n) va maxlength cua form.
 */
function chung_do_dai($value)
{
    return function_exists('mb_strlen')
        ? mb_strlen((string) $value, 'UTF-8')
        : strlen((string) $value);
}

/**
 * Ghi loi nhac de hien o lan tai trang ke tiep.
 */
function chung_dat_flash($key, $message, $type, $extra = [])
{
    $_SESSION[$key] = array_merge(
        [
            'message' => $message,
            'type' => $type,
        ],
        $extra
    );
}

/**
 * Doc loi nhac da ghi, roi xoa de khong hien lai lan sau.
 */
function chung_doc_flash($key)
{
    $flash = $_SESSION[$key] ?? [];
    unset($_SESSION[$key]);

    return is_array($flash) ? $flash : [];
}

/**
 * Chuyen trang va dung chuong trinh.
 */
function chung_chuyen_trang($url)
{
    header('Location: ' . $url);
    exit;
}

/**
 * Chuyen trang sau khi gui form POST: 303 doi thanh GET de F5 khong gui lai.
 */
function chung_chuyen_trang_sau_post($url)
{
    header('Location: ' . $url, true, 303);
    exit;
}

/**
 * Chuyen trang khi chua dang nhap / het phien / sai vai tro.
 * Request AJAX nhan JSON "redirect" thay vi 302, vi fetch se di theo 302
 * va nhan ve ca trang HTML.
 */
function chung_chuyen_trang_bao_loi($url, $message)
{
    if (function_exists('ajax_is_request') && ajax_is_request()) {
        ajax_json([
            'ok' => false,
            'redirect' => $url,
            'message' => $message,
        ]);
    }

    chung_chuyen_trang($url);
}

/**
 * Xoa toan bo phien roi ve trang dang nhap.
 */
function chung_ket_thuc_phien($message = 'Phiên đăng nhập đã kết thúc. Vui lòng đăng nhập lại.')
{
    $_SESSION = [];
    session_destroy();
    chung_chuyen_trang_bao_loi('dang-nhap.php', $message);
}
