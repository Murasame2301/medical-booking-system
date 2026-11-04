<?php
$isLoggedIn = !empty($_SESSION['is_logged_in']);
$displayName = htmlspecialchars($_SESSION['user_fullname'] ?? '');

<nav class="main-nav">
<a href="dang-ky-tai-khoan.php">Đăng ký</a>
<a href="dang-nhap.php">Đăng nhập</a>
<a href=""></a>
</nav>
?>