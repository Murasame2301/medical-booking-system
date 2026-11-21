<?php
//Ảnh đại diện: lưu trong assets/uploads/avatar/

require_once __DIR__ . '/utils.php';

if (!function_exists('anh_thu_muc_that')) {
    //Đường dẫn tuyệt đối trên đĩa của thư mục ảnh.
    function anh_thu_muc_that(): string
    {
        return dirname(__DIR__, 2) . '/' . ANH_THU_MUC;
    }
}

if (!function_exists('chu_cai_viet_tat')) {
    //Chữ cái đầu của tên riêng (từ cuối trong họ tên).
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
    //Đường dẫn web của ảnh kèm ?v=<mtime> để không dính cache cũ. Null nếu chưa có ảnh.
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
    //Khung avatar: có ảnh thì hiện ảnh, chưa có thì hiện chữ viết tắt.
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