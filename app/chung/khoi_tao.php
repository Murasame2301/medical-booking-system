<?php
session_start();

require_once __DIR__ . '/hang_so.php';
require_once __DIR__ . '/co_so_du_lieu.php';
require_once __DIR__ . '/tien_ich.php';

$trang = $_GET['trang'] ?? 'tongquan';

if ($trang === 'dangxuat') {
  $_SESSION = [];
  if (ini_get('session.use_cookies')) {
    $thamSoCookie = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $thamSoCookie['path'], $thamSoCookie['domain'], $thamSoCookie['secure'], $thamSoCookie['httponly']);
  }
  session_destroy();
  header('Location: index.php?trang=dangnhap');
  exit;
}

if ($trang === 'dangnhap' || $trang === 'dangky') {
  require_once __DIR__ . '/../xu_ly/xac_thuc.php';
  require __DIR__ . '/../giao_dien/xac_thuc.php';
  exit;
}

if (empty($_SESSION['ma_nguoi_dung']) || (int)($_SESSION['vai_tro'] ?? 0) !== VT_BAC_SI) {
  header('Location: index.php?trang=dangnhap');
  exit;
}

// yeu cau danh sach trang
$danh_sach_trang = require_once __DIR__ . '/../chuyen_huong/web.php';

// tim trang trong web.php, neu khong co thi lay trang mac dinh
if (!isset($danh_sach_trang[$trang])) {
  $trang = 'tongquan';
}

// lay trang de cau hinh
$trang_lay_duoc = $danh_sach_trang[$trang];

// chay xu li
$du_lieu = [];
if (!empty($trang_lay_duoc['xu_ly'])) {
  foreach ((array)$trang_lay_duoc['xu_ly'] as $ten_file_xu_ly) {
    $duong_dan = __DIR__ . '/../xu_ly/' . $ten_file_xu_ly;
    if (is_file($duong_dan)) {
      require $duong_dan;
    }
  }
}

$tieu_de = $trang_lay_duoc['tieu_de'];
$js_trang_lay_duoc = $trang_lay_duoc['js'] ?? [];


// hien giao dien trang
require_once __DIR__ . '/../giao_dien/bac_si/layout/header.php';
require_once __DIR__ . '/../giao_dien/bac_si/layout/sidebar.php';
require_once __DIR__ . '/../giao_dien/bac_si/' . $trang_lay_duoc['view'];
require_once __DIR__ . '/../giao_dien/bac_si/layout/footer.php';
