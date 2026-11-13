<?php
require_once __DIR__ . '/../chung/hang_so.php';
require_once __DIR__ . '/../chung/co_so_du_lieu.php';
require_once __DIR__ . '/../chung/tien_ich.php';


// tim kiem lich kham (tuong tu nhu tim kiem lich lam viec)
function tim_kiem_lich_kham(int $maBacSi, string $ngay, string $tuNgay, string $denNgay, string $trangThai = ''): array
{
  if (!hop_le_ngay($ngay) || !hop_le_ngay($tuNgay) || !hop_le_ngay($denNgay)) {
    return ket_qua(false, 'Ngày không hợp lệ.');
  }
  if ($tuNgay > $denNgay) {
    return ket_qua(false, 'Ngày bắt đầu phải nhỏ hơn ngày kết thúc.');
  }

  $tt = is_string($trangThai) ? explode(',', $trangThai) : $trangThai;
  $tt = array_values(array_unique(array_filter(array_map('trim', array_map('strval', (array)$tt)), fn ($v) => $v !== '')));

  if (!empty(array_diff($tt, [LK_CHO_XAC_NHAN, LK_DA_XAC_NHAN, LK_DA_KHAM, LK_DA_HUY]))) {
    return ket_qua(false, 'Trạng thái không hợp lệ.');
  }

  $tuNgayGio = ghep_ngay_gio($tuNgay, '00:00', ':00');
  $denNgayGio = ghep_ngay_gio($denNgay, '23:59', ':59');

  $sql = "SELECT lk.trangthai, lk.malichkham, nd.hoten, nd.ngaysinh,
                 nd.gioitinh, nd.sdt, lk.lydo, llv.thoigianbatdau, llv.thoigianketthuc
          FROM lich_lam_viec llv
          JOIN lich_kham lk ON llv.malichlamviec = lk.malichlamviec
          JOIN nguoi_dung nd ON lk.manguoidung = nd.manguoidung
          WHERE llv.manguoidung = ? AND llv.thoigianbatdau >= ? AND llv.thoigianketthuc <= ?";

  $kieu = 'iss';
  $thamSo = [$maBacSi, $tuNgayGio, $denNgayGio];

  if (!empty($tt)) {
    $sql .= " AND lk.trangthai IN (" . implode(',', array_fill(0, count($tt), '?')) . ')';
    $kieu .= str_repeat('s', count($tt));
    $thamSo = array_merge($thamSo, $tt);
  }

  $sql .= ' ORDER BY llv.thoigianbatdau ASC, lk.malichkham ASC';

  $danhSach = truy_van($sql, $kieu, $thamSo);

  return ket_qua(true, 'Lấy danh sách lịch khám thành công.', ['danh_sach' => $danhSach]);
}


// lay mot lich kham (co hay khong)
function lay_lich_kham(int $maBacSi, int $maLichKham): ?array
{
  $sql = "SELECT lk.*, llv.thoigianbatdau, llv.thoigianketthuc
          FROM lich_kham lk
          JOIN lich_lam_viec llv ON lk.malichlamviec = llv.malichlamviec
          WHERE llv.manguoidung = ? AND lk.malichkham = ?";
  $ketQua = truy_van_mot($sql, 'ii', [$maBacSi, $maLichKham]);
  return $ketQua ?: null;
}


// doi trang thai lich kham 
function doi_trang_thai_lich_kham(int $maLichKham, int $maBacSi, array $tuCacTT, string $sangTT, string $msgThanhCong): array
{
  $lk = lay_lich_kham($maBacSi, $maLichKham);
  if (!$lk) {
    return ket_qua(false, 'Không tìm thấy lịch khám.');
  }
  if (!in_array($lk['trangthai'], $tuCacTT, true)) {
    $msg = 'Trạng thái hiện tại (' . nhan_trang_thai($lk['trangthai'])[0] . ') không cho phép thao tác này.';
    return ket_qua(false, $msg);
  }

  $sql = "UPDATE lich_kham
          SET trangthai = ?
          WHERE malichkham = ? AND trangthai = ?";
  $kieu = 'sis';
  $thamSo = [$sangTT, $maLichKham, $lk['trangthai']];

  $stmt = thi_hanh($sql, $kieu, $thamSo);
  $soDong = $stmt->affected_rows;
  $stmt->close();

  if ($soDong === 0) {
    return ket_qua(false, 'Lượt khám vừa được thay đổi.');
  }

  return ket_qua(true, $msgThanhCong);
}

function xac_nhan_lich_kham(int $maBacSi, $maLichKham): array {
  return doi_trang_thai_lich_kham($maLichKham, $maBacSi,[LK_CHO_XAC_NHAN], LK_DA_XAC_NHAN, "Đã xác nhận lịch khám.");
}

function huy_lich_kham(int $maBacSi, $maLichKham): array {
  return doi_trang_thai_lich_kham($maLichKham, $maBacSi,[LK_CHO_XAC_NHAN, LK_DA_KHAM],LK_DA_HUY, "Đã huy lịch khám.");
}