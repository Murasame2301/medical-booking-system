<?php
//Các endpoint AJAX của quản trị

require_once __DIR__ . '/__nap.php';

//Tìm người dùng: trả về HTML bảng (không phải JSON).
function quan_tri_ajax_tim_nguoi_dung($conn)
{
    [$tukhoa, $vaitro] = quan_tri_doc_bo_loc();

    $html = render_fragment(__DIR__ . '/../../Views/quan-tri/partials/bang-nguoi-dung.php', [
        'ds' => quan_tri_lay_nguoi_dung($conn, $tukhoa, $vaitro),
        'tukhoa' => $tukhoa,
        'vaitro' => $vaitro,
        'nguoiHienTai' => ma_nguoi_dung_hien_tai(),
    ]);

    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store');
    echo $html;
    exit;
}

//Mở thẳng đường dẫn tìm kiếm (không AJAX): về trang danh sách.
function quan_tri_tim_nguoi_dung_trang($conn)
{
    [$tukhoa, $vaitro] = quan_tri_doc_bo_loc();
    chuyen_trang(quan_tri_url_ve_danh_sach($tukhoa, $vaitro));
}

//Khoá, mở khoá tài khoản. Trả JSON.
function quan_tri_ajax_khoa_mo($conn)
{
    if (!la_vai_tro(VT_QUAN_TRI)) {
        tra_json(ket_qua(false, 'Bạn không có quyền thực hiện thao tác này.'), 403);
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        tra_json(ket_qua(false, 'Chỉ chấp nhận yêu cầu POST.'), 405);
    }

    $kq = quan_tri_doi_trang_thai_tai_khoan(
        $conn,
        chung_so_nguyen($_POST['manguoidung'] ?? 0),
        chung_chuoi($_POST['hanhdong'] ?? ''),
        ma_nguoi_dung_hien_tai()
    );

    if (!$kq['ok']) {
        tra_json($kq, 400);
    }

    tra_json($kq);
}

//Kiểm tra trùng SĐT, email cho form bác sĩ -> Trả JSON.
function quan_tri_ajax_kiem_tra_trung($conn)
{
    $cot = chung_chuoi($_GET['cot'] ?? '');
    $giatri = chung_chuoi($_GET['giatri'] ?? '');
    $boQuaMa = chung_so_nguyen($_GET['bo_qua_id'] ?? 0);

    tra_json(quan_tri_kiem_tra_truong($conn, $cot, $giatri, $boQuaMa));
}

//Mở thẳng đường dẫn kiểm tra trùng (không AJAX): về form bác sĩ.
function quan_tri_kiem_tra_trung_trang($conn)
{
    $ma = chung_so_nguyen($_GET['bo_qua_id'] ?? 0);
    chuyen_trang($ma > 0
        ? 'index.php?page=quan-tri/bac-si/sua&id=' . $ma
        : 'index.php?page=quan-tri/bac-si/them');
}