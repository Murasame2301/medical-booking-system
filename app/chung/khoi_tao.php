<?php
session_start();

require_once __DIR__ . '/hang_so.php';
require_once __DIR__ . '/co_so_du_lieu.php';
require_once __DIR__ . '/tien_ich.php';

$trang = $_GET['trang'] ?? 'tongquan';
$la_ajax = isset($_GET['ajax']);

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

// chua dang nhap / khong phai bac si
if (empty($_SESSION['ma_nguoi_dung']) || (int)($_SESSION['vai_tro'] ?? 0) !== VT_BAC_SI) {
  if ($la_ajax) {
    http_response_code(401);
    tra_json(ket_qua(false, 'Phiên đăng nhập đã hết hạn, vui lòng đăng nhập lại.'));
  }
  header('Location: index.php?trang=dangnhap');
  exit;
}

// yeu cau danh sach trang
$danh_sach_trang = require __DIR__ . '/../chuyen_huong/web.php';

// tim trang trong web.php
if (!isset($danh_sach_trang[$trang])) {
  if ($la_ajax) {
    http_response_code(404);
    tra_json(ket_qua(false, 'Không tìm thấy trang yêu cầu: ' . $trang));
  }
  $trang = 'tongquan'; // truy cap binh thuong thi ve trang mac dinh
}

// lay cau hinh trang
$trang_lay_duoc = $danh_sach_trang[$trang];

// chay xu ly (file xu_ly co the chua khoi AJAX va tu exit)
$du_lieu = [];
foreach ((array)($trang_lay_duoc['xu_ly'] ?? []) as $ten_file_xu_ly) {
  $duong_dan = __DIR__ . '/../xu_ly/' . $ten_file_xu_ly;
  if (!is_file($duong_dan)) {
    error_log('khoi_tao: khong tim thay file xu ly ' . $duong_dan);
    if ($la_ajax) {
      http_response_code(500);
      tra_json(ket_qua(false, 'Thiếu file xử lý: ' . $ten_file_xu_ly));
    }
    continue;
  }
  require $duong_dan;
}

// neu la AJAX ma khong file xu ly nao tra JSON thi bao loi thay vi in ca trang HTML
if ($la_ajax) {
  http_response_code(400);
  tra_json(ket_qua(false, 'Yêu cầu AJAX không được xử lý: ' . h((string)$_GET['ajax'])));
}

$tieu_de = $trang_lay_duoc['tieu_de'];
$js_trang_lay_duoc = $trang_lay_duoc['js'] ?? [];

// hien giao dien trang
require_once __DIR__ . '/../giao_dien/bac_si/layout/header.php';
require_once __DIR__ . '/../giao_dien/bac_si/layout/sidebar.php';
require_once __DIR__ . '/../giao_dien/bac_si/' . $trang_lay_duoc['view'];
require_once __DIR__ . '/../giao_dien/bac_si/layout/footer.php';