# Gộp ba vai trò vào một nhánh `test` — thiết kế

Ngày: 2026-11-18
Nhánh đích: `test` (tách từ `lengtrungtin` tại commit `2ef9b5c`)

## 1. Bối cảnh

Ba nhánh được làm song song, mỗi nhánh một vai trò, **không phải ba phần của
một hệ thống** mà là ba ứng dụng độc lập:

| Nhánh | Vai trò | Kiến trúc | Giao diện |
|---|---|---|---|
| `lengtrungtin` | Bệnh nhân | Hàm thủ tục, 4 controller, 2 model | 8 file CSS, thiết kế hoàn thiện nhất |
| `vmnhutanh` | Bác sĩ | Hàm thủ tục, router `index.php?trang=`, 6 controller, 5 model | `doctor.css` 747 dòng, bố cục sidebar |
| `lleduy` | Quản trị | Hàm thủ tục, file phẳng ở gốc | `style.css` 397 dòng, một cột đơn giản |

Ba nhánh khác nhau ở cả kiến trúc, cơ chế phiên đăng nhập và CSS. Mục tiêu là
gộp thành **một hệ thống hoàn chỉnh** trên nhánh `test`: một trang đăng nhập
chung, ba khu vực theo vai trò, mỗi vai trò giữ nguyên bộ giao diện của mình.

## 2. Mục tiêu

- Một trang đăng nhập duy nhất, giao diện của `lengtrungtin`, rẽ nhánh theo vai trò.
- Ba khu vực chức năng đầy đủ: bệnh nhân, bác sĩ, quản trị.
- Mỗi vai trò giữ nguyên bộ CSS riêng, không dùng chung stylesheet.
- Bác sĩ và quản trị dùng được các chức năng đã có ở nhánh của họ.
- Một schema cơ sở dữ liệu chuẩn cho cả hệ thống.

## 3. Ngoài mục tiêu (không làm trong lần này)

- Không thêm chức năng mới cho bất kỳ vai trò nào. Quản trị chỉ có đúng những gì
  `lleduy` đã làm.
- Không có chức năng quên mật khẩu / đặt lại mật khẩu cho quản trị viên.
- Không có trang đăng ký dành cho bác sĩ.
- Không hợp nhất ba bộ CSS thành một hệ thống thiết kế.
- Không viết lại giao diện bệnh nhân.

## 4. Chống mục tiêu

- Gộp CSS của ba vai trò thành một file chung.
- Giữ nhiều cơ chế phiên đăng nhập song song.
- Trộn lịch sử Git ba nhánh vào `test` rồi dọn xung đột (đã loại, xem mục 5).

## 5. Cách merge: B — tách từ `lengtrungtin`, port code

Đã chọn hướng B. Lý do: `lengtrungtin` là nhánh duy nhất đã kiểm thử end-to-end
(đăng nhập, đặt lịch, huỷ lịch, hồ sơ đều chạy) và là chuẩn giao diện cho trang
đăng nhập. Merge Git ba cây sẽ trộn ba thư mục `assets/css` trái ngược yêu cầu
tách biệt giao diện.

Hướng A (merge ba nhánh bằng Git) và C (viết lại từ đầu) bị loại: A vì trộn
giao diện và sinh xung đột không đáng có; C vì tốn công và dễ bỏ sót chi tiết
đã làm đúng.

Hệ quả chấp nhận: lịch sử của nhánh `test` gồm commit của `lengtrungtin` và các
commit mới. Công của hai nhánh kia không hiện trong lịch sử `test`.

## 6. Cơ sở dữ liệu

### 6.1 Schema chuẩn

Lấy schema của `lleduy` (`database/DATLICHKHAM_FINAL.sql`), gồm 9 bảng:
`VAI_TRO`, `NGUOI_DUNG`, `BAC_SI`, `KHOA`, `CHUYEN_KHOA`,
`BAC_SI_CHUYEN_KHOA`, `LICH_LAM_VIEC`, `LICH_KHAM`, `HO_SO_BENH_NHAN`. Ràng
buộc trạng thái nằm ngay trong bảng: cột sinh `LICH_KHAM.malichdangdung` cộng
`UNIQUE` (mỗi khung giờ tối đa một lịch chưa huỷ) và `CHECK chk_lich_kham_huy`.

