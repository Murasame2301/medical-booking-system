<?php

$message = $viewData['message'] ?? '';
$fullname = $viewData['fullname'] ?? '';
$phonenumber = $viewData['phonenumber'] ?? '';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký thành viên</title>
    <link rel="stylesheet" href="assets/css/benh-nhan/auth.css?v=8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/benh-nhan/footer.css?v=4">
    <script src="assets/js/benh-nhan/auth.js" defer></script>
</head>

<body class="register-page">
    <?php require __DIR__ . '/auth-header.php'; ?>

    <main class="auth-layout">
        <?php require __DIR__ . '/auth-story.php'; ?>
        <section class="auth-panel" aria-labelledby="auth-title">
            <div class="auth-content">
                <h1 id="auth-title">Tạo tài khoản của bạn</h1>
                <p class="form-intro">Để việc đặt lịch khám thuận tiện hơn.</p>

                <?php if ($message !== ''): ?>
                    <div class="form-alert" role="alert">
                        <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <form action="index.php?page=dang-ky" method="post">
                    <?php echo csrf_field('dang_ky'); ?>
                    <div class="field">
                        <label for="hoten">Họ và tên</label>
                        <input type="text" id="hoten" name="hoten"
                            value="<?php echo htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="name"
                            placeholder="Nhập họ và tên của bạn" required>
                    </div>

                    <div class="field">
                        <label for="sdt">Số điện thoại</label>
                        <input type="tel" id="sdt" name="sdt"
                            value="<?php echo htmlspecialchars($phonenumber, ENT_QUOTES, 'UTF-8'); ?>"
                            autocomplete="tel" placeholder="Nhập số điện thoại của bạn" required>
                    </div>

                    <div class="field">
                        <label for="mat_khau">Mật khẩu</label>
                        <div class="password-control">
                            <input type="password" id="mat_khau" name="mat_khau" autocomplete="new-password"
                                placeholder="Tạo mật khẩu" required>
                            <?php
                            $passwordFieldId = 'mat_khau';
                            require __DIR__ . '/password-toggle.php';
                            ?>
                        </div>
                    </div>

                    <div class="field">
                        <label for="nhap_lai_mat_khau">Nhập lại mật khẩu</label>
                        <div class="password-control">
                            <input type="password" id="nhap_lai_mat_khau" name="nhap_lai_mat_khau"
                                autocomplete="new-password" placeholder="Nhập lại mật khẩu vừa tạo" required>
                            <?php
                            $passwordFieldId = 'nhap_lai_mat_khau';
                            require __DIR__ . '/password-toggle.php';
                            ?>
                        </div>
                    </div>

                    <button class="primary-button" type="submit">Tạo tài khoản</button>
                </form>

                <p class="auth-switch">Đã có tài khoản? <a href="index.php?page=dang-nhap">Đăng nhập</a></p>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>