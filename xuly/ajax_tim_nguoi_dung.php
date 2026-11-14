<?php
require_once __DIR__ . '/../includes/khoitao.php';

// Chỉ quản trị viên được dùng (không dùng bat_buoc_quan_tri vì hàm đó chuyển trang)
$la_quan_tri = isset($_SESSION['id']) && lay_vai_tro_cua($conn, $_SESSION['id']) == 'quan_tri';

if (!$la_quan_tri) {
    http_response_code(403);
    echo 'Bạn không có quyền thực hiện thao tác này.';
    exit;
}

$tukhoa = trim($_GET['tukhoa'] ?? '');
$vaitro = (int)($_GET['vaitro'] ?? 0);

hien_bang_nguoi_dung(lay_ds_nguoi_dung($conn, $tukhoa, $vaitro), $tukhoa, $vaitro);