File SQL **không** tạo view `v_lich_trong` và trigger `trg_lich_kham_bu` — hai
cái tên đó chỉ xuất hiện trong comment. Thiết kế này không dựng lại
`v_lich_trong` (không cần, vì `trangthai` giờ là dữ liệu thật) và **viết mới**
trigger đồng bộ ở mục 6.2.

Dữ liệu hiện có trong `dat_lich_kham` được **drop và import lại**; không viết
script chuyển đổi.

### 6.2 Thay đổi so với `lleduy`

**`LICH_LAM_VIEC.trangthai`** đổi thành `ENUM('con_trong', 'da_dat')`.
`ngung_nhan` bị bỏ: bác sĩ không có khái niệm "ngừng nhận lịch".

Trạng thái `da_dat` là dữ liệu **lưu thật**, không suy ra từ `LICH_KHAM`.

Để không có hai nguồn sự thật: **một trigger trên `LICH_KHAM` là nguồn ghi duy
nhất** của `LICH_LAM_VIEC.trangthai`. Trigger chạy sau mỗi `INSERT`, `UPDATE`,
`DELETE` trên `LICH_KHAM` và đặt lại trạng thái khung giờ theo việc còn dòng
nào `trangthai <> 'da_huy'` hay không.

Hệ quả bắt buộc ở tầng ứng dụng:

- Đặt lịch và huỷ lịch **chỉ ghi `LICH_KHAM`**, không `UPDATE LICH_LAM_VIEC`
  bằng tay. Không còn hàm kiểu `lich_lam_viec_mo_khung_gio`.
- Bỏ toàn bộ kiểm tra `NOT EXISTS (SELECT 1 FROM LICH_KHAM ...)` đi kèm cột
  `trangthai`: 3 chỗ ở model bệnh nhân, 2 chỗ ở model bác sĩ. Điều kiện còn lại
  chỉ là `trangthai = 'con_trong'`, cộng `SELECT ... FOR UPDATE` trên dòng khung
  giờ khi đặt lịch để hai người không đặt cùng lúc.
- Lưới an toàn cuối cùng vẫn là `UNIQUE(malichdangdung)`: dù trigger sai, một
  khung giờ không thể có hai lịch chưa huỷ.

**Thêm cột `NGUOI_DUNG.anhdaidien VARCHAR(255) NULL`** cho ảnh đại diện bác sĩ.
`lleduy` và `lengtrungtin` không có cột này nhưng code `vmnhutanh` đọc/ghi nó.

Không thêm ràng buộc CSDL nào cho việc khoá quản trị; quy tắc đó thuộc tầng ứng
dụng (mục 7).

### 6.3 Dữ liệu mẫu

Bộ dữ liệu mới, quy mô vừa: vài chục người dùng, vài bác sĩ kèm chuyên khoa và
lịch làm việc cho vài ngày tới, lịch khám ở đủ bốn trạng thái để demo. Ba tài
khoản demo (bệnh nhân, bác sĩ, quản trị) có mật khẩu ghi trong README.

## 7. Đăng nhập và phân quyền

- Một trang đăng nhập duy nhất, giao diện của `lengtrungtin`.
- **Đăng nhập bằng số điện thoại** và mật khẩu. Ba nhánh cũ khác nhau (SĐT,
  email, SĐT); thống nhất theo SĐT vì đó là lựa chọn của nhánh đang dùng giao
  diện đăng nhập.
- Sau khi đăng nhập, hệ thống đọc vai trò và chuyển trang:
  - bệnh nhân → `trang-chu`
  - bác sĩ → `bac-si/tong-quan`
  - quản trị → `quan-tri/tong-quan`
- Tài khoản có `trangthai='bi_khoa'` không đăng nhập được, và **bị đẩy ra ngay
  giữa phiên**: router đọc lại `trangthai` mỗi request, thấy `bi_khoa` thì xoá
  phiên và về trang đăng nhập kèm lời nhắn.
