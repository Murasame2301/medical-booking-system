<?php
require_once 'includes/khoitao.php';

// lay du lieu tu db
$dsChuyenKhoa = lay_chuyen_khoa($conn);
$dsBacSi = lay_bac_si($conn);

hien_dau_trang('Trang chủ');
?>
<section class="banner">
    <div class="rong">
        <h1>Đặt lịch khám bệnh trực tuyến</h1>
        <p>Chọn bác sĩ, chọn giờ khám ngay tại nhà. Nhanh chóng, không cần xếp hàng chờ đợi.</p>
        <a href="dat_lich.php" class="nut-lon">Đặt lịch khám</a>
    </div>
</section>

<section class="khu rong">
    <h2 class="tieu-de-khu">Đặt lịch chỉ với 3 bước</h2>
    <div class="luoi">
        <?php
        hien_the('1', 'Tạo tài khoản', 'Đăng ký nhanh bằng họ tên và ngày sinh.');
        hien_the('2', 'Chọn bác sĩ', 'Chọn chuyên khoa, bác sĩ và giờ khám phù hợp.');
        hien_the('3', 'Đến khám', 'Đến đúng giờ hẹn, không cần xếp hàng chờ.');
        ?>
    </div>
</section>

<section class="khu rong" id="chuyen-khoa">
    <h2 class="tieu-de-khu">Chuyên khoa</h2>
    <div class="luoi">
        <?php
        foreach ($dsChuyenKhoa as $khoa) {
            hien_the(bieu_tuong_chuyen_khoa($khoa['ChuyenKhoa']), $khoa['ChuyenKhoa']);
        }
        ?>
    </div>
</section>

<section class="khu rong" id="bac-si">
    <h2 class="tieu-de-khu">Đội ngũ bác sĩ</h2>
    <div class="luoi">
        <?php
        foreach ($dsBacSi as $bs) {
            hien_the('BS', $bs['HoTen'], $bs['ChuyenKhoa']);
        }
        ?>
    </div>
</section>
<?php
hien_cuoi_trang();