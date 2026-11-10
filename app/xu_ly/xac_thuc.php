<?php

if (!empty($_SESSION['ma_nguoi_dung']) && (int)($_SESSION['vai_tro'] ?? 0) === VT_BAC_SI) {
  header('Location: index.php?trang=tongquan');
  exit;
}

if (empty($_SESSION['token_xac_thuc'])) {
  $_SESSION['token_xac_thuc'] = bin2hex(random_bytes(32));
}

$loi_xac_thuc = $_SESSION['loi_xac_thuc'] ?? '';
$du_lieu_cu = $_SESSION['du_lieu_xac_thuc'] ?? [];
unset($_SESSION['loi_xac_thuc'], $_SESSION['du_lieu_xac_thuc']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  return;
}

$trang_dang_xu_ly = $trang;
$token = $_POST['token_xac_thuc'] ?? '';
if (!is_string($token) || !hash_equals($_SESSION['token_xac_thuc'], $token)) {
  $loi_xac_thuc = 'Phiên gửi biểu mẫu không hợp lệ. Vui lòng thử lại.';
} else {
  $email = trim((string)($_POST['email'] ?? ''));
  $mat_khau = (string)($_POST['mat_khau'] ?? '');

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $loi_xac_thuc = 'Vui lòng nhập địa chỉ email hợp lệ.';
  } elseif ($trang_dang_xu_ly === 'dangnhap') {
    $nguoi_dung = truy_van_mot(
      'SELECT manguoidung, hoten, matkhau, mavaitro FROM nguoi_dung WHERE email = ? LIMIT 1',
      's',
      [$email]
    );

    if ($nguoi_dung === null || !password_verify($mat_khau, $nguoi_dung['matkhau']) || (int)$nguoi_dung['mavaitro'] !== VT_BAC_SI) {
      $loi_xac_thuc = 'Email, mật khẩu không đúng hoặc tài khoản không phải bác sĩ.';
    } else {
      session_regenerate_id(true);
      $_SESSION['ma_nguoi_dung'] = (int)$nguoi_dung['manguoidung'];
      $_SESSION['ho_ten'] = $nguoi_dung['hoten'];
      $_SESSION['vai_tro'] = (int)$nguoi_dung['mavaitro'];
      header('Location: index.php?trang=tongquan');
      exit;
    }
  } else {
    $ho_ten = trim((string)($_POST['ho_ten'] ?? ''));
    $so_dien_thoai = trim((string)($_POST['so_dien_thoai'] ?? ''));
    $ngay_sinh = trim((string)($_POST['ngay_sinh'] ?? ''));
    $gioi_tinh = trim((string)($_POST['gioi_tinh'] ?? ''));
    $dia_chi = trim((string)($_POST['dia_chi'] ?? ''));
    $xac_nhan_mat_khau = (string)($_POST['xac_nhan_mat_khau'] ?? '');

    $du_lieu_cu = [
      'ho_ten' => $ho_ten,
      'email' => $email,
      'so_dien_thoai' => $so_dien_thoai,
      'ngay_sinh' => $ngay_sinh,
      'gioi_tinh' => $gioi_tinh,
      'dia_chi' => $dia_chi,
    ];

    if ($ho_ten === '' || $so_dien_thoai === '' || $gioi_tinh === '' || $dia_chi === '') {
      $loi_xac_thuc = 'Vui lòng điền đầy đủ thông tin.';
    } elseif (!hop_le_ngay($ngay_sinh)) {
      $loi_xac_thuc = 'Ngày sinh không hợp lệ.';
    } elseif (strlen($mat_khau) < 8) {
      $loi_xac_thuc = 'Mật khẩu phải có ít nhất 8 ký tự.';
    } elseif ($mat_khau !== $xac_nhan_mat_khau) {
      $loi_xac_thuc = 'Mật khẩu xác nhận không khớp.';
    } elseif (truy_van_mot('SELECT manguoidung FROM nguoi_dung WHERE email = ? LIMIT 1', 's', [$email]) !== null) {
      $loi_xac_thuc = 'Email này đã được đăng ký.';
    } else {
      $mat_khau_ma_hoa = password_hash($mat_khau, PASSWORD_DEFAULT);
      try {
        thi_hanh(
          'INSERT INTO nguoi_dung (hoten, sdt, ngaysinh, gioitinh, email, diachi, matkhau, mavaitro)
           VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
          'sssssssi',
          [$ho_ten, $so_dien_thoai, $ngay_sinh, $gioi_tinh, $email, $dia_chi, $mat_khau_ma_hoa, VT_BAC_SI]
        );
      } catch (mysqli_sql_exception $e) {
        if ($e->getCode() !== 1062) {
          throw $e;
        }
        $loi_xac_thuc = 'Email này đã được đăng ký.';
      }

      if ($loi_xac_thuc === '') {
        $nguoi_dung_moi = truy_van_mot(
          'SELECT manguoidung, hoten, mavaitro FROM nguoi_dung WHERE email = ? LIMIT 1',
          's',
          [$email]
        );
        if ($nguoi_dung_moi === null) {
          throw new RuntimeException('Không tìm thấy tài khoản vừa đăng ký.');
        }
        session_regenerate_id(true);
        $_SESSION['ma_nguoi_dung'] = (int)$nguoi_dung_moi['manguoidung'];
        $_SESSION['ho_ten'] = $nguoi_dung_moi['hoten'];
        $_SESSION['vai_tro'] = (int)$nguoi_dung_moi['mavaitro'];
        header('Location: index.php?trang=tongquan');
        exit;
      }
    }
  }
}

$_SESSION['loi_xac_thuc'] = $loi_xac_thuc;
$_SESSION['du_lieu_xac_thuc'] = $du_lieu_cu;
header('Location: index.php?trang=' . $trang_dang_xu_ly);
exit;
