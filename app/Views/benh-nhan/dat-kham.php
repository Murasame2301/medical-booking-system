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
    <link rel="stylesheet" href="assets/css/benh-nhan/appointment.css?v=7">
    <link rel="stylesheet" href="assets/css/benh-nhan/nav-menu.css?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/benh-nhan/footer.css?v=4">
    <script src="assets/js/benh-nhan/ajax.js?v=6" defer></script>
</head>

<body class="appointment-page">
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

    <main>
        <section class="appointment-content" aria-labelledby="search-title">
            <div class="search-panel">
                <div class="section-heading">
                    <h1 id="search-title">Tìm khung giờ trống</h1>
                    <p class="section-description">Chọn ngày khám và một bộ lọc nếu bạn muốn tìm nhanh hơn.</p>
                </div>

                <form class="search-form" method="get" action="index.php" data-ajax="tim_khung_gio">
                    <input type="hidden" name="page" value="dat-kham">
                    <div class="search-field search-field-date">
                        <label for="ngay">Ngày khám</label>
                        <input type="date" id="ngay" name="ngay" value="<?php echo $escape($ngay); ?>"
                            min="<?php echo $escape(date('Y-m-d')); ?>" required>
                    </div>
                    <div class="search-field">
                        <label for="machuyenkhoa">Chuyên khoa</label>
                        <select id="machuyenkhoa" name="machuyenkhoa">
                            <option value="">Tất cả chuyên khoa</option>
                            <?php foreach ($specialties as $specialty): ?>
                            <option value="<?php echo $escape($specialty['machuyenkhoa']); ?>"
                                <?php echo (string) $machuyenkhoa === (string) $specialty['machuyenkhoa'] ? 'selected' : ''; ?>>
                                <?php echo $escape($specialty['tenchuyenkhoa']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="search-field">
                        <label for="mabacsi">Bác sĩ</label>
                        <select id="mabacsi" name="mabacsi">
                            <?php require __DIR__ . '/partials/doctor-options.php'; ?>
                        </select>
                    </div>
                    <button class="search-button" type="submit">
                        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                        Tìm lịch trống
                    </button>
                </form>

                <div id="search-alert">
                    <?php require __DIR__ . '/partials/search-alert.php'; ?>
                </div>
            </div>

            <div id="slot-results">
                <?php require __DIR__ . '/partials/slot-results.php'; ?>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>