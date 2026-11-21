<?php

// The "Ho so benh nhan": phan hien thi chi doc + drawer chinh sua.
// Tach ra de sau khi luu qua AJAX chi can thay outerHTML cua ca the.
// Bien: $hoSo, $hoSoIncomplete, $hoSoFormValues, $hoSoError, $moHoSo.

$hoSo = is_array($hoSo ?? null) ? $hoSo : [];
$hoSoFormValues = is_array($hoSoFormValues ?? null) ? $hoSoFormValues : [];
$hoSoIncomplete = !empty($hoSoIncomplete);
$hoSoError = (string) ($hoSoError ?? '');
$moHoSo = !empty($moHoSo);

if (!isset($escape) || !is_callable($escape)) {
    $escape = static function ($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    };
}
if (!isset($formatDate) || !is_callable($formatDate)) {
    $formatDate = static function ($date) {
        $timestamp = strtotime((string) $date);

        return $timestamp ? date('d/m/Y', $timestamp) : '';
    };
}
if (!isset($hienThi) || !is_callable($hienThi)) {
    $hienThi = static function ($value) {
        $value = trim((string) $value);

        return $value !== '' ? $value : 'Chưa cập nhật';
    };
}
if (!isset($gioiTinh) || !is_callable($gioiTinh)) {
    $gioiTinh = static function ($value) {
        if ($value === 'nam') {
            return 'Nam';
        }

        return $value === 'nu' ? 'Nữ' : '';
    };
}
?>
<section class="account-card account-card-profile" id="ho-so-card" aria-labelledby="account-profile-title">
    <div class="account-card-head">
        <div>
            <h2 id="account-profile-title">Hồ sơ bệnh nhân</h2>
        </div>
        <i class="fa-regular fa-user account-card-icon" aria-hidden="true"></i>
    </div>

    <?php if ($hoSoIncomplete): ?>
    <p class="account-note" role="note">
        <strong>Hồ sơ chưa hoàn thiện.</strong> Bổ sung ngày sinh, giới tính, địa chỉ và số BHYT để có thể
        đặt lịch khám.
    </p>
    <?php endif; ?>

    <dl class="account-fields account-fields-profile">
        <div>
            <dt>Họ và tên</dt>
            <dd><?php echo $escape($hienThi($hoSo['hoten'] ?? '')); ?></dd>
        </div>
        <div>
            <dt>Ngày sinh</dt>
            <dd><?php echo $escape($formatDate($hoSo['ngaysinh'] ?? '') ?: 'Chưa cập nhật'); ?></dd>
        </div>
        <div>
            <dt>Giới tính</dt>
            <dd><?php echo $escape($gioiTinh($hoSo['gioitinh'] ?? '') ?: 'Chưa cập nhật'); ?></dd>
        </div>
        <div>
            <dt>Số CCCD</dt>
            <dd><?php echo $escape($hienThi($hoSo['cccd'] ?? '')); ?></dd>
        </div>
        <div>
            <dt>Dân tộc</dt>
            <dd><?php echo $escape($hienThi($hoSo['dantoc'] ?? '')); ?></dd>
        </div>
        <div>
            <dt>Số BHYT</dt>
            <dd><?php echo $escape($hienThi($hoSo['bhyt'] ?? '')); ?></dd>
        </div>
        <div class="account-field-wide">
            <dt>Địa chỉ</dt>
            <dd><?php echo $escape($hienThi($hoSo['diachi'] ?? '')); ?></dd>
        </div>
    </dl>

    <details class="account-drawer account-drawer-profile" <?php echo $moHoSo ? 'open' : ''; ?>>
        <summary class="account-toggle">
            <span class="account-toggle-open">
                <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                <?php echo $hoSoIncomplete ? 'Bổ sung hồ sơ bệnh nhân' : 'Chỉnh sửa hồ sơ bệnh nhân'; ?>
            </span>
            <span class="account-toggle-close">Đóng</span>
        </summary>

        <?php
        $hoSoAction = 'index.php?page=thong-tin-tai-khoan';
        $hoSoHanhDong = 'cap_nhat_ho_so';
        $hoSoNameEditable = true;
        $hoSoShowEmail = true;
        $hoSoShowExtras = true;
        $hoSoIdSuffix = 'tai_khoan';
        $hoSoSubmitLabel = 'Lưu hồ sơ bệnh nhân';
        $hoSoCsrfForm = 'cap_nhat_ho_so';
        $hoSoAjax = true;
        require __DIR__ . '/ho-so-form.php';
        ?>
    </details>
</section>
