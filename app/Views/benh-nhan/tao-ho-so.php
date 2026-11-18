<?php

$message = $viewData['message'] ?? '';
$userFound = $viewData['userFound'] ?? false;
$hoten = $viewData['hoten'] ?? '';
$ngaysinh = $viewData['ngaysinh'] ?? '';
$gioitinh = $viewData['gioitinh'] ?? '';
$email = $viewData['email'] ?? '';
$diachi = $viewData['diachi'] ?? '';
$bhyt = $viewData['bhyt'] ?? '';
$cccd = $viewData['cccd'] ?? '';
$dantoc = $viewData['dantoc'] ?? '';

$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo hồ sơ khám bệnh</title>
    <link rel="stylesheet" href="assets/css/benh-nhan/auth.css?v=8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/benh-nhan/footer.css?v=4">
    <script src="assets/js/benh-nhan/auth.js" defer></script>
</head>

<body class="profile-page">
    <?php require __DIR__ . '/../auth/auth-header.php'; ?>

    <main class="auth-layout">
        <?php require __DIR__ . '/../auth/auth-story.php'; ?>
        <section class="auth-panel" aria-labelledby="auth-title">
            <div class="auth-content">
                <h1 id="auth-title">Hoàn thiện hồ sơ khám bệnh</h1>

                <?php if ($message !== ''): ?>
                    <div class="form-alert" role="alert">
                        <?php echo $escape($message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($userFound): ?>
                    <?php
                    $hoSoFormValues = [
                        'hoten' => $hoten,
                        'ngaysinh' => $ngaysinh,
                        'gioitinh' => $gioitinh,
                        'email' => $email,
                        'diachi' => $diachi,
                        'bhyt' => $bhyt,
                        'cccd' => $cccd,
                        'dantoc' => $dantoc,
                    ];
                    $hoSoAction = '';
                    $hoSoHanhDong = '';
                    $hoSoNameEditable = false;
                    $hoSoShowEmail = true;
                    $hoSoShowExtras = true;
                    $hoSoIdSuffix = 'tao_ho_so';
                    $hoSoSubmitLabel = 'Lưu hồ sơ';
                    $hoSoError = '';
                    $hoSoCsrfForm = 'ho_so';
                    require __DIR__ . '/partials/ho-so-form.php';
                    ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/../partials/footer.php'; ?>
</body>

</html>
