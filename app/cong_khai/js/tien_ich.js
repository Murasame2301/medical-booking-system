
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

function gioDauCuoi(gioBatDau, gioKetThuc){
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
  if (input.value === "" || input.validity.badInput) input.value = input.dataset.cu;
}

function giuKhongTrong(input) {
  if (!input) return;
  input.dataset.cu = input.value;
  const ghiNhoNeuHopLe = () => {
    if (input.value !== "" && !input.validity.badInput) input.dataset.cu = input.value;
  };
  input.addEventListener("input", ghiNhoNeuHopLe);
  input.addEventListener("change", ghiNhoNeuHopLe);
  input.addEventListener("blur", () => khoiPhuc(input));
  input.addEventListener("keydown", (e) => {
    if (e.key === "Backspace" || e.key === "Delete") e.preventDefault();
  });
}

