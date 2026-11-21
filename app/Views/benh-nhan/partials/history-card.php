<?php

// Mot the lich kham trong lich su. Tach ra de sau khi huy chi can thay
// outerHTML cua dung the vua huy.
// Bien: $item, $isReopen, $lydoHuy.
// Chot chan: partial nay chi dung khi vong lap truyen $item; neu bi goi le thi
// van render mot the rong thay vi bao loi.
if (!isset($item) || !is_array($item)) {
    $item = [];
}
$isReopen = !empty($isReopen);
$lydoHuy = (string) ($lydoHuy ?? '');

if (!isset($escape) || !is_callable($escape)) {
    $escape = static function ($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    };
}
if (!isset($formatDate) || !is_callable($formatDate)) {
    $formatDate = static function ($date) {
        $timestamp = strtotime((string) $date);

        return $timestamp ? date('d/m/Y', $timestamp) : (string) $date;
    };
}
if (!isset($formatTime) || !is_callable($formatTime)) {
    $formatTime = static function ($datetime) {
        $timestamp = strtotime((string) $datetime);

        return $timestamp ? date('H:i', $timestamp) : (string) $datetime;
    };
}
if (!isset($statusMeta) || !is_callable($statusMeta)) {
    $statusMeta = static function ($trangthai) {
        switch ($trangthai) {
            case 'cho_xac_nhan':
                return ['Chờ xác nhận', 'pending'];
            case 'da_xac_nhan':
                return ['Đã xác nhận', 'confirmed'];
            case 'da_kham':
                return ['Đã khám', 'done'];
            case 'da_huy':
                return ['Đã huỷ', 'cancelled'];
            default:
                return [(string) $trangthai, 'cancelled'];
        }
    };
}

$meta = $statusMeta($item['trangthai'] ?? '');
?>
<article class="history-card history-card-<?php echo $escape($meta[1]); ?>"
    data-malichkham="<?php echo $escape($item['malichkham'] ?? ''); ?>">
    <div class="history-card-top">
        <div class="history-when">
            <strong class="history-time"><?php echo $escape($formatTime($item['thoigianbatdau'] ?? '')); ?>
                – <?php echo $escape($formatTime($item['thoigianketthuc'] ?? '')); ?></strong>
            <span class="history-date"><?php echo $escape($formatDate($item['thoigianbatdau'] ?? '')); ?></span>
        </div>
        <span class="history-status history-status-<?php echo $escape($meta[1]); ?>">
            <?php echo $escape($meta[0]); ?>
        </span>
    </div>

    <div class="history-card-body">
        <h2><?php echo $escape($item['tenbacsi'] ?? ''); ?></h2>
        <p class="history-role"><?php echo $escape(($item['trinhdo'] ?? '') ?: 'Bác sĩ'); ?></p>
        <?php if (trim((string) ($item['chuyenkhoa'] ?? '')) !== ''): ?>
        <p class="history-specialty"><?php echo $escape($item['chuyenkhoa']); ?></p>
        <?php endif; ?>

        <dl class="history-meta">
            <?php if (trim((string) ($item['lydo'] ?? '')) !== ''): ?>
            <div>
                <dt>Lý do khám</dt>
                <dd><?php echo $escape($item['lydo']); ?></dd>
            </div>
            <?php endif; ?>
            <div>
                <dt>Ngày đặt</dt>
                <dd><?php echo $escape($formatDate($item['ngaytao'] ?? '')); ?></dd>
            </div>
            <?php if (($item['trangthai'] ?? '') === 'da_huy' && trim((string) ($item['lydohuy'] ?? '')) !== ''): ?>
            <div>
                <dt>Lý do huỷ</dt>
                <dd><?php echo $escape($item['lydohuy']); ?></dd>
            </div>
            <?php endif; ?>
        </dl>
    </div>

    <?php if (!empty($item['coTheHuy'])): ?>
    <details class="cancel-drawer" <?php echo $isReopen ? 'open' : ''; ?>>
        <summary class="cancel-toggle">
            <span class="cancel-toggle-open">Huỷ đặt lịch</span>
            <span class="cancel-toggle-close">Đóng</span>
        </summary>

        <form class="cancel-form" method="post" action="index.php?page=lich-su-dat-kham" data-ajax="huy_lich">
            <?php echo csrf_field('huy_lich'); ?>
            <input type="hidden" name="hanh_dong" value="huy_lich">
            <input type="hidden" name="malichkham" value="<?php echo $escape($item['malichkham'] ?? ''); ?>">

            <label for="lydo_huy_<?php echo $escape($item['malichkham'] ?? ''); ?>">Lý do huỷ</label>
            <textarea id="lydo_huy_<?php echo $escape($item['malichkham'] ?? ''); ?>" name="lydo_huy" rows="3"
                maxlength="255" required
                placeholder="Ví dụ: bận việc gia đình, muốn đổi sang bác sĩ khác…"><?php echo $isReopen ? $escape($lydoHuy) : ''; ?></textarea>

            <div class="cancel-actions">
                <button class="cancel-submit" type="submit">Xác nhận huỷ</button>
                <span class="cancel-hint">Khung giờ sẽ được mở lại cho người khác.</span>
            </div>
        </form>
    </details>
    <?php endif; ?>
</article>
