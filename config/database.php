<?php
/* Ket noi co so du lieu dung chung cho toan he thong.
   Cac file model/controller nhan $conn lam tham so dau tien, khong dung bien
   toan cuc ngam, de mot ham co the goi tu bat ky trang nao. */

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = new mysqli('localhost', 'root', '', 'dat_lich_kham');
$conn->set_charset('utf8mb4');
