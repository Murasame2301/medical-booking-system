<?php

/**
 * Phan dau trang bac si: mo <html>, nap CSS cua khu bac si.
 *
 * Bien co san khi file nay chay (do index.php chuan bi truoc do):
 *   $tieuDe  - tieu de trang hien tai, lay tu config/routes.php
 *   $trang   - khoa route hien tai, dung de to sang muc dang chon tren menu
 *   $jsTrang - danh sach file JS cua trang, in o footer.php
 */
?>

<!doctype html>
<html lang="vi">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="<?= h(csrf_token()) ?>" />
  <title><?= h($tieuDe ?? '') ?></title>
  <link rel="stylesheet" href="assets/css/bac-si/doctor.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
</head>

<body>
  <div class="app">
