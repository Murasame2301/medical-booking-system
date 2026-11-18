<?php

// Khay thong bao cua trang tim lich. Dung chung cho lan tai trang dau tien
// (search.php) va cho fragment tra ve qua AJAX.
// Bien: $dateError, $message, $messageType.

$dateError = (string) ($dateError ?? '');
$message = (string) ($message ?? '');
$messageType = (string) ($messageType ?? '');

if (!isset($escape) || !is_callable($escape)) {
    $escape = static function ($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    };
}
?>
<?php if ($dateError !== ''): ?>
<div class="appointment-alert appointment-alert-error" role="alert">
    <?php echo $escape($dateError); ?>
</div>
<?php elseif ($message !== ''): ?>
<div class="appointment-alert appointment-alert-<?php echo $escape($messageType ?: 'error'); ?>" role="status">
    <?php echo $escape($message); ?>
</div>
<?php endif; ?>
