<?php
$trangHienTai = $trang ?? '';

$mucQuanTri = [
    'quan-tri/tong-quan' => 'Tổng quan',
    'quan-tri/nguoi-dung' => 'Người dùng',
    'quan-tri/bac-si' => 'Quản lý bác sĩ',
];

$mucPhu = [
    'quan-tri/bac-si/them' => 'quan-tri/bac-si',
    'quan-tri/bac-si/sua' => 'quan-tri/bac-si',
    'quan-tri/bac-si/luu' => 'quan-tri/bac-si',
];
$mucDangChon = $mucPhu[$trangHienTai] ?? $trangHienTai;
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= h(csrf_token()) ?>">
    <title><?= h($tieuDe ?? 'Quản trị') ?> - Đặt Lịch Khám</title>
    <link rel="stylesheet" href="assets/css/quan-tri/style.css">
</head>

<body>

    <div class="thanh-tren">
        <div class="rong">
            <span>Hotline: 0292 000 2026</span>
            <span>Thứ 2 đến Thứ 7 | 07:30 đến 16:30</span>
        </div>
    </div>

    <header class="dau-trang">
        <div class="rong dau-trang-ben-trong">
            <a href="index.php?page=quan-tri/tong-quan" class="logo">Đặt Lịch Khám Bệnh</a>
            <nav class="menu">
                <?php foreach ($mucQuanTri as $page => $chu): ?>
                    <a href="index.php?page=<?= h($page) ?>" <?= $mucDangChon === $page ? ' class="dang-chon"' : '' ?>>
                        <?= h($chu) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            <div class="tai-khoan">
                <span>Xin chào, <strong><?= h(ho_ten_hien_tai()) ?></strong></span>
                <a href="index.php?page=dang-xuat" class="nut-nho nut-vien">Đăng xuất</a>
            </div>
        </div>
    </header>

    <main>