<?php
require_once __DIR__ . '/../chung/hang_so.php';
require_once __DIR__ . '/../chung/co_so_du_lieu.php';
require_once __DIR__ . '/../chung/tien_ich.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// ************ TIEN ICH DUNG CHUNG

// gio ket thuc = gio bat dau + so phut (khong qua 23:59)
function tinh_gio_ket_thuc(string $gioBatDau, int $soPhut): string
{
  return phut_sang_gio(min(gio_sang_phut($gioBatDau) + $soPhut, CUOI_NGAY_PHUT));
}

// tra ve noi dung loi, "" neu hop le
function kiem_tra_khung_gio(string $ngay, string $tuGio, int $soPhut): string
{
  if (!hop_le_ngay($ngay)) return 'Ngày không hợp lệ.';
  if ($ngay < date('Y-m-d')) return 'Không thể đặt khung giờ ở ngày đã qua.';
  if (!hop_le_gio($tuGio)) return 'Giờ bắt đầu không hợp lệ.';
  if ($soPhut < SO_PHUT_TOI_THIEU || $soPhut > SO_PHUT_TOI_DA) {
    return 'Số phút cho mỗi lượt phải từ ' . SO_PHUT_TOI_THIEU . ' đến ' . SO_PHUT_TOI_DA . ' phút.';
  }
  if (gio_sang_phut($tuGio) + $soPhut > CUOI_NGAY_PHUT) return 'Khung giờ vượt quá cuối ngày.';
  return '';
}

function thong_bao_trung(string $ngay, string $tuGio, string $denGio): string
{
  return "Khung giờ từ $tuGio đến $denGio ngày $ngay đã được tạo trong lịch làm việc.";
}

// 1062: trung gio bat dau (UNIQUE), 1644: trigger bao chong lan
function la_loi_trung_lich(mysqli_sql_exception $e): bool
{
  return in_array($e->getCode(), [1062, 1644], true);
}

// ************ TRUY VAN 

// khung gio da co luot kham dang hieu luc (khac da huy) chua
function khung_gio_co_luot_kham(int $maLichLamViec): bool
{
  $sql = "SELECT COUNT(*) AS so_luong FROM lich_kham WHERE malichlamviec = ? AND trangthai <> ?";
  $ketQua = truy_van_mot($sql, 'is', [$maLichLamViec, LK_DA_HUY]);
  return (int)($ketQua['so_luong'] ?? 0) > 0;
}

// $boQuaMa: ma khung gio dang sua (khong tinh trung voi chinh no), 0 = khong bo qua
function khung_gio_bi_trung(int $maBacSi, string $ngay, string $gioBatDau, string $gioKetThuc, int $boQuaMa = 0): bool
{
  $sql = "SELECT COUNT(*) AS so_luong
          FROM lich_lam_viec
          WHERE manguoidung = ? AND thoigianbatdau < ? AND thoigianketthuc > ? AND malichlamviec <> ?";
  $ketQua = truy_van_mot($sql, 'issi', [
    $maBacSi,
    ghep_ngay_gio($ngay, $gioKetThuc),
    ghep_ngay_gio($ngay, $gioBatDau),
    $boQuaMa,
  ]);
  return (int)($ketQua['so_luong'] ?? 0) > 0;
}

function lay_lich_lam_viec(int $maBacSi, int $maLichLamViec): array
{
  $sql = "SELECT malichlamviec, manguoidung, thoigianbatdau, thoigianketthuc, trangthai
          FROM lich_lam_viec
          WHERE malichlamviec = ? AND manguoidung = ?";
  $lich = truy_van_mot($sql, 'ii', [$maLichLamViec, $maBacSi]);
  return $lich
    ? ket_qua(true, 'Lấy thông tin lịch làm việc thành công.', ['lich_lam_viec' => $lich])
    : ket_qua(false, 'Không tìm thấy lịch làm việc.');
}

