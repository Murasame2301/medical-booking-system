document.addEventListener("DOMContentLoaded", () => {
  const formLoc = document.getElementById("form-loc-kham");
  const locTuKhoa = document.getElementById("loc-tu-khoa");
  const locTu = document.getElementById("loc-tu");
  const locDen = document.getElementById("loc-den");
  const locTrangThai = document.getElementById("loc-trang-thai");
  const thanBang = document.getElementById("than-bang-kham");
  const datLai = document.getElementById("dat-lai");

  // Nếu không có bảng trên trang thì dừng JS lại (để không lỗi khi dùng chung file)
  if (!thanBang) return;

  // 1. Cấu hình giao diện Trạng Thái
  const NHAN_TRANG_THAI = {
    "Chờ xác nhận": "n-vang",
    "Đã xác nhận": "n-xanh",
    "Đã khám": "n-luc",
    "Đã hủy": "n-do"
  };

  // 2. Mảng dữ liệu mẫu (Sau này PHP sẽ echo mảng JSON vào biến này)
  let danhSachLichKham = [
    { ma: 105, ten: "Phạm Thu Hà", ngaySinh: "25/07/1995", gioiTinh: "Nữ", sdt: "0912345678", ngay: "2026-11-04", gio: "09:00–09:30", lyDo: "Ho kéo dài", trangThai: "Chờ xác nhận" },
    { ma: 104, ten: "Lê Văn Minh", ngaySinh: "12/03/1990", gioiTinh: "Nam", sdt: "0987654321", ngay: "2026-11-04", gio: "08:30–09:00", lyDo: "Khám tổng quát", trangThai: "Đã xác nhận" },
    { ma: 103, ten: "Trần Thị Hoa", ngaySinh: "02/09/1988", gioiTinh: "Nữ", sdt: "0903111222", ngay: "2026-11-03", gio: "08:00–08:30", lyDo: "Đau đầu kéo dài", trangThai: "Đã khám" },
    { ma: 102, ten: "Nguyễn Quốc Bảo", ngaySinh: "18/01/2001", gioiTinh: "Nam", sdt: "0935222333", ngay: "2026-11-05", gio: "10:00–10:30", lyDo: "Đau dạ dày", trangThai: "Đã hủy" }
  ];

  // 3. Hàm tạo HTML cho 1 dòng (row)
  const taoDongHTML = (k) => {
    const classNhan = NHAN_TRANG_THAI[k.trangThai] || "";
    let thaoTac = "—";

    // Phân quyền nút bấm theo trạng thái
    if (k.trangThai === "Chờ xác nhận") {
      thaoTac = `
        <button class="btn xanh nho" data-xacnhan="${k.ma}">Xác nhận</button>
        <button class="btn do nho" data-huy="${k.ma}">Hủy</button>
      `;
    } else if (k.trangThai === "Đã xác nhận") {
      thaoTac = `<button class="btn do nho" data-huy="${k.ma}">Hủy</button>`;
    }

    // Sdt được format lại cho đẹp (tuỳ chọn)
    const sdtDinhDang = k.sdt.replace(/(\d{4})(\d{3})(\d{3})/, '$1 $2 $3');

    return `
      <tr>
        <td><span class="nhan ${classNhan}">${k.trangThai}</span></td>
        <td>${k.ma}</td>
        <td>${k.ten}</td>
        <td>${k.ngaySinh}</td>
        <td>${k.gioiTinh}</td>
        <td>${sdtDinhDang}</td>
        <td>${k.gio}</td>
        <td>${k.lyDo}</td>
        <td>${thaoTac}</td>
      </tr>
    `;
  };

  // 4. Hàm Lọc & Vẽ Bảng
  const veBang = () => {
    const tuKhoa = (locTuKhoa.value || "").toLowerCase().trim();
    const tuNgay = locTu.value;
    const denNgay = locDen.value;
    const tt = locTrangThai.value;

    const danhSachLoc = danhSachLichKham.filter((k) => {
      // Lọc ngày (bỏ qua nếu ô ngày trống)
      const dungNgay = (!tuNgay || k.ngay >= tuNgay) && (!denNgay || k.ngay <= denNgay);
      // Lọc trạng thái
      const dungTrangThai = !tt || k.trangThai === tt;
      // Lọc từ khóa (tên hoặc SĐT)
      const dungTuKhoa = !tuKhoa || k.ten.toLowerCase().includes(tuKhoa) || k.sdt.includes(tuKhoa.replace(/\s/g, ""));

      return dungNgay && dungTrangThai && dungTuKhoa;
    });

    thanBang.innerHTML = danhSachLoc.length 
      ? danhSachLoc.map(taoDongHTML).join("") 
      : `<tr><td colspan="9" style="text-align:center;">Không tìm thấy lịch khám phù hợp.</td></tr>`;
  };

  // SU KIEN
  // Fix lỗi khoảng ngày (Đến < Từ)
  const chinhKhoangNgay = () => {
    if (locDen.value && locTu.value && locDen.value < locTu.value) locDen.value = locTu.value;
  };

  [locTu, locDen].forEach(el => el.addEventListener("blur", chinhKhoangNgay));
  
  formLoc.addEventListener("submit", (e) => {
    e.preventDefault();
    chinhKhoangNgay();
    veBang();
  });

  datLai.addEventListener("click", () => {
    formLoc.reset(); // Reset nhanh mọi input
    veBang();
  });

  // Ủy quyền sự kiện cho các nút Thao tác (Xác nhận / Hủy)
  thanBang.addEventListener("click", (e) => {
    const nutXacNhan = e.target.closest("[data-xacnhan]");
    const nutHuy = e.target.closest("[data-huy]");

    if (nutXacNhan) {
      const ma = Number(nutXacNhan.dataset.xacnhan);
      const lich = danhSachLichKham.find(k => k.ma === ma);
      if (lich && confirm(`Xác nhận lịch khám của ${lich.ten}?`)) {
        lich.trangThai = "Đã xác nhận";
        veBang(); // Vẽ lại bảng
        // Sau này gọi AJAX/Fetch gửi lên PHP ở đây
      }
    }

    if (nutHuy) {
      const ma = Number(nutHuy.dataset.huy);
      const lich = danhSachLichKham.find(k => k.ma === ma);
      if (lich && confirm(`Bạn muốn HỦY lịch khám của ${lich.ten}?`)) {
        lich.trangThai = "Đã hủy";
        veBang();
        // Sau này gọi AJAX/Fetch gửi lên PHP ở đây
      }
    }
  });

  // Gọi lần đầu để render dữ liệu lúc load trang
  veBang();
});