<?php
//Trang tổng quan quản trị.

require_once __DIR__ . '/__nap.php';

function quan_tri_tong_quan($conn)
{
    $tk = quan_tri_thong_ke($conn);

    return [
        'flash' => quan_tri_lay_flash(),
        'the' => [
            ['so' => $tk['benh_nhan'], 'tieu_de' => 'Bệnh nhân', 'lien_ket' => 'index.php?page=quan-tri/nguoi-dung&vaitro=' . VT_BENH_NHAN, 'chu' => 'Quản lý'],
            ['so' => $tk['bac_si'], 'tieu_de' => 'Bác sĩ', 'lien_ket' => 'index.php?page=quan-tri/bac-si', 'chu' => 'Quản lý'],
            ['so' => $tk['lich_cho'], 'tieu_de' => 'Lịch chờ xác nhận', 'lien_ket' => '', 'chu' => ''],
            ['so' => $tk['tai_khoan_khoa'], 'tieu_de' => 'Tài khoản bị khóa', 'lien_ket' => 'index.php?page=quan-tri/nguoi-dung', 'chu' => 'Xem'],
        ],
    ];
}