// lay lich lam viec tu ngay den ngay
function lay_danh_sach_lich_lam_viec(int $maBacSi, string $tuNgay, string $denNgay, string $trangThai = ''): array
{
  if (!hop_le_ngay($tuNgay) || !hop_le_ngay($denNgay)) return ket_qua(false, 'Ngày không hợp lệ.');
  if ($tuNgay > $denNgay) return ket_qua(false, 'Ngày bắt đầu phải trước hoặc bằng ngày kết thúc.');

  // rong hoac "tat_ca" = khong loc
  $tt = ($trangThai === '' || $trangThai === LLV_TAT_CA)
    ? []
    : array_values(array_unique(array_filter(array_map('trim', explode(',', $trangThai)))));
  if (array_diff($tt, LLV_TRANG_THAI)) return ket_qua(false, 'Trạng thái không hợp lệ.');

  $sql = "SELECT llv.malichlamviec, llv.manguoidung, llv.thoigianbatdau, llv.thoigianketthuc, llv.trangthai,
                 EXISTS (SELECT 1 FROM lich_kham lk
                         WHERE lk.malichlamviec = llv.malichlamviec AND lk.trangthai <> ?) AS da_co_luot
          FROM lich_lam_viec llv
          WHERE llv.manguoidung = ? AND llv.thoigianbatdau >= ? AND llv.thoigianbatdau <= ?";
  $kieu = 'siss';
  $thamSo = [
    LK_DA_HUY,
    $maBacSi,
    ghep_ngay_gio($tuNgay, GIO_DAU_NGAY),
    ghep_ngay_gio($denNgay, GIO_CUOI_NGAY, ':59'),
  ];

  if ($tt) {
    $sql .= " AND llv.trangthai IN (" . implode(',', array_fill(0, count($tt), '?')) . ")";
    $kieu .= str_repeat('s', count($tt));
    $thamSo = array_merge($thamSo, $tt);
  }
  $sql .= " ORDER BY llv.thoigianbatdau ASC";

  return ket_qua(true, 'Lấy danh sách lịch làm việc thành công.', ['danh_sach' => truy_van($sql, $kieu, $thamSo)]);
}

// ************ TAO / SUA / XOA 

function tao_mot_lich_lam_viec(int $maBacSi, string $ngay, string $tuGio, int $soPhut): array
{
  if ($loi = kiem_tra_khung_gio($ngay, $tuGio, $soPhut)) return ket_qua(false, $loi);

  $denGio = tinh_gio_ket_thuc($tuGio, $soPhut);
  $baoTrung = thong_bao_trung($ngay, $tuGio, $denGio);
  if (khung_gio_bi_trung($maBacSi, $ngay, $tuGio, $denGio)) return ket_qua(false, $baoTrung);

  $sql = "INSERT INTO lich_lam_viec (manguoidung, thoigianbatdau, thoigianketthuc, trangthai) VALUES (?, ?, ?, ?)";
  try {
    $stmt = thi_hanh($sql, 'isss', [
      $maBacSi,
      ghep_ngay_gio($ngay, $tuGio),
      ghep_ngay_gio($ngay, $denGio),
      LLV_CON_TRONG,
    ]);
  } catch (mysqli_sql_exception $e) {
    if (la_loi_trung_lich($e)) return ket_qua(false, $baoTrung);
    throw $e;
  }
  $soDong = $stmt->affected_rows;
  $maLich = $stmt->insert_id;
  $stmt->close();

  return $soDong > 0
    ? ket_qua(true, 'Tạo lịch làm việc thành công.', ['ma_lich' => $maLich])
    : ket_qua(false, 'Tạo lịch làm việc thất bại.');
}

// sua ngay / gio bat dau / so phut cua khung gio CON TRONG va chua co luot kham
function cap_nhat_lich_lam_viec(int $maBacSi, int $maLichLamViec, string $ngay, string $tuGio, int $soPhut): array
{
  if ($loi = kiem_tra_khung_gio($ngay, $tuGio, $soPhut)) return ket_qua(false, $loi);

  $hienTai = lay_lich_lam_viec($maBacSi, $maLichLamViec);
  if (!$hienTai['ok']) return $hienTai;
  if ($hienTai['data']['lich_lam_viec']['trangthai'] !== LLV_CON_TRONG || khung_gio_co_luot_kham($maLichLamViec)) {
    return ket_qua(false, 'Chỉ sửa được khung giờ còn trống.');
  }

  $denGio = tinh_gio_ket_thuc($tuGio, $soPhut);
  $baoTrung = thong_bao_trung($ngay, $tuGio, $denGio);
  if (khung_gio_bi_trung($maBacSi, $ngay, $tuGio, $denGio, $maLichLamViec)) return ket_qua(false, $baoTrung);

  // dieu kien trangthai trong WHERE: neu vua co nguoi dat thi khong ghi de
  $sql = "UPDATE lich_lam_viec
          SET thoigianbatdau = ?, thoigianketthuc = ?
          WHERE malichlamviec = ? AND manguoidung = ? AND trangthai = ?";
  try {
    $stmt = thi_hanh($sql, 'ssiis', [
      ghep_ngay_gio($ngay, $tuGio),
      ghep_ngay_gio($ngay, $denGio),
      $maLichLamViec,
      $maBacSi,
      LLV_CON_TRONG,
    ]);
  } catch (mysqli_sql_exception $e) {
    if (la_loi_trung_lich($e)) return ket_qua(false, $baoTrung);
    throw $e;
  }
  $soDong = $stmt->affected_rows;
  $stmt->close();

  return $soDong > 0
    ? ket_qua(true, 'Cập nhật lịch làm việc thành công.')
    : ket_qua(false, 'Cập nhật lịch làm việc thất bại hoặc không có thay đổi.');
}

