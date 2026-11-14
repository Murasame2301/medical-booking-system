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

    <div id="khu_bang">
        <?php hien_bang_nguoi_dung($ds, $tukhoa, $vaitro) ?>
    </div>
</section>
<script src="js/qt_nguoi_dung.js"></script>
<?php
hien_cuoi_trang();