<?php
require_once __DIR__ . '/../chung/hang_so.php';
require_once __DIR__ . '/../chung/co_so_du_lieu.php';
require_once __DIR__ . '/../chung/tien_ich.php';



// tim kiem lich kham

function tim_kiem_lich_khám(int $maBacSi, string $ngay, string $tuNgay, string $denNgay, string $trangThai = ''):array {
  if(!hop_le_ngay($ngay) || hop_le_ngay($denNgay)) return ket_qua(false, "Ngày không hợp lệ.");
  if($tuNgay > $denNgay) return ket_qua(false, "Ngày bắt đầu phải nhỏ hơn ngày kết thúc.");

  #chuan hoa kieu trang thai
  $tt = is_string($trangThai) ? explode(',', $trangThai) : $trangThai;
  $tt = array_unique(array_filter(array_map('trim', $tt)));

  if (array_diff($tt, [LLV_CON_TRONG, LLV_NGUNG_NHAN])) {
        return ket_qua(false, 'Trạng thái không hợp lệ.');
  }

  $tuNgayGio = ghep_ngay_gio($ngay, '00:00');
  $denNgayGio = ghep_ngay_gio($ngay, '23:59', ':59');
  //sql
  $sql = "SELECT lk.trangthai, lk.malichkham, nd.hoten, nd.ngaysinh,
                 nd.gioitinh, nd.sdt,  lk.lido, llv.thoigianbatdau, llv.thoigiankethuc
          FROM lich_lam_viec llv JOIN lich_kham lk ON llv.malichlamviec = lk.malichklamviec
                                JOIN nguoi_dung nd ON lk.manguoidung =  nd.manguoidung
          WHERE nd.manguoidung = ? AND llv.thoigianbatdau >= ? AND llv.thoigianketthuc <= ? ";

  $kieu = 'iss';
  $thamSo = [$maBacSi, $tuNgayGio, $denNgayGio];

  #loc trang thai neu co
  if($tt){
    $sql .= "AND lk.trangthai IN (" . implode(',', array_fill(0,count($tt), '?')) . ")";
    $kieu .= str_repeat('s', count($tt));
    $thamSo = array_merge($thamSo, $tt);
  }


  $sql .= "ORDER BY lk.malichkham ASC";

  $danhSach = truy_van($sql, $kieu, $thamSo);

  return ket_qua(true, 'Lấy danh sách lịch khám thành công.', ['danh_sach' => $danhSach]);
  
}
?>