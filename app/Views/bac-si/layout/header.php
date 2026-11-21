<?php

//Phần đầu trang bác sĩ: mở <html>, nạp CSS. Dùng $tieuDe, $trang, $jsTrang do index.php chuẩn bị.
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
