<?php

$message = $viewData['message'] ?? '';
$phonenumber = $viewData['phonenumber'] ?? '';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="assets/css/auth.css?v=3">
    <link rel="stylesheet" href="assets/css/footer.css?v=1">
    <script src="assets/js/auth.js" defer></script>
</head>

<body>
    <?php require __DIR__ . '/../../../include/auth-header.php'; ?>

    <main class="auth-layout">
        <?php require __DIR__ . '/../../../include/auth-story.php'; ?>
        <section class="auth-panel" aria-labelledby="auth-title">
            <div class="auth-content">
                <h1 id="auth-title">Chào mừng bạn trở lại</h1>
                <p class="form-intro">Đăng nhập để đặt lịch và theo dõi lịch khám.</p>

                <?php if ($message !== ''): ?>
                    <div class="form-alert" role="alert">
                        <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <form action="" method="post">
                    <div class="field">
                        <label for="sdt">Số điện thoại</label>
                        <input
                            type="tel"
                            id="sdt"
                            name="sdt"
                            value="<?php echo htmlspecialchars($phonenumber, ENT_QUOTES, 'UTF-8'); ?>"
                            autocomplete="tel"
                            placeholder="Nhập số điện thoại của bạn"
                            required>
                    </div>

                    <div class="field">
                        <label for="mat_khau">Mật khẩu</label>
                        <div class="password-control">
                            <input
                                type="password"
                                id="mat_khau"
                                name="mat_khau"
                                autocomplete="current-password"
                                placeholder="Nhập mật khẩu"
                                required>
                            <?php
                            $passwordFieldId = 'mat_khau';
                            require __DIR__ . '/../../../include/auth-password-toggle.php';
                            ?>
                        </div>
                    </div>

                    <button class="primary-button" type="submit">Đăng nhập</button>
                </form>

                <p class="auth-switch">Chưa có tài khoản? <a href="dang-ky-tai-khoan.php">Đăng ký ngay</a></p>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/../../../include/footer.php'; ?>
</body>

</html>