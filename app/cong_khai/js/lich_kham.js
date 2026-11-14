document.addEventListener("DOMContentLoaded", function () {
  // HANG SO ************
  const SO_COT = 9;
  const URL_AJAX = "index.php?trang=lichkham&ajax=";

  // ************ CHON PHAN TU
  const $ = (id) => document.getElementById(id);
  const formLoc = $("form-loc-kham");
  const locTuKhoa = $("loc-tu-khoa");
  const locTu = $("loc-tu");
  const locDen = $("loc-den");
  const locTrangThai = $("loc-trang-thai");
  const thanBang = $("than-bang-kham");
  const datLai = $("dat-lai");

  if (!thanBang) return;
  let hen = null; // bộ hẹn giờ cho ô tìm kiếm

  let danhSachLichKham = [
    // { ma: 105, ten: "Phạm Thu Hà", ngaySinh: "25/07/1995", gioiTinh: "Nữ", sdt: "0912345678", ngay: "2026-11-04", gioBatDau: "09:00", gioKetThuc: "09:30", lyDo: "Ho kéo dài", trangThai: "cho_xac_nhan" },
    // { ma: 104, ten: "Lê Văn Minh", ngaySinh: "12/03/1990", gioiTinh: "Nam", sdt: "0987654321", ngay: "2026-11-04", gioBatDau: "08:30", gioKetThuc: "09:00", lyDo: "Khám tổng quát", trangThai: "da_xac_nhan" },
    // { ma: 103, ten: "Trần Thị Hoa", ngaySinh: "02/09/1988", gioiTinh: "Nữ", sdt: "0903111222", ngay: "2026-11-03", gioBatDau: "08:00", gioKetThuc: "08:30", lyDo: "Đau đầu kéo dài", trangThai: "da_kham" },
    // { ma: 102, ten: "Nguyễn Quốc Bảo", ngaySinh: "18/01/2001", gioiTinh: "Nam", sdt: "0935222333", ngay: "2026-11-05", gioBatDau: "10:00", gioKetThuc: "10:30", lyDo: "Đau dạ dày", trangThai: "da_huy" },
  ];

  // ************ TIEN TICH NHO
  // dinh dang tim kiem trong tim kiem
  function chuanHoa(s) {
    return String(s || "")
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .replace(/đ/g, "d")
      .replace(/Đ/g, "D")
      .toLowerCase()
      .trim();
  }

  // 1 dong du lieu tra ve tu sever sang phia client
  function chuyenLichKham(k) {
    return {
      ma: Number(k.malichkham),
      trangThai: String(k.trangthai),
      ten: String(k.hoten),
      ngaySinh: hienNgay(String(k.ngaysinh).slice(0, 10)),
      gioiTinh: String(k.gioitinh),
      sdt: String(k.sdt),
      ngay: String(k.thoigianbatdau).slice(0, 10),
      gioBatDau: String(k.thoigianbatdau).slice(11, 16),
      gioKetThuc: String(k.thoigianketthuc).slice(11, 16),
      lyDo: String(k.lydo || ""),
    };
  }
  // ************ SO DIEN THOAI DINH DANG 4 3 3
  function dinhDangSdt(sdt) {
    return sdt.replace(/(\d{4})(\d{3})(\d{3})/, "$1 $2 $3");
  }

  // ************  HAM LOC
  function locLichKham(danhSach, dieuKien) {
    const tuKhoa = (dieuKien && dieuKien.tuKhoa) || "";
    const tuNgay = (dieuKien && dieuKien.tuNgay) || "";
    const denNgay = (dieuKien && dieuKien.denNgay) || "";
    const trangThai = (dieuKien && dieuKien.trangThai) || "";

    const tk = chuanHoa(tuKhoa);
    // tim so dien thoai
    const tkSo = tuKhoa.replace(/\s/g, "");

    return danhSach.filter(function (k) {
      const dungNgay =
        (!tuNgay || k.ngay >= tuNgay) && (!denNgay || k.ngay <= denNgay);
      const dungTrangThai =
        !trangThai || trangThai === TAT_CA || k.trangThai === trangThai;
      const dungTuKhoa =
        !tk ||
        chuanHoa(k.ten).includes(tk) ||
        chuanHoa(k.lyDo).includes(tk) ||
        String(k.ma).includes(tk) ||
        (tkSo !== "" && k.sdt.includes(tkSo));
      return dungNgay && dungTrangThai && dungTuKhoa;
    });
  }

  // ************  VE BANG LICH KHAM
  // o thao tac tuong ung voi trang thai cua
  function taoThaoTac(k) {
    if (k.trangThai === "cho_xac_nhan") {
      return taoOChua(
        taoNut("Xác nhận", "btn xanh nho", { xacnhan: k.ma }),
        " ",
        taoNut("Hủy", "btn do nho", { huy: k.ma }),
      );
    }
    if (k.trangThai === "da_xac_nhan") {
      return taoOChua(taoNut("Hủy", "btn do nho", { huy: k.ma }));
    }
    return taoO("td", "—");
  }

  function taoDongKham(k) {
    const dong = taoPhanTu("tr");
    dong.append(
      taoOChua(taoNhan(k.trangThai)),
      taoO("td", k.ma),
      taoO("td", k.ten),
      taoO("td", k.ngaySinh),
      taoO("td", k.gioiTinh),
      taoO("td", dinhDangSdt(k.sdt)),
      taoO(
        "td",
        hienNgay(k.ngay) + " " + gioDauCuoi(k.gioBatDau, k.gioKetThuc),
      ),
      taoO("td", k.lyDo),
      taoThaoTac(k),
    );
    return dong;
  }

  function veBang() {
    const ketQua = locLichKham(danhSachLichKham, {
      tuKhoa: locTuKhoa.value,
      tuNgay: locTu.value,
      denNgay: locDen.value,
      trangThai: locTrangThai.value,
    });
    veDanhSach(
      thanBang,
      ketQua,
      taoDongKham,
      SO_COT,
      "Không tìm thấy lịch khám phù hợp.",
    );
  }

  // ************  HAM CHO BO LOC
  function xuLyDoiBoLoc() {
    dieuChinhNgayLoc(locTu, locDen);
    veBang();
  }

  function xuLyGoTuKhoa() {
    clearTimeout(hen);
    hen = setTimeout(veBang, 250); // chờ 250ms sau khi ngừng gõ
  }

  function xuLyGuiBoLoc(e) {
    e.preventDefault();
    clearTimeout(hen);
    xuLyDoiBoLoc();
  }

  function xuLyDatLai() {
    clearTimeout(hen);
    formLoc.reset(); // ô trống, trạng thái về "Tất cả"
    veBang();
  }

  let soLanTai = 0;
  let dangGui = false;

  //  ************ YEU CAU SEVER
  // HAM TUONG TU HAM LASY LLV
  async function taiLichKham(baoLoi) {
    if (baoLoi === undefined) baoLoi = true;

    const lanNay = ++soLanTai;
    const tt = layLocLichKham(locTrangThai.value);
    const params = new URLSearchParams({
      tu_ngay: locTu.value,
      den_ngay: locDen.value,
      trang_thai: layLocLichKham(locTrangThai.value),
    });

    let ketQua;
    try {
      ketQua = await get(URL_AJAX + "lich-kham&" + params);
    } catch (loi) {
      if (lanNay !== soLanTai) return false;
      danhSachLichKham = [];
      thanBang.replaceChildren(taoDongRong(SO_COT, "Không tải được dữ liệu."));
      if (baoLoi) alert(loi.message);
      return false;
    }
    if (lanNay !== soLanTai) return true; // // da co yeu cau moi hon

    danhSachLichKham = (ketQua.data?.danh_sach || []).map(chuyenLichKham);
    veBang();
    return true;
  }

  // ************  XU LY TRANG THAI: XAC NHAN HOAC HUY BO
// yeu cau cua LK
 async function guiYeuCauLK(hanhDong, duLieu) {
  try {
    await post(URL_AJAX + hanhDong, duLieu);
    return true;
  } catch (loi) {
    console.error("Lỗi " + hanhDong + " lịch khám:", loi);
    alert(loi.message);
    return false;
  }
}

// xac nhan hay huy bo
async function doiTrangThai(ma, hanhDong, cauHoi) {
  if (dangGui) return;

  const lich = danhSachLichKham.find(function (k) {
    return k.ma === ma;
  });
  if (!lich || !confirm(cauHoi(lich))) return;

  dangGui = true;
  try {
    const xong = await guiYeuCauLK(hanhDong, { ma: ma });
    if (xong) await taiLichKham(false); // tai lay trang thai that tu db
  } finally {
    dangGui = false;
  }
}

// o thao tac xac nhan hay huy cho khi tao ra dong lich kham
function xuLyClickBang(e) {
  const nutXacNhan = e.target.closest("[data-xacnhan]");
  const nutHuy = e.target.closest("[data-huy]");

  if (nutXacNhan) {
    doiTrangThai(Number(nutXacNhan.dataset.xacnhan), "xac-nhan", function (l) {
      return "Xác nhận lịch khám của " + l.ten + "?";
    });
  } else if (nutHuy) {
    doiTrangThai(Number(nutHuy.dataset.huy), "huy", function (l) {
      return "Bạn muốn HỦY lịch khám của " + l.ten + "?";
    });
  }
}

  //************  GAN SU KIEN
  function ganSuKien() {
    locTuKhoa.addEventListener("input", xuLyGoTuKhoa);
    [locTu, locDen, locTrangThai].forEach(function (el) {
      el.addEventListener("change", xuLyDoiBoLoc);
    });
    formLoc.addEventListener("submit", xuLyGuiBoLoc);
    datLai.addEventListener("click", xuLyDatLai);
    thanBang.addEventListener("click", xuLyClickBang);
  }

  // ************ KHOI TAO
  function khoiTao() {
    ganSuKien();
    veBang();
  }

  khoiTao();
});
