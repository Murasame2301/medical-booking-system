<?php
$trang_hien_tai = $_GET['trang'] ?? 'tongquan';
if (!function_exists('lop_muc')) {
  function lop_muc(string|array $cacTrang): string
  {
    global $trang_hien_tai;
    return in_array($trang_hien_tai, (array) $cacTrang, true) ? 'muc on' : 'muc';
  }
}

if (!function_exists('tao_link')) {
  function tao_link(string $ten_trang): string
  {
    return 'index.php?trang=' . urlencode($ten_trang);
  }
}
?>

<aside class="side">
  <div>
    <div class="user">
      <span class="av-lon-home av-lon"></span>
      <span class="ten"><?= h($_SESSION['ho_ten'] ?? 'Bác sĩ') ?></span>
    </div>
    <nav>
      <a class="<?= lop_muc('tongquan') ?>" href="<?= tao_link('tongquan') ?>"><i>📊</i>Tổng quan</a>
      <div class="nhom">PHÒNG KHÁM</div>
      <a class="<?= lop_muc(['lichlamviec', 'khunggio']) ?>" href="<?= tao_link('lichlamviec') ?>"><i>🗓️</i>Lịch làm việc</a>
      <a class="<?= lop_muc('lichkham') ?>" href="<?= tao_link('lichkham') ?>"><i>📅</i>Lịch khám</a>
      <a class="<?= lop_muc('khambenh') ?>" href="<?= tao_link('khambenh') ?>"><i>🩺</i>Khám bệnh</a>
    </nav>
  </div>
  <div class="thoat">
    <a class="<?= lop_muc('canhan') ?>" href="<?= tao_link('canhan') ?>"><i>👤</i>Cá nhân</a>
    <a class="muc" href="<?= tao_link('dangxuat') ?>"><i>🚪</i>Đăng xuất</a>
  </div>
</aside>