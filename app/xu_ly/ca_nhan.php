<?php
// trang ca nhan

function cap_nhat_trang_ca_nhan(int $manguoidung, string $ten, string $ngaySinh, string $gioTinh, string $trinhDo, string $sdt, string $email, string $diaChi): array
{
  $ten = trim($ten);
  $ngaySinh = trim($ngaySinh);
  $gioiTinh = trim($gioTinh);
  $trinhDo = trim($trinhDo);
  $sdt = trim($sdt);
  $email = trim($email);
  $diaChi = trim($diaChi);

  if ($ten === '') return ket_qua(false, 'Vui lòng nhập họ và tên.');
  if ($ngaySinh === '') return ket_qua(false, 'Vui lòng nhập ngày sinh.');
  if ($gioiTinh === '') return ket_qua(false, 'Vui lòng chọn giới tính.');
  if ($trinhDo === '') return ket_qua(false, 'Vui lòng chọn trình độ.');
  if ($sdt === '') return ket_qua(false, 'Vui lòng nhập số điện thoại.');
  if ($email === '') return ket_qua(false, 'Vui lòng nhập email.');
  if ($diaChi === '') return ket_qua(false, 'Vui lòng nhập địa chỉ.');

  $sql = "UPDATE nguoi_dung
          SET  hoten = ?, ngaysinh = ?, gioitinh = ?, trinhdo = ?, sdt = ?, email = ?, diachi = ?
          WHERE manguoidung = ?";
  $kieu = 'sssssssi';
  $thamSo = [$ten, $ngaySinh, $gioiTinh, $trinhDo, $sdt, $email, $diaChi, $manguoidung];
  $capNhat = thi_hanh($sql,$kieu, $thamSo);
  if ($capNhat->affected_rows !== 1) return ket_qua(false, 'Cập nhật thông tin thất bại.');

  return ket_qua(true);
}

function thay_doi_mat_khau(int $manguoidung, string $matKhauHienTai,  string $matKhauMoi, string $xacNhanMatKhau): array
{
  $matKhauHienTai = trim($matKhauHienTai);
  $matKhauMoi = trim($matKhauMoi);
  $xacNhanMatKhau = trim($xacNhanMatKhau);

  if ($matKhauHienTai === '') return ket_qua(false, 'Vui lòng nhập mật khẩu hiện tại.');
  if ($matKhauMoi === '') return ket_qua(false, 'Vui lòng nhập mật khẩu mới.');
  if ($xacNhanMatKhau === '') return ket_qua(false, 'Vui lòng xác nhận mật khẩu mới.');
  if ($matKhauMoi !== $xacNhanMatKhau) return ket_qua(false, 'Mật khẩu mới và xác nhận mật khẩu không khớp.');

  // sql
  $sql = "SELECT matkhau FROM nguoi_dung WHERE manguoidung = ?";
  $nguoiDung = truy_van_mot($sql, 'i', [$manguoidung]);
  if ($nguoiDung === null) return ket_qua(false, 'Không tìm thấy người dùng.');

  if (!password_verify($matKhauHienTai, $nguoiDung['matkhau'])) return ket_qua(false, 'Mật khẩu hiện tại không đúng.');
  // cap nhat mat khau moi
  $matKhauMoiHash = password_hash($matKhauMoi, PASSWORD_DEFAULT);
  $sql_mk = "UPDATE nguoi_dung SET matkhau = ? WHERE manguoidung = ?";
  $capNhat = thi_hanh($sql_mk, 'si', [$matKhauMoiHash, $manguoidung]);
  if ($capNhat->affected_rows !== 1) return ket_qua(false, 'Thay đổi mật khẩu thất bại.');

  return ket_qua(true);
}

function lay_thong_tin_ca_nhan(int $manguoidung): ? array {
  $sql = "SELECT nd.hoten, nd.sdt, nd.ngaysinh, nd.gioitinh, bs.trinhdo, nd.email, nd.diachi
          FROM nguoi_dung nd JOIN bac_si bs ON nd.manguoidung = bs.manguoidung
          WHERE nd.manguoidung = ?";
  $ketQua = truy_van_mot($sql, 'i', [$manguoidung]);
  return $ketQua ?: null;  
}
