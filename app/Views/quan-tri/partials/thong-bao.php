<?php
//Thông báo flash dùng chung cho các trang quản trị.
if (!empty($flash['message'])):
    $loai = $flash['type'] === 'ok' ? 'ok' : 'loi';
    ?>
    <p class="<?= h($loai) ?>"><?= h($flash['message']) ?></p>
<?php endif; ?>
