<?php
/* Ket noi co so du lieu dung chung cho toan he thong.
   Cac file model/controller nhan $conn lam tham so dau tien, khong dung bien
   toan cuc ngam, de mot ham co the goi tu bat ky trang nao. */

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = new mysqli('localhost', 'root', '', 'dat_lich_kham');
$conn->set_charset('utf8mb4');

/* Dong bo mui gio voi PHP (config/constants.php). MySQL tren Windows thuong
   khong co bang ten mui gio, nen dung do lech co dinh so voi UTC. */
$conn->query("SET time_zone = '" . MUI_GIO_MYSQL . "'");
