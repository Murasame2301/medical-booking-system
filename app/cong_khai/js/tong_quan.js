const thongKe = document.getElementById("the-thong-ke");
const bangLichKham = document.getElementById("bang-lich-kham");

// tao thong ke sever tra ve data : {thong_ke :{ trang thai }}
const oThongKe = {
  tong_luot: $("so-tong-luot"),
  cho_xac_nhan: $("so-cho-xac-nhan"),
  da_kham: $("so-da-kham"),
  khung_gio_trong: $("so-khung-gio-trong"),
};
const thanBangLich = $("than-bang-lich");

const SO_COT_LICH = 4;

// chi cap nhat so, khung giu co dinh; thieu du lieu thi = 0
function taoThongKe(thongKe) {
  thongKe = thongKe || {};
  Object.keys(oThongKe).forEach(function (khoa) {
    const phanTu = oThongKe[khoa];
    if (!phanTu) return;
    phanTu.textContent = thongKe[khoa] === undefined ? 0 : thongKe[khoa];
  });
}

// dong thong bao nam trong tbody (giu nguyen tieu de bang)
function hienThongBaoBang(noiDung) {
  if (!thanBangLich) return;
  thanBangLich.replaceChildren(taoDongRong(SO_COT_LICH, noiDung));
}

// tao 1 dong lich: gio, benh nhan, ly do, trang thai
function taoDongLich(lich) {
  const gio = gioDauCuoi(
    String(lich.gio_bat_dau || "").slice(0, 5),
    String(lich.gio_ket_thuc || "").slice(0, 5),
  );
  return taoOChua(
    gio,
  ) && (function () {
    const dong = taoDong([gio, lich.ho_ten_benh_nhan || "", lich.lydo || ""]);
    dong.append(taoOChua(taoNhanLK(lich.trangthai)));
    return dong;
  })();
}

// hien bang lich
function hienThiLichKham(danhSachLich) {
  if (!thanBangLich) return;
  veDanhSach(
    thanBangLich,
    danhSachLich,
    taoDongLich,
    SO_COT_LICH,
    "Hôm nay chưa có lịch đặt khám",
  );
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
    const url =
      "index.php?trang=tongquan&ajax=tong-quan&ngay=" + encodeURIComponent(ngay);

    const ketQua = await get(url);
    if (!ketQua.ok) throw new Error(ketQua.msg || "Không thể tải dữ liệu");

    const data = ketQua.data;
    if (!data || !Array.isArray(data.danh_sach_lich)) {
      throw new Error("Dữ liệu server không hợp lệ");
    }

    taoThongKe(data.thong_ke);
    hienThiLichKham(data.danh_sach_lich);
  } catch (loi) {
    hienLoiLayDuLieu(loi);
  }
}

window.addEventListener("DOMContentLoaded", layDuLieuTongQuan);