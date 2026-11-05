<!doctype html>
<html lang="vi">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Lịch khám - Phòng khám</title>
  <link rel="stylesheet" href="../public/style/style_bacsi.css" />
</head>

<body>
  <div class="app">
    <aside class="side">
      <div>
        <div class="user">
          <span class="av-lon-home av-lon"></span>
          <span class="ten">Nguyễn Văn An</span>
        </div>
        <nav>
          <a class="muc" href="tongquan.php"><i>📊</i>Tổng quan</a>
          <div class="nhom">PHÒNG KHÁM</div>
          <a class="muc" href="lichlamviec.php"><i>🗓️</i>Lịch làm việc</a>
          <a class="muc" href="lichkham.php"><i>📅</i>Lịch khám</a>
          <a class="muc" href="khambenh.php"><i>🩺</i>Khám bệnh</a>
        </nav>
      </div>
      <div class="thoat">
        <a class="muc on" href="ca_nhan.php"><i>👤</i>Cá nhân</a>
        <a class="muc" href="logout.php"><i>🚪</i>Đăng xuất</a>
      </div>
    </aside>
    <main>
      <header class="top">
        <h1>Lịch khám</h1>
      </header>
      <div class="noidung">
        <form class="loc" id="form-loc-kham">
          <input class="nhan-ghi" type="search" placeholder="Tìm theo tên hoặc số điện thoại" id="loc-tu-khoa" />
          <label class="nhan-ghi">Từ<input type="date" value="2026-11-02" id="loc-tu" /></label>
          <label class="nhan-ghi">Đến<input type="date" value="2026-11-08" id="loc-den" /></label>
          <label class="nhan-ghi">Trạng thái
            <select id="loc-trang-thai">
              <option value="">Tất cả</option>
              <option value="Chờ xác nhận">Chờ xác nhận</option>
              <option value="Đã xác nhận">Đã xác nhận</option>
              <option value="Đã khám">Đã khám</option>
              <option value="Đã hủy">Đã hủy</option>
            </select>
          </label>
          <button class="btn xanh" type="submit">Tìm</button>
          <button class="btn vien" type="button" id="dat-lai">Đặt lại</button>
        </form>
        <div class="bang">
          <table>
            <thead>
              <tr>
                <th>Trạng thái</th>
                <th>Mã LK</th>
                <th>Họ và tên</th>
                <th>Ngày sinh</th>
                <th>Giới tính</th>
                <th>Điện thoại</th>
                <th>Giờ khám</th>
                <th>Lý do</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody id="than-bang-kham">
              <!-- JavaScript sẽ tự động chèn <tr> vào đây -->
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
  <script src="../public/js/tien_ich.js" defer></script>
  <script src="../public/js/lich_kham.js" defer></script>
</body>

</html>