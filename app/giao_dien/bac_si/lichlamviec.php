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
          <input type="number" id="so-phut" min="5" max="480" step="1" value="30" required />
        </label>
        <button class="btn xanh" type="submit">Tạo lịch</button>
      </form>
    </div>

    <form class="loc" id="form-loc">
      <label class="nhan-ghi">Từ<input type="date" id="loc-tu" required /></label>
      <label class="nhan-ghi">Đến<input type="date" id="loc-den" required /></label>
      <label class="nhan-ghi">Trạng thái
        <select id="loc-trang-thai">
          <option value="tat-ca">Tất cả</option>
          <option value="con_nhan">Còn trống</option>
          <option value="ngung_nhan">Ngừng nhận</option>
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