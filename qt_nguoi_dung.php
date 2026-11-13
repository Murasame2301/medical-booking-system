<?php
require_once 'includes/khoitao.php';

bat_buoc_quan_tri();

$tukhoa = trim($_GET['tukhoa'] ?? '');
$vaitro = (int)($_GET['vaitro'] ?? 0);

$ds_vaitro = lay_ds_vai_tro($conn);
$ds = lay_ds_nguoi_dung($conn, $tukhoa, $vaitro);

hien_dau_trang('Quản lý người dùng');
?>
<section class="khu rong">
    <h2 class="tieu-de-khu">Quản lý người dùng</h2>

    <?php hien_thong_bao_phien(); ?>
    <p id="thong_bao_ajax"></p>
    <!-- Bộ lọc: gửi bằng GET nên có thể lưu hoặc gửi lại đường dẫn -->
    <form method="get" class="bo-loc">
        <input type="text" name="tukhoa" placeholder="Tên hoặc số điện thoại" value="<?php echo h($tukhoa); ?>">
        <select name="vaitro">
            <option value="0">Tất cả vai trò</option>
            <?php foreach ($ds_vaitro as $ma => $ten): ?>
                <option value="<?php echo h($ma); ?>" <?php if ($ma == $vaitro) { echo 'selected'; } ?>>
                    <?php echo h($ten); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="nut-nho">Tìm</button>
    </form>

    <?php if (count($ds) == 0): ?>
        <p class="trong">Không tìm thấy người dùng nào.</p>
    <?php else: ?>
        <div class="bang-cuon">
            <table class="bang">
                <tr>
                    <th>Họ tên</th>
                    <th>Số điện thoại</th>
                    <th>Vai trò</th>
                    <th>Ngày tạo</th>
                    <th>Trạng thái</th>
                    <th></th>
                </tr>
                <?php foreach ($ds as $u): ?>
                    <tr>
                        <td><?php echo h($u['HoTen']); ?></td>
                        <td><?php echo h($u['SDT']); ?></td>
                        <td><?php echo h($u['TenVaiTro']); ?></td>
                        <td><?php echo h(date('d/m/Y', strtotime($u['NgayTao']))); ?></td>
                        <td class="o-trang-thai">
                            <?php if ($u['TrangThai'] == 'hoat_dong'): ?>
                                <span class="trangthai tt-xacnhan">Hoạt động</span>
                            <?php else: ?>
                                <span class="trangthai tt-huy">Bị khóa</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($u['VaiTro'] == 'quan_tri'): ?>
                                <span class="trong">—</span>
                            <?php else: ?>
                                <form method="post" action="xuly/xuly_khoa_mo_tai_khoan.php" class="form-khoa-mo">
                                    <?php
                                    o_an('id', $u['ID']);
                                    o_an('tukhoa', $tukhoa);   // gửi kèm để quay về đúng danh sách đang xem
                                    o_an('vaitro', $vaitro);
                                    ?>
                                    <?php if ($u['TrangThai'] == 'hoat_dong'): ?>
                                        <button type="submit" name="hanhdong" value="khoa" class="nut-huy">Khóa</button>
                                    <?php else: ?>
                                        <button type="submit" name="hanhdong" value="mo" class="nut-mo">Mở khóa</button>
                                    <?php endif; ?>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    <?php endif; ?>
</section>
<script src="js/qt_nguoi_dung.js"></script>
<?php
hien_cuoi_trang();