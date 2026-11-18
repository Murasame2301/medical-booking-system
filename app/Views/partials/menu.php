<?php
$isLoggedIn = !empty($_SESSION['is_logged_in']);
$currentPage = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
$accountPages = ['thong-tin-tai-khoan.php', 'doi-mat-khau.php'];
$isAccountPage = in_array($currentPage, $accountPages, true);
?>

<nav aria-label="Điều hướng chính">
    <a href="dat-kham.php">Đặt khám</a>
    <a href="xem-lich-kham.php">Lịch sử đặt khám</a>

    <?php if ($isLoggedIn): ?>
    <details class="nav-account">
        <summary aria-haspopup="menu">
            <span>Quản lý tài khoản</span>
            <i class="fa-solid fa-caret-down" aria-hidden="true"></i>
        </summary>
        <div class="nav-account-menu" role="menu">
            <a role="menuitem" class="<?php echo $currentPage === 'thong-tin-tai-khoan.php' ? 'is-current' : ''; ?>"
                href="thong-tin-tai-khoan.php" <?php echo $currentPage === 'thong-tin-tai-khoan.php' ? 'aria-current="page"' : ''; ?>>
                <i class="fa-regular fa-id-card" aria-hidden="true"></i>
                Thông tin tài khoản
            </a>
            <a role="menuitem" class="<?php echo $currentPage === 'doi-mat-khau.php' ? 'is-current' : ''; ?>"
                href="doi-mat-khau.php" <?php echo $currentPage === 'doi-mat-khau.php' ? 'aria-current="page"' : ''; ?>>
                <i class="fa-solid fa-key" aria-hidden="true"></i>
                Đổi mật khẩu
            </a>
            <a role="menuitem" href="logout.php">
                <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                Đăng xuất
            </a>
        </div>
    </details>
    <?php else: ?>
    <a href="dang-ky-tai-khoan.php">Đăng ký</a>
    <a href="dang-nhap.php">Đăng nhập</a>
    <?php endif; ?>
</nav>
