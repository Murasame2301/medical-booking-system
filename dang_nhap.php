<?php
require_once __DIR__ . '/app/Core/khoitao.php';

if (isset($_SESSION['id'])) {
    chuyen_trang('index.php');
}

$cu = lay_du_lieu_cu();

hien_dau_trang('Đăng nhập');
?>
<div class="khung">
    <h2>Đăng nhập</h2>

    <?php hien_thong_bao_phien(); ?>

    <form method="post" action="app/Controllers/xuly_dang_nhap.php">
        <?php
        nhap('Số điện thoại', 'sdt', 'tel', $cu['sdt'] ?? '');
        nhap('Mật khẩu', 'matkhau', 'password');
        ?>
        <button type="submit" class="nut">Đăng nhập</button>
    </form>

    <a class="lien-ket" href="dang_ky.php">Chưa có tài khoản? Đăng ký ngay</a>
    <a class="lien-ket" href="index.php">Quay lại trang chủ</a>
</div>
<?php
hien_cuoi_trang();