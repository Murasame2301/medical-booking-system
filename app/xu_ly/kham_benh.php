<?php
// tu ma lich kham
function lay_ho_so_kham(int $maLichKham, int $maBacSi, string $chuanDoan, string $loiDan): array
{
  $chuanDoan = trim($chuanDoan);
  $loiDan = trim($loiDan);

  if ($chuanDoan === '') return ket_qua(false, 'Vui lòng nhập chuẩn đoán.');

  $lichKham = lay_lich_kham($maBacSi, $maLichKham);
  if (!$lichKham) return ket_qua(false, 'Không tìm thấy lịch khám.');

  $sql = "SELECT chuandoan, loidan, ngaylap
          FROM ho_so_benh_nhan
          WHERE malichkham = ?";
  $hoSo = truy_van_mot($sql, 'i', [$maLichKham]);

  if ($hoSo === null) {
    $hoSo = [
      'chuandoan' => '',
      'loidan' => '',
      'ngaykham' => null
    ];
  }
  // tra ve lich kham va ho so
  return ket_qua(true, '', $lichKham + $hoSo);
}


function hoan_thanh_kham(int $maLichKham, int $maBacSi, string $chuanDoan, string  $loiDan): array
{
  $chuanDoan = trim($chuanDoan);
  $loiDan = trim($loiDan);
  if ($chuanDoan === '') return ket_qua(false, 'Vui lòng nhập chuẩn đoán.');

  $lichKham = lay_lich_kham($maBacSi, $maLichKham);
  if ($lichKham === null) return ket_qua(false, 'Không tìm thấy lịch khám.');
  if ($lichKham['trangthai'] !== LK_DA_XAC_NHAN) return ket_qua(false, 'Lượt khám chưa được xác nhận.');

  // doi trang thai va luu ho so benh nhan
  $mysqli = ket_noi_db();
  $mysqli->begin_transaction();
  try {
    $sql = "UPDATE lich_kham
            SET trangthai = ?
            WHERE malichkham = ? AND trangthai = ?";
    $kieu = 'sis';
    $thamSo = [LK_DA_KHAM, $maLichKham, LK_DA_XAC_NHAN];


    // truy van lay ket qua so dong thay doi
    $stmt  = thi_hanh($sql, $kieu, $thamSo);
    $soDong = $stmt->affected_rows;
    $stmt->close();

    if ($soDong !== 1) {
      $mysqli->rollback();
      return ket_qua(false, 'Chỉ khám được lượt đã xác nhận.');
    }

    //ssql luu ho so
    $sqlHoSo = "INSERT INTO ho_so_benh_nhan(malichkham, chuandoan, loidan, ngaylap)
            VALUES (?, ?, ?, NOW())";
    $kieuHoSo = 'iss';
    $thamSoHoSo = [$maLichKham, $chuanDoan, $loiDan];
    thi_hanh($sqlHoSo, $kieuHoSo, $thamSoHoSo)->close();
    $mysqli->commit();

    return ket_qua(true, "Đã hoàn thành khám.");
  } catch (Throwable $loi) {
    $mysqli->rollback();
    error_log('hoan_thanh_kham: ' . $loi->getMessage());
    return ket_qua(false, 'Không lưu được hồ sơ, vui lòng thử lại.');
  }
}
