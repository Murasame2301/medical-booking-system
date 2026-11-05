<!doctype html>
<html lang="vi">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Lịch làm việc - Phòng khám</title>
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
        <h1>Lịch làm việc</h1>
      </header>
      <div class="noidung">
        <div class="khung">
          <h2>Tạo lịch làm việc</h2>
          <p class="goiy">
            Chọn ngày và khoảng giờ, hệ thống chia thành các khung bằng nhau.
          </p>
          <form class="loc" id="form-tao">
            <label class="nhan-ghi hom-nay">Ngày<input type="date" id="ngay-kham" required /></label>
            <label class="nhan-ghi">Từ giờ<input type="time" id="tg-bat-dau" required /></label>
            <label class="nhan-ghi">Đến giờ<input type="time" id="tg-ket-thuc" required /></label>
            <label class="nhan-ghi" for="so-phut" style="width:120px">Mỗi lượt (phút)
              <input type="number" id="so-phut" min="5" max="60" step="1" value="30" required />
            </label>
            <button class="btn xanh" type="submit">Tạo lịch</button>
          </form>
        </div>

        <form class="loc" id="form-loc">
          <label class="nhan-ghi">Từ<input type="date" id="loc-tu" required /></label>
          <label class="nhan-ghi">Đến<input type="date" id="loc-den" required /></label>
          <label class="nhan-ghi">Trạng thái
            <select id="loc-trang-thai">
              <option value="">Tất cả</option>
              <option value="conTrong">Còn trống</option>
              <option value="ngungNhan">Ngừng nhận</option>
            </select></label>
          <button class="btn xanh" type="submit">Tìm</button>
          <a class="btn vien" id="dat-lai" href="#">Đặt lại</a>
        </form>

        <div class="bang">
          <table>
            <thead>
              <tr>
                <th>Mã</th>
                <th>Ngày</th>
                <th>Giờ</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody id="than-bang"></tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
  <script src="../public/js/tien_ich.js" defer></script>
  <script src="../public/js/lich_lam_viec.js" defer></script>
</body>

</html>