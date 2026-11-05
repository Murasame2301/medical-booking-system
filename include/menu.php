<?php
$isLoggedIn = !empty($_SESSION['is_logged_in']);
?>

<nav aria-label="Điều hướng chính">
    <a href="tim-lich-kham.php">Đặt khám</a>
    <a href="xem-lich-kham.php">Lịch sử đặt khám</a>
    <a href="tao-ho-so.php">Hồ sơ khám bệnh</a>

    <?php if ($isLoggedIn): ?>
        <a href="quan-ly-tai-khoan.php">Quản lý tài khoản</a>
        <a href="logout.php">Đăng xuất</a>
    <?php else: ?>
        <a href="dang-ky-tai-khoan.php">Đăng ký</a>
        <a href="dang-nhap.php">Đăng nhập</a>
    <?php endif; ?>
</nav>
