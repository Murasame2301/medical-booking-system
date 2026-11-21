<?php

$message = $viewData['message'] ?? '';
$messageType = $viewData['messageType'] ?? '';
$lichSu = $viewData['lichSu'] ?? [];
$lydoHuy = $viewData['lydoHuy'] ?? '';
$malichkhamLoi = $viewData['malichkhamLoi'] ?? null;

$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Xem lại lịch sử đặt khám và huỷ lịch hẹn của bạn.">
    <title>Lịch sử đặt khám</title>
    <link rel="stylesheet" href="assets/css/benh-nhan/appointment.css?v=7">
    <link rel="stylesheet" href="assets/css/benh-nhan/appointment-history.css?v=2">
    <link rel="stylesheet" href="assets/css/benh-nhan/nav-menu.css?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/benh-nhan/footer.css?v=4">
    <script src="assets/js/benh-nhan/ajax.js?v=6" defer></script>
</head>

<body class="appointment-page appointment-history-page">
    <header class="appointment-header">
        <a class="appointment-brand" href="index.php?page=trang-chu" aria-label="Đặt lịch khám — Trang chủ">
            <img class="appointment-brand-logo" src="assets/images/logo.png?v=2" width="28" height="28"
                alt="Logo cổng đặt lịch khám">
            <span>
                <span class="appointment-brand-name">Đặt lịch khám</span>
                <span class="appointment-brand-description">Cổng đặt lịch khám bệnh trực tuyến</span>
            </span>
        </a>

        <div class="appointment-nav">
            <?php require __DIR__ . '/partials/menu.php'; ?>
        </div>
    </header>

    <main class="history-main">
        <div class="history-breadcrumb">
            <a href="index.php?page=trang-chu">Trang chủ</a>
            <span aria-hidden="true">/</span>
            <span>Lịch sử đặt khám</span>
        </div>

        <div class="history-heading">
            <div>
                <p class="history-eyebrow">Lịch hẹn của bạn</p>
                <h1>Lịch sử đặt khám</h1>
                <p>Theo dõi các lịch hẹn đã đặt và huỷ những lịch chưa tới giờ khám.</p>
            </div>
            <span class="history-count"><?php echo count($lichSu); ?> lịch hẹn</span>
        </div>

        <?php if ($message !== ''): ?>
        <div class="history-alert history-alert-<?php echo $escape($messageType ?: 'error'); ?>"
            role="<?php echo $messageType === 'success' ? 'status' : 'alert'; ?>">
            <?php echo $escape($message); ?>
        </div>
        <?php endif; ?>

        <div id="history-alert" aria-live="polite"></div>

        <?php if (count($lichSu) === 0): ?>
        <section class="history-empty" aria-labelledby="history-empty-title">
            <div class="history-empty-icon" aria-hidden="true">
                <i class="fa-regular fa-calendar-days"></i>
            </div>
            <h2 id="history-empty-title">Bạn chưa có lịch hẹn nào</h2>
            <p>Chọn một khung giờ còn trống để đặt lịch khám đầu tiên.</p>
            <a class="history-primary-button" href="index.php?page=dat-kham">Tìm lịch khám</a>
        </section>
        <?php else: ?>
        <div class="history-list" id="history-list">
            <?php foreach ($lichSu as $item): ?>
            <?php
            $isReopen = $malichkhamLoi !== null && (int) $malichkhamLoi === (int) $item['malichkham'];
            require __DIR__ . '/partials/history-card.php';
            ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </main>

    <?php require __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>
