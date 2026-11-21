<?php
/* Form them / sua bac si.
   Bien: $viewData['ma'] (0 = them), ['bs'] (du lieu dien san), ['ds_chuyenkhoa'],
   ['flash'] */
$ma = (int) ($viewData['ma'] ?? 0);
$bs = $viewData['bs'] ?? [];
$dsChuyenKhoa = $viewData['ds_chuyenkhoa'] ?? [];
$flash = $viewData['flash'] ?? null;
$dangSua = $ma > 0;
?>
<div class="khung">
    <h2><?= $dangSua ? 'Sửa thông tin bác sĩ' : 'Thêm bác sĩ' ?></h2>

    <?php require __DIR__ . '/partials/thong-bao.php'; ?>

    <form method="post" action="index.php?page=quan-tri/bac-si/luu">
        <?= csrf_field() ?>
        <input type="hidden" name="manguoidung" value="<?= $ma ?>">

        <label for="hoten">Họ tên *</label>
        <input type="text" id="hoten" name="hoten" value="<?= h($bs['hoten'] ?? '') ?>" maxlength="100" required>

        <label for="sdt">Số điện thoại * (dùng để đăng nhập)</label>
        <input type="tel" id="sdt" name="sdt" value="<?= h($bs['sdt'] ?? '') ?>" maxlength="11" required>
        <span id="thong_bao_sdt"></span>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= h($bs['email'] ?? '') ?>" maxlength="100">
        <span id="thong_bao_email"></span>

        <!-- Them moi: mat khau bat buoc. Sua: de trong nghia la giu nguyen mat khau cu. -->
        <label for="matkhau"><?= $dangSua ? 'Mật khẩu mới (để trống nếu không đổi)' : 'Mật khẩu ban đầu *' ?></label>
        <input type="password" id="matkhau" name="matkhau" autocomplete="new-password"
            minlength="8" <?= $dangSua ? '' : 'required' ?>>

        <label for="trinhdo">Trình độ</label>
        <input type="text" id="trinhdo" name="trinhdo" value="<?= h($bs['trinhdo'] ?? '') ?>" maxlength="100">

        <label for="gioithieu">Giới thiệu</label>
        <textarea id="gioithieu" name="gioithieu" rows="4"><?= h($bs['gioithieu'] ?? '') ?></textarea>

        <label>Chuyên khoa * (chọn ít nhất một)</label>
        <div class="nhom-tich">
            <?php $daChon = array_map('intval', (array) ($bs['chuyenkhoa'] ?? [])); ?>
            <?php foreach ($dsChuyenKhoa as $maCK => $tenCK): ?>
                <label class="o-tich">
                    <input type="checkbox" name="chuyenkhoa[]" value="<?= (int) $maCK ?>"
                        <?= in_array((int) $maCK, $daChon, true) ? 'checked' : '' ?>>
                    <?= h($tenCK) ?>
                </label>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="nut nut-luc">Lưu</button>
    </form>

    <a class="lien-ket" href="index.php?page=quan-tri/bac-si">Quay lại danh sách</a>
</div>
