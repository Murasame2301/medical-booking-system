<?php

/**
 * dau_trang.php
 * Phần đầu trang (header), include ở đầu mọi trang trong giao diện bác sĩ.
 *
 * Các biến có sẵn khi file này chạy (được khoi_tao.php chuẩn bị trước đó):
 *   $tieu_de - tiêu đề trang hiện tại, lấy từ cấu hình route trong web.php
 *   $trang   - khóa route hiện tại (vd: 'tongquan', 'lich_hen'...) dùng để
 *              tô sáng mục đang chọn trên menu
 */
?>

<!doctype html>
<html lang="vi">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= h($tieu_de ?? '') ?></title>
  <link rel="stylesheet" href="cong_khai/css/style_bacsi.css" />
</head>

<body>
  <div class="app">
<!-- tiep theo la sidebar -->