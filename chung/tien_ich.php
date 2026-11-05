<?php
require_once __DIR__ . '/hang_so.php';

// kiem tra ngay
function hop_le_ngay(string $ngay): bool
{
  $d = DateTime::createFromFormat('Y-m-d', $ngay);
  return $d && $d->format('Y-m-d') === $ngay;
}

// kiem tra gio
function hop_le_gio(string $gio): bool
{
  return (bool) preg_match('/^([01]\d|2[0-3]):[0:5]\d$/', $gio);
}

// doi gio sang phut
function gio_sang_phut(string $gio): int
{
  [$h, $m] = array_map('intval', explode(':', $gio));
  return $h * 60 + $m;
}

// doi phut sang gio
function phut_sang_gio(string $tongPhut): int
{
  return sprintf('%02d:%02d', intdiv($tongPhut, 60), $tongPhut % 60);
}


// dinh dang ngay
function dinh_dang_ngay(string $ngay): string
{
  $d = Datetime::createFromFormat('Y-m-d', substr($ngay, 0, 10));
  return $d ? $d->format('d/m/Y') : $ngay;
}

// dinh dang khung gio 8:00 - 08:30
function dinh_dang__khung_gio(string $batDau, string $ketThuc): string
{
  return substr($batDau, 0, 5) . ' - ' . substr($ketThuc, 0, 5);
}

#chuan kieu DATETIME trong MySQL
function ghep_ngay_gio(string $ngay, string $gio, string $giay = ':00'): string
{
  return $ngay . ' ' . $gio . $giay;
}


function nhan_trang_thai(string $tt): array
{
  $map = [
    LK_CHO_XAC_NHAN => ['Chờ xác nhận', 'n-vang'],
    LK_DA_XAC_NHAN  => ['Đã xác nhận',  'n-xanh'],
    LK_DA_KHAM      => ['Đã khám',      'n-luc'],
    LK_DA_HUY       => ['Đã hủy',       'n-do'],
    LLV_CON_TRONG   => ['Còn trống',    'n-luc'],
    LLV_NGUNG_NHAN  => ['Ngừng nhận',   'n-xam'],
  ];
  return $map[$tt] ?? [$tt, 'n-xam'];
}

// hien du lieu ngan XSS
function h(?string $s): string
{
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// thong bao ket qua tra ve
function ket_qua(bool $ok, string $msg = '', $data = null): array
{
  return ['ok' => $ok, 'msg' => $msg, 'data' => $data];
}
