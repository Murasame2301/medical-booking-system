<?php

//Các <option> của ô chọn bác sĩ, thay qua AJAX khi đổi chuyên khoa

$doctors = is_array($doctors ?? null) ? $doctors : [];
$mabacsi = $mabacsi ?? null;

if (!isset($escape) || !is_callable($escape)) {
    $escape = static function ($value) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    };
}
?>
<option value="">Tất cả bác sĩ</option>
<?php foreach ($doctors as $doctor): ?>
<option value="<?php echo $escape($doctor['manguoidung']); ?>"
    <?php echo (string) $mabacsi === (string) $doctor['manguoidung'] ? 'selected' : ''; ?>>
    <?php echo $escape($doctor['hoten']); ?>
</option>
<?php endforeach; ?>