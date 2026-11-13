<?php
require_once __DIR__ . '/../chung/hang_so.php';
require_once __DIR__ . '/../chung/co_so_du_lieu.php';
require_once __DIR__ . '/../chung/tien_ich.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function thong_ke_tong_quan(int $maBacSi, string $ngay): array
{
  if (!hop_le_ngay($ngay)) return ket_qua(false, 'Ngày không hợp lệ.');

  // tong so luot va so luot theo trang thai (5 dau ? khop voi 5 tham so)
  $sql = "SELECT SUM(lk.trangthai <> ?) AS tong_luot,
                 SUM(lk.trangthai = ?) AS cho_xac_nhan,
                 SUM(lk.trangthai = ?) AS da_kham
          FROM lich_kham lk JOIN lich_lam_viec llv ON lk.malichlamviec = llv.malichlamviec
          WHERE llv.manguoidung = ? AND DATE(llv.thoigianbatdau) = ?";

  $rThongKe = truy_van_mot($sql, 'sssis', [
    LK_DA_HUY,
    LK_CHO_XAC_NHAN,
    LK_DA_KHAM,
    $maBacSi,
    $ngay,
  ]);

  // dem khung gio con trong trong ngay (mac dinh la hom nay)
  $sql2 = "SELECT COUNT(*) AS con_trong
           FROM lich_lam_viec llv
           WHERE llv.manguoidung = ? AND DATE(llv.thoigianbatdau) = ? AND llv.trangthai = ?
             AND NOT EXISTS (
               SELECT 1
               FROM lich_kham lk
               WHERE lk.malichlamviec = llv.malichlamviec AND lk.trangthai <> ?
             )";

  $rTrong = truy_van_mot($sql2, 'isss', [$maBacSi, $ngay, LLV_CON_TRONG, LK_DA_HUY]);

  // ten khoa khop voi JS: tong_luot, cho_xac_nhan, da_kham, khung_gio_trong
  return ket_qua(true, '', [
    'tong_luot'       => (int)($rThongKe['tong_luot'] ?? 0),
    'cho_xac_nhan'    => (int)($rThongKe['cho_xac_nhan'] ?? 0),
    'da_kham'         => (int)($rThongKe['da_kham'] ?? 0),
    'khung_gio_trong' => (int)($rTrong['con_trong'] ?? 0),
  ]);
}

// lay lich kham cua mot ngay
function lay_lich_kham_theo_ngay(int $maBacSi, string $ngay, bool $boDaHuy = true): array
{
  if (!hop_le_ngay($ngay)) return ket_qua(false, 'Ngày không hợp lệ.');

  $batDau  = ghep_ngay_gio($ngay, '00:00');
  $ketThuc = ghep_ngay_gio($ngay, '23:59', ':59');

  $sql = "SELECT
                llv.manguoidung AS ma_bac_si,
                nd.hoten AS ho_ten_benh_nhan,
                lk.malichkham,
                lk.lydo,
                lk.trangthai,
                TIME(llv.thoigianbatdau) AS gio_bat_dau,
                TIME(llv.thoigianketthuc) AS gio_ket_thuc
          FROM lich_kham lk
          JOIN lich_lam_viec llv ON lk.malichlamviec = llv.malichlamviec
          JOIN nguoi_dung nd ON lk.manguoidung = nd.manguoidung
          WHERE llv.manguoidung = ? AND llv.thoigianbatdau BETWEEN ? AND ?";

  $kieu = 'iss';
  $tham = [$maBacSi, $batDau, $ketThuc];

  if ($boDaHuy) {
    $sql .= " AND lk.trangthai <> ?";
    $kieu .= 's';
    $tham[] = LK_DA_HUY;
  }

  // sap xep theo thoi gian
  $sql .= " ORDER BY llv.thoigianbatdau";

  return ket_qua(true, '', truy_van($sql, $kieu, $tham));
}

// lay danh sach khung gio con trong cua mot ngay (cung dieu kien voi dem con_trong)
function lay_lich_con_trong(int $maBacSi, string $ngay): array
{
  if (!hop_le_ngay($ngay)) return ket_qua(false, 'Ngày không hợp lệ.');

  $sql = "SELECT llv.malichlamviec,
                 TIME(llv.thoigianbatdau) AS gio_bat_dau,
                 TIME(llv.thoigianketthuc) AS gio_ket_thuc
          FROM lich_lam_viec llv
          WHERE llv.manguoidung = ? AND DATE(llv.thoigianbatdau) = ? AND llv.trangthai = ?
            AND NOT EXISTS (
              SELECT 1
              FROM lich_kham lk
              WHERE lk.malichlamviec = llv.malichlamviec AND lk.trangthai <> ?
            )
          ORDER BY llv.thoigianbatdau";

  return ket_qua(true, '', truy_van($sql, 'isss', [$maBacSi, $ngay, LLV_CON_TRONG, LK_DA_HUY]));
}

/* XU LY AJAX: mot request tra ve thong ke, danh sach lich va khung gio con trong */
if (($_GET['ajax'] ?? '') === 'tong-quan') {
  $ngay = (string)($_GET['ngay'] ?? date('Y-m-d'));
  // NOTE LAI: COI LAI FILE DANG NHAP CHINH THUC
  $manguoidung = (int)($_SESSION['ma_nguoi_dung'] ?? 0);

  if ($manguoidung <= 0) {
    tra_json(ket_qua(false, 'Không tìm thấy thông tin bác sĩ.'));
  }

  try {
    $thongKe = thong_ke_tong_quan($manguoidung, $ngay);
    if (!$thongKe['ok']) {
      tra_json($thongKe);
    }

    $lich = lay_lich_kham_theo_ngay($manguoidung, $ngay);
    if (!$lich['ok']) {
      tra_json($lich);
    }

    $lichTrong = lay_lich_con_trong($manguoidung, $ngay);
    if (!$lichTrong['ok']) {
      tra_json($lichTrong);
    }

    tra_json(ket_qua(true, '', [
      'thong_ke'       => $thongKe['data'],
      'danh_sach_lich' => $lich['data'],
      'lich_con_trong' => $lichTrong['data'],
    ]));
  } catch (Throwable $e) {
    // loi DB/PHP van tra JSON hop le de JS hien thong bao, khong vo giao dien
    error_log('tong-quan: ' . $e->getMessage());
    tra_json(ket_qua(false, 'Lỗi máy chủ, vui lòng thử lại.'));
  }
}