<?php

$message = $viewData['message'] ?? '';
$messageType = $viewData['messageType'] ?? '';
$taiKhoan = $viewData['taiKhoan'] ?? [];
$hoSo = $viewData['hoSo'] ?? [];
$hoSoFormValues = $viewData['hoSoFormValues'] ?? [];
$hoSoError = $viewData['hoSoError'] ?? '';
$hoSoIncomplete = !empty($viewData['hoSoIncomplete']);
$moHoSo = !empty($viewData['moHoSo']);
// Loi truy van tai khoan: khong biet trang thai that, khong hien "Bi khoa".
$taiKhoanLoi = !empty($viewData['taiKhoanLoi']);

$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$hienThi = static function ($value) {
    $value = trim((string) $value);
    return $value !== '' ? $value : 'Chưa cập nhật';
};

$laHoatDong = ($taiKhoan['trangthai'] ?? '') === 'hoat_dong';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Xem và cập nhật thông tin tài khoản, hồ sơ bệnh nhân và mật khẩu.">
    <title>Quản lý tài khoản</title>
    <link rel="stylesheet" href="assets/css/benh-nhan/appointment.css?v=7">
    <link rel="stylesheet" href="assets/css/benh-nhan/tai-khoan.css?v=3">
    <link rel="stylesheet" href="assets/css/benh-nhan/nav-menu.css?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/benh-nhan/footer.css?v=4">
    <script src="assets/js/benh-nhan/ajax.js?v=6" defer></script>
    <script src="assets/js/benh-nhan/account.js" defer></script>
</head>

<body class="appointment-page account-page">
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

    <main class="account-main">
        <div class="account-breadcrumb">
            <a href="index.php?page=trang-chu">Trang chủ</a>
            <span aria-hidden="true">/</span>
            <span>Quản lý tài khoản</span>
        </div>

        <div class="account-heading">
            <div>
                <h1>Quản lý tài khoản</h1>
            </div>
            <?php if (!$taiKhoanLoi): ?>
            <span class="account-badge account-badge-<?php echo $laHoatDong ? 'active' : 'locked'; ?>">
                <?php echo $laHoatDong ? 'Đang hoạt động' : 'Bị khoá'; ?>
            </span>
            <?php endif; ?>
        </div>

        <nav class="account-tabs" aria-label="Quản lý tài khoản">
            <a class="account-tab account-tab-active" href="index.php?page=thong-tin-tai-khoan" aria-current="page">
                <i class="fa-regular fa-id-card" aria-hidden="true"></i>
                Thông tin tài khoản
            </a>
            <a class="account-tab" href="index.php?page=doi-mat-khau">
                <i class="fa-solid fa-key" aria-hidden="true"></i>
                Đổi mật khẩu
            </a>
        </nav>

        <?php if ($message !== ''): ?>
            <div class="account-alert account-alert-<?php echo $escape($messageType ?: 'error'); ?>"
                role="<?php echo $messageType === 'success' ? 'status' : 'alert'; ?>">
                <?php echo $escape($message); ?>
            </div>
        <?php endif; ?>

        <section class="account-card account-card-info" aria-labelledby="account-info-title">
            <div class="account-card-head">
                <div>
                    <h2 id="account-info-title">Thông tin tài khoản</h2>
                </div>
                <i class="fa-regular fa-id-card account-card-icon" aria-hidden="true"></i>
            </div>

            <?php if ($taiKhoanLoi): ?>
            <p class="account-note" role="note">Chưa tải được thông tin tài khoản. Vui lòng tải lại trang.</p>
            <?php else: ?>
            <dl class="account-fields">
                <div>
                    <dt>Số điện thoại đăng nhập</dt>
                    <dd><?php echo $escape($taiKhoan['sdt'] ?? ''); ?></dd>
                </div>
                <div>
                    <dt>Email</dt>
                    <dd id="account-email-value"><?php echo $escape($hienThi($taiKhoan['email'] ?? '')); ?></dd>
                </div>
                <div>
                    <dt>Trạng thái</dt>
                    <dd><?php echo $laHoatDong ? 'Đang hoạt động' : 'Bị khoá'; ?></dd>
                </div>
            </dl>
            <?php endif; ?>
        </section>

        <?php require __DIR__ . '/partials/profile-card.php'; ?>
    </main>

    <?php require __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>