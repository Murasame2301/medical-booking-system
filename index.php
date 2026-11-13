<?php
session_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Xem lịch trống và đặt lịch khám bệnh trực tuyến.">
    <title>Đặt lịch khám</title>
    <link rel="stylesheet" href="assets/css/home.css?v=3">
    <link rel="stylesheet" href="assets/css/footer.css?v=1">
</head>
<body class="home-page">
    <header class="home-header">
        <a class="home-brand" href="index.php" aria-label="Đặt lịch khám — Trang chủ">
            <img class="home-brand-logo" src="assets/images/logo.jpg?v=2" width="28" height="28" alt="Logo cổng đặt lịch khám">
            <span>
                <span class="home-brand-name">Đặt lịch khám</span>
                <span class="home-brand-description">Cổng đặt lịch khám bệnh trực tuyến</span>
            </span>
        </a>

        <div class="home-nav">
            <?php require __DIR__ . '/include/menu.php'; ?>
        </div>
    </header>

    <main>
        <section class="home-hero" aria-labelledby="home-title">
            <div class="hero-copy">
                <h1 id="home-title">Tìm lịch khám<br>phù hợp với bạn.</h1>
                <p class="hero-description">Xem khung giờ còn trống theo ngày, chuyên khoa và bác sĩ trước khi đặt lịch.</p>
                <div class="hero-actions">
                    <a class="hero-button hero-button-primary" href="dat-kham.php">Tìm lịch khám</a>
                    <a class="hero-button hero-button-secondary" href="tao-ho-so.php">Mở hồ sơ</a>
                </div>
            </div>

            <section class="home-actions" aria-labelledby="home-actions-title">
                <h2 id="home-actions-title">Bạn muốn làm gì?</h2>
                <div class="home-action-list">
                    <a class="home-action" href="dat-kham.php">
                        <span class="home-action-copy">
                            <strong>Tìm lịch khám</strong>
                            <span>Chọn ngày, chuyên khoa và bác sĩ.</span>
                        </span>
                    </a>
                    <a class="home-action" href="xem-lich-kham.php">
                        <span class="home-action-copy">
                            <strong>Xem lịch đã đặt</strong>
                            <span>Theo dõi các lịch hẹn của bạn.</span>
                        </span>
                    </a>
                    <a class="home-action" href="tao-ho-so.php">
                        <span class="home-action-copy">
                            <strong>Cập nhật hồ sơ</strong>
                            <span>Bổ sung thông tin trước buổi khám.</span>
                        </span>
                    </a>
                </div>
            </section>
        </section>
    </main>

    <?php require __DIR__ . '/include/footer.php'; ?>
</body>
</html>
