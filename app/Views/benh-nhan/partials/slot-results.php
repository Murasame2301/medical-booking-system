<?php

// Tieu de ket qua + danh sach khung gio (hoac trang thai rong).
// Bien: $ngay, $slots.

$ngay = (string) ($ngay ?? '');
$slots = is_array($slots ?? null) ? $slots : [];

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
?>
<div class="results-heading">
    <div>
        <h2><?php echo $escape($formatDate($ngay)); ?></h2>
    </div>
    <span class="results-count"><?php echo count($slots); ?> khung giờ</span>
</div>

<?php if (count($slots) > 0): ?>
<div class="slot-list">
    <?php foreach ($slots as $slot): ?>
    <article class="slot-card">
        <div class="slot-time">
            <strong><?php echo $escape($formatTime($slot['thoigianbatdau'])); ?></strong>
            <span><?php echo $escape($formatTime($slot['thoigianketthuc'])); ?></span>
        </div>

        <div class="slot-details">
            <h3><?php echo $escape($slot['tenbacsi']); ?></h3>
            <p><?php echo $escape($slot['trinhdo'] ?: 'Bác sĩ'); ?></p>
            <span class="slot-specialty"><?php echo $escape($slot['chuyenkhoa']); ?></span>
        </div>

        <form class="booking-form" method="get" action="index.php">
            <!-- Form GET bo query trong action, nen 'page' phai la o an. -->
            <input type="hidden" name="page" value="chi-tiet-lich-kham">
            <input type="hidden" name="malichlamviec" value="<?php echo $escape($slot['malichlamviec']); ?>">
            <button class="detail-button" type="submit">Xem chi tiết</button>
        </form>
    </article>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="empty-state">
    <div class="empty-icon" aria-hidden="true">
        <i class="fa-regular fa-calendar-days"></i>
    </div>
    <h3>Chưa có khung giờ phù hợp</h3>
    <p>Thử chọn ngày khác hoặc bỏ bớt một bộ lọc để xem thêm lịch khám.</p>
</div>
<?php endif; ?>
