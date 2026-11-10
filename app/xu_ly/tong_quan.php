<?php
require_once __DIR__ . '/../chung/hang_so.php';
require_once __DIR__ . '/../chung/co_so_du_lieu.php';
require_once __DIR__ . '/../chung/tien_ich.php';


function thong_ke_tong_quan(int $maBacSi, string $ngay): array
{
  if (!hop_le_ngay($ngay)) ket_qua(false, 'Ngày không hợp lệ.');
  // tong so luot va so luot cua trang thai
  $sql = "SELECT SUM(lk.trang_thai <> ?) AS tong_luot,
                SUM(lk.trang_thai = ?) AS cho_xac_nhan,
                SUM(lk.trang_thai = ?) AS da_kham
          FROM lich_kham lk JOIN lich_lam_viec llv ON lk.malichlamviec = llv.malichlamviec
          WHERE llv.manguoidung = ? AND DATE(llv.thoigianbatdau) = ?";

  $r = truy_van_mot($sql, 'sssis', [LK_DA_HUY, LK_CHO_XAC_NHAN, LK_DA_KHAM, $maBacSi]);

  // dem lich con_trong trong ngay hien tai
  $sql2 = "SELECT COUNT(*) AS so_luong
          FROM lich_lam_viec llv
          WHERE llv.manguoidung = ? AND DATE(llv.thoigianbatdau) = ? AND llv.trangthai = ?
          AND NOT EXISTS(
            SELECT 1 
            FROM lich_kham lk
            WHERE lk.malichlamviec = llv.malichlamviec AND lk.trangthai <> ?
            )";

  $r = truy_van_mot($sql2, 'isss', [$maBacSi, $ngay, LLV_CON_TRONG, LK_DA_HUY]);

  return ket_qua(true, '', [
    'tong_luot' => (int)($r['tong_luot'] ?? 0),
    'cho_xac_nhan' => (int)($r['cho_xac_nhan'] ?? 0),
    'da_kham' => (int)($r['da_kham'] ?? 0),
    'con_trong' => (int)($r['con_trong'] ?? 0),
  ]);
}

// lay lich kham cua mot ngay hoac nhieu ngay 
function lay_lich_kham_theo_ngay(int $maBacSi, string $ngay, bool $boDaHuy = true): array
{
  if (!hop_le_ngay($ngay)) return ket_qua(false, "Ngày không hợp lệ.");
  $batDau = ghep_ngay_gio($ngay, '00:00');
  $ketThuc = ghep_ngay_gio($ngay, '23:59', ':59');


  $sql = "SELECT 
                llv.manguoidung AS ma_bac_si, 
                nd.hoten AS ho_ten_benh_nhan,
                lk.malichkham, 
                lk.lydo, 
                lk.trangthai,
                TIME(llv.thoigianbatdau) AS gio_bat_dau
            FROM LICH_KHAM lk JOIN LICH_LAM_VIEC llv ON lk.malichlamviec = llv.malichlamviec
                              JOIN NGUOI_DUNG nd ON lk.manguoidung = nd.manguoidung
            WHERE llv.manguoidung = ?  AND TIME(llv.thoigianbatdau) BETWEEN ? AND ?";

  $kieu = 'iss';
  $tham = [$maBacSi, $batDau, $ketThuc];

  if ($boDaHuy) {
    $sql .= " AND lk.trangthai <> ?";
    $kieu .= "s";
    $tham[] = LK_DA_HUY;
  }

  //  sap xep theo thoigian
  $sql .= " ORDER BY llv.thoigianbatdau";

  return ket_qua(true, '', truy_van($sql, $kieu, $tham));
}
