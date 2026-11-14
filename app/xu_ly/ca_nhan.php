<?php
const MAT_KHAU_TOI_THIEU   = 8;
const MAT_KHAU_TOI_DA_BYTE = 72;              // bcrypt chỉ dùng 72 byte đầu
const ANH_TOI_DA_BYTE      = 2 * 1024 * 1024; // 2MB
const ANH_TOI_DA_DIEM_ANH  = 25000000;        // chặn ảnh có số điểm ảnh quá lớn
const ANH_THU_MUC          = 'uploads/avatar/';
const GIOI_THIEU_TOI_DA    = 1000;

// ===== Thông tin cá nhân =====
function lay_thong_tin_ca_nhan(int $maNguoiDung): array
{
  $r = truy_van_mot(
    "SELECT nd.hoten, nd.ngaysinh, nd.gioitinh, bs.trinhdo, bs.gioithieu,
            nd.sdt, nd.email, nd.diachi, nd.anhdaidien
     FROM nguoi_dung nd
     JOIN bac_si bs ON nd.manguoidung = bs.manguoidung
     WHERE nd.manguoidung = ?",
    'i',
    [$maNguoiDung]
  );
  if ($r === null) return ket_qua(false, 'Không tìm thấy thông tin cá nhân.');
  return ket_qua(true, '', array_map('strval', $r));
}

// lỗi 1062 -> cho biết trùng email hay số điện thoại
function thong_bao_trung(Throwable $loi): string
{
  $msg = $loi->getMessage();
  if (stripos($msg, 'email') !== false) return 'Email đã được sử dụng.';
  if (stripos($msg, 'sdt') !== false)   return 'Số điện thoại đã được sử dụng.';
  return 'Email hoặc số điện thoại đã được sử dụng.';
}

function cap_nhat_trang_ca_nhan(int $ma, array $dl): array
{
  $ten      = trim((string)($dl['ten'] ?? ''));
  $ngaySinh = trim((string)($dl['ngay_sinh'] ?? ''));
  $gioiTinh = trim((string)($dl['gioi_tinh'] ?? ''));
  $trinhDo  = trim((string)($dl['trinh_do'] ?? ''));
  $sdt      = trim((string)($dl['sdt'] ?? ''));
  $email    = trim((string)($dl['email'] ?? ''));
  $diaChi   = trim((string)($dl['dia_chi'] ?? ''));

  // giới thiệu: tùy chọn, chỉ cập nhật khi form có gửi trường này
  $coGioiThieu = array_key_exists('gioi_thieu', $dl);
  $gioiThieu   = $coGioiThieu ? trim((string)$dl['gioi_thieu']) : null;

  if ($ten === '' || $ngaySinh === '' || $gioiTinh === '' || $trinhDo === ''
      || $sdt === '' || $email === '' || $diaChi === '') {
    return ket_qua(false, 'Vui lòng nhập đầy đủ thông tin.');
  }
  // độ dài khớp cột DB
  if (mb_strlen($ten) > 100 || mb_strlen($trinhDo) > 100
      || mb_strlen($email) > 100 || mb_strlen($diaChi) > 255) {
    return ket_qua(false, 'Thông tin nhập quá dài.');
  }
  if ($coGioiThieu && mb_strlen($gioiThieu) > GIOI_THIEU_TOI_DA) {
    return ket_qua(false, 'Phần giới thiệu tối đa ' . GIOI_THIEU_TOI_DA . ' ký tự.');
  }
  if (!hop_le_ngay($ngaySinh) || $ngaySinh > date('Y-m-d') || $ngaySinh < '1900-01-01') {
    return ket_qua(false, 'Ngày sinh không hợp lệ.');
  }
  if (!in_array($gioiTinh, ['nam', 'nu'], true)) {
    return ket_qua(false, 'Giới tính không hợp lệ.');
  }
  if (!preg_match('/^0\d{9,10}$/', $sdt)) {
    return ket_qua(false, 'Số điện thoại không hợp lệ.');
  }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    return ket_qua(false, 'Email không hợp lệ.');
  }

  $mysqli = ket_noi_db();
  $mysqli->begin_transaction();
  try {
    thi_hanh(
      "UPDATE nguoi_dung
       SET hoten = ?, ngaysinh = ?, gioitinh = ?, sdt = ?, email = ?, diachi = ?
       WHERE manguoidung = ?",
      'ssssssi',
      [$ten, $ngaySinh, $gioiTinh, $sdt, $email, $diaChi, $ma]
    )->close();

    if ($coGioiThieu) {
      thi_hanh(
        "UPDATE bac_si SET trinhdo = ?, gioithieu = ? WHERE manguoidung = ?",
        'ssi',
        [$trinhDo, $gioiThieu === '' ? null : $gioiThieu, $ma]
      )->close();
    } else {
      thi_hanh("UPDATE bac_si SET trinhdo = ? WHERE manguoidung = ?", 'si', [$trinhDo, $ma])->close();
    }

    $mysqli->commit();
  } catch (Throwable $loi) {
    $mysqli->rollback();
    if ($loi instanceof mysqli_sql_exception && (int)$loi->getCode() === 1062) {
      return ket_qua(false, thong_bao_trung($loi));
    }
    error_log('cap_nhat_trang_ca_nhan: ' . $loi->getMessage());
    return ket_qua(false, 'Không cập nhật được thông tin, vui lòng thử lại.');
  }

  // đọc lại từ DB để form và avatar hiển thị đúng giá trị đã lưu
  $moi = lay_thong_tin_ca_nhan($ma);
  return ket_qua(true, 'Đã cập nhật thông tin cá nhân.', $moi['data'] ?? ['hoten' => $ten]);
}

