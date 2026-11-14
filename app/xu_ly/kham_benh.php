<?php

if (!function_exists('lay_lich_kham')) {
  function lay_lich_kham(int $maBacSi, int $maLichKham): ?array
  {
    return truy_van_mot(
      "SELECT lk.malichkham, lk.trangthai, llv.thoigianbatdau
       FROM lich_kham lk
       JOIN lich_lam_viec llv ON lk.malichlamviec = llv.malichlamviec
       WHERE lk.malichkham = ? AND llv.manguoidung = ?",
      'ii',
      [$maLichKham, $maBacSi]
    );
  }
}

// chi lay cac ca DA XAC NHAN cua ngay hom nay
function lay_ca_kham_hom_nay(int $maBacSi): array
{
  $homNay  = date('Y-m-d');
  $ngayMai = date('Y-m-d', strtotime('+1 day'));

  $ds = truy_van(
    "SELECT lk.malichkham      AS ma,
            llv.thoigianbatdau  AS gio_bat_dau,
            llv.thoigianketthuc AS gio_ket_thuc,
            lk.lydo             AS ly_do,
            lk.trangthai        AS trang_thai,
            nd.hoten            AS ten,
            nd.ngaysinh         AS ngay_sinh,
            nd.gioitinh         AS gioi_tinh,
            nd.sdt              AS sdt
     FROM lich_lam_viec llv
     JOIN lich_kham lk  ON llv.malichlamviec = lk.malichlamviec
     JOIN nguoi_dung nd ON lk.manguoidung = nd.manguoidung
     WHERE llv.manguoidung = ?
       AND llv.thoigianbatdau >= ?
       AND llv.thoigianbatdau <  ?
       AND lk.trangthai = ?
     ORDER BY llv.thoigianbatdau ASC, lk.malichkham ASC",
    'isss',
    [
      $maBacSi,
      ghep_ngay_gio($homNay, '00:00'),
      ghep_ngay_gio($ngayMai, '00:00'),
      LK_DA_XAC_NHAN,
    ]
  );

  foreach ($ds as &$k) {
    $k['ma']           = (int)$k['ma'];
    $k['gio_bat_dau']  = substr((string)$k['gio_bat_dau'], 11, 5);
    $k['gio_ket_thuc'] = substr((string)$k['gio_ket_thuc'], 11, 5);
    $k['ngay_sinh']    = $k['ngay_sinh'] ? dinh_dang_ngay((string)$k['ngay_sinh']) : '';
    $k['ly_do']        = (string)$k['ly_do'];
    $k['sdt']          = (string)$k['sdt'];
  }
  unset($k);

  return ket_qua(true, '', $ds);
}

// hoan tat kham, them du lieu tham vao
function hoan_thanh_kham(int $maLichKham, int $maBacSi, string $chuanDoan, string $loiDan): array
{
  $chuanDoan = trim($chuanDoan);
  $loiDan    = trim($loiDan);
  if ($chuanDoan === '') return ket_qua(false, 'Vui lòng nhập chẩn đoán.');

  $lichKham = lay_lich_kham($maBacSi, $maLichKham);
  if ($lichKham === null) return ket_qua(false, 'Không tìm thấy lịch khám.');
  if ($lichKham['trangthai'] !== LK_DA_XAC_NHAN) {
    return ket_qua(false, 'Chỉ khám được lượt đã xác nhận.');
  }
  // chi kham cho ca cua ngay hien tai
  if (substr((string)$lichKham['thoigianbatdau'], 0, 10) !== date('Y-m-d')) {
    return ket_qua(false, 'Chỉ được khám các ca trong ngày hôm nay.');
  }

  $mysqli = ket_noi_db();
  $mysqli->begin_transaction();
  try {
    $stmt = thi_hanh(
      "UPDATE lich_kham SET trangthai = ? WHERE malichkham = ? AND trangthai = ?",
      'sis',
      [LK_DA_KHAM, $maLichKham, LK_DA_XAC_NHAN]
    );
    $soDong = $stmt->affected_rows;
    $stmt->close();

    if ($soDong !== 1) {
      $mysqli->rollback();
      return ket_qua(false, 'Lượt khám đã được xử lý trước đó.');
    }

    // ho_so_benh_nhan có UNIQUE(malichkham) -> upsert
    thi_hanh(
      "INSERT INTO ho_so_benh_nhan (malichkham, chuandoan, loidan, ngaylap)
       VALUES (?, ?, ?, NOW())
       ON DUPLICATE KEY UPDATE
         chuandoan = VALUES(chuandoan),
         loidan    = VALUES(loidan),
         ngaylap   = NOW()",
      'iss',
      [$maLichKham, $chuanDoan, $loiDan]
    )->close();

    $mysqli->commit();
    return ket_qua(true, 'Đã hoàn thành khám.');
  } catch (Throwable $loi) {
    $mysqli->rollback();
    error_log('hoan_thanh_kham: ' . $loi->getMessage());
    return ket_qua(false, 'Không lưu được hồ sơ, vui lòng thử lại.');
  }
}

// lay ho so kham da tham
function lay_ho_so_kham(int $maLichKham, int $maBacSi): array
{
  $lichKham = lay_lich_kham($maBacSi, $maLichKham);
  if ($lichKham === null) return ket_qua(false, 'Không tìm thấy lịch khám.');
  if ($lichKham['trangthai'] !== LK_DA_KHAM) {
    return ket_qua(false, 'Lượt khám này chưa có kết quả.');
  }

  $hoSo = truy_van_mot(
    "SELECT chuandoan, loidan, ngaylap FROM ho_so_benh_nhan WHERE malichkham = ?",
    'i',
    [$maLichKham]
  ) ?? ['chuandoan' => '', 'loidan' => '', 'ngaylap' => null];

  $hoSo['chuandoan'] = (string)$hoSo['chuandoan'];
  $hoSo['loidan']    = (string)$hoSo['loidan'];

  return ket_qua(true, '', $hoSo);
}

// ===== Router AJAX =====
if (($_GET['trang'] ?? '') === 'khambenh' && isset($_GET['ajax'])) {
  if (session_status() === PHP_SESSION_NONE) session_start();

  // Chỉ bác sĩ đã đăng nhập mới được gọi
  if ((int)($_SESSION[SS_VAI_TRO] ?? 0) !== VT_BAC_SI) {
    http_response_code(403);
    tra_json(ket_qua(false, 'Bạn không có quyền thực hiện thao tác này.'));
  }
  $maBacSi = (int)($_SESSION[SS_MA_NGUOI_DUNG] ?? 0);

  try {
    $dl = doc_du_lieu_gui();
    $ketQua = match ((string)$_GET['ajax']) {
      'ds'         => lay_ca_kham_hom_nay($maBacSi),
      'xem'        => lay_ho_so_kham((int)($_GET['ma'] ?? 0), $maBacSi),
      'hoan_thanh' => hoan_thanh_kham(
        (int)($dl['ma'] ?? 0),
        $maBacSi,
        (string)($dl['chan_doan'] ?? ''),
        (string)($dl['loi_dan'] ?? '')
      ),
      default      => ket_qua(false, 'Hành động không hợp lệ.'),
    };
  } catch (Throwable $e) {
    error_log('kham-benh: ' . $e->getMessage());
    http_response_code(500);
    $ketQua = ket_qua(false, 'Lỗi máy chủ, vui lòng thử lại.');
  }

  tra_json($ketQua);
}