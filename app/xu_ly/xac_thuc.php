<?php

// ===== Cấu hình =====
$gioi_tinh_cho_phep = ['nam', 'nu'];          // khớp với ca_nhan.php
$so_lan_sai_toi_da = 5;                      // số lần đăng nhập sai trước khi khóa tạm
$thoi_gian_khoa = 900;                       // giây (15 phút)

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

// Chỉ chấp nhận tên trang dạng chữ thường/gạch dưới, tránh chèn ký tự lạ vào header Location
$trang_dang_xu_ly = preg_match('/^[a-z_]{1,30}$/', (string)($trang ?? '')) ? $trang : 'dangnhap';
$la_dang_nhap = ($trang_dang_xu_ly === 'dangnhap');

// Hàm dùng chung: mở phiên đăng nhập
$bat_dau_phien = function (array $nd): void {
  session_regenerate_id(true);
  $_SESSION['token_xac_thuc'] = bin2hex(random_bytes(32)); // đổi token sau khi đăng nhập
  $_SESSION['ma_nguoi_dung'] = (int)$nd['manguoidung'];
  $_SESSION['ho_ten'] = $nd['hoten'];
  $_SESSION['vai_tro'] = (int)$nd['mavaitro'];
  unset($_SESSION['dang_nhap_sai']);
  header('Location: index.php?trang=tongquan');
  exit;
};

$token = $_POST['token_xac_thuc'] ?? '';
if (!is_string($token) || !hash_equals($_SESSION['token_xac_thuc'], $token)) {
  $loi_xac_thuc = 'Phiên gửi biểu mẫu không hợp lệ. Vui lòng thử lại.';
} else {
  $email = mb_strtolower(trim((string)($_POST['email'] ?? '')));
  $mat_khau = (string)($_POST['mat_khau'] ?? '');

  if (mb_strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $loi_xac_thuc = 'Vui lòng nhập địa chỉ email hợp lệ.';
    $du_lieu_cu = ['email' => $email];
  } elseif ($la_dang_nhap) {
    $du_lieu_cu = ['email' => $email]; // giữ lại email khi đăng nhập sai
    $sai = $_SESSION['dang_nhap_sai'] ?? ['so_lan' => 0, 'mo_khoa' => 0];

    if ($sai['mo_khoa'] > time()) {
      $phut = (int)ceil(($sai['mo_khoa'] - time()) / 60);
      $loi_xac_thuc = "Bạn đã đăng nhập sai quá nhiều lần. Vui lòng thử lại sau {$phut} phút.";
    } else {
      $nguoi_dung = truy_van_mot(
        'SELECT manguoidung, hoten, matkhau, mavaitro, trangthai FROM nguoi_dung WHERE email = ? LIMIT 1',
        's',
        [$email]
      );

      // Luôn chạy password_verify (kể cả khi không có tài khoản) để thời gian phản hồi không lộ email nào tồn tại
      $hash = $nguoi_dung['matkhau'] ?? password_hash('khong-ton-tai', PASSWORD_DEFAULT);
      $dung_mat_khau = password_verify($mat_khau, $hash);

      if ($nguoi_dung === null || !$dung_mat_khau || (int)$nguoi_dung['mavaitro'] !== VT_BAC_SI
          || ($nguoi_dung['trangthai'] ?? '') !== 'hoat_dong') {
        $sai['so_lan']++;
        if ($sai['so_lan'] >= $so_lan_sai_toi_da) {
          $sai = ['so_lan' => 0, 'mo_khoa' => time() + $thoi_gian_khoa];
        }
        $_SESSION['dang_nhap_sai'] = $sai;
        $loi_xac_thuc = 'Email, mật khẩu không đúng hoặc tài khoản không thể đăng nhập.';
      } else {
        // Nâng cấp hash nếu thuật toán mặc định của PHP đã đổi
        if (password_needs_rehash($nguoi_dung['matkhau'], PASSWORD_DEFAULT)) {
          thi_hanh(
            'UPDATE nguoi_dung SET matkhau = ? WHERE manguoidung = ?',
            'si',
            [password_hash($mat_khau, PASSWORD_DEFAULT), (int)$nguoi_dung['manguoidung']]
          );
        }
        $bat_dau_phien($nguoi_dung);
      }
    }
  } else {
    $ho_ten = trim((string)($_POST['ho_ten'] ?? ''));
    $so_dien_thoai = preg_replace('/[\s.\-]/', '', trim((string)($_POST['so_dien_thoai'] ?? '')));
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

    $ngay = DateTimeImmutable::createFromFormat('!Y-m-d', $ngay_sinh);

    if ($ho_ten === '' || $so_dien_thoai === '' || $gioi_tinh === '' || $dia_chi === '') {
      $loi_xac_thuc = 'Vui lòng điền đầy đủ thông tin.';
    } elseif (mb_strlen($ho_ten) > 100 || mb_strlen($dia_chi) > 255) {
      $loi_xac_thuc = 'Họ tên hoặc địa chỉ quá dài.';
    } elseif (!preg_match('/^0[0-9]{9,10}$/', $so_dien_thoai)) {
      $loi_xac_thuc = 'Số điện thoại không hợp lệ.';
    } elseif (!in_array($gioi_tinh, $gioi_tinh_cho_phep, true)) {
      $loi_xac_thuc = 'Giới tính không hợp lệ.';
    } elseif (!hop_le_ngay($ngay_sinh) || !$ngay || $ngay > new DateTimeImmutable('today') || $ngay < new DateTimeImmutable('1900-01-01')) {
      $loi_xac_thuc = 'Ngày sinh không hợp lệ.';
    } elseif (strlen($mat_khau) < 8) {
      $loi_xac_thuc = 'Mật khẩu phải có ít nhất 8 ký tự.';
    } elseif (strlen($mat_khau) > 72) {
      // bcrypt chỉ dùng 72 byte đầu, nên giới hạn để không bị cắt ngầm
      $loi_xac_thuc = 'Mật khẩu không được dài quá 72 ký tự.';
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
        $bat_dau_phien($nguoi_dung_moi);
      }
    }
  }
}

$_SESSION['loi_xac_thuc'] = $loi_xac_thuc;
$_SESSION['du_lieu_xac_thuc'] = $du_lieu_cu;
header('Location: index.php?trang=' . urlencode($trang_dang_xu_ly));
exit;