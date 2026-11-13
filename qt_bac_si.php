<?php
require_once 'includes/khoitao.php';

bat_buoc_quan_tri();

$ds = lay_ds_bac_si_quan_tri($conn);

hien_dau_trang('Quản lý bác sĩ');
?>
<section class="khu rong">
    <h2 class="tieu-de-khu">Quản lý bác sĩ</h2>

    <?php hien_thong_bao_phien(); ?>

    <p class="trong"><a href="qt_bac_si_form.php" class="nut-nho">+ Thêm bác sĩ</a></p>

    <?php if (count($ds) == 0): ?>
        <p class="trong">Chưa có bác sĩ nào.</p>
    <?php else: ?>
        <div class="bang-cuon">
            <table class="bang">
                <tr>
                    <th>Họ tên</th>
                    <th>Số điện thoại</th>
                    <th>Trình độ</th>
                    <th>Chuyên khoa</th>
                    <th>Trạng thái</th>
                    <th></th>
                </tr>
                <?php foreach ($ds as $b): ?>
                    <tr>
                        <td><?php echo h($b['HoTen']); ?></td>
                        <td><?php echo h($b['SDT']); ?></td>
                        <td><?php echo h($b['TrinhDo']); ?></td>
                        <td><?php echo h($b['ChuyenKhoa']); ?></td>
                        <td>
                            <?php if ($b['TrangThai'] == 'hoat_dong'): ?>
                                <span class="trangthai tt-xacnhan">Hoạt động</span>
                            <?php else: ?>
                                <span class="trangthai tt-huy">Bị khóa</span>
                            <?php endif; ?>
                        </td>
                        <td><a href="qt_bac_si_form.php?id=<?php echo h($b['ID']); ?>">Sửa</a></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php
hien_cuoi_trang();