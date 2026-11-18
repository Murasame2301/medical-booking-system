<?php
/* Cac endpoint AJAX cua khu quan tri. Router da kiem tra dang nhap, vai tro
   quan tri va CSRF truoc khi toi day. Moi route AJAX khai bao trong
   config/routes.php: 'ham' xu ly khi khong phai AJAX (chuyen ve trang chinh),
   'xu_ly' xu ly request AJAX. */

require_once __DIR__ . '/__nap.php';

/* Tim nguoi dung: tra ve HTML bang (khong phai JSON). */
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

/* Khi mo thang duong dan tim kiem (khong AJAX): ve trang danh sach. */
function quan_tri_tim_nguoi_dung_trang($conn)
{
    [$tukhoa, $vaitro] = quan_tri_doc_bo_loc();
    chuyen_trang(quan_tri_url_ve_danh_sach($tukhoa, $vaitro));
}

/* Khoa / mo khoa tai khoan, tra JSON. */
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

/* Kiem tra trung mot truong (sdt / email) cho form bac si, tra JSON. */
function quan_tri_ajax_kiem_tra_trung($conn)
{
    $cot = chung_chuoi($_GET['cot'] ?? '');
    $giatri = chung_chuoi($_GET['giatri'] ?? '');
    $boQuaMa = chung_so_nguyen($_GET['bo_qua_id'] ?? 0);

    tra_json(quan_tri_kiem_tra_truong($conn, $cot, $giatri, $boQuaMa));
}

/* Mo thang duong dan kiem tra trung (khong AJAX): ve form bac si. */
function quan_tri_kiem_tra_trung_trang($conn)
{
    $ma = chung_so_nguyen($_GET['bo_qua_id'] ?? 0);
    chuyen_trang($ma > 0
        ? 'index.php?page=quan-tri/bac-si/sua&id=' . $ma
        : 'index.php?page=quan-tri/bac-si/them');
}
