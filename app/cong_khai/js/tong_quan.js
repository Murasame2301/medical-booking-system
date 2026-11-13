const thongKe = document.getElementById("the-thong-ke");
const bangLichKham = document.getElementById("bang-lich-kham");

// tao thong ke sever tra ve data : {thong_ke :{ trang thai }}
const oThongKe = {
  tong_luot: document.getElementById("so-tong-luot"),
  cho_xac_nhan: document.getElementById("so-cho-xac-nhan"),
  da_kham: document.getElementById("so-da-kham"),
  khung_gio_trong: document.getElementById("so-khung-gio-trong"),
};
const thanBangLich = document.getElementById("than-bang-lich");

// chi cap nhat so, khung giu co dinh; thieu du lieu thi = 0
function taoThongKe(thong_ke = {}) {
  Object.entries(oThongKe).forEach(([khoa, phanTu]) => {
    if (!phanTu) return;
    phanTu.textContent = thong_ke[khoa] ?? 0;
  });
}

// dong thong bao nam trong tbody (giu nguyen tieu de bang)
function hienThongBaoBang(noiDung) {
  if (!thanBangLich) return;
  const dong = taoPhanTu("tr");
  const o = taoPhanTu("td", "", noiDung);
  o.colSpan = 4;
  dong.appendChild(o);
  thanBangLich.replaceChildren(dong);
}

// tao dong lich trong bang
function taoDongLich(lich) {
  const gioBatDau = String(lich.gio_bat_dau || "").slice(0, 5);
  const gioKetThuc = String(lich.gio_ket_thuc || "").slice(0, 5);
  const giokham = gioDauCuoi(gioBatDau, gioKetThuc);
  const cacGiaTri = [giokham, lich.ho_ten_benh_nhan || "", lich.lydo || ""];
  const dong = taoDong(cacGiaTri);
  const oTrangThai = taoPhanTu("td");
  oTrangThai.appendChild(taoNhan(lich.trangthai));
  dong.appendChild(oTrangThai);
  return dong;
}

// hien bang lich
function hienThiLichKham(danhSachLich) {
  if (!thanBangLich) return;
  if (danhSachLich.length === 0) {
    hienThongBaoBang("Hôm nay chưa có lịch đặt khám");
    return;
  }
  thanBangLich.replaceChildren(...danhSachLich.map(taoDongLich));
}

// loi tai du lieu: thong ke ve 0, bang bao loi
function hienLoiLayDuLieu(loi) {
  console.error("Lỗi tải dữ liệu:", loi);
  taoThongKe({});
  hienThongBaoBang("Không thể tải lịch khám");
}

async function layDuLieuTongQuan() {
  try {
    const ngay = layNgayThuc();
    const URL_XU_LY = `index.php?trang=tongquan&ajax=tong-quan&ngay=${encodeURIComponent(ngay)}`;

    const ketQua = await get(URL_XU_LY);
    if (!ketQua.ok) {
      throw new Error(ketQua.msg || "Không thể tải dữ liệu");
    }
    const data = ketQua.data;
    if (!data || !Array.isArray(data.danh_sach_lich)) {
      throw new Error("Dữ liệu server không hợp lệ");
    }

    taoThongKe(data.thong_ke || {});
    hienThiLichKham(data.danh_sach_lich);
  } catch (loi) {
    hienLoiLayDuLieu(loi);
  }
}
window.addEventListener("DOMContentLoaded", layDuLieuTongQuan);