<?php
require_once 'includes/khoitao.php';

bat_buoc_benh_nhan();

$cu = lay_du_lieu_cu();                        // lựa chọn lần trước (nếu bị lỗi)
$id_bacsi = (int)($_GET['bacsi'] ?? 0);        // bác sĩ đang chọn (0 = chưa chọn)

// Danh sách bác sĩ cho ô chọn: [ID => "Tên - Chuyên khoa"]
$ds_bacsi = [];
foreach (lay_bac_si($conn) as $bs) {
    $ds_bacsi[$bs['ID']] = $bs['HoTen'] . ' - ' . $bs['ChuyenKhoa'];
}

// Đã chọn bác sĩ thì lấy các khung giờ còn trống: [ID khung giờ => "dd/mm/yyyy hh:mm - hh:mm"]
$ds_gio = [];
if (isset($ds_bacsi[$id_bacsi])) {
    foreach (lay_gio_trong($conn, $id_bacsi) as $g) {
        $ds_gio[$g['malichlamviec']] = date('d/m/Y H:i', strtotime($g['thoigianbatdau']))
            . ' - ' . date('H:i', strtotime($g['thoigianketthuc']));
    }
}

hien_dau_trang('Đặt lịch khám');
?>
<div class="khung">
    <h2>Đặt lịch khám</h2>

    <?php hien_thong_bao_phien(); ?>

    <!-- Bước 1: chọn bác sĩ (GET, chỉ để xem giờ trống) -->
    <form method="get">
        <?php chon('Bác sĩ *', 'bacsi', $ds_bacsi, $id_bacsi); ?>
        <button type="submit" class="nut">Xem giờ trống</button>
    </form>

    <?php if (isset($ds_bacsi[$id_bacsi])): ?>
        <hr>
        <?php if (count($ds_gio) == 0): ?>
            <p class="trong">Bác sĩ này hiện chưa có khung giờ trống.</p>
        <?php else: ?>
            <!-- Bước 2: chọn khung giờ (POST sang file xử lý) -->
            <form method="post" action="xuly/xuly_dat_lich.php">
                <?php
                o_an('bacsi', $id_bacsi);
                chon('Khung giờ khám *', 'giokham', $ds_gio, $cu['giokham'] ?? '');
                nhap('Lý do khám', 'lydo', 'text', $cu['lydo'] ?? '', false);
                ?>
                <button type="submit" class="nut nut-luc">Xác nhận đặt lịch</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>

    <a class="lien-ket" href="lich_cua_toi.php">Xem lịch khám của tôi</a>
</div>
<?php
hien_cuoi_trang();