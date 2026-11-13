<?php
require_once '../includes/khoitao.php';

header('Content-Type: application/json; charset=utf-8');

$sdt = trim($_GET['sdt'] ?? '');

if(!sdt_hop_le($sdt)){
    echo json_encode(['hop_le' => false, 'thong_bao' => 'So dien thoai 10 chu so va bat dau bang so 0']);
    exit;
}

if(da_co_nguoi_dung($conn, 'sdt', $sdt)){
    echo json_encode(['hop_le' => false, 'thong_bao' => 'So dien thoai nay da duoc dang ky']);
    exit;
}

echo json_encode(['hop_le' => true, 'thong_bao' => 'Co the dung so nay']);