<?php
require_once __DIR__ . '/../includes/khoitao.php';

header('Content-Type: application/json; charset=utf-8');

$la_quan_tri = isset($_SESSION['id']) && lay_vai_tro_cua($conn, $_SESSION['id']) == 'quan_tri';

if(!$la_quan_tri){
    http_response_code(403);
    echo json_encode([
        'ok' => false,
        'thong_bao' => 'Ban khong co quyen thuc hien thao tac nay',
    ]);
    exit;
}

if($_SERVER['REQUEST_METHOD'] != 'POST'){
    http_response_code(405);
    echo json_encode([
        'ok' => false,
        'thong_bao' => 'Chi chap nhan yeu cau POST',
    ]);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$hanhdong = $_POST['hanhdong'] ?? '';

echo json_encode(doi_trang_thai_tai_khoan($conn, $id, $hanhdong, $_SESSION['id']));