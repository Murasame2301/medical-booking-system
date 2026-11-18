<?php
/* Anh dai dien: luu trong assets/uploads/avatar/, hien thi bang mot khung
   chung cho ca ba vai tro. */

require_once __DIR__ . '/utils.php';

if (!function_exists('anh_thu_muc_that')) {
    /**
     * Duong dan tuyet doi tren dia cua thu muc anh. Dung __DIR__ thay vi
     * SCRIPT_FILENAME de khong phu thuoc file nao dang duoc goi.
     */
    function anh_thu_muc_that(): string
    {
        return dirname(__DIR__, 2) . '/' . ANH_THU_MUC;
    }
}

if (!function_exists('chu_cai_viet_tat')) {
    /**
     * Chu cai first cua tu cuoi trong ho ten (ten rieng).
     */
    function chu_cai_viet_tat(string $hoTen): string
    {
        $tu = preg_split('/\s+/u', trim($hoTen), -1, PREG_SPLIT_NO_EMPTY);
        if (!$tu) {
            return '?';
        }

        return mb_strtoupper(mb_substr(end($tu), 0, 1, 'UTF-8'), 'UTF-8');
    }
}

if (!function_exists('lay_anh_dai_dien')) {
    /**
     * Duong dan web cua anh kem ?v=<mtime> de khong bi dinh cache cu.
     * Null neu chua co anh hoac tep khong con.
     */
    function lay_anh_dai_dien(?string $duongDan): ?string
    {
        static $boNho = [];

        $duongDan = (string) $duongDan;
        if (array_key_exists($duongDan, $boNho)) {
            return $boNho[$duongDan];
        }

        if ($duongDan === '') {
            return $boNho[$duongDan] = null;
        }

        $tep = dirname(__DIR__, 2) . '/' . ltrim($duongDan, '/');
        if (!is_file($tep)) {
            return $boNho[$duongDan] = null;
        }

        return $boNho[$duongDan] = $duongDan . '?v=' . filemtime($tep);
    }
}

if (!function_exists('html_avatar')) {
    /**
     * Khung avatar: co anh thi hien anh, chua co thi hien chu viet tat.
     * Anh duoc in san trong HTML nen hien ngay, khong bi nhay chu.
     */
    function html_avatar(?string $duongDanAnh, string $hoTen, string $lop = 'av-lon'): string
    {
        $anh = lay_anh_dai_dien($duongDanAnh);
        $chu = chu_cai_viet_tat($hoTen);
        $style = $anh
            ? ' style="background-image:url(\'' . h($anh) . '\');background-size:cover;background-position:center"'
            : '';

        return '<div class="' . h($lop) . '" data-anh="' . h((string) $anh) . '" data-chu="' . h($chu) . '"'
            . $style . '>' . ($anh ? '' : h($chu)) . '</div>';
    }
}
