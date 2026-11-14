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
  return (bool) preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $gio);
}

// doi gio sang phut
function gio_sang_phut(string $gio): int
{
  [$h, $m] = array_map('intval', explode(':', $gio));
  return $h * 60 + $m;
}

// doi phut sang gio
function phut_sang_gio(int $tongPhut): string
{
  return sprintf('%02d:%02d', intdiv($tongPhut, 60), $tongPhut % 60);
}

// dinh dang ngay
function dinh_dang_ngay(string $ngay): string
{
  $d = DateTime::createFromFormat('Y-m-d', substr($ngay, 0, 10));
  return $d ? $d->format('d/m/Y') : $ngay;
}

// dinh dang khung gio 8:00 - 08:30
function dinh_dang_khung_gio(string $batDau, string $ketThuc): string
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
    LLV_DA_DAT      => ['Đã đặt',       'n-xam'],
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

function tra_json($ketQuaGoiHam = [])
{
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($ketQuaGoiHam, JSON_UNESCAPED_UNICODE);
  exit;
}

// khi post hoac json
function doc_du_lieu_gui(): array
{
  $json = json_decode(file_get_contents('php://input') ?: '', true);
  return is_array($json) ? $json : $_POST;
}

// ===== ANH DAI DIEN =====

// Chu cai viet tat khi chua co anh: chu cai dau cua tu cuoi trong ho ten (ten rieng)
function chu_cai_viet_tat(string $hoTen): string
{
  $tu = preg_split('/\s+/u', trim($hoTen), -1, PREG_SPLIT_NO_EMPTY);
  if (!$tu) return '?';
  return mb_strtoupper(mb_substr(end($tu), 0, 1, 'UTF-8'), 'UTF-8');
}

// Duong dan anh dai dien kem ?v=<thoi gian sua tep> de khong bi dinh cache cu.
// Tra ve null neu chua co anh hoac tep khong con ton tai.
function lay_anh_dai_dien(int $maNguoiDung): ?string
{
  static $boNho = [];
  if (array_key_exists($maNguoiDung, $boNho)) return $boNho[$maNguoiDung];

  $r = truy_van_mot(
    "SELECT anhdaidien FROM nguoi_dung WHERE manguoidung = ?",
    'i',
    [$maNguoiDung]
  );
  $duongDan = (string)($r['anhdaidien'] ?? '');
  $tep = rtrim(dirname($_SERVER['SCRIPT_FILENAME']), '/\\') . '/' . $duongDan;

  if ($duongDan !== '' && is_file($tep)) {
    return $boNho[$maNguoiDung] = $duongDan . '?v=' . filemtime($tep);
  }
  return $boNho[$maNguoiDung] = null;
}

// Khung avatar dung chung cho moi trang (view, header, sidebar...)
// Anh duoc in san trong HTML nen hien ngay, khong bi nhay chu cai.
function html_avatar(int $maNguoiDung, string $hoTen, string $lop = 'av-lon'): string
{
  $anh = lay_anh_dai_dien($maNguoiDung);
  $chu = chu_cai_viet_tat($hoTen);
  $style = $anh
    ? ' style="background-image:url(\'' . h($anh) . '\');background-size:cover;background-position:center"'
    : '';

  return '<div class="' . h($lop) . '" data-anh="' . h($anh) . '" data-chu="' . h($chu) . '"'
    . $style . '>' . ($anh ? '' : h($chu)) . '</div>';
}