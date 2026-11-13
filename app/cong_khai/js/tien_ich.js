// ===== NGAY, GIO, TUAN =====
const pad2 = (n) => String(n).padStart(2, "0");

function dinhDangNgay(d) {
  return `${d.getFullYear()}-${pad2(d.getMonth() + 1)}-${pad2(d.getDate())}`;
}

function layNgayThuc(soNgayCongThem = 0) {
  const d = new Date();
  d.setDate(d.getDate() + Number(soNgayCongThem));
  return dinhDangNgay(d);
}

function layGioThuc(soPhutCongThem = 0) {
  const d = new Date(Date.now() + Number(soPhutCongThem) * 60000);
  return `${pad2(d.getHours())}:${pad2(d.getMinutes())}`;
}

function gioSangPhut(gio) {
  const [h, m] = gio.split(":").map(Number);
  return h * 60 + m;
}

function phutSangGio(tong) {
  return `${pad2(Math.floor(tong / 60))}:${pad2(tong % 60)}`;
}

function hienNgay(chuoiNgay) {
  return chuoiNgay.split("-").reverse().join("/");
}

function gioDauCuoi(gioBatDau, gioKetThuc) {
  return `${gioBatDau}-${gioKetThuc}`;
}

// tra ve thu 2 -> chu nhat cua tuan chua ngay da cho
function tuanChua(chuoiNgay) {
  const [nam, thang, ngay] = chuoiNgay.split("-").map(Number);
  const d = new Date(nam, thang - 1, ngay);
  d.setDate(d.getDate() - ((d.getDay() + 6) % 7)); // thu 2
  const tu = dinhDangNgay(d);
  d.setDate(d.getDate() + 6); // chu nhat
  return { tu, den: dinhDangNgay(d) };
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
  const ghiNhoNeuHopLe = () => {
    if (input.value !== "" && !input.validity.badInput)
      input.dataset.cu = input.value;
  };
  ["input", "change"].forEach((ev) => input.addEventListener(ev, ghiNhoNeuHopLe));
  input.addEventListener("blur", () => khoiPhuc(input));
  input.addEventListener("keydown", (e) => {
    if (e.key === "Backspace" || e.key === "Delete") e.preventDefault();
  });
}

// ===== NHAN TRANG THAI =====
const NHAN_TRANG_THAI = {
  cho_xac_nhan: "Chờ xác nhận",
  da_xac_nhan: "Đã xác nhận",
  da_kham: "Đã khám",
  da_huy: "Đã hủy",
};

const LOP_TRANG_THAI = {
  cho_xac_nhan: "n-vang",
  da_xac_nhan: "n-xanh",
  da_kham: "n-luc",
  da_huy: "n-do",
};

// trang thai lich lam viec (khoa phai khop gia tri ENUM trong DB)
const LLV_NHAN_TRANG_THAI = {
  con_trong: { chu: "Còn trống", lop: "n-luc" },
  da_dat: { chu: "Đã đặt", lop: "n-xam" },
};

// ===== TAO PHAN TU DOM =====
function taoPhanTu(tag, className = "", noiDung = "") {
  const phanTu = document.createElement(tag);
  if (className) phanTu.className = className;
  phanTu.textContent = noiDung;
  return phanTu;
}

const taoO = (tag, noiDung = "") => taoPhanTu(tag, "", noiDung);

function taoDong(cacGiaTri, tag = "td") {
  const dong = taoPhanTu("tr");
  dong.append(...cacGiaTri.map((giaTri) => taoO(tag, giaTri)));
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
function taoNut(chu, className, duLieu = {}) {
  const nut = taoPhanTu("button", className, chu);
  nut.type = "button";
  Object.assign(nut.dataset, duLieu);
  return nut;
}

function taoNhanMau(chu, lop) {
  return taoPhanTu("span", lop ? `nhan ${lop}` : "nhan", chu || "Không xác định");
}

function taoNhan(trangThai) {
  return taoNhanMau(NHAN_TRANG_THAI[trangThai], LOP_TRANG_THAI[trangThai]);
}

// nhan mau cho trang thai lich lam viec (con_trong / da_dat)
function taoNhanLLV(trangThai) {
  const { chu, lop } = LLV_NHAN_TRANG_THAI[trangThai] || {};
  return taoNhanMau(chu, lop);
}