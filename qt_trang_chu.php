<?php
require_once 'includes/khoitao.php';

bat_buoc_quan_tri();   // chốt chặn: chỉ quản trị viên mới vào được

$tk = thong_ke_tong_quan($conn);

hien_dau_trang('Quản trị');
?>
<section class="khu rong">
    <h2 class="tieu-de-khu">Trang quản trị</h2>

    <?php hien_thong_bao_phien(); ?>

    <div class="luoi">
        <?php
        hien_the($tk['benh_nhan'], 'Bệnh nhân', '', 'qt_nguoi_dung.php', 'Quản lý');
        hien_the($tk['bac_si'], 'Bác sĩ', '', 'qt_bac_si.php', 'Quản lý');
        hien_the($tk['lich_cho'], 'Lịch chờ xác nhận');
        hien_the($tk['tai_khoan_khoa'], 'Tài khoản bị khóa', '', 'qt_nguoi_dung.php', 'Xem');
        ?>
    </div>
</section>
<?php
hien_cuoi_trang();