// ===== Đổi mật khẩu =====
function thay_doi_mat_khau(int $ma, array $dl): array
{
  $hienTai = (string)($dl['mat_khau_hien_tai'] ?? '');
  $moi     = (string)($dl['mat_khau_moi'] ?? '');
  $xacNhan = (string)($dl['xac_nhan'] ?? '');

  if ($hienTai === '' || $moi === '' || $xacNhan === '') {
    return ket_qua(false, 'Vui lòng nhập đầy đủ mật khẩu.');
  }
  if ($moi !== $xacNhan) return ket_qua(false, 'Mật khẩu mới và xác nhận không khớp.');
  if (mb_strlen($moi) < MAT_KHAU_TOI_THIEU) {
    return ket_qua(false, 'Mật khẩu mới phải có ít nhất ' . MAT_KHAU_TOI_THIEU . ' ký tự.');
  }
  if (strlen($moi) > MAT_KHAU_TOI_DA_BYTE) {
    return ket_qua(false, 'Mật khẩu mới quá dài (tối đa ' . MAT_KHAU_TOI_DA_BYTE . ' byte).');
  }
  if ($moi === $hienTai) {
    return ket_qua(false, 'Mật khẩu mới phải khác mật khẩu hiện tại.');
  }

  $nd = truy_van_mot("SELECT matkhau FROM nguoi_dung WHERE manguoidung = ?", 'i', [$ma]);
  if ($nd === null || !password_verify($hienTai, (string)$nd['matkhau'])) {
    return ket_qua(false, 'Mật khẩu hiện tại không đúng.');
  }

  thi_hanh(
    "UPDATE nguoi_dung SET matkhau = ? WHERE manguoidung = ?",
    'si',
    [password_hash($moi, PASSWORD_DEFAULT), $ma]
  )->close();

  // đổi mật khẩu xong thì cấp lại mã phiên (chống chiếm phiên cũ)
  if (session_status() === PHP_SESSION_ACTIVE) session_regenerate_id(true);

  return ket_qua(true, 'Đã đổi mật khẩu.');
}

// ===== Ảnh đại diện: lưu tệp vào uploads/avatar/, lưu đường dẫn vào DB =====
function thu_muc_anh(): string
{
  return rtrim(dirname($_SERVER['SCRIPT_FILENAME']), '/\\') . '/' . ANH_THU_MUC;
}

function xoa_tep_anh(?string $duongDan): void
{
  if (!$duongDan) return;
  $tep = thu_muc_anh() . basename($duongDan);
  if (is_file($tep)) @unlink($tep);
}

// tạo thư mục ảnh (nếu chưa có) và cấm chạy mã PHP trong đó
function chuan_bi_thu_muc_anh(): ?string
{
  $thuMuc = thu_muc_anh();
  if (!is_dir($thuMuc) && !mkdir($thuMuc, 0755, true) && !is_dir($thuMuc)) return null;

  $ht = $thuMuc . '.htaccess';
  if (!is_file($ht)) {
    @file_put_contents(
      $ht,
      "<FilesMatch \"\\.(php|phtml|phar)$\">\n  Require all denied\n</FilesMatch>\n"
    );
  }
  return $thuMuc;
}

