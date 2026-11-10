document.addEventListener("DOMContentLoaded", () => {
  const thanBangKhamBenh = document.getElementById("than-bang-kham-benh");
  const khungHoSo = document.getElementById("khung-ho-so");
  const hsTieuDe = document.getElementById("hs-tieu-de");
  const hsNgaySinh = document.getElementById("hs-ngay-sinh");
  const hsGioiTinh = document.getElementById("hs-gioi-tinh");
  const hsSdt = document.getElementById("hs-sdt");
  const hsLyDo = document.getElementById("hs-ly-do");
  
  const formKham = khungHoSo ? khungHoSo.querySelector("form.mau") : null;
  const txtChanDoan = formKham ? formKham.querySelectorAll("textarea")[0] : null;
  const txtGhiChu = formKham ? formKham.querySelectorAll("textarea")[1] : null;
  const btnHoanThanh = formKham ? formKham.querySelector("button[type='submit']") : null;
  const btnDongHoSo = document.getElementById("btn-dong-ho-so");

  if (!thanBangKhamBenh) return;

  // Biến lưu mã bệnh nhân đang mở hồ sơ
  let maBenhNhanDangChon = null;

  // Mảng dữ liệu giả lập ca khám (Sau này PHP sẽ đổ JSON vào)
  let danhSachCaKham = [
    { ma: 104, ten: "Lê Văn Minh", ngaySinh: "12/03/1990", gioiTinh: "Nam", sdt: "0987 654 321", gio: "08:30–09:00", lyDo: "Khám tổng quát", trangThai: "Đã xác nhận", chanDoan: "", ghiChu: "" },
    { ma: 106, ten: "Võ Thị Lan", ngaySinh: "05/11/1992", gioiTinh: "Nữ", sdt: "0912 888 999", gio: "09:30–10:00", lyDo: "Đau lưng", trangThai: "Đã xác nhận", chanDoan: "", ghiChu: "" },
    { ma: 103, ten: "Trần Thị Hoa", ngaySinh: "02/09/1988", gioiTinh: "Nữ", sdt: "0903 111 222", gio: "08:00–08:30", lyDo: "Đau đầu kéo dài", trangThai: "Đã khám", chanDoan: "Thiếu máu não nhẹ", ghiChu: "Nghỉ ngơi, uống nhiều nước" }
  ];

  const NHAN_TRANG_THAI = {
    "Đã xác nhận": { lop: "n-xanh", chu: "Đã xác nhận" },
    "Đã khám": { lop: "n-luc", chu: "Đã khám" },
    "Đã hủy": { lop: "n-do", chu: "Đã hủy" }
  };

  // 1. Hàm vẽ lại toàn bộ bảng
  const veBang = () => {
    thanBangKhamBenh.innerHTML = danhSachCaKham.length ? danhSachCaKham.map(k => {
      const configNhan = NHAN_TRANG_THAI[k.trangThai] || { lop: "n-xam", chu: k.trangThai };
      let thaoTac = "—";

      if (k.trangThai === "Đã xác nhận") {
        thaoTac = `
          <button class="btn xanh nho" data-kham="${k.ma}">Khám</button>
          <button class="btn do nho" data-huy="${k.ma}">Hủy</button>
        `;
      } else if (k.trangThai === "Đã khám") {
        thaoTac = `<button class="btn vien nho" data-xem="${k.ma}">Xem kết quả</button>`;
      }

      // Đánh dấu dòng đang được chọn/xem
      const isSelected = maBenhNhanDangChon === k.ma ? 'style="background-color: #f0f7ff; cursor: pointer;"' : 'style="cursor: pointer;"';

      return `
        <tr data-dong-ma="${k.ma}" ${isSelected}>
          <td>${k.gio}</td>
          <td><b>${k.ten}</b></td>
          <td>${k.lyDo}</td>
          <td><span class="nhan ${configNhan.lop}">${configNhan.chu}</span></td>
          <td>${thaoTac}</td>
        </tr>
      `;
    }).join("") : `<tr><td colspan="5" style="text-align:center;">Không có ca khám nào.</td></tr>`;
  };

  // 2. Mở hồ sơ khám bệnh / xem kết quả
  const moHoSo = (khamObj, cheDoXem = false) => {
    maBenhNhanDangChon = khamObj.ma;
    hsTieuDe.textContent = `${cheDoXem ? 'Kết quả khám' : 'Hồ sơ khám'}: ${khamObj.ten}`;
    hsNgaySinh.textContent = khamObj.ngaySinh;
    hsGioiTinh.textContent = khamObj.gioiTinh;
    hsSdt.textContent = khamObj.sdt;
    hsLyDo.textContent = khamObj.lyDo;

    txtChanDoan.value = khamObj.chanDoan || "";
    txtGhiChu.value = khamObj.ghiChu || "";

    // Nếu ở chế độ "Xem kết quả" (Đã khám): Khóa ô nhập dữ liệu & ẩn nút hoàn thành
    txtChanDoan.readOnly = cheDoXem;
    txtGhiChu.readOnly = cheDoXem;
    if (btnHoanThanh) btnHoanThanh.style.display = cheDoXem ? "none" : "inline-block";

    khungHoSo.style.display = "block";
    veBang(); // Cập nhật style highlight dòng được chọn
  };

  // 3. Sự kiện Click trên Bảng (Ủy quyền sự kiện)
  thanBangKhamBenh.addEventListener("click", (e) => {
    const nutHuy = e.target.closest("[data-huy]");
    const nutKham = e.target.closest("[data-kham]");
    const nutXem = e.target.closest("[data-xem]");
    const dongTr = e.target.closest("tr[data-dong-ma]");

    // --- NÚT HỦY ---
    if (nutHuy) {
      e.stopPropagation();
      const ma = Number(nutHuy.dataset.huy);
      const k = danhSachCaKham.find(item => item.ma === ma);
      if (k && confirm(`Bạn có chắc chắn muốn HỦY ca khám của bệnh nhân ${k.ten}?`)) {
        k.trangThai = "Đã hủy";
        if (maBenhNhanDangChon === ma) {
          khungHoSo.style.display = "none";
          maBenhNhanDangChon = null;
        }
        veBang();
      }
      return;
    }

    // --- NÚT KHÁM ---
    if (nutKham) {
      e.stopPropagation();
      const ma = Number(nutKham.dataset.kham);
      const k = danhSachCaKham.find(item => item.ma === ma);
      if (k) moHoSo(k, false);
      return;
    }

    // --- NÚT XEM KẾT QUẢ ---
    if (nutXem) {
      e.stopPropagation();
      const ma = Number(nutXem.dataset.xem);
      const k = danhSachCaKham.find(item => item.ma === ma);
      if (k) moHoSo(k, true);
      return;
    }

    // --- NHẤP VÀO BẤM KỲ ĐẦU TRÊN DÒNG CỦA BỆNH NHÂN ĐÃ KHÁM ---
    if (dongTr) {
      const ma = Number(dongTr.dataset.dongMa);
      const k = danhSachCaKham.find(item => item.ma === ma);
      if (k) {
        if (k.trangThai === "Đã khám") {
          moHoSo(k, true); // Xem kết quả
        } else if (k.trangThai === "Đã xác nhận") {
          moHoSo(k, false); // Mở form khám
        }
      }
    }
  });

  // 4. Sự kiện Submit Form "Hoàn thành khám"
  if (formKham) {
    formKham.addEventListener("submit", (e) => {
      e.preventDefault();
      if (!maBenhNhanDangChon) return;

      const k = danhSachCaKham.find(item => item.ma === maBenhNhanDangChon);
      if (!k) return;

      if (!txtChanDoan.value.trim()) {
        alert("Vui lòng nhập chẩn đoán trước khi hoàn thành!");
        txtChanDoan.focus();
        return;
      }

      // Lưu thông tin & đổi trạng thái sang "Đã khám"
      k.chanDoan = txtChanDoan.value.trim();
      k.ghiChu = txtGhiChu.value.trim();
      k.trangThai = "Đã khám";

      alert(`Đã hoàn tất khám bệnh cho: ${k.ten}`);
      
      // Chuyển ngay form hồ sơ sang chế độ "Xem kết quả" (chỉ đọc)
      moHoSo(k, true);
    });
  }

  // 5. Đóng khung hồ sơ
  if (btnDongHoSo) {
    btnDongHoSo.addEventListener("click", () => {
      khungHoSo.style.display = "none";
      maBenhNhanDangChon = null;
      veBang();
    });
  }

  // Khởi tạo bảng ban đầu
  veBang();
});