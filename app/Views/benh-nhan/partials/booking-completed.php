<?php

// Panel thay the form dat lich sau khi dat thanh cong.
// Bien: $message, $backUrl.

$message = (string) ($message ?? '');
$backUrl = (string) ($backUrl ?? 'index.php?page=dat-kham');

if (!isset($escape) || !is_callable($escape)) {
    $escape = static function ($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    };
}
?>
<div class="detail-alert detail-alert-success" role="status">
    <?php echo $escape($message); ?>
</div>
<div class="completed-booking">
    <p>Lịch khám của bạn đã được ghi nhận và đang chờ cơ sở y tế xác nhận.</p>
    <div class="completed-actions">
        <a class="detail-primary-button" href="index.php?page=lich-su-dat-kham">Xem lịch đã đặt</a>
        <a class="detail-secondary-button" href="<?php echo $escape($backUrl); ?>">Tìm lịch khác</a>
    </div>
</div>
