<?php
require_once __DIR__ . '/../includes/khoitao.php';

header('Content-Type: application/json; charset=utf-8');

// Chỉ quản trị viên được dùng (không dùng bat_buoc_quan_tri vì hàm đó chuyển trang)
$la_quan_tri = isset($_SESSION['id']) && lay_vai_tro_cua($conn, $_SESSION['id']) == 'quan_tri';

if (!$la_quan_tri) {
    http_response_code(403);
    echo json_encode(['hop_le' => false, 'thong_bao' => 'Bạn không có quyền thực hiện thao tác này.']);
    exit;
}

$cot = $_GET['cot'] ?? '';
$giatri = $_GET['giatri'] ?? '';
$bo_qua_id = (int)($_GET['bo_qua_id'] ?? 0);

echo json_encode(kiem_tra_truong_nguoi_dung($conn, $cot, $giatri, $bo_qua_id));