function luu_anh_dai_dien(int $ma, ?array $file): array
{
  $loiTai = [
    UPLOAD_ERR_INI_SIZE   => 'Ảnh vượt giới hạn của máy chủ.',
    UPLOAD_ERR_FORM_SIZE  => 'Ảnh vượt giới hạn cho phép.',
    UPLOAD_ERR_PARTIAL    => 'Ảnh tải lên chưa đầy đủ, vui lòng thử lại.',
    UPLOAD_ERR_NO_FILE    => 'Bạn chưa chọn ảnh (hoặc ảnh quá lớn so với máy chủ).',
    UPLOAD_ERR_NO_TMP_DIR => 'Máy chủ thiếu thư mục tạm.',
    UPLOAD_ERR_CANT_WRITE => 'Máy chủ không ghi được ảnh.',
  ];
  $maLoi = $file['error'] ?? UPLOAD_ERR_NO_FILE;
  if (!$file || $maLoi !== UPLOAD_ERR_OK) {
    return ket_qua(false, $loiTai[$maLoi] ?? 'Không nhận được ảnh.');
  }
  if ($file['size'] > ANH_TOI_DA_BYTE) return ket_qua(false, 'Ảnh tối đa 2MB.');
  if (!is_uploaded_file($file['tmp_name'])) return ket_qua(false, 'Tệp tải lên không hợp lệ.');

  // Kiểm tra loại ảnh thật bằng nội dung tệp, không tin tên tệp
  $info = @getimagesize($file['tmp_name']);
  $duoi = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'][$info[2] ?? 0] ?? null;
  if ($duoi === null) return ket_qua(false, 'Chỉ chấp nhận ảnh JPG, PNG hoặc WEBP.');
  if ($info[0] * $info[1] > ANH_TOI_DA_DIEM_ANH) return ket_qua(false, 'Kích thước ảnh quá lớn.');

  $thuMuc = chuan_bi_thu_muc_anh();
  if ($thuMuc === null) return ket_qua(false, 'Không tạo được thư mục lưu ảnh.');

  $ten    = 'av_' . $ma . '_' . bin2hex(random_bytes(6)) . '.' . $duoi;
  $tepMoi = $thuMuc . $ten;
  if (!move_uploaded_file($file['tmp_name'], $tepMoi)) {
    return ket_qua(false, 'Không lưu được ảnh.');
  }

  $duongDan = ANH_THU_MUC . $ten;
  try {
    $cu = truy_van_mot("SELECT anhdaidien FROM nguoi_dung WHERE manguoidung = ?", 'i', [$ma]);
    thi_hanh("UPDATE nguoi_dung SET anhdaidien = ? WHERE manguoidung = ?", 'si', [$duongDan, $ma])->close();
  } catch (Throwable $e) {
    @unlink($tepMoi); // DB lỗi -> không để lại tệp mồ côi
    throw $e;
  }

  xoa_tep_anh($cu['anhdaidien'] ?? null); // xóa ảnh cũ sau khi lưu ảnh mới xong
  return ket_qua(true, 'Đã cập nhật ảnh đại diện.', ['anh' => $duongDan]);
}

function xoa_anh_dai_dien(int $ma): array
{
  $cu = truy_van_mot("SELECT anhdaidien FROM nguoi_dung WHERE manguoidung = ?", 'i', [$ma]);
  thi_hanh("UPDATE nguoi_dung SET anhdaidien = NULL WHERE manguoidung = ?", 'i', [$ma])->close();
  xoa_tep_anh($cu['anhdaidien'] ?? null);
  return ket_qua(true, 'Đã xóa ảnh đại diện.');
}

// ===== Router AJAX =====
if (($_GET['trang'] ?? '') === 'canhan' && isset($_GET['ajax'])) {
  $ma = (int)($_SESSION[SS_MA_NGUOI_DUNG] ?? 0);

  try {
    // tài khoản bị khóa giữa chừng thì không cho thao tác nữa
    $tk = truy_van_mot("SELECT trangthai FROM nguoi_dung WHERE manguoidung = ?", 'i', [$ma]);
    if ($tk === null || ($tk['trangthai'] ?? '') !== 'hoat_dong') {
      http_response_code(403);
      tra_json(ket_qua(false, 'Tài khoản không còn hoạt động.'));
    }

    $dl = doc_du_lieu_gui();
    $ketQua = match ((string)$_GET['ajax']) {
      'cap_nhat'     => cap_nhat_trang_ca_nhan($ma, $dl),
      'doi_mat_khau' => thay_doi_mat_khau($ma, $dl),
      'doi_anh'      => luu_anh_dai_dien($ma, $_FILES['anh'] ?? null),
      'xoa_anh'      => xoa_anh_dai_dien($ma),
      default        => ket_qua(false, 'Hành động không hợp lệ.'),
    };
  } catch (Throwable $e) {
    error_log('ca-nhan: ' . $e->getMessage());
    http_response_code(500);
    $ketQua = ket_qua(false, 'Lỗi máy chủ, vui lòng thử lại.');
  }

  tra_json($ketQua);
}