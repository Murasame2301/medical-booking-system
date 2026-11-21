<?php
//Quản lý người dùng

require_once __DIR__ . '/__nap.php';

//Đọc bộ lọc từ GET. Dùng chung cho trang và endpoint AJAX.
function quan_tri_doc_bo_loc()
{
    $tukhoa = chung_chuoi($_GET['tukhoa'] ?? '');
    if (chung_do_dai($tukhoa) > 100) {
        $tukhoa = mb_substr($tukhoa, 0, 100, 'UTF-8');
    }

    return [$tukhoa, (int) chung_so_nguyen($_GET['vaitro'] ?? 0)];
}

function quan_tri_nguoi_dung($conn)
{
    [$tukhoa, $vaitro] = quan_tri_doc_bo_loc();

    return [
        'flash' => quan_tri_lay_flash(),
        'tukhoa' => $tukhoa,
        'vaitro' => $vaitro,
        'ds_vaitro' => quan_tri_lay_vai_tro($conn),
        'ds' => quan_tri_lay_nguoi_dung($conn, $tukhoa, $vaitro),
    ];
}

//Đường quay về danh sách, giữ nguyên ô tìm kiếm và bộ lọc.
function quan_tri_url_ve_danh_sach($tukhoa, $vaitro)
{
    return 'index.php?' . http_build_query([
        'page' => 'quan-tri/nguoi-dung',
        'tukhoa' => $tukhoa,
        'vaitro' => $vaitro,
    ]);
}

//Khoá, mở khoá khi JS bị tắt: form POST thường, xong thì chuyển trang.
function quan_tri_khoa_mo_tai_khoan($conn)
{
    $tukhoa = chung_chuoi($_POST['tukhoa'] ?? '');
    $vaitro = chung_so_nguyen($_POST['vaitro'] ?? 0);
    $ve = quan_tri_url_ve_danh_sach($tukhoa, $vaitro);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        chuyen_trang($ve);
    }

    $kq = quan_tri_doi_trang_thai_tai_khoan(
        $conn,
        chung_so_nguyen($_POST['manguoidung'] ?? 0),
        chung_chuoi($_POST['hanhdong'] ?? ''),
        ma_nguoi_dung_hien_tai()
    );

    quan_tri_dat_flash($kq['ok'] ? 'ok' : 'loi', $kq['msg']);
    chuyen_trang_sau_post($ve);
}