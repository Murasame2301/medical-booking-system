<?php
require_once __DIR__ . '/../Core/khoitao.php';

header('Content-Type: application/json; charset=utf-8');

$sdt = trim($_GET['sdt'] ?? '');

if(!sdt_hop_le($sdt)){
    echo json_encode(['hop_le' => false, 'thong_bao' => 'Số điện thoại 10 chữ số và bắt đầu bằng số 0']);
    exit;
}

if(da_co_nguoi_dung($conn, 'sdt', $sdt)){
    echo json_encode(['hop_le' => false, 'thong_bao' => 'Số điện thoại này đã được đăng ký']);
    exit;
}

echo json_encode(['hop_le' => true, 'thong_bao' => 'Có thể dùng số này']);