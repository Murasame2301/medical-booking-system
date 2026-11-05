<?php
require_once __DIR__ . '/../chung/hang_so.php';
require_once __DIR__ . '/../chung/co_so_du_lieu.php';
require_once __DIR__ . '/../chung/tien_ich.php';

function tinh_gio_ket_thuc(string $gioBatDau, int $soPhut): string
{
  return phut_sang_gio(min(gio_sang_phut($gioBatDau) + $soPhut, 23 * 60 + 59));
}

// khong tinh lich da huy
function khung_gio_co_luot_kham(int $maLichKhan): bool
{
  $sql = "SELECT COUNT(*) AS so_luong 
          FROM lich_kham
          WHERE malichkham = ? AND trangthai <> ? ";
  $ketQua = truy_van_mot($sql, 'is', [$maLichKhan, LK_DA_HUY]);
  return (int)($ketQua['so_luong'] ?? 0) > 0;
}

function khung_gio_bi_trung(int $maBacSi, string $ngay, string $gioBatDau, string $gioKetThuc, int $boQuaMa = 0): int
{
  $thoiGianBatDau = ngayGio($ngay, $gioBatDau);
  $thoiGianKetThuc = ngayGio($ngay, $gioKetThuc);


  $sql = "SELECT COUNT(*) AS so_luong 
        FROM lich_lam_viec
        WHERE manguoidung = ? AND malichlamviec <> ? AND thoigianbatdau < ? AND thoigianketthuc > ?";
  $ketQua = truy_van_mot($sql, 'iiss', [$maBacSi, $boQuaMa, $thoiGianKetThuc, $thoiGianBatDau]);
  return (int)($ketQua['so_luong'] ?? 0) > 0;
}

function tao_lich_lam_viec(int $maBacSi, string $ngay, string $tuGio, string $denGio, int $soPhut): array
{
  if (!hop_le_ngay($ngay)) return ket_qua(false, 'Ngày không hợp lệ.');
  if (!hop_le_gio($tuGio) || !hop_le_gio($denGio)) return ket_qua(false, 'Giờ không hợp lệ.');
  if ($soPhut < 5 || $soPhut > 60) return ket_qua(false, 'Số phút mỗi lượt phải từ 5 phút đến 60 phút.');

  $dau = gio_sang_phut($tuGio);
  $cuoi = gio_sang_phut($denGio);

  if ($cuoi - $dau < $soPhut) return ket_qua(false, 'Khoảng giờ ngắn hơn một lượt khám.');

  $mysqli = ket_noi_db();

  // Câu lệnh chuẩn bị với 4 tham số
  $sql = "INSERT INTO lich_lam_viec (manguoidung, thoigianbatdau, thoigianketthuc, trangthai)
            VALUES (?, ?, ?, ?)";
  $them = $mysqli->prepare($sql);


  $p_thoigianbatdau = '';
  $p_thoigianketthuc = '';
  $p_trangthai = 'Chờ duyệt';
  $them->bind_param("isss", $maBacSi, $p_thoigianbatdau, $p_thoigianketthuc, $p_trangthai);

  $daTao = 0;
  $boQua = [];

  $mysqli->begin_transaction();
  try {
    for ($t = $dau; $t + $soPhut <= $cuoi; $t += $soPhut) {
      $bd = phut_sang_gio($t);
      $kt = phut_sang_gio($t + $soPhut);

      if (khung_gio_bi_trung($maBacSi, $ngay, $bd, $kt)) {
        $boQua[] = "$bd-$kt";
        continue;
      }
      $p_thoigianbatdau = $ngay . ' ' . $bd . ':00';
      $p_thoigianketthuc = $ngay . ' ' . $kt . ':00';

      $them->execute();
      $daTao++;
    }
    $mysqli->commit();
  } catch (Throwable $ex) {
    $mysqli->rollback();
    $them->close();
    return ket_qua(false, 'Lỗi khi tạo lịch: ' . $ex->getMessage());
  }
  $them->close();

  $msg = "Đã tạo $daTao khung giờ. ";
  if ($boQua) {
    $msg .= 'Bỏ qua do trùng: ' . implode(', ', $boQua) . '.';
  }

  return ket_qua($daTao > 0, $daTao > 0 ? $msg : 'Không tạo được khung nào (đều trùng giờ).', ['da_tao' => $daTao, 'bo_qua' => $boQua]);
}