// chi xoa khi "con trong" va khong co luot kham dang hieu luc
function xoa_lich_lam_viec(int $maBacSi, int $maLichLamViec): array
{
  $lich = lay_lich_lam_viec($maBacSi, $maLichLamViec);
  if (!$lich['ok']) return $lich;

  if ($lich['data']['lich_lam_viec']['trangthai'] !== LLV_CON_TRONG) {
    return ket_qua(false, 'Chỉ xóa được khung giờ còn trống.');
  }
  if (khung_gio_co_luot_kham($maLichLamViec)) {
    return ket_qua(false, 'Không thể xóa lịch làm việc vì đã có lượt khám.');
  }

  try {
    // luot da huy khong con y nghia, xoa truoc de khong vuong khoa ngoai RESTRICT
    thi_hanh("DELETE FROM lich_kham WHERE malichlamviec = ? AND trangthai = ?", 'is', [$maLichLamViec, LK_DA_HUY])->close();

    $stmt = thi_hanh(
      "DELETE FROM lich_lam_viec WHERE malichlamviec = ? AND manguoidung = ?",
      'ii',
      [$maLichLamViec, $maBacSi]
    );
  } catch (mysqli_sql_exception $e) {
    // 1451: van con dong tham chieu (vd: vua co nguoi dat)
    if ($e->getCode() === 1451) return ket_qua(false, 'Không thể xóa lịch làm việc vì đã có lượt khám.');
    throw $e;
  }
  $soDong = $stmt->affected_rows;
  $stmt->close();

  return $soDong > 0
    ? ket_qua(true, 'Xóa lịch làm việc thành công.')
    : ket_qua(false, 'Xóa lịch làm việc thất bại.');
}

// ************ XU LY AJAX: tao, sua, xoa va loc lich 
if (($_GET['trang'] ?? '') === 'lichlamviec' && isset($_GET['ajax'])) {
  $maBacSi = (int)($_SESSION[SS_MA_NGUOI_DUNG] ?? 0);
  $hanhDong = (string)$_GET['ajax'];

  if ($maBacSi <= 0 || (int)($_SESSION[SS_VAI_TRO] ?? 0) !== VT_BAC_SI) {
    http_response_code(401);
    tra_json(ket_qua(false, 'Bạn cần đăng nhập bằng tài khoản bác sĩ.'));
  }

  if (!in_array($hanhDong, ['lich-lam-viec', 'tao', 'sua', 'xoa'], true)) {
    http_response_code(400);
    tra_json(ket_qua(false, 'Yêu cầu không hợp lệ.'));
  }

  // tao / sua / xoa bat buoc POST
  if ($hanhDong !== 'lich-lam-viec' && ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    tra_json(ket_qua(false, 'Phương thức không được hỗ trợ.'));
  }

  try {
    // cac ham ben duoi tu kiem tra du lieu
    $r = doc_du_lieu_gui();
    $ketQua = match ($hanhDong) {
      'lich-lam-viec' => lay_danh_sach_lich_lam_viec(
        $maBacSi,
        (string)($_GET['tu_ngay'] ?? ''),
        (string)($_GET['den_ngay'] ?? ''),
        (string)($_GET['trang_thai'] ?? '')
      ),
      'tao' => tao_mot_lich_lam_viec(
        $maBacSi,
        (string)($r['ngay'] ?? ''),
        (string)($r['gio_bat_dau'] ?? ''),
        (int)($r['so_phut'] ?? 0)
      ),
      'sua' => cap_nhat_lich_lam_viec(
        $maBacSi,
        (int)($r['ma'] ?? 0),
        (string)($r['ngay'] ?? ''),
        (string)($r['gio_bat_dau'] ?? ''),
        (int)($r['so_phut'] ?? 0)
      ),
      'xoa' => xoa_lich_lam_viec($maBacSi, (int)($r['ma'] ?? 0)),
    };
  } catch (Throwable $e) {
    error_log('lich-lam-viec: ' . $e->getMessage());
    http_response_code(500);
    $ketQua = ket_qua(false, 'Lỗi máy chủ, vui lòng thử lại.');
  }

  tra_json($ketQua);
}