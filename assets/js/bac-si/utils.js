// ===== HANG SO TRANG THAI LOC =====
const TAT_CA = "tat_ca";

// lich lam viec (khop ENUM trong DB)
const LOC_LLV = Object.freeze({
  TAT_CA: TAT_CA,
  CON_TRONG: "con_trong",
  DA_DAT: "da_dat",
});

// lich kham (khop ENUM trong DB)
const LOC_LICH_KHAM = Object.freeze({
  TAT_CA: TAT_CA,
  CHO_XAC_NHAN: "cho_xac_nhan",
  DA_XAC_NHAN: "da_xac_nhan",
  DA_KHAM: "da_kham",
  DA_HUY: "da_huy",
});

const LOC_LLV_TUY_CHON = Object.freeze([
  { giaTri: LOC_LLV.TAT_CA, chu: "Tất cả" },
  { giaTri: LOC_LLV.CON_TRONG, chu: "Còn trống" },
  { giaTri: LOC_LLV.DA_DAT, chu: "Đã đặt" },
]);

const LOC_LICH_KHAM_TUY_CHON = Object.freeze([
  { giaTri: LOC_LICH_KHAM.TAT_CA, chu: "Tất cả" },
  { giaTri: LOC_LICH_KHAM.CHO_XAC_NHAN, chu: "Chờ xác nhận" },
  { giaTri: LOC_LICH_KHAM.DA_XAC_NHAN, chu: "Đã xác nhận" },
  { giaTri: LOC_LICH_KHAM.DA_KHAM, chu: "Đã khám" },
  { giaTri: LOC_LICH_KHAM.DA_HUY, chu: "Đã hủy" },
]);

// gia tri ENUM trong DB ('nam', 'nu') -> chu hien thi
const NHAN_GIOI_TINH = { nam: "Nam", nu: "Nữ" };
// kiem tra hop le LLV
function hopLeLocLLV(giaTri) {
  return LOC_LLV_TUY_CHON.some(function (o) {
    return o.giaTri === giaTri;
  });
}

// gia tri khong hop le thi ve "tat_ca"
function layLocLLV(giaTri) {
  return hopLeLocLLV(giaTri) ? giaTri : LOC_LLV.TAT_CA;
}

// kiem tra hop le LK
function hopLeLocLichKham(giaTri) {
  return LOC_LICH_KHAM_TUY_CHON.some(function (o) {
    return o.giaTri === giaTri;
  });
}

// gia tri khong hop le thi ve "tat_ca"
function layLocLichKham(giaTri) {
  return hopLeLocLichKham(giaTri) ? giaTri : LOC_LICH_KHAM.TAT_CA;
}

// ===== NHAN TRANG THAI =====
const NHAN_TRANG_THAI_LK = {
  [LOC_LICH_KHAM.CHO_XAC_NHAN]: "Chờ xác nhận",
  [LOC_LICH_KHAM.DA_XAC_NHAN]: "Đã xác nhận",
  [LOC_LICH_KHAM.DA_KHAM]: "Đã khám",
  [LOC_LICH_KHAM.DA_HUY]: "Đã hủy",
};

const LOP_TRANG_THAI_LK = {
  [LOC_LICH_KHAM.CHO_XAC_NHAN]: "n-vang",
  [LOC_LICH_KHAM.DA_XAC_NHAN]: "n-xanh",
  [LOC_LICH_KHAM.DA_KHAM]: "n-luc",
  [LOC_LICH_KHAM.DA_HUY]: "n-do",
};

const LLV_NHAN_TRANG_THAI = {
  [LOC_LLV.CON_TRONG]: { chu: "Còn trống", lop: "n-luc" },
  [LOC_LLV.DA_DAT]: { chu: "Đã đặt", lop: "n-xam" },
};

// ===== CHON PHAN TU =====
function $(id) {
  return document.getElementById(id);
}

// ===== NGAY, GIO, TUAN =====
function pad2(n) {
  return String(n).padStart(2, "0");
}

function dinhDangNgay(d) {
  return (
    d.getFullYear() + "-" + pad2(d.getMonth() + 1) + "-" + pad2(d.getDate())
  );
}

function layNgayThuc(soNgayCongThem) {
  const d = new Date();
  d.setDate(d.getDate() + Number(soNgayCongThem || 0));
  return dinhDangNgay(d);
}

function layGioThuc(soPhutCongThem) {
  const d = new Date(Date.now() + Number(soPhutCongThem || 0) * 60000);
  return pad2(d.getHours()) + ":" + pad2(d.getMinutes());
}

function gioSangPhut(gio) {
  const phan = gio.split(":").map(Number);
  return phan[0] * 60 + phan[1];
}

function phutSangGio(tong) {
  return pad2(Math.floor(tong / 60)) + ":" + pad2(tong % 60);
}

function hienNgay(chuoiNgay) {
  return chuoiNgay.split("-").reverse().join("/");
}

function gioDauCuoi(gioBatDau, gioKetThuc) {
  return gioBatDau + "-" + gioKetThuc;
}

// tra ve thu 2 -> chu nhat cua tuan chua ngay da cho
function tuanChua(chuoiNgay) {
  const phan = chuoiNgay.split("-").map(Number);
  const d = new Date(phan[0], phan[1] - 1, phan[2]);
  d.setDate(d.getDate() - ((d.getDay() + 6) % 7)); // thu 2
  const tu = dinhDangNgay(d);
  d.setDate(d.getDate() + 6); // chu nhat
  return { tu: tu, den: dinhDangNgay(d) };
}

// ===== XU LY O NHAP =====
function ghi(input, giaTri) {
  if (!input) return;
  input.value = giaTri;
  input.dataset.cu = giaTri;
}

function khoiPhuc(input) {
  if (!input) return;
  if (input.value === "" || input.validity.badInput)
    input.value = input.dataset.cu;
}

function giuKhongTrong(input) {
  if (!input) return;
  input.dataset.cu = input.value;

  function ghiNhoNeuHopLe() {
    if (input.value !== "" && !input.validity.badInput)
      input.dataset.cu = input.value;
  }

  ["input", "change"].forEach(function (ev) {
    input.addEventListener(ev, ghiNhoNeuHopLe);
  });
  input.addEventListener("blur", function () {
    khoiPhuc(input);
  });
  input.addEventListener("keydown", function (e) {
    if (e.key === "Backspace" || e.key === "Delete") e.preventDefault();
  });
}

// ===== TAO PHAN TU DOM =====
function taoPhanTu(tag, className, noiDung) {
  const phanTu = document.createElement(tag);
  if (className) phanTu.className = className;
  phanTu.textContent = noiDung === undefined ? "" : noiDung;
  return phanTu;
}

function taoO(tag, noiDung) {
  return taoPhanTu(tag, "", noiDung);
}

function taoDong(cacGiaTri, tag) {
  const dong = taoPhanTu("tr");
  dong.append(
    ...cacGiaTri.map(function (giaTri) {
      return taoO(tag || "td", giaTri);
    }),
  );
  return dong;
}

// o <td> chua cac node/chuoi bat ky
function taoOChua(...nodes) {
  const o = taoPhanTu("td");
  o.append(...nodes);
  return o;
}

function taoOInput(loai, truong, giaTri) {
  const o = document.createElement("input");
  o.type = loai;
  o.value = giaTri;
  o.dataset.truong = truong;
  return o;
}

// nut bam; duLieu -> data-*, vd taoNut("Sửa", "btn nho", { sua: 5 })
function taoNut(chu, className, duLieu) {
  const nut = taoPhanTu("button", className, chu);
  nut.type = "button";
  Object.assign(nut.dataset, duLieu || {});
  return nut;
}

function taoNhanMau(chu, lop) {
  return taoPhanTu(
    "span",
    lop ? "nhan " + lop : "nhan",
    chu || "Không xác định",
  );
}

// nhan mau cho trang thai lich kham
function taoNhanLK(trangThai) {
  return taoNhanMau(
    NHAN_TRANG_THAI_LK[trangThai],
    LOP_TRANG_THAI_LK[trangThai],
  );
}

// nhan mau cho trang thai lich lam viec
function taoNhanLLV(trangThai) {
  const m = LLV_NHAN_TRANG_THAI[trangThai] || {};
  return taoNhanMau(m.chu, m.lop);
}

// ===== BO LOC & BANG DUNG CHUNG =====
function datKhoangLoc(locTu, locDen, khoang) {
  ghi(locTu, khoang.tu);
  ghi(locDen, khoang.den);
}

// dat bo loc ve tuan hien tai (thu 2 -> chu nhat) + trang thai "tat ca"
function datTuanHienTai(locTu, locDen, locTrangThai, giaTriTatCa) {
  datKhoangLoc(locTu, locDen, tuanChua(layNgayThuc()));
  locTrangThai.value = giaTriTatCa === undefined ? TAT_CA : giaTriTatCa;
}

// den ngay khong duoc nho hon tu ngay
function dieuChinhNgayLoc(locTu, locDen) {
  if (locTu.value && locDen.value && locDen.value < locTu.value)
    ghi(locDen, locTu.value);
}

function taoDongRong(soCot, noiDung) {
  const o = taoPhanTu("td", "", noiDung || "Không có dữ liệu phù hợp.");
  o.colSpan = soCot;
  o.style.textAlign = "center";
  o.style.color = "#667085";
  const dong = taoPhanTu("tr");
  dong.append(o);
  return dong;
}

// ve bang: danh sach rong thi hien dong thong bao
function veDanhSach(thanBang, danhSach, taoDongDuLieu, soCot, thongBaoRong) {
  thanBang.replaceChildren(
    ...(danhSach.length
      ? danhSach.map(taoDongDuLieu)
      : [taoDongRong(soCot, thongBaoRong)]),
  );
}

// do option vao <select> (tuy chon)
function napTuyChon(select, tuyChon, giaTriChon) {
  select.replaceChildren();
  tuyChon.forEach(function (o) {
    const opt = taoPhanTu("option", "", o.chu);
    opt.value = o.giaTri;
    opt.selected = o.giaTri === giaTriChon;
    select.append(opt);
  });
}