- **Không có khoá tạm sau nhiều lần đăng nhập sai.** Đăng nhập sai chỉ báo "Số
  điện thoại hoặc mật khẩu không đúng." (Cơ chế 5 lần / 15 phút của
  `vmnhutanh` bị bỏ, không port sang.)
- **Quản trị viên không bao giờ bị khoá:** tầng xử lý từ chối thao tác khoá khi
  tài khoản đích có vai trò 3 hoặc là chính người đang đăng nhập, và giao diện
  không hiển thị nút khoá ở hai trường hợp đó.
- **Phiên đăng nhập thống nhất:** `$_SESSION['manguoidung']` (số) và
  `$_SESSION['vai_tro']` (số: 1 bệnh nhân, 2 bác sĩ, 3 quản trị). Ba cơ chế cũ
  (`is_logged_in`, `SS_MA_NGUOI_DUNG`, `$_SESSION['id']`) bị loại bỏ hoàn toàn.

## 8. Cấu trúc thư mục

```
config/            constants.php, database.php, routes.php
app/Controllers/   AuthController.php
                   BenhNhan/  (trang_chu, dat_kham, chi_tiet_lich_kham, lich_cua_toi,
                               thong_tin_tai_khoan, doi_mat_khau, ho_so)
                   BacSi/     (tong_quan, lich_lam_viec, lich_kham, kham_benh, ca_nhan)
                   QuanTri/   (nguoi_dung, bac_si)
app/Models/        NguoiDung, BacSi, LichLamViec, LichKham, HoSoKham, HoSoBenhNhan
app/Helpers/       csrf, session, utils, avatar, ho_so
app/Views/         auth/, benh-nhan/, bac-si/, quan-tri/, partials/
assets/css/        benh-nhan/ (8 file của lengtrungtin), bac-si/, quan-tri/
assets/js/         benh-nhan/, bac-si/, quan-tri/
assets/uploads/    avatar/ (có .htaccess chặn chạy PHP)
index.php          router chung, đọc tham số `page`
```

Quy tắc: mỗi vai trò một thư mục con trong `Controllers`, `Views`, `assets/css`,
`assets/js`. Không có thư mục `chung/` cho CSS.

## 9. Điều hướng

- Một `index.php` ở gốc, đọc `$_GET['page']`.
- Tên biến bên trong code vẫn là tiếng Việt (`$trang`, `$danhSachTrang`); chỉ
  tên tham số URL là `page`.
- Bảng định tuyến khai báo: khóa `page` → tiêu đề, view, controller, JS, và
  **vai trò được phép**. Router kiểm tra quyền trước khi chạy controller.
- Tiền tố vai trò trong giá trị `page`:

| `page` | Ai vào được |
|---|---|
| `dang-nhap`, `dang-ky`, `dang-xuat`, `trang-chu`, `dat-kham` | công khai |
| `chi-tiet-lich-kham` | công khai xem; bấm đặt lịch mới bắt đăng nhập |
| `lich-cua-toi`, `thong-tin-tai-khoan`, `doi-mat-khau`, `tao-ho-so` | bệnh nhân |
| `bac-si/tong-quan`, `bac-si/lich-lam-viec`, `bac-si/lich-kham`, `bac-si/kham-benh`, `bac-si/ca-nhan` | bác sĩ |
| `quan-tri/tong-quan`, `quan-tri/nguoi-dung`, `quan-tri/bac-si`, `quan-tri/bac-si/them`, `quan-tri/bac-si/sua` | quản trị |

- Các file JS của `vmnhutanh` đổi URL từ `index.php?trang=...&ajax=...` thành
  `index.php?page=bac-si/...&ajax=...`. Đây là chỗ duy nhất trong code giao diện
  phải sửa.

## 10. Chức năng từng vai trò

### Bệnh nhân (nguồn: `lengtrungtin`, giữ nguyên)

Đăng ký tài khoản, đăng nhập, tạo/cập nhật hồ sơ, tìm lịch khám theo ngày và
chuyên khoa, đặt lịch, xem lịch sử, huỷ lịch, xem/đổi thông tin tài khoản, đổi
mật khẩu. Toàn bộ HTML và CSS giữ nguyên; chỉ lớp xử lý phía sau được sửa để
đọc phiên đăng nhập thống nhất và schema mới.

### Bác sĩ (nguồn: `vmnhutanh`)

Tổng quan trong ngày, quản lý lịch làm việc (tạo/sửa/xoá khung giờ), xem và tìm
lịch khám, xác nhận/huỷ lịch khám, khám bệnh và ghi hồ sơ kết quả, trang cá nhân
(đổi thông tin, email cá nhân, mật khẩu, ảnh đại diện). Bỏ hoàn toàn trang tự
đăng ký bác sĩ.

`vmnhutanh` đã dùng sẵn `con_trong`/`da_dat` từ trước
(`config/constants.php`, bộ lọc trong `Views/doctor/lichlamviec.php`), nên phần
giao diện này giữ nguyên. Thay đổi thật nằm ở hai chỗ: enum trong CSDL, và
đường ghi `da_dat` — hiện chưa có gì ghi nó, vì bệnh nhân đặt lịch không đụng
`LICH_LAM_VIEC` và bác sĩ xác nhận chỉ sửa `LICH_KHAM`. Sau khi có trigger, cả
hai đường đó tự động đúng mà không phải thêm code.

### Quản trị (nguồn: `lleduy`, giữ nguyên phạm vi)

Trang tổng quan có thống kê (số bệnh nhân, số bác sĩ, số lịch chờ xác nhận, số
tài khoản bị khoá), danh sách và tìm kiếm người dùng, khoá/mở khoá tài khoản
(trừ vai trò quản trị), danh sách bác sĩ, thêm và sửa bác sĩ kèm chuyên khoa.
**Không** có trang xác nhận/huỷ lịch khám; việc đó chỉ bác sĩ làm. Trang đăng
ký tự phát của `lleduy` bị bỏ — đăng ký chỉ dành cho bệnh nhân.

## 11. Những điểm đã quyết trong hội thoại

| Vấn đề | Quyết định |
|---|---|
| Schema chuẩn | `lleduy`, sửa `LICH_LAM_VIEC.trangthai` thành `('con_trong','da_dat')` |
| Dữ liệu hiện có | Drop và import lại |
| Tài khoản bác sĩ | Chỉ quản trị tạo; không tự đăng ký |
| Đăng nhập quản trị | Chung trang đăng nhập, rẽ nhánh theo vai trò |
| Ai xác nhận lịch khám | Chỉ bác sĩ |
| Nhánh | Một nhánh `test` duy nhất |
| Cấu trúc thư mục | Như mục 8 |
| Tham số URL | `page` |
| Phiên đăng nhập | `manguoidung` + `vai_tro` |
| Trang cá nhân | Mỗi vai trò một trang riêng |
| Giao diện bệnh nhân | Giữ nguyên 100% |
| Tài khoản demo | Ba tài khoản, mật khẩu trong README |
| CSS | Ba bộ tách biệt, không dùng chung |
| Đăng nhập bằng | Số điện thoại |
| Email bác sĩ | Bác sĩ tự bổ sung ở trang cá nhân |
| Ảnh đại diện | Lưu ở `assets/uploads/avatar/` |
| Quên mật khẩu quản trị | Không làm chức năng; ghi cách khôi phục trong README |
| Chức năng quản trị | Đúng phạm vi `lleduy` |
| Quản trị bị khoá | Không bao giờ |
| Cách merge | B — tách từ `lengtrungtin`, port code hai nhánh kia |

## 12. Những gì để lại cho người thiết kế kế hoạch

- Thứ tự thực thi và cách chia bước.
- Cách chuyển đổi từng file code `vmnhutanh`/`lleduy` sang cấu trúc mới, file nào
  viết lại, file nào chỉ sửa đường dẫn.
- Nội dung cụ thể của file trigger đồng bộ `LICH_LAM_VIEC`.
- Kịch bản kiểm thử cho từng vai trò.
