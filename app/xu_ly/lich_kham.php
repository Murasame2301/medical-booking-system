<?php
require_once __DIR__ . '/../chung/hang_so.php';
require_once __DIR__ . '/../chung/co_so_du_lieu.php';
require_once __DIR__ . '/../chung/tien_ich.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// tim kiem lich kham cua bac si trong khoang ngay
function tim_kiem_lich_kham(int $maBacSi, string $tuNgay, string $denNgay, string $trangThai = ''): array
{
  if (!hop_le_ngay($tuNgay) || !hop_le_ngay($denNgay)) return ket_qua(false, 'Ngày không hợp lệ.');
  if ($tuNgay > $denNgay) return ket_qua(false, 'Ngày bắt đầu phải trước hoặc bằng ngày kết thúc.');

  // rong hoac "tat_ca" = khong loc
  $tt = ($trangThai === '' || $trangThai === TAT_CA)
    ? []
    : array_values(array_unique(array_filter(array_map('trim', explode(',', $trangThai)))));
  if (array_diff($tt, LK_TRANG_THAI)) return ket_qua(false, 'Trạng thái không hợp lệ.');

  $sql = "SELECT lk.trangthai, lk.malichkham, nd.hoten, nd.ngaysinh,
                 nd.gioitinh, nd.sdt, lk.lydo, llv.thoigianbatdau, llv.thoigianketthuc
          FROM lich_lam_viec llv
          JOIN lich_kham lk ON llv.malichlamviec = lk.malichlamviec
          JOIN nguoi_dung nd ON lk.manguoidung = nd.manguoidung
          WHERE llv.manguoidung = ? AND llv.thoigianbatdau >= ? AND llv.thoigianketthuc <= ?";
  $kieu = 'iss';
  $thamSo = [
    $maBacSi,
    ghep_ngay_gio($tuNgay, GIO_DAU_NGAY),
    ghep_ngay_gio($denNgay, GIO_CUOI_NGAY, ':59'),
  ];

  if ($tt) {
    $sql .= " AND lk.trangthai IN (" . implode(',', array_fill(0, count($tt), '?')) . ')';
    $kieu .= str_repeat('s', count($tt));
    $thamSo = array_merge($thamSo, $tt);
  }
  $sql .= ' ORDER BY llv.thoigianbatdau ASC, lk.malichkham ASC';

  return ket_qua(true, 'Lấy danh sách lịch khám thành công.', ['danh_sach' => truy_van($sql, $kieu, $thamSo)]);
}

// lay mot lich kham cua bac si (null neu khong co)
function lay_lich_kham(int $maBacSi, int $maLichKham): ?array
{
  $sql = "SELECT lk.*, llv.thoigianbatdau, llv.thoigianketthuc
          FROM lich_kham lk
          JOIN lich_lam_viec llv ON lk.malichlamviec = llv.malichlamviec
          WHERE llv.manguoidung = ? AND lk.malichkham = ?";
  return truy_van_mot($sql, 'ii', [$maBacSi, $maLichKham]) ?: null;
}

// doi trang thai lich kham
function doi_trang_thai_lich_kham(int $maLichKham, int $maBacSi, array $tuCacTT, string $sangTT, string $msgThanhCong): array
{
  $lk = lay_lich_kham($maBacSi, $maLichKham);
  if (!$lk) return ket_qua(false, 'Không tìm thấy lịch khám.');

  if (!in_array($lk['trangthai'], $tuCacTT, true)) {
    return ket_qua(false, 'Trạng thái hiện tại (' . nhan_trang_thai($lk['trangthai'])[0] . ') không cho phép thao tác này.');
  }

  // dieu kien trangthai trong WHERE: neu vua bi doi thi khong ghi de
  $stmt = thi_hanh(
    "UPDATE lich_kham SET trangthai = ? WHERE malichkham = ? AND trangthai = ?",
    'sis',
    [$sangTT, $maLichKham, $lk['trangthai']]
  );
  $soDong = $stmt->affected_rows;
  $stmt->close();

  return $soDong > 0 ? ket_qua(true, $msgThanhCong) : ket_qua(false, 'Lượt khám vừa được thay đổi.');
}

function xac_nhan_lich_kham(int $maBacSi, int $maLichKham): array
{
  return doi_trang_thai_lich_kham($maLichKham, $maBacSi, [LK_CHO_XAC_NHAN], LK_DA_XAC_NHAN, 'Đã xác nhận lịch khám.');
}

// khop nut Huy tren giao dien: cho xac nhan + da xac nhan
function huy_lich_kham(int $maBacSi, int $maLichKham): array
{
  return doi_trang_thai_lich_kham($maLichKham, $maBacSi, [LK_CHO_XAC_NHAN, LK_DA_XAC_NHAN], LK_DA_HUY, 'Đã hủy lịch khám.');
}

// ===== XU LY AJAX =====
if (($_GET['trang'] ?? '') === 'lichkham' && isset($_GET['ajax'])) {
  $maBacSi = (int)($_SESSION[SS_MA_NGUOI_DUNG] ?? 0);
  $hanhDong = (string)$_GET['ajax'];

  if ($maBacSi <= 0 || (int)($_SESSION[SS_VAI_TRO] ?? 0) !== VT_BAC_SI) {
    http_response_code(401);
    tra_json(ket_qua(false, 'Bạn cần đăng nhập bằng tài khoản bác sĩ.'));
  }

  if (!in_array($hanhDong, ['lich-kham', 'xac-nhan', 'huy'], true)) {
    http_response_code(400);
    tra_json(ket_qua(false, 'Yêu cầu không hợp lệ.'));
  }

  // xac-nhan / huy bat buoc POST
  if ($hanhDong !== 'lich-kham' && ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    tra_json(ket_qua(false, 'Phương thức không được hỗ trợ.'));
  }

  try {
    $ma = (int)(doc_du_lieu_gui()['ma'] ?? 0);
    $ketQua = match ($hanhDong) {
      'lich-kham' => tim_kiem_lich_kham(
        $maBacSi,
        (string)($_GET['tu_ngay'] ?? ''),
        (string)($_GET['den_ngay'] ?? ''),
        (string)($_GET['trang_thai'] ?? '')
      ),
      'xac-nhan' => xac_nhan_lich_kham($maBacSi, $ma),
      'huy'      => huy_lich_kham($maBacSi, $ma),
    };
  } catch (Throwable $e) {
    error_log('lich-kham: ' . $e->getMessage());
    http_response_code(500);
    $ketQua = ket_qua(false, 'Lỗi máy chủ, vui lòng thử lại.');
  }

  tra_json($ketQua);
}