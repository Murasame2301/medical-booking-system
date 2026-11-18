<?php
/* Danh sach nguoi dung, kem bo loc va o tim kiem.
   Bien: $viewData['ds'], ['tukhoa'], ['vaitro'], ['ds_vaitro'], ['flash'] */
$ds = $viewData['ds'] ?? [];
$tukhoa = $viewData['tukhoa'] ?? '';
$vaitro = (int) ($viewData['vaitro'] ?? 0);
$dsVaiTro = $viewData['ds_vaitro'] ?? [];
$flash = $viewData['flash'] ?? null;
?>
<section class="khu rong">
    <h2 class="tieu-de-khu">Quản lý người dùng</h2>

    <?php require __DIR__ . '/partials/thong-bao.php'; ?>
    <p id="thong_bao_ajax"></p>

    <!-- Bo loc: gui bang GET nen co the luu hoac gui lai duong dan -->
    <form method="get" class="bo-loc" id="form-loc-nguoi-dung">
        <input type="hidden" name="page" value="quan-tri/nguoi-dung">
        <input type="text" name="tukhoa" placeholder="Tên hoặc số điện thoại" value="<?= h($tukhoa) ?>">
        <select name="vaitro">
            <option value="0">Tất cả vai trò</option>
            <?php foreach ($dsVaiTro as $ma => $ten): ?>
                <option value="<?= h($ma) ?>" <?= (int) $ma === $vaitro ? 'selected' : '' ?>>
                    <?= h($ten) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="nut-nho">Tìm</button>
    </form>

    <div id="khu_bang">
        <?php
        $tukhoaBang = $tukhoa;
        $vaitroBang = $vaitro;
        $nguoiHienTai = ma_nguoi_dung_hien_tai();
        require __DIR__ . '/partials/bang-nguoi-dung.php';
        ?>
    </div>
</section>
