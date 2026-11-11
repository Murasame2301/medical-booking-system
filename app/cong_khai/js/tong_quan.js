const thongKe = document.getElementById("the-thong-ke");
const bangLichKham = document.getElementById("bang-lich-kham");

// tao thong ke sever tra ve data : {thong_ke :{ trang thai }}
function taoThongKe(thong_ke) {
  const danhSach = [
    ["Lượt khám hôm nay", thong_ke.tong_luot],
    ["Chờ xác nhận", thong_ke.cho_xac_nhan],
    ["Đã khám", thong_ke.da_kham],
    ["Khung giờ còn trống", thong_ke.khung_gio_trong],
  ];

  thongKe.replaceChildren();
  danhSach.forEach(([tieuDe, giaTri]) => {
    const o = taoPhanTu("div");
    const ten = taoPhanTu("span", "", tieuDe);
    const so = taoPhanTu("b", "", giaTri ?? 0);
    o.append(ten, so);
    thongKe.appendChild(o);
  });
}

// tao dau bang lich
function taoDauBang() {
  const dauBang = taoPhanTu('thead');
  const cacTieuDe = ["Giờ khám", "Họ và tên", "Lý do", "Trạng thái"];
  const dongTieuDe = taoDong(cacTieuDe, "th");
  dauBang.appendChild(dongTieuDe);
  return dauBang;
}

// tao dong lich trong bang
function taoDongLich(lich) {
  const gioBatDau = String(lich.gio_bat_dau || "").slice(0, 5);
  const gioKetThuc = String(lich.gio_ket_thuc || "").slice(0, 5);
  const giokham = gioDauCuoi(gioBatDau, gioKetThuc);
  // gia tri cac o
  const cacGiaTri = [giokham, lich.ho_ten_benh_nhan || "", lich.lydo || ""];
  // tao 3 gia tri dau
  const dong = taoDong(cacGiaTri);
  // tao o trang thai
  const oTrangThai = taoPhanTu('td');
  oTrangThai.appendChild(taoNhan(lich.trangthai));
  dong.appendChild(oTrangThai);
  return dong;
}

// hien bang lich
function hienThiLichKham(danhSachLich) {
  // chua co lich
  if (danhSachLich.length === 0) {
    bangLichKham.textContent = "Hôm nay chưa có lịch đặt khám";
    return;
  }
  const bang =  taoPhanTu('table')
  const dauBang = taoDauBang();
  const thanBang = taoPhanTu('tbody')

  // tao dong lich
  danhSachLich.forEach((lich) => {
    const dong = taoDongLich(lich);

    thanBang.appendChild(dong);
  });

  
  bang.append(dauBang);
  bang.append(thanBang);
  bangLichKham.replaceChildren(bang);
}

// hien loi cua bang 
function hienLoiLayDuLieu(loi) {
  console.error("Lỗi tải dữ liệu:", loi);
  bangLichKham.textContent = "Không thể tải lịch khám";
}


//  GUI YEU CAU 
async function layDuLieuTongQuan() {
  try {
    const ngay = layNgayThuc();
    const URL_XU_LY = `index.php?trang=tongquan&ajax=tong-quan&ngay=${encodeURIComponent(ngay)}`;

    // yeu cau
    const ketQua = await get(URL_XU_LY);
    if (!ketQua.ok) {
      throw new Error(ketQua.msg || "Không thể tải dữ liệu");
    }
    const data = ketQua.data;
    // check data
    if (!data || !Array.isArray(data.danh_sach_lich)) {
      throw new Error("Dữ liệu server không hợp lệ");
    }

    // hien thong ke
    taoThongKe(data.thong_ke || {});

    // hien bang lich
    hienThiLichKham(data.danh_sach_lich);
  } catch (loi) {
    hienLoiLayDuLieu(loi);
  }
}
// goi khi vua tai trang
window.addEventListener("DOMContentLoaded", layDuLieuTongQuan);
