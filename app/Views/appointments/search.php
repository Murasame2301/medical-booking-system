<?php

$message = $viewData['message'] ?? '';
$messageType = $viewData['messageType'] ?? '';
$dateError = $viewData['dateError'] ?? '';
$ngay = $viewData['ngay'] ?? '';
$machuyenkhoa = $viewData['machuyenkhoa'] ?? null;
$mabacsi = $viewData['mabacsi'] ?? null;
$specialties = $viewData['specialties'] ?? [];
$doctors = $viewData['doctors'] ?? [];
$slots = $viewData['slots'] ?? [];
$isLoggedIn = !empty($viewData['isLoggedIn']);

$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$formatDate = static function ($date) {
    $timestamp = strtotime($date);
    return $timestamp ? date('d/m/Y', $timestamp) : $date;
};

$formatTime = static function ($datetime) {
    $timestamp = strtotime($datetime);
    return $timestamp ? date('H:i', $timestamp) : $datetime;
};
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Tìm và đặt khung giờ khám bệnh phù hợp.">
    <title>Tìm lịch khám</title>
    <link rel="stylesheet" href="assets/css/appointment.css?v=3">
    <link rel="stylesheet" href="assets/css/footer.css?v=1">
</head>
<body class="appointment-page">
    <header class="appointment-header">
        <a class="appointment-brand" href="index.php" aria-label="Đặt lịch khám — Trang chủ">
            <img class="appointment-brand-logo" src="assets/images/logo.jpg?v=2" width="28" height="28" alt="Logo cổng đặt lịch khám">
            <span>
                <span class="appointment-brand-name">Đặt lịch khám</span>
                <span class="appointment-brand-description">Cổng đặt lịch khám bệnh trực tuyến</span>
            </span>
        </a>

        <div class="appointment-nav">
            <?php require __DIR__ . '/../../../include/menu.php'; ?>
        </div>
    </header>

    <main>
        <section class="appointment-content" aria-labelledby="search-title">
            <div class="search-panel">
                <div class="section-heading">
                    <h1 id="search-title">Tìm khung giờ trống</h1>
                    <p class="section-description">Chọn ngày khám và một bộ lọc nếu bạn muốn tìm nhanh hơn.</p>
                </div>

                <form class="search-form" method="get" action="dat-kham.php">
                    <div class="search-field search-field-date">
                        <label for="ngay">Ngày khám</label>
                        <input
                            type="date"
                            id="ngay"
                            name="ngay"
                            value="<?php echo $escape($ngay); ?>"
                            min="<?php echo $escape(date('Y-m-d')); ?>"
                            required
                        >
                    </div>
                    <div class="search-field">
                        <label for="machuyenkhoa">Chuyên khoa</label>
                        <select id="machuyenkhoa" name="machuyenkhoa">
                            <option value="">Tất cả chuyên khoa</option>
                            <?php foreach ($specialties as $specialty): ?>
                                <option
                                    value="<?php echo $escape($specialty['machuyenkhoa']); ?>"
                                    <?php echo (string) $machuyenkhoa === (string) $specialty['machuyenkhoa'] ? 'selected' : ''; ?>
                                >
                                    <?php echo $escape($specialty['tenchuyenkhoa']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="search-field">
                        <label for="mabacsi">Bác sĩ</label>
                        <select id="mabacsi" name="mabacsi">
                            <option value="">Tất cả bác sĩ</option>
                            <?php foreach ($doctors as $doctor): ?>
                                <option
                                    value="<?php echo $escape($doctor['manguoidung']); ?>"
                                    <?php echo (string) $mabacsi === (string) $doctor['manguoidung'] ? 'selected' : ''; ?>
                                >
                                    <?php echo $escape($doctor['hoten']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="search-button" type="submit">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <circle cx="10.8" cy="10.8" r="6.8"></circle>
                            <path d="m16 16 5 5"></path>
                        </svg>
                        Tìm lịch trống
                    </button>
                </form>

                <?php if ($dateError !== ''): ?>
                    <div class="appointment-alert appointment-alert-error" role="alert">
                        <?php echo $escape($dateError); ?>
                    </div>
                <?php elseif ($message !== ''): ?>
                    <div class="appointment-alert appointment-alert-<?php echo $escape($messageType ?: 'error'); ?>" role="status">
                        <?php echo $escape($message); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="results-heading">
                <div>
                    <h2><?php echo $escape($formatDate($ngay)); ?></h2>
                </div>
                <span class="results-count"><?php echo count($slots); ?> khung giờ</span>
            </div>

            <?php if (count($slots) > 0): ?>
                <div class="slot-list">
                    <?php foreach ($slots as $slot): ?>
                        <article class="slot-card">
                            <div class="slot-time">
                                <strong><?php echo $escape($formatTime($slot['thoigianbatdau'])); ?></strong>
                                <span><?php echo $escape($formatTime($slot['thoigianketthuc'])); ?></span>
                            </div>

                            <div class="slot-details">
                                <h3><?php echo $escape($slot['tenbacsi']); ?></h3>
                                <p><?php echo $escape($slot['trinhdo'] ?: 'Bác sĩ'); ?></p>
                                <span class="slot-specialty"><?php echo $escape($slot['chuyenkhoa']); ?></span>
                            </div>

                            <form class="booking-form" method="post" action="dat-kham.php">
                                <input type="hidden" name="hanh_dong" value="dat_lich">
                                <input type="hidden" name="ngay" value="<?php echo $escape($ngay); ?>">
                                <input type="hidden" name="machuyenkhoa" value="<?php echo $escape($machuyenkhoa); ?>">
                                <input type="hidden" name="mabacsi" value="<?php echo $escape($mabacsi); ?>">
                                <input type="hidden" name="malichlamviec" value="<?php echo $escape($slot['malichlamviec']); ?>">
                                <?php if ($isLoggedIn): ?>
                                    <label class="sr-only" for="lydo-<?php echo $escape($slot['malichlamviec']); ?>">Lý do khám</label>
                                    <input
                                        class="reason-input"
                                        type="text"
                                        id="lydo-<?php echo $escape($slot['malichlamviec']); ?>"
                                        name="lydo"
                                        maxlength="255"
                                        placeholder="Lý do khám (tùy chọn)"
                                    >
                                <?php endif; ?>
                                <button class="book-button" type="submit">
                                    <?php echo $isLoggedIn ? 'Đặt lịch' : 'Đăng nhập để đặt'; ?>
                                </button>
                            </form>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon" aria-hidden="true">
                        <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.6">
                            <rect x="9" y="11" width="30" height="28" rx="5"></rect>
                            <path d="M15 8v7M33 8v7M9 20h30M17 27h5M26 27h5M17 33h5"></path>
                        </svg>
                    </div>
                    <h3>Chưa có khung giờ phù hợp</h3>
                    <p>Thử chọn ngày khác hoặc bỏ bớt một bộ lọc để xem thêm lịch khám.</p>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <?php require __DIR__ . '/../../../include/footer.php'; ?>
</body>
</html>
