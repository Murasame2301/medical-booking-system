<?php
/* cau hinh chung va ket noi co so du lieu cau */
const DB_HOST = 'localhost';
const DB_NAME = 'dat_lich_kham';
const DB_USER = 'root';
const DB_PASS = '';

function ket_noi_db(): mysqli
{
  static $mysqli = null;
  if ($mysqli === null) {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $mysqli->set_charset('utf8mb4'); // tranh loi font/dau tieng Viet
  }
  return $mysqli;
}

// dung cho select
function truy_van(string $sql, string $kieu = '', array $thamSo = []): array
{
  $stmt = ket_noi_db()->prepare($sql);
  if ($thamSo !== []) {
    $stmt->bind_param($kieu, ...$thamSo);
  }
  $stmt->execute();
  $ketQua = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  return $ketQua;
}

// truy van mot dong dau tien hoac null
function truy_van_mot(string $sql, string $kieu = '', array $thamSo = []): ?array
{
  $rows = truy_van($sql, $kieu, $thamSo);
  return $rows[0] ?? null;
}

// dung cho them, sua, xoa -> tra ve mot mysqli_stmt da execute
function thi_hanh(string $sql, string $kieu = '', array $thamSo = []): mysqli_stmt
{
  $stmt = ket_noi_db()->prepare($sql);
  if ($thamSo !== []) {
    $stmt->bind_param($kieu, ...$thamSo);
  }
  $stmt->execute();
  return $stmt;
}