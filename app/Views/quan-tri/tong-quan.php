<?php
/* Trang tong quan quan tri: bon the thong ke.
   Bien: $viewData['the'], $viewData['flash'] */
$the = $viewData['the'] ?? [];
$flash = $viewData['flash'] ?? null;
?>
<section class="khu rong">
    <h2 class="tieu-de-khu">Trang quản trị</h2>

    <?php require __DIR__ . '/partials/thong-bao.php'; ?>

    <div class="luoi">
        <?php foreach ($the as $t): ?>
            <div class="the">
                <div class="vong"><?= h($t['so']) ?></div>
                <h3><?= h($t['tieu_de']) ?></h3>
                <?php if (!empty($t['lien_ket'])): ?>
                    <br>
                    <a href="<?= h($t['lien_ket']) ?>" class="nut-nho"><?= h($t['chu']) ?></a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>
