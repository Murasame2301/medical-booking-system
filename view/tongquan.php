<!doctype html>
<html lang="vi">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Tổng quan - Phòng khám</title>
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
        <h1>Tổng quan</h1>
      </header>
      <div class="noidung">
        <div class="the">
          <div><span>Lượt khám hôm nay</span><b>8</b></div>
          <div><span>Chờ xác nhận</span><b>3</b></div>
          <div><span>Đã khám</span><b>4</b></div>
          <div><span>Khung giờ còn trống</span><b>5</b></div>
        </div>
        <h2>Lịch khám hôm nay</h2>
        <div class="bang">
          <table>
            <thead>
              <tr>
                <th>Giờ khám</th>
                <th>Họ và tên</th>
                <th>Lý do</th>
                <th>Trạng thái</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>08:00–08:30</td>
                <td>Trần Thị Hoa</td>
                <td>Đau đầu kéo dài</td>
                <td><span class="nhan n-luc">Đã khám</span></td>
              </tr>
              <tr>
                <td>08:30–09:00</td>
                <td>Lê Văn Minh</td>
                <td>Khám tổng quát</td>
                <td><span class="nhan n-xanh">Đã xác nhận</span></td>
              </tr>
              <tr>
                <td>09:00–09:30</td>
                <td>Phạm Thu Hà</td>
                <td>Ho kéo dài</td>
                <td><span class="nhan n-vang">Chờ xác nhận</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</body>

</html>