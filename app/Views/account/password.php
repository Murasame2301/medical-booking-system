<?php

$message = $viewData['message'] ?? '';
$messageType = $viewData['messageType'] ?? '';

$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Đổi mật khẩu đăng nhập của tài khoản đặt lịch khám.">
    <title>Đổi mật khẩu</title>
    <link rel="stylesheet" href="assets/css/appointment.css?v=7">
    <link rel="stylesheet" href="assets/css/tai-khoan.css?v=2">
    <link rel="stylesheet" href="assets/css/nav-menu.css?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/footer.css?v=4">
    <script src="assets/js/account.js" defer></script>
</head>

<body class="appointment-page account-page">
    <header class="appointment-header">
        <a class="appointment-brand" href="index.php" aria-label="Đặt lịch khám — Trang chủ">
            <img class="appointment-brand-logo" src="assets/images/logo.png?v=2" width="28" height="28"
                alt="Logo cổng đặt lịch khám">
            <span>
                <span class="appointment-brand-name">Đặt lịch khám</span>
                <span class="appointment-brand-description">Cổng đặt lịch khám bệnh trực tuyến</span>
            </span>
        </a>

        <div class="appointment-nav">
            <?php require __DIR__ . '/../partials/menu.php'; ?>
        </div>
    </header>

    <main class="account-main">
        <div class="account-breadcrumb">
            <a href="thong-tin-tai-khoan.php">Quản lý tài khoản</a>
            <span aria-hidden="true">/</span>
            <span>Đổi mật khẩu</span>
        </div>

        <div class="account-heading">
            <div>
                <p class="account-eyebrow">Bảo mật tài khoản</p>
                <h1>Đổi mật khẩu</h1>
            </div>
        </div>

        <nav class="account-tabs" aria-label="Quản lý tài khoản">
            <a class="account-tab" href="thong-tin-tai-khoan.php">
                <i class="fa-regular fa-id-card" aria-hidden="true"></i>
                Thông tin tài khoản
            </a>
            <a class="account-tab account-tab-active" href="doi-mat-khau.php" aria-current="page">
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

        <section class="account-card account-card-security account-card-standalone"
            aria-labelledby="password-title">
            <div class="account-card-head">
                <div>
                    <h2 id="password-title">Mật khẩu đăng nhập</h2>
                </div>
                <i class="fa-solid fa-lock account-card-icon" aria-hidden="true"></i>
            </div>

            <form class="account-form" method="post" action="doi-mat-khau.php">
                <?php echo csrf_field('doi_mat_khau'); ?>

                <div class="account-form-grid">
                    <div class="field account-field-wide">
                        <label for="mat_khau_hien_tai">Mật khẩu hiện tại</label>
                        <div class="password-control">
                            <input type="password" id="mat_khau_hien_tai" name="mat_khau_hien_tai"
                                autocomplete="current-password" placeholder="Nhập mật khẩu đang dùng" required>
                            <button class="account-password-toggle" type="button"
                                data-password-target="mat_khau_hien_tai" aria-pressed="false"
                                aria-label="Hiện mật khẩu" hidden>
                                <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                <i class="fa-solid fa-eye-slash" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>

                    <div class="field">
                        <label for="mat_khau_moi">Mật khẩu mới</label>
                        <div class="password-control">
                            <input type="password" id="mat_khau_moi" name="mat_khau_moi"
                                autocomplete="new-password" placeholder="Ít nhất 8 ký tự" required>
                            <button class="account-password-toggle" type="button"
                                data-password-target="mat_khau_moi" aria-pressed="false" aria-label="Hiện mật khẩu"
                                hidden>
                                <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                <i class="fa-solid fa-eye-slash" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>

                    <div class="field">
                        <label for="nhap_lai_mat_khau_moi">Nhập lại mật khẩu mới</label>
                        <div class="password-control">
                            <input type="password" id="nhap_lai_mat_khau_moi" name="nhap_lai_mat_khau"
                                autocomplete="new-password" placeholder="Nhập lại mật khẩu mới" required
                                data-password-confirm-for="mat_khau_moi">
                            <button class="account-password-toggle" type="button"
                                data-password-target="nhap_lai_mat_khau_moi" aria-pressed="false"
                                aria-label="Hiện mật khẩu" hidden>
                                <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                <i class="fa-solid fa-eye-slash" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <button class="account-submit account-submit-danger" type="submit">Đổi mật khẩu</button>
            </form>
        </section>
    </main>

    <?php require __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>
