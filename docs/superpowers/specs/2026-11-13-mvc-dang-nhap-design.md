# Thiết kế: Tách trang đăng nhập theo MVC

## Ý định

Tổ chức lại luồng đăng nhập hiện đang nằm toàn bộ trong `dang-nhap.php` theo MVC thuần PHP, giúp phần truy vấn dữ liệu, xử lý nghiệp vụ và giao diện dễ đọc/dễ bảo trì hơn. Giao diện, URL hiện tại và hành vi đăng nhập phải được giữ nguyên.

## Phạm vi

- **Có:** đăng nhập bằng số điện thoại và mật khẩu; kiểm tra dữ liệu; truy vấn `NGUOI_DUNG`; `password_verify()`; session; redirect sau đăng nhập; thông báo lỗi; giữ lại số điện thoại đã nhập.
- **Không có:** chuyển đổi đăng ký, tạo hồ sơ, đặt lịch, menu, logout hoặc toàn bộ ứng dụng sang MVC.
- **Không làm:** thêm framework, Composer, router URL mới hoặc thay đổi thiết kế giao diện.

## Hướng đã chọn

Dùng MVC thuần PHP vì dự án hiện tại là PHP procedural, đang dùng `mysqli` và các file entry point trực tiếp. Hướng này có chi phí thay đổi thấp nhất nhưng vẫn tách được trách nhiệm rõ ràng. Router hoặc Laravel sẽ làm phạm vi thay đổi lớn hơn mà không đem lại lợi ích cần thiết cho riêng trang đăng nhập.

## Thiết kế

- `app/Models/NguoiDung.php`: lớp model nhận `mysqli`, dùng prepared statement để tìm người dùng theo `sdt`, trả về dữ liệu tài khoản hoặc `null`. Không chứa HTML, session hay redirect.
- `app/Controllers/AuthController.php`: action đăng nhập kiểm tra session hiện tại, nhận POST, kiểm tra trường bắt buộc, gọi model, xác thực mật khẩu, thiết lập `$_SESSION['manguoidung']` và `$_SESSION['is_logged_in']`, rồi redirect đến `xem-lich-kham.php`. Action trả dữ liệu form/message cho view khi chưa thành công.
- `app/Views/auth/login.php`: giữ nguyên HTML, class CSS, nội dung tiếng Việt và các include giao diện hiện có. View chỉ hiển thị dữ liệu được truyền vào và escape bằng `htmlspecialchars()`.
- `dang-nhap.php`: entry point mỏng; khởi động session, nạp kết nối/model/controller và gọi action. URL này tiếp tục là URL đăng nhập hiện tại.

## Tiêu chí hoàn thành

1. Các file mới và entry point không có lỗi cú pháp PHP.
2. Giao diện đăng nhập không đổi về bố cục và style.
3. Sai hoặc thiếu thông tin vẫn cho đúng thông báo hiện tại.
4. Đăng nhập hợp lệ vẫn tạo session và chuyển tới `xem-lich-kham.php`.
5. Truy cập lại trang đăng nhập khi đã đăng nhập vẫn chuyển hướng như trước.
6. Không còn SQL hoặc nghiệp vụ xác thực trong view.
7. Đăng ký và các trang khác vẫn hoạt động vì không bị thay đổi.
