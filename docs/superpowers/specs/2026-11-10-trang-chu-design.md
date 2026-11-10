# Thiết kế trang chủ đặt lịch khám

- **Ngày:** 2026-11-10
- **Phạm vi:** hoàn thiện [index.php](../../../../index.php) bằng menu có sẵn trong [include/menu.php](../../../../include/menu.php).

## Ý định

Tạo điểm bắt đầu rõ ràng cho cổng đặt lịch khám bệnh trực tuyến. Người dùng mới cần hiểu dịch vụ và đi đến đăng ký/đặt khám nhanh; người dùng đã đăng nhập cần thấy ngay đường dẫn đặt lịch và các khu vực cá nhân.

## Hướng đã chọn

**Trang chủ tối giản, tập trung đặt lịch.** Đây là lựa chọn phù hợp với code hiện tại vì hệ thống đã có các luồng đăng nhập, đăng ký, tạo hồ sơ và đặt lịch; trang chủ chỉ cần kết nối chúng bằng một lời kêu gọi hành động rõ ràng.

## Bố cục dự kiến

```text
┌──────────────────────────────────────────────────────────────┐
│ [logo] Đặt lịch khám                 menu.php                │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│  Chủ động lịch khám.                 [minh họa lịch hẹn]     │
│  An tâm chăm sóc sức khỏe.                                    │
│  Đặt lịch và theo dõi lịch hẹn                                │
│  ở cùng một nơi.                                             │
│  [Đặt khám ngay]  [Xem hồ sơ]                                │
│                                                              │
├──────────────────────────────────────────────────────────────┤
│  Đặt lịch thuận tiện  |  Theo dõi lịch hẹn  |  Hồ sơ rõ ràng  │
└──────────────────────────────────────────────────────────────┘
```

- Header căn trái logo, căn phải menu từ `include/menu.php`; không tạo bản sao các liên kết trong menu.
- Hero căn trái phần nội dung, căn phải minh họa lịch hẹn bằng SVG inline cùng ngôn ngữ hình ảnh với `auth-story.php`.
- CTA chính là `Đặt khám ngay` trỏ đến `tim-lich-kham.php`; CTA phụ là `Xem hồ sơ` trỏ đến `tao-ho-so.php`.
- Ba lợi ích bên dưới là nội dung tĩnh, không giả lập số liệu hoặc đánh giá.
- Trên mobile, header chuyển thành bố cục dọc; menu vẫn hiển thị đầy đủ và hero xếp một cột.

## Token giao diện

- **Màu nền:** `#f3f7fc` cho vùng hero, `#ffffff` cho header và các vùng nội dung.
- **Màu chính:** `#1559b7` cho logo, CTA và điểm nhấn.
- **Màu chữ:** `#153452` cho tiêu đề; `#586b80` cho mô tả.
- **Màu hỗ trợ:** `#e4f5f1` và `#dce6f2` cho minh họa, giữ liên kết với giao diện xác thực.
- **Chữ:** tiếp tục dùng `Be Vietnam Pro` từ `auth.css`, không thêm thư viện mới.

## Mục tiêu

- Dùng trực tiếp `include/menu.php` và giữ đúng trạng thái đăng nhập trong session.
- Có CTA rõ ràng để bắt đầu đặt lịch.
- Đồng bộ font, màu và hình ảnh với trang đăng nhập/đăng ký.
- Responsive, có focus keyboard và tôn trọng `prefers-reduced-motion`.
- Tách CSS trang chủ vào `assets/css/home.css` để không làm rối CSS form xác thực.

## Không làm

- Không thay đổi logic hoặc nội dung của `include/menu.php`.
- Không thêm backend, truy vấn database, số liệu thống kê giả hoặc dữ liệu bác sĩ giả.
- Không thêm framework hoặc dependency mới.
- Không thay đổi các trang đặt lịch, đăng nhập, đăng ký hay hồ sơ.

## Phần còn lại cho triển khai

- Thêm `session_start()` và include `include/menu.php` trong `index.php`.
- Viết markup header, hero, CTA, lợi ích và footer.
- Tạo `assets/css/home.css` với breakpoint mobile, trạng thái focus/hover và reduced motion.
- Kiểm tra PHP syntax, đường dẫn liên kết, diff formatting và trạng thái diagnostics.
