<?php
//Truy vấn bảng HO_SO_BENH_NHAN: kết quả khám của một lượt khám.

//Hồ sơ khám đã lập của một lịch khám (rỗng nếu chưa có).
function ho_so_kham_lay($conn, int $maLichKham): array
{
    $hoSo = truy_van_mot(
        $conn,
        "SELECT chuandoan, loidan, ngaylap FROM HO_SO_BENH_NHAN WHERE malichkham = ?",
        'i',
        [$maLichKham]
    ) ?? ['chuandoan' => '', 'loidan' => '', 'ngaylap' => null];

    $hoSo['chuandoan'] = (string) $hoSo['chuandoan'];
    $hoSo['loidan'] = (string) $hoSo['loidan'];

    return $hoSo;
}

//Ghi hồ sơ khám (upsert vì UNIQUE(malichkham)).
//Phải gọi trong transaction của nơi gọi.
function ho_so_kham_luu($conn, int $maLichKham, string $chuanDoan, string $loiDan): void
{
    thi_hanh(
        $conn,
        "INSERT INTO HO_SO_BENH_NHAN (malichkham, chuandoan, loidan, ngaylap)
         VALUES (?, ?, ?, NOW())
         ON DUPLICATE KEY UPDATE
           chuandoan = VALUES(chuandoan),
           loidan    = VALUES(loidan),
           ngaylap   = NOW()",
        'iss',
        [$maLichKham, $chuanDoan, $loiDan]
    )->close();
}