<?php
$isLoggedIn = da_dang_nhap();
$currentPage = (string) ($_GET['page'] ?? 'trang-chu');
$accountPages = ['thong-tin-tai-khoan', 'doi-mat-khau'];
$isAccountPage = in_array($currentPage, $accountPages, true);
?>

<nav aria-label="Điều hướng chính">
    <a href="index.php?page=dat-kham">Đặt khám</a>
    <a href="index.php?page=lich-cua-toi">Lịch sử đặt khám</a>

    <?php if ($isLoggedIn): ?>
    <details class="nav-account">
        <summary aria-haspopup="menu">
            <span>Quản lý tài khoản</span>
            <i class="fa-solid fa-caret-down" aria-hidden="true"></i>
        </summary>
        <div class="nav-account-menu" role="menu">
            <a role="menuitem" class="<?php echo $currentPage === 'thong-tin-tai-khoan' ? 'is-current' : ''; ?>"
                href="index.php?page=thong-tin-tai-khoan" <?php echo $currentPage === 'thong-tin-tai-khoan' ? 'aria-current="page"' : ''; ?>>
                <i class="fa-regular fa-id-card" aria-hidden="true"></i>
                Thông tin tài khoản
            </a>
            <a role="menuitem" class="<?php echo $currentPage === 'doi-mat-khau' ? 'is-current' : ''; ?>"
                href="index.php?page=doi-mat-khau" <?php echo $currentPage === 'doi-mat-khau' ? 'aria-current="page"' : ''; ?>>
                <i class="fa-solid fa-key" aria-hidden="true"></i>
                Đổi mật khẩu
            </a>
            <a role="menuitem" href="index.php?page=dang-xuat">
                <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                Đăng xuất
            </a>
        </div>
    </details>
    <?php else: ?>
    <a href="index.php?page=dang-ky">Đăng ký</a>
    <a href="index.php?page=dang-nhap">Đăng nhập</a>
    <?php endif; ?>
</nav>
