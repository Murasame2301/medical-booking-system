<?php
//Bảng danh sách người dùng. Dùng cho cả trang và endpoint AJAX tìm kiếm.

if (count($ds) === 0) {
    echo '<p class="trong">Không tìm thấy người dùng nào.</p>';

    return;
}
?>
<div class="bang-cuon">
    <table class="bang">
        <tr>
            <th>Họ tên</th>
            <th>Số điện thoại</th>
            <th>Vai trò</th>
            <th>Ngày tạo</th>
            <th>Trạng thái</th>
            <th></th>
        </tr>
        <?php foreach ($ds as $u): ?>
            <?php $laQuanTri = (int) $u['mavaitro'] === VT_QUAN_TRI; ?>
            <tr>
                <td><?= h($u['hoten']) ?></td>
                <td><?= h($u['sdt']) ?></td>
                <td><?= h($u['tenvaitro_hien_thi']) ?></td>
                <td><?= h(date('d/m/Y', strtotime($u['ngaytao']))) ?></td>
                <td class="o-trang-thai">
                    <?php if ($u['trangthai'] === TK_HOAT_DONG): ?>
                        <span class="trangthai tt-xacnhan">Hoạt động</span>
                    <?php else: ?>
                        <span class="trangthai tt-huy">Bị khoá</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($laQuanTri || (int) $u['manguoidung'] === (int) $nguoiHienTai): ?>
                        <span class="trong">—</span>
                    <?php else: ?>
                        <form method="post" action="index.php?page=quan-tri/nguoi-dung/khoa" class="form-khoa-mo"
                            data-ma="<?= (int) $u['manguoidung'] ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="manguoidung" value="<?= (int) $u['manguoidung'] ?>">
                            <input type="hidden" name="tukhoa" value="<?= h($tukhoa) ?>">
                            <input type="hidden" name="vaitro" value="<?= (int) $vaitro ?>">
                            <?php if ($u['trangthai'] === TK_HOAT_DONG): ?>
                                <button type="submit" name="hanhdong" value="khoa" class="nut-huy">Khoá</button>
                            <?php else: ?>
                                <button type="submit" name="hanhdong" value="mo" class="nut-mo">Mở khoá</button>
                            <?php endif; ?>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
