<?php
// view ca nhan
$maNguoiDung = (int)($_SESSION[SS_MA_NGUOI_DUNG] ?? 0);
$tt = lay_thong_tin_ca_nhan($maNguoiDung)['data'] ?? [];
$gt = $tt['gioitinh'] ?? '';
?>
<main>
  <header class="top">
    <h1>Trang cá nhân</h1>
  </header>
  <div class="noidung">
    <div class="khung">
      <div class="tab-header" role="tablist">
        <button type="button" class="tab-nut on" role="tab" id="tab-1" aria-controls="tab-tt" aria-selected="true"><i>🪪</i>Thông tin cá nhân</button>
        <button type="button" class="tab-nut" role="tab" id="tab-2" aria-controls="tab-bm" aria-selected="false" tabindex="-1"><i>🛡️</i>Bảo mật</button>
      </div>

      <!-- TAB 1: THÔNG TIN CÁ NHÂN -->
      <div id="tab-tt" class="tab-noi-dung on" role="tabpanel" aria-labelledby="tab-1">
        <form class="form-ca-nhan" id="form-thong-tin">
          <div class="anh-dai-dien">
            <?= html_avatar($maNguoiDung, (string)($tt['hoten'] ?? '')) ?>
            <div>
              <button type="button" class="btn vien nho avt" id="btn-doi-anh">Đổi ảnh đại diện</button>
              <button type="button" class="btn vien nho" id="btn-xoa-anh">Xóa ảnh</button>
              <input type="file" id="inp-anh" accept="image/jpeg,image/png,image/webp" hidden />
              <p class="goiy" style="margin: 4px 0 0;">Cho phép JPG, PNG, WEBP. Tối đa 2MB.</p>
            </div>
          </div>

          <div class="hang-doi">
            <div class="nhom-truong">
              <label for="f-ten">Họ và tên</label>
              <input type="text" id="f-ten" name="ten" maxlength="100" value="<?= h($tt['hoten'] ?? '') ?>" required />
            </div>
            <div class="nhom-truong">
              <label for="f-sdt">Số điện thoại</label>
              <input type="tel" id="f-sdt" name="sdt" maxlength="11" inputmode="numeric" pattern="0[0-9]{9,10}"
                     title="10–11 chữ số, bắt đầu bằng 0" value="<?= h($tt['sdt'] ?? '') ?>" required />
            </div>
          </div>

          <div class="hang-doi">
            <div class="nhom-truong">
              <label for="f-ngaysinh">Ngày sinh</label>
              <input type="date" id="f-ngaysinh" name="ngay_sinh" min="1900-01-01" max="<?= date('Y-m-d') ?>"
                     value="<?= h($tt['ngaysinh'] ?? '') ?>" required />
            </div>
            <div class="nhom-truong">
              <label for="f-gioitinh">Giới tính</label>
              <select id="f-gioitinh" name="gioi_tinh" required>
                <option value="nam" <?= $gt === 'nam' ? 'selected' : '' ?>>Nam</option>
                <option value="nu" <?= $gt === 'nu' ? 'selected' : '' ?>>Nữ</option>
              </select>
            </div>

            <div class="nhom-truong">
              <label for="f-trinhdo">Học hàm, học vị, chuyên môn nghề nghiệp</label>
              <input type="text" id="f-trinhdo" name="trinh_do" maxlength="100" value="<?= h($tt['trinhdo'] ?? '') ?>" required />
            </div>
          </div>

          <div class="nhom-truong">
            <label for="f-email">Email</label>
            <input type="email" id="f-email" name="email" maxlength="100" value="<?= h($tt['email'] ?? '') ?>" required />
          </div>

          <div class="nhom-truong">
            <label for="f-diachi">Địa chỉ</label>
            <input type="text" id="f-diachi" name="dia_chi" maxlength="255" value="<?= h($tt['diachi'] ?? '') ?>" required />
          </div>

          <button class="btn xanh" type="submit">Cập nhật thông tin</button>
        </form>
      </div>

      <!-- TAB 2: BẢO MẬT -->
      <div id="tab-bm" class="tab-noi-dung" role="tabpanel" aria-labelledby="tab-2">
        <form class="form-ca-nhan form-bao-mat" id="form-mat-khau" autocomplete="off">
          <div class="nhom-truong">
            <label for="f-mk-ht">Mật khẩu hiện tại</label>
            <input type="password" id="f-mk-ht" name="mat_khau_hien_tai" placeholder="••••••••" autocomplete="current-password" required />
          </div>
          <div class="nhom-truong">
            <label for="f-mk-moi">Mật khẩu mới (tối thiểu 8 ký tự)</label>
            <input type="password" id="f-mk-moi" name="mat_khau_moi" placeholder="••••••••" minlength="8" maxlength="72" autocomplete="new-password" required />
          </div>
          <div class="nhom-truong">
            <label for="f-mk-xn">Xác nhận mật khẩu mới</label>
            <input type="password" id="f-mk-xn" name="xac_nhan" placeholder="••••••••" minlength="8" maxlength="72" autocomplete="new-password" required />
          </div>

          <button class="btn xanh" type="submit">Đổi mật khẩu</button>
        </form>
      </div>
    </div>
  </div>
