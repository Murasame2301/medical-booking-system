<main>
  <header class="top">
    <h1>Lịch khám</h1>
  </header>
  <div class="noidung">
    <form class="loc" id="form-loc-kham">
      <input class="nhan-ghi" type="search" placeholder="Tìm theo tên hoặc số điện thoại" id="loc-tu-khoa" />
      <label class="nhan-ghi">Từ<input type="date" id="loc-tu" /></label>
      <label class="nhan-ghi">Đến<input type="date" id="loc-den" /></label>
      <label class="nhan-ghi">Trạng thái
        <select id="loc-trang-thai">
          <option value="tat_ca">Tất cả</option>
          <option value="cho_xac_nhan">Chờ xác nhận</option>
          <option value="da_xac_nhan">Đã xác nhận</option>
          <option value="da_kham">Đã khám</option>
          <option value="da_huy">Đã hủy</option>
        </select>
      </label>
      <button class="btn xanh" type="submit">Tìm</button>
      <button class="btn vien" type="button" id="dat-lai">Đặt lại</button>
    </form>
    <div class="bang bang-vua">
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

  <!-- HOP THOAI HUY LICH KHAM -->
  <dialog id="hop-huy-kham">
    <form id="form-huy-kham">
      <h2>Hủy lịch khám</h2>
      <p class="huy-thong-tin" id="huy-thong-tin"></p>
      <div class="nhom-truong">
        <label for="huy-ly-do">Lý do hủy (không bắt buộc)</label>
        <textarea id="huy-ly-do" rows="3" maxlength="255" placeholder="Ví dụ: bác sĩ bận đột xuất"></textarea>
      </div>
      <div class="hang-nut">
        <button class="btn vien" type="button" id="huy-nut-dong">Đóng</button>
        <button class="btn do" type="submit">Hủy lịch</button>
      </div>
    </form>
  </dialog>
