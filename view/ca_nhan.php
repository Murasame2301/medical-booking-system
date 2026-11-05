<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Trang cá nhân - Phòng khám</title>
  <link rel="stylesheet" href="../public/style/style_bacsi.css" />
</head>

<body>
  <div class="app">
    <!-- Sidebar -->
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
        <h1>Trang cá nhân</h1>
      </header>
      <div class="noidung">
        <div class="khung">
          <div class="tab-header">
            <button class="tab-nut on"><i>🪪</i>Thông tin cá nhân</button>
            <button class="tab-nut"><i>🛡️</i>Bảo mật</button>
          </div>
          <div id="tab-tt" data-tab="tab-tt" class="tab-noi-dung on">
            <form class="form-ca-nhan" action="" method=" POST">
              <div class="anh-dai-dien">
                <div class="av-lon"></div>
                <div>
                  <button type="button" class="btn vien nho avt">Đổi ảnh đại diện</button>
                  <input type="file" id="inp-anh" accept="image/jpeg,image/png" hidden />
                  <p class="goiy" style="margin: 4px 0 0;">Cho phép JPG, PNG. Tối đa 2MB.</p>
                </div>
              </div>

              <div class="hang-doi">
                <div class="nhom-truong">
                  <label>Họ và tên</label>
                  <input type="text" name="hoten" value="Nguyễn Văn An" required />
                </div>
                <div class="nhom-truong">
                  <label>Số điện thoại</label>
                  <input type="text" name="sdt" value="0912 345 678" required />
                </div>
              </div>

              <div class="hang-doi">
                <div class="nhom-truong">
                  <label>Ngày sinh</label>
                  <input type="date" name="ngaysinh" value="1990-05-15" required />
                </div>
                <div class="nhom-truong">
                  <label>Giới tính</label>
                  <select name="gioitinh">
                    <option value="Nam" selected>Nam</option>
                    <option value="Nữ">Nữ</option>
                  </select>
                </div>

                <div class="nhom-truong">
                <label>Học hàm, học vị, chuyên môn nghề nghiệp </label>
                 <!-- <p class="goiy" style="margin: 4px 0 0;">PGS.TS.BS, Th.BS, ... .</p> -->
                <input type="text" name="trinhdo" value="PGS.TS.BS"/>
              </div>
              </div>

              

              <div class="nhom-truong">
                <label>Email</label>
                <input type="email" name="email" value="nguyenvanan@gmail.com" required />
              </div>

              <div class="nhom-truong">
                <label>Địa chỉ</label>
                <input type="text" name="diachi" value="123 Đường Cần Thơ, Q. Ninh Kiều, Cần Thơ" />
              </div>

              <button class="btn xanh" type="submit">Cập nhật thông tin</button>
            </form>
          </div>

          <!-- TAB 2: BẢO MẬT -->
          <div id="tab-bm" data-tab="tab-bm" class="tab-noi-dung">
            <form class="form-ca-nhan form-bao-mat" action="" method="POST">
              <div class="nhom-truong">
                <label>Mật khẩu hiện tại</label>
                <input type="password" name="mk_hientai" placeholder="••••••••" required />
              </div>
              <div class="nhom-truong">
                <label>Mật khẩu mới</label>
                <input type="password" name="mk_moi" placeholder="••••••••" required />
              </div>
              <div class="nhom-truong">
                <label>Xác nhận mật khẩu mới</label>
                <input type="password" name="mk_xacnhan" placeholder="••••••••" required />
              </div>

              <button class="btn xanh" type="submit">Đổi mật khẩu</button>
            </form>
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- JS chuyển Tab riêng -->
  <script src="../public/js/ca_nhan.js" defer></script>
</body>

</html>