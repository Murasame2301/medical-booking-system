# Hệ thống đặt lịch khám bệnh

Đồ án môn CT428. Một hệ thống PHP thủ tục (không OOP, không framework) phục vụ
ba vai trò trên cùng một trang đăng nhập:

- **Bệnh nhân** — đăng ký, khai hồ sơ, tìm khung giờ trống, đặt và huỷ lịch khám.
- **Bác sĩ** — quản lý lịch làm việc, xác nhận/huỷ lịch khám, khám bệnh và ghi hồ sơ kết quả.
- **Quản trị viên** — thống kê, quản lý người dùng, khoá/mở khoá tài khoản, thêm và sửa bác sĩ.

Mỗi vai trò có bộ giao diện và CSS riêng; không dùng chung stylesheet.

## Yêu cầu

- XAMPP với PHP 8.0 trở lên và MariaDB 10.4 trở lên.
- Đã bật `mysqli` và `mbstring`.

## Cài đặt

1. Chép mã nguồn vào `C:\xampp\htdocs\medical-booking-system`.
2. Bật MySQL trong XAMPP Control Panel.
3. Tạo lại cơ sở dữ liệu và nạp dữ liệu mẫu:

   ```
   mysql -u root < database/schema.sql
   mysql -u root --default-character-set=utf8mb4 < database/du_lieu_mau.sql
   ```

   `schema.sql` **xoá** database `dat_lich_kham` cũ rồi dựng lại, nên chỉ chạy
   khi chấp nhận mất dữ liệu. `du_lieu_mau.sql` chạy lại được nhiều lần: nó dọn
   dữ liệu cũ và nạp bộ mẫu mới, nhưng giữ danh mục khoa/chuyên khoa.

4. Mở `http://localhost/medical-booking-system/`.

## Tài khoản demo

Mọi tài khoản trong dữ liệu mẫu dùng chung mật khẩu `Demo@12345`.

| Vai trò | Số điện thoại | Ghi chú |
|---|---|---|
| Quản trị viên | `0900000001` | Đăng nhập sẽ vào trang tổng quan quản trị |
| Bác sĩ | `0900000002` | Nguyễn Văn An, chuyên khoa Tim mạch |
| Bệnh nhân | `0910000001` | Trần Gia Cường, hồ sơ đã đầy đủ |

Tài khoản `0910000010` đang ở trạng thái bị khoá, dùng để thử màn hình quản lý
người dùng.

## Cấu trúc

```
index.php                 router chung, đọc $_GET['page']
config/                   constants, database, routes
app/Helpers/              csrf, session, utils, db, avatar, ho_so, ajax
app/Controllers/          AuthController.php
                          BenhNhan/  BacSi/  QuanTri/
app/Models/               truy vấn CSDL, mỗi hàm nhận $conn đầu tiên
app/Views/                auth/, benh-nhan/, bac-si/, quan-tri/, partials/, loi/
assets/css/               benh-nhan/, bac-si/, quan-tri/
assets/js/                benh-nhan/, bac-si/, quan-tri/
assets/uploads/avatar/    ảnh đại diện bác sĩ
database/                 schema.sql, du_lieu_mau.sql
```

Mọi trang đi qua `index.php?page=...`. Bảng định tuyến ở `config/routes.php`
khai báo tiêu đề, view, controller, JS và **vai trò được phép vào**.

## Ghi chú kỹ thuật

- `LICH_LAM_VIEC.trangthai` (`con_trong` / `da_dat`) là dữ liệu thật, do trigger
  trong `database/schema.sql` ghi. Code ứng dụng **không** cập nhật cột này;
  đặt lịch và huỷ lịch chỉ ghi bảng `LICH_KHAM`.
- Phiên đăng nhập dùng `$_SESSION['manguoidung']`, `['vai_tro']`, `['ho_ten']`.
  Tài khoản bị khoá giữa phiên sẽ bị đẩy ra ở request kế tiếp.
- Quản trị viên không thể bị khoá, và không thể tự khoá chính mình.
- Không có chức năng quên mật khẩu. Khi cần khôi phục mật khẩu quản trị, chạy
  trên máy chủ:

  ```
  php -r "echo password_hash('MatKhauMoi@123', PASSWORD_DEFAULT), PHP_EOL;"
  ```

  rồi cập nhật cột `matkhau` của tài khoản trong bảng `NGUOI_DUNG`.
- Đăng nhập sai nhiều lần **không** bị khoá tạm; chỉ báo sai số điện thoại hoặc
  mật khẩu.
