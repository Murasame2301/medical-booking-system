<?php

// Form ho so dung chung cho tao-ho-so.php (profile/edit.php) va trang quan ly tai khoan.
// View cha thuong da dinh nghia $escape; neu chua thi partial tu lo de khong vo khi require rieng.

if (!isset($escape) || !is_callable($escape)) {
    $escape = static function ($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    };
}

$hoSoFormValues = $hoSoFormValues ?? [];
$hoSoAction = $hoSoAction ?? '';
$hoSoHanhDong = $hoSoHanhDong ?? '';
$hoSoNameEditable = !empty($hoSoNameEditable);
$hoSoShowEmail = $hoSoShowEmail ?? true;
$hoSoShowExtras = !empty($hoSoShowExtras);
$hoSoIdSuffix = (string) ($hoSoIdSuffix ?? 'ho_so');
$hoSoSubmitLabel = $hoSoSubmitLabel ?? 'Lưu hồ sơ';
$hoSoNote = $hoSoNote ?? '';
$hoSoError = $hoSoError ?? '';
$hoSoCsrfForm = $hoSoCsrfForm ?? 'ho_so';
$hoSoAjax = !empty($hoSoAjax);

$val = static function ($key) use ($hoSoFormValues) {
    return (string) ($hoSoFormValues[$key] ?? '');
};
$sfx = $escape($hoSoIdSuffix);
?>
<form class="ho-so-form" method="post" action="<?php echo $escape($hoSoAction); ?>"
    <?php echo $hoSoAjax ? 'data-ajax="cap_nhat_ho_so"' : ''; ?>>
    <?php echo csrf_field($hoSoCsrfForm); ?>
    <?php if ($hoSoHanhDong !== ''): ?>
    <input type="hidden" name="hanh_dong" value="<?php echo $escape($hoSoHanhDong); ?>">
    <?php endif; ?>

    <?php if ($hoSoError !== ''): ?>
    <div class="ho-so-alert" role="alert"><?php echo $escape($hoSoError); ?></div>
    <?php endif; ?>

    <div class="ho-so-grid">
        <div class="field ho-so-field-wide">
            <label for="hoten_<?php echo $sfx; ?>">Họ và tên</label>
            <?php if ($hoSoNameEditable): ?>
            <input type="text" id="hoten_<?php echo $sfx; ?>" name="hoten"
                value="<?php echo $escape($val('hoten')); ?>" maxlength="100" autocomplete="name" required>
            <?php else: ?>
            <input type="text" id="hoten_<?php echo $sfx; ?>" value="<?php echo $escape($val('hoten')); ?>"
                readonly>
            <?php endif; ?>
        </div>

        <div class="field">
            <label for="ngaysinh_<?php echo $sfx; ?>">Ngày sinh</label>
            <input type="date" id="ngaysinh_<?php echo $sfx; ?>" name="ngaysinh"
                value="<?php echo $escape($val('ngaysinh')); ?>" autocomplete="bday" required>
        </div>

        <div class="field gender-field">
            <span class="field-label">Giới tính</span>
            <div class="gender-options">
                <label class="radio-option" for="gioitinh_nam_<?php echo $sfx; ?>">
                    <input type="radio" id="gioitinh_nam_<?php echo $sfx; ?>" name="gioitinh" value="nam"
                        <?php echo $val('gioitinh') === 'nam' ? 'checked' : ''; ?> required>
                    <span>Nam</span>
                </label>
                <label class="radio-option" for="gioitinh_nu_<?php echo $sfx; ?>">
                    <input type="radio" id="gioitinh_nu_<?php echo $sfx; ?>" name="gioitinh" value="nu"
                        <?php echo $val('gioitinh') === 'nu' ? 'checked' : ''; ?>>
                    <span>Nữ</span>
                </label>
            </div>
        </div>

        <?php if ($hoSoShowEmail): ?>
        <div class="field ho-so-field-wide">
            <label for="email_<?php echo $sfx; ?>">Email</label>
            <input type="email" id="email_<?php echo $sfx; ?>" name="email"
                value="<?php echo $escape($val('email')); ?>" maxlength="100" autocomplete="email"
                placeholder="Nhập email của bạn">
        </div>
        <?php endif; ?>

        <div class="field ho-so-field-wide">
            <label for="diachi_<?php echo $sfx; ?>">Địa chỉ</label>
            <input type="text" id="diachi_<?php echo $sfx; ?>" name="diachi"
                value="<?php echo $escape($val('diachi')); ?>" maxlength="255" autocomplete="street-address"
                placeholder="Nhập địa chỉ hiện tại" required>
        </div>

        <div class="field">
            <label for="bhyt_<?php echo $sfx; ?>">Số BHYT</label>
            <input type="text" id="bhyt_<?php echo $sfx; ?>" name="bhyt" value="<?php echo $escape($val('bhyt')); ?>"
                maxlength="20" autocomplete="off" placeholder="Nhập số BHYT của bạn" required>
        </div>

        <?php if ($hoSoShowExtras): ?>
        <div class="field">
            <label for="cccd_<?php echo $sfx; ?>">Số CCCD</label>
            <input type="text" id="cccd_<?php echo $sfx; ?>" name="cccd" value="<?php echo $escape($val('cccd')); ?>"
                maxlength="12" inputmode="numeric" autocomplete="off" placeholder="12 chữ số">
        </div>

        <div class="field">
            <label for="dantoc_<?php echo $sfx; ?>">Dân tộc</label>
            <input type="text" id="dantoc_<?php echo $sfx; ?>" name="dantoc"
                value="<?php echo $escape($val('dantoc')); ?>" maxlength="15" autocomplete="off"
                placeholder="Ví dụ: Kinh">
        </div>
        <?php endif; ?>
    </div>

    <button class="ho-so-submit" type="submit"><?php echo $escape($hoSoSubmitLabel); ?></button>
    <?php if ($hoSoNote !== ''): ?>
    <p class="form-note"><?php echo $escape($hoSoNote); ?></p>
    <?php endif; ?>
</form>
