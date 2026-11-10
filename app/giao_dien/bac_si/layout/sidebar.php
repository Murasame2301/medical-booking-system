<?php
$trang_hien_tai = basename($_SERVER['SCRIPT_NAME']);
function lop_muc(string $tenFile): string
{
  global $trang_hien_tai;
  return $trang_hien_tai === $tenFile ? 'muc on' : 'muc';
}
function tao_link(string $ten_trang): string
{
  return "index.php?trang=" . $ten_trang;
}
?>

<!-- header -->
<aside class="side">
  <div>
    <div class="user">
      <span class="av-lon-home av-lon"></span>
      <span class="ten"><?= h($_SESSION['ho_ten'] ?? 'Bác sĩ') ?></span>
    </div>
    <nav>
      <a class="<?= lop_muc('tongquan.php') ?>" href="<?= tao_link('tongquan') ?>"><i>📊</i>Tổng quan</a>
      <div class="nhom">PHÒNG KHÁM</div>
      <a class="<?= lop_muc('lichlamviec.php') ?>" href="<?= tao_link('lichlamviec') ?>"><i>🗓️</i>Lịch làm việc</a>
      <a class="<?= lop_muc('lichkham.php') ?>" href="<?= tao_link('lichkham') ?>"><i>📅</i>Lịch khám</a>
      <a class="<?= lop_muc('khambenh.php') ?>" href="<?= tao_link('khambenh') ?>"><i>🩺</i>Khám bệnh</a>
    </nav>
  </div>
  <div class="thoat">
    <a class="<?= lop_muc('canhan.php') ?>" href="<?= tao_link('canhan') ?>"><i>👤</i>Cá nhân</a>
    <a class="muc" href="index.php?trang=dangxuat"><i>🚪</i>Đăng xuất</a>
  </div>
</aside>
<!-- tiep theo la than -->