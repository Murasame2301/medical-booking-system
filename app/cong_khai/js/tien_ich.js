// HANG SO VA NHAN CLASS CSS

// cac ham tien ich ve gio, ngay, tuan, thang, nam, thoi gian
function dinhDangNgay(d) {
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
}

function layNgayThuc(soNgayCongThem = 0) {
  const d = new Date();
  d.setDate(d.getDate() + Number(soNgayCongThem));
  return dinhDangNgay(d);
}

function layGioThuc(soPhutCongThem = 0) {
  const d = new Date();
  d.setMinutes(d.getMinutes() + Number(soPhutCongThem));
  return `${String(d.getHours()).padStart(2, "0")}:${String(d.getMinutes()).padStart(2, "0")}`;
}

function gioSangPhut(gio) {
  const [h, m] = gio.split(":").map(Number);
  return h * 60 + m;
}

function phutSangGio(tong) {
  return `${String(Math.floor(tong / 60)).padStart(2, "0")}:${String(tong % 60).padStart(2, "0")}`;
}

function hienNgay(chuoiNgay) {
  return chuoiNgay.split("-").reverse().join("/");
}

function gioDauCuoi(gioBatDau, gioKetThuc) {
  return `${gioBatDau}-${gioKetThuc}`;
}

function tuanChua(chuoiNgay) {
  const [nam, thang, ngay] = chuoiNgay.split("-").map(Number);
  const d = new Date(nam, thang - 1, ngay);
  d.setDate(d.getDate() - ((d.getDay() + 6) % 7)); // thu 2
  const tu = dinhDangNgay(d);
  d.setDate(d.getDate() + 6); // cong 6 de lay chu nhat
  return { tu, den: dinhDangNgay(d) };
}

// xu ly du lieu nhap vao

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
  input.addEventListener("input", ghiNhoNeuHopLe);
  input.addEventListener("change", ghiNhoNeuHopLe);
  input.addEventListener("blur", () => khoiPhuc(input));
  input.addEventListener("keydown", (e) => {
    if (e.key === "Backspace" || e.key === "Delete") e.preventDefault();
  });
}


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


function taoPhanTu(tag, className = "", noiDung = "") {
  const phanTu = document.createElement(tag);

  if (className) {
    phanTu.className = className;
  }
  phanTu.textContent = noiDung;
  return phanTu;
}

function taoO(tag, noiDung = "") {
  const cell = document.createElement(tag);
  cell.textContent = noiDung;
  return cell;
}

function taoDong(cacGiaTri, tag = "td") {
  const dong = taoPhanTu('tr');
  cacGiaTri.forEach((giaTri) => {
    dong.appendChild(taoO(tag, giaTri));
  });

  return dong;
}

function taoNhan(trangThai) {
    const nhan = taoPhanTu('span', 'nhan');
    nhan.textContent = NHAN_TRANG_THAI[trangThai] || 'Không xác định';

    const lop = LOP_TRANG_THAI[trangThai];
    if (lop) {
        nhan.classList.add(lop);
    }
    return nhan;
}

 const LLV_NHAN_TRANG_THAI = {
    conTrong: { chu: "Còn trống", lop: "n-luc" },
    ngungNhan: { chu: "Ngừng nhận", lop: "n-xam" },
  };
