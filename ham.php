<?php
/* ham dung chung - khong lien quan den co so du lieu*/

// ham in du lieu
function h($s) {return htmlspecialchars((string)$s,ENT_QUOTES ,'UTF-8');}

//doc gia tri tren URL - phuong thuc GET
function tham_so($k, $mac_dinh = ''){
    return (isset($_GET[$k]) && is_string($_GET[$k])) ? trim($_GET[$k]) : $mac_dinh;
}

// doc gia tri - phuong thuc POST
function post_chuoi($k){
  return (isset($_POST[$k]) && is_string($_POST[$k])) ? trim($_POST[$k]) : '';
}

// kiem tra ngay
function ngay_ok($s, $mac_dinh) {
  $d = DateTime:: createFromFormat('Y-m-d', (string)$s);
  return ($d && $d->format('Y-m-d') === $s) ? $s : $mac_dinh;
}

// doc ?tu  va ? den, tu dao neu nhap nguoc
function khoang_ngay($mac_tu, $mac_den){
  $tu = ngay_ok(tham_so('tu'),$mac_tu);
  $den = ngay_ok(tham_so('den'), $mac_den);
  return $tu > $den ? [$den, $tu] : [$tu, $den];
}

// chuyen huong 
function chuyen_huong($url){
  header('Location: ' .$url);
  exit;
}

//hien thong bao khui chuyen trang 
function flash_dat($loai, $noi_dung){ $_SESSION['flash'] = [$loai, $noi_dung];}
// lay thong bao hien thi roi xoa di
function flash_lay($loai, $noi_dung){
  $f = isset($_SESSION['flash']) ? $_SESSION['flash'] : null;
  unset($_SESSION['flash']);
  return $f;
}

// chong CRSF cho moi form POST
function csrf_token(){
  if(empty($_SESSION['crsf'])) $_SESSION['crsf'] = bin2hex(random_bytes(16));
  return $_SESSION['crsf'];
}

function crsf_hop_le() {
  return isset($_POST['crsf'], $_SESSION['crsf']) && is_string($_POST['crsf']) &&  hash_equals($_SESSION['crsf'], $_POST['crsf']);
}
?>