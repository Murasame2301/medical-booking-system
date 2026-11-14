<main>
  <header class="top">
    <h1>Khám bệnh</h1>
  </header>

  <div class="noidung">
    <!-- Danh sách ca khám hôm nay -->
    <section class="khung">
      <h2>Ca khám hôm nay</h2>

      <!-- bang-cuon: bảng có thanh cuộn riêng khi có nhiều ca -->
      <div class="bang bang-cuon">
        <table>
          <thead>
            <tr>
              <th>Giờ khám</th>
              <th>Họ và tên</th>
              <th>Lý do</th>
              <th>Trạng thái</th>
              <th>Thao tác</th>
            </tr>
          </thead>
          <!-- lay giao dien js -->
          <tbody id="than-bang-kham-benh"></tbody>
        </table>
      </div>
    </section>

    
    <section class="khung" id="khung-ho-so" style="display: none;">
      <h2 id="hs-tieu-de">Hồ sơ khám: </h2>

      <dl class="tt">
        <div>
          <dt>Ngày sinh</dt>
          <dd id="hs-ngay-sinh"></dd>
        </div>
        <div>
          <dt>Giới tính</dt>
          <dd id="hs-gioi-tinh"></dd>
        </div>
        <div>
          <dt>Điện thoại</dt>
          <dd id="hs-sdt"></dd>
        </div>
        <div>
          <dt>Lý do khám</dt>
          <dd id="hs-ly-do"></dd>
        </div>
      </dl>

      <form class="mau">
        <label class="nhan-ghi">Chẩn đoán
          <textarea rows="3"></textarea>
        </label>
        <label class="nhan-ghi">Lời dặn, ghi chú
          <textarea rows="3"></textarea>
        </label>
        <button class="btn xanh" type="submit">Hoàn thành khám</button>
        <button class="btn do" type="button" id="btn-dong-ho-so">Đóng</button>
      </form>
    </section>
  </div>
