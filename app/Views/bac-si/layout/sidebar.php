<?php

/**
 * Menu trai cua khu bac si.
 * Duong dan dung router chung: index.php?page=bac-si/...
 */

$hoTen = ho_ten_hien_tai() !== '' ? ho_ten_hien_tai() : 'Bác sĩ';
$maNd = ma_nguoi_dung_hien_tai();
$trangHienTai = $trang ?? 'bac-si/tong-quan';

if (!function_exists('lop_muc')) {
  function lop_muc($cacTrang): string
  {
    global $trangHienTai;

    return in_array($trangHienTai, (array) $cacTrang, true) ? 'muc on' : 'muc';
  }
}
?>

<aside class="side">
  <div>
    <div class="user">
      <?= html_avatar(nguoi_dung_lay_anh($conn, $maNd), $hoTen, 'av-lon-home av-lon') ?>
      <span class="ten" data-ten><?= h($hoTen) ?></span>
    </div>
    <nav>
      <a class="<?= lop_muc('bac-si/tong-quan') ?>" href="index.php?page=bac-si/tong-quan"><i>📊</i>Tổng quan</a>
      <div class="nhom">PHÒNG KHÁM</div>
      <a class="<?= lop_muc('bac-si/lich-lam-viec') ?>" href="index.php?page=bac-si/lich-lam-viec"><i>🗓️</i>Lịch làm việc</a>
      <a class="<?= lop_muc('bac-si/lich-kham') ?>" href="index.php?page=bac-si/lich-kham"><i>📅</i>Lịch khám</a>
      <a class="<?= lop_muc('bac-si/kham-benh') ?>" href="index.php?page=bac-si/kham-benh"><i>🩺</i>Khám bệnh</a>
    </nav>
  </div>
  <div class="thoat">
    <a class="<?= lop_muc('bac-si/ca-nhan') ?>" href="index.php?page=bac-si/ca-nhan"><i>👤</i>Cá nhân</a>
    <a class="muc" href="index.php?page=dang-xuat"><i>🚪</i>Đăng xuất</a>
  </div>
</aside>
