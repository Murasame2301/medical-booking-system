<?php
$la_dang_ky = $trang === 'dangky';
$lay_gia_tri = static fn(string $ten): string => h((string)($du_lieu_cu[$ten] ?? ''));
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $la_dang_ky ? 'Đăng ký bác sĩ' : 'Đăng nhập bác sĩ' ?> - Phòng khám</title>
  <link rel="stylesheet" href="cong_khai/css/xac_thuc.css">
</head>
<body>
  <main class="khung-xac-thuc">
    <section class="the-xac-thuc">
      <a class="thuong-hieu" href="index.php?trang=dangnhap">Phòng khám</a>
      <p class="nhan">CỔNG DÀNH CHO BÁC SĨ</p>
      <h1><?= $la_dang_ky ? 'Tạo tài khoản bác sĩ' : 'Chào mừng trở lại' ?></h1>
      <p class="mo-ta"><?= $la_dang_ky ? 'Điền thông tin để tạo tài khoản và sử dụng hệ thống.' : 'Đăng nhập để quản lý lịch khám và hồ sơ của bạn.' ?></p>

      <?php if ($loi_xac_thuc !== ''): ?>
        <div class="thong-bao" role="alert"><?= h($loi_xac_thuc) ?></div>
      <?php endif; ?>

      <form method="post" action="index.php?trang=<?= $la_dang_ky ? 'dangky' : 'dangnhap' ?>">
        <input type="hidden" name="token_xac_thuc" value="<?= h($_SESSION['token_xac_thuc']) ?>">

        <?php if ($la_dang_ky): ?>
          <label for="ho_ten">Họ và tên</label>
          <input id="ho_ten" name="ho_ten" type="text" autocomplete="name" value="<?= $lay_gia_tri('ho_ten') ?>" required>

          <label for="so_dien_thoai">Số điện thoại</label>
          <input id="so_dien_thoai" name="so_dien_thoai" type="tel" autocomplete="tel" value="<?= $lay_gia_tri('so_dien_thoai') ?>" required>

          <div class="hang">
            <div>
              <label for="ngay_sinh">Ngày sinh</label>
              <input id="ngay_sinh" name="ngay_sinh" type="date" value="<?= $lay_gia_tri('ngay_sinh') ?>" required>
            </div>
            <div>
              <label for="gioi_tinh">Giới tính</label>
              <select id="gioi_tinh" name="gioi_tinh" required>
                <option value="">Chọn giới tính</option>
                <option value="Nam" <?= ($du_lieu_cu['gioi_tinh'] ?? '') === 'Nam' ? 'selected' : '' ?>>Nam</option>
                <option value="Nữ" <?= ($du_lieu_cu['gioi_tinh'] ?? '') === 'Nữ' ? 'selected' : '' ?>>Nữ</option>
                <option value="Khác" <?= ($du_lieu_cu['gioi_tinh'] ?? '') === 'Khác' ? 'selected' : '' ?>>Khác</option>
              </select>
            </div>
          </div>

          <label for="dia_chi">Địa chỉ</label>
          <input id="dia_chi" name="dia_chi" type="text" autocomplete="street-address" value="<?= $lay_gia_tri('dia_chi') ?>" required>
        <?php endif; ?>

        <label for="email">Email</label>
        <input id="email" name="email" type="email" autocomplete="email" value="<?= $lay_gia_tri('email') ?>" required>

        <label for="mat_khau">Mật khẩu</label>
        <input id="mat_khau" name="mat_khau" type="password" autocomplete="<?= $la_dang_ky ? 'new-password' : 'current-password' ?>" <?= $la_dang_ky ? 'minlength="8"' : '' ?> required>

        <?php if ($la_dang_ky): ?>
          <label for="xac_nhan_mat_khau">Xác nhận mật khẩu</label>
          <input id="xac_nhan_mat_khau" name="xac_nhan_mat_khau" type="password" autocomplete="new-password" minlength="8" required>
        <?php endif; ?>

        <button type="submit"><?= $la_dang_ky ? 'Đăng ký' : 'Đăng nhập' ?></button>
      </form>

      <p class="doi-trang">
        <?= $la_dang_ky ? 'Đã có tài khoản?' : 'Chưa có tài khoản?' ?>
        <a href="index.php?trang=<?= $la_dang_ky ? 'dangnhap' : 'dangky' ?>"><?= $la_dang_ky ? 'Đăng nhập' : 'Đăng ký bác sĩ' ?></a>
      </p>
    </section>
  </main>
</body>
</html>
