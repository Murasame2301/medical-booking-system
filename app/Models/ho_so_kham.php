<?php
/* Truy van bang ho_so_benh_nhan: ket qua kham cua mot luot kham.
   Moi ham nhan $conn lam tham so dau tien. */

/**
 * Ho so kham da lap cua mot lich kham (rong neu chua co).
 */
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

/**
 * Ghi ho so kham (bang co UNIQUE(malichkham) nen dung upsert).
 * Phai goi ben trong transaction cua nguoi goi.
 */
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
