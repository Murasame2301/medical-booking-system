dữ liệu trả về luôn có dạng từ gạch chân: ví dụ như 'du_lieu'
và các giá trị của hằng số cũng như vậy 

ĐANG XỬ LÝ CHỨC NĂNG TẠO Ở TRANG LỊCH LÀM VIỆC: GỬI VÀ NHẬN DỮ LIỆU 

NOTE: COI LAI FILE DANG NHAP CHINH THUC VE LUU SESSION

Loi 405: tra ve 'Hệ thống đang xử lý'

1. Nhóm 1xx – Thông tin (Informational)
Server đã nhận được yêu cầu và đang tiếp tục quá trình xử lý.

100 - Continue: Server đã nhận phần đầu của yêu cầu, client có thể tiếp tục gửi phần còn lại.

101 - Switching Protocols: Server đồng ý chuyển đổi giao thức theo yêu cầu của client (ví dụ: sang WebSocket).

102 - Processing: Server đang xử lý yêu cầu nhưng chưa có phản hồi cuối cùng (WebDAV).

103 - Early Hints: Trả về một số header trước để trình duyệt bắt đầu tải trước tài nguyên trong lúc server chuẩn bị phản hồi chính.

2. Nhóm 2xx – Thành công (Success)
Yêu cầu đã được server tiếp nhận, hiểu và xử lý thành công.

200 - OK: Yêu cầu thành công (mã phổ biến nhất cho GET, POST, PUT...).

201 - Created: Yêu cầu thành công và một tài nguyên mới đã được tạo ra (thường dùng cho POST).

202 - Accepted: Yêu cầu đã được chấp nhận để xử lý bất đồng bộ, nhưng chưa hoàn tất.

204 - No Content: Xử lý thành công nhưng không trả về nội dung (body) nào (thường dùng cho DELETE hoặc cập nhật ngầm).

206 - Partial Content: Server chỉ trả về một phần dữ liệu theo header Range (dùng khi tải file lớn hoặc stream video).

3. Nhóm 3xx – Điều hướng (Redirection)
Client cần thực hiện thêm hành động (thường là chuyển hướng sang URL khác) để hoàn tất yêu cầu.

301 - Moved Permanently: Tài nguyên đã được chuyển vĩnh viễn sang URL mới (tốt cho SEO khi đổi domain/đường dẫn).

302 - Found: Chuyển hướng tạm thời sang URL khác, lần sau client vẫn nên gọi URL cũ.

304 - Not Modified: Dữ liệu không thay đổi kể từ lần truy cập trước, client có thể dùng lại bản lưu trong bộ nhớ đệm (cache).

307 - Temporary Redirect: Chuyển hướng tạm thời nhưng bắt buộc giữ nguyên phương thức HTTP ban đầu (ví dụ POST vẫn phải là POST).

308 - Permanent Redirect: Chuyển hướng vĩnh viễn và giữ nguyên phương thức HTTP ban đầu.

4. Nhóm 4xx – Lỗi phía Client (Client Error)
Yêu cầu chứa cú pháp sai, thiếu quyền truy cập hoặc không thể thực hiện do lỗi từ phía người dùng/ứng dụng gọi API.

400 - Bad Request: Yêu cầu không hợp lệ (sai cú pháp JSON, thiếu tham số, dữ liệu sai định dạng).

401 - Unauthorized: Chưa xác thực danh tính (thiếu token, token hết hạn hoặc sai thông tin đăng nhập).

403 - Forbidden: Đã xác thực nhưng không có quyền truy cập vào tài nguyên này.

404 - Not Found: Không tìm thấy tài nguyên hoặc đường dẫn (URL) trên server.

405 - Method Not Allowed: Phương thức HTTP không được hỗ trợ cho endpoint này (ví dụ dùng GET cho API chỉ nhận POST).

408 - Request Timeout: Server ngắt kết nối vì chờ client gửi yêu cầu quá lâu.

409 - Conflict: Xung đột dữ liệu với trạng thái hiện tại trên server (ví dụ đăng ký trùng email/username).

413 - Content Too Large: Dung lượng dữ liệu gửi lên (payload/file upload) vượt quá giới hạn cho phép của server.

415 - Unsupported Media Type: Định dạng dữ liệu gửi lên không được hỗ trợ (ví dụ sai Content-Type).

422 - Unprocessable Content: Cú pháp đúng nhưng dữ liệu vi phạm quy tắc nghiệp vụ/validation (thường gặp trong REST API).

429 - Too Many Requests: Client gửi quá nhiều yêu cầu trong một khoảng thời gian ngắn (bị giới hạn Rate Limit).

5. Nhóm 5xx – Lỗi phía Server (Server Error)
Yêu cầu từ client hợp lệ nhưng server gặp sự cố trong quá trình xử lý.

500 - Internal Server Error: Lỗi hệ thống chung trên server (thường do ngoại lệ/exception chưa được xử lý trong code).

501 - Not Implemented: Server chưa hỗ trợ chức năng hoặc phương thức được yêu cầu.

502 - Bad Gateway: Server đóng vai trò gateway/proxy (như Nginx, Cloudflare) nhận phản hồi không hợp lệ từ server backend phía sau.

503 - Service Unavailable: Server tạm thời không sẵn sàng (đang bảo trì hoặc quá tải).

504 - Gateway Timeout: Gateway/proxy chờ phản hồi từ server backend quá thời gian quy định.