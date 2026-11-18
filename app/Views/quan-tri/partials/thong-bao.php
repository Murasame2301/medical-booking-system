<?php
/* Thong bao flash dung chung cho cac trang quan tri.
   Bien: $flash = ['type' => 'ok'|'loi', 'message' => ...] hoac null */
if (!empty($flash['message'])):
    $loai = $flash['type'] === 'ok' ? 'ok' : 'loi';
    ?>
    <p class="<?= h($loai) ?>"><?= h($flash['message']) ?></p>
<?php endif; ?>
