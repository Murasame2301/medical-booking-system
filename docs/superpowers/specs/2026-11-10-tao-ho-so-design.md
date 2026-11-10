# Thiết kế chức năng tạo hồ sơ khám bệnh

- **Ngày:** 2026-11-10
- **Phạm vi:** hoàn thiện [tao-ho-so.php](../../../../tao-ho-so.php) và schema hồ sơ bệnh nhân.

## Ý định

Cho phép bệnh nhân đã đăng ký tài khoản nhập và lưu thông tin hồ sơ khám bệnh, bao gồm số BHYT. Tình trạng sức khỏe sẽ được nhập ở bước đặt lịch khám, không thuộc form tạo hồ sơ hiện tại. Sau khi lưu thành công, người dùng được chuyển đến luồng đặt lịch khám.

## Mục tiêu

- Lưu số BHYT trong bảng `HO_SO_BENH`.
- Lưu ngày sinh, giới tính, email và địa chỉ trong `NGUOI_DUNG`.
- Tạo bản ghi `BENH_NHAN` trước khi tạo hồ sơ để thỏa mãn khóa ngoại.
- Cho phép mở lại trang để cập nhật hồ sơ hiện có thay vì tạo bản ghi trùng.
- Dùng prepared statements, transaction và thông báo lỗi theo phong cách các trang xác thực hiện tại.
- Giữ giao diện theo hệ thống `auth.css`, dùng lại header/story và hỗ trợ hiển thị dữ liệu đã lưu.

## Không làm

- Không thay đổi luồng đăng ký, đăng nhập hoặc cấu trúc đặt lịch ngoài chuyển hướng sau khi lưu.
- Không lưu số BHYT vào `NGUOI_DUNG`.
- Không ép số BHYT là duy nhất trong database.
- Không thêm upload giấy tờ, xác thực BHYT với bên thứ ba hoặc chức năng quản trị.

## Ràng buộc và quy tắc

- `NGUOI_DUNG` đã có các cột `email`, `ngaysinh`, `gioitinh`, `diachi`.
- `HO_SO_BENH` hiện thiếu cột số BHYT; bổ sung `sobhyt VARCHAR(20) NULL`.
- Số BHYT là trường bắt buộc trên form, tối đa 20 ký tự. Database vẫn để `NULL` để tương thích dữ liệu cũ.
- Chỉ tài khoản có `manguoidung` trong session mới được tạo/cập nhật hồ sơ.
- Transaction gồm cập nhật `NGUOI_DUNG`, tạo `BENH_NHAN` nếu chưa có, và insert/update `HO_SO_BENH`; lỗi ở bất kỳ bước nào sẽ rollback.
- Nếu hồ sơ đã tồn tại thì cập nhật bản ghi đó, không tạo bản ghi mới.

## Các phương án đã cân nhắc

1. **Thêm `sobhyt` vào `HO_SO_BENH` — chọn.** Phù hợp với ý nghĩa dữ liệu và schema đang tách thông tin tài khoản, bệnh nhân và hồ sơ.
2. **Thêm `sobhyt` vào `NGUOI_DUNG`.** Ít truy vấn hơn nhưng trộn dữ liệu y tế vào bảng tài khoản, không phù hợp với thiết kế hiện tại.
3. **Chỉ làm giao diện hoặc bỏ trường BHYT.** Không đáp ứng yêu cầu lưu hồ sơ thực tế.

## Phần còn lại cho triển khai

- Cập nhật `DatLichKham.sql` để schema mới có `sobhyt`.
- Có thể thêm một migration an toàn trong `du-lieu-test.sql` hoặc ghi rõ câu lệnh `ALTER TABLE` để database hiện tại cập nhật được.
- Hoàn thiện xử lý PHP, form HTML và CSS bổ sung nếu cần.
- Kiểm tra cú pháp PHP và các nhánh: chưa đăng nhập, hồ sơ mới, cập nhật hồ sơ, dữ liệu thiếu, lỗi database.
