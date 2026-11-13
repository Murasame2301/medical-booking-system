<?php
require_once 'includes/khoitao.php';

$id_chuyenkhoa = (int)($_GET['chuyenkhoa'] ?? 0);   // 0 = tất cả

$ds_chuyenkhoa = lay_chuyen_khoa($conn);
$ds_bacsi = lay_bac_si($conn, $id_chuyenkhoa);

hien_dau_trang('Đội ngũ bác sĩ');
?>
<section class="khu rong">
    <h2 class="tieu-de-khu">Đội ngũ bác sĩ</h2>

    <!-- Bộ lọc theo chuyên khoa -->
    <form method="get" class="bo-loc">
        <select name="chuyenkhoa">
            <option value="0">Tất cả chuyên khoa</option>
            <?php foreach ($ds_chuyenkhoa as $ck): ?>
                <option value="<?php echo h($ck['ID']); ?>" <?php if ($ck['ID'] == $id_chuyenkhoa) { echo 'selected'; } ?>>
                    <?php echo h($ck['ChuyenKhoa']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="nut-nho">Lọc</button>
    </form>

    <?php if (count($ds_bacsi) == 0): ?>
        <p class="trong">Không có bác sĩ nào thuộc chuyên khoa này.</p>
    <?php else: ?>
        <div class="luoi">
            <?php
            foreach ($ds_bacsi as $bs) {
                hien_the('BS', $bs['HoTen'], $bs['ChuyenKhoa'], 'dat_lich.php?bacsi=' . $bs['ID'], 'Đặt lịch');
            }
            ?>
        </div>
    <?php endif; ?>
</section>
<?php
hien_cuoi_trang();