<?php
/* Danh sach bac si.
   Bien: $viewData['ds'], $viewData['flash'] */
$ds = $viewData['ds'] ?? [];
$flash = $viewData['flash'] ?? null;
?>
<section class="khu rong">
    <h2 class="tieu-de-khu">Quản lý bác sĩ</h2>

    <?php require __DIR__ . '/partials/thong-bao.php'; ?>

    <p class="trong"><a href="index.php?page=quan-tri/bac-si/them" class="nut-nho">+ Thêm bác sĩ</a></p>

    <?php if (count($ds) === 0): ?>
        <p class="trong">Chưa có bác sĩ nào.</p>
    <?php else: ?>
        <div class="bang-cuon">
            <table class="bang">
                <tr>
                    <th>Họ tên</th>
                    <th>Số điện thoại</th>
                    <th>Trình độ</th>
                    <th>Chuyên khoa</th>
                    <th>Trạng thái</th>
                    <th></th>
                </tr>
                <?php foreach ($ds as $b): ?>
                    <tr>
                        <td><?= h($b['hoten']) ?></td>
                        <td><?= h($b['sdt']) ?></td>
                        <td><?= h($b['trinhdo']) ?></td>
                        <td><?= h($b['chuyenkhoa']) ?></td>
                        <td>
                            <?php if ($b['trangthai'] === TK_HOAT_DONG): ?>
                                <span class="trangthai tt-xacnhan">Hoạt động</span>
                            <?php else: ?>
                                <span class="trangthai tt-huy">Bị khoá</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="index.php?page=quan-tri/bac-si/sua&id=<?= (int) $b['manguoidung'] ?>">Sửa</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    <?php endif; ?>
</section>
