<?php
require_once 'includes/khoitao.php';

// Đã đăng nhập rồi thì không cần đăng ký
if (isset($_SESSION['id'])) {
    chuyen_trang('index.php');
}

// Dữ liệu đã gõ ở lần trước (nếu lần trước bị lỗi)
$cu = lay_du_lieu_cu();

hien_dau_trang('Đăng ký');
?>

<div class="khung">
    <h2>Đăng ký tài khoản</h2>

    <?php hien_thong_bao_phien(); ?>

    <form method="post" action="xuly/xuly_dang_ky.php">
        <?php
        nhap('Họ tên *', 'hoten', 'text', $cu['hoten'] ?? '');
        nhap('Ngày sinh *', 'ngaysinh', 'date', $cu['ngaysinh'] ?? '');
        nhap('Số điện thoại (dùng để đăng nhập)', 'sdt', 'tel', $cu['sdt'] ?? '');
        echo '<span id="thong_bao_sdt"></span>';
        nhap('Email', 'email', 'email', $cu['email'] ?? '', false);
        nhap('Mật khẩu *', 'matkhau', 'password');
        nhap('Nhập lại mật khẩu *', 'nhaplai', 'password');
        ?>
        <button type="submit" class="nut nut-luc">Đăng ký</button>
    </form>

    <a class="lien-ket" href="dang_nhap.php">Đã có tài khoản? Đăng nhập ngay</a>
</div>
<script src="js/dang_ky.js"></script>
<?php
hien_cuoi_trang();