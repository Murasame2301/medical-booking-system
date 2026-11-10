const thongKe = document.getElementById('the-thong-ke');
const bangLichKham = document.getElementById('bang-lich-kham');


function taoDauBang() {
  const dauBang = document.createElement('thead');
  const dongTieuDe = document.createElement('tr');
  const cacTieuDe = ['Giờ khám', 'Họ và tên', 'Lý do', 'Trạng thái'];
  cacTieuDe.forEach((tieude) => {
    const cellTieuDe = document.createElement('th');
    cellTieuDe.textContent = tieude;
    dongTieuDe.appendChild(cellTieuDe);
  });
  dauBang.appendChild(dongTieuDe);
  return dauBang;
}

function taoDongLich(lich) {
  const dongLich = document.createElement('tr');
  // gio:phut , khong lay giay
  const gioBatDau = String(lich.gio_bat_dau || '').slice(0, 5);
  const gioKetThuc = String(lich.gio_ket_thuc || '').slice(0, 5);
  // ghep theo dang [gioBatDau-gioKetThuc]
  const gioKham = gioDauCuoi(gioBatDau, gioKetThuc);
  const cacGiaTri = [gioKham,
    lich.ho_ten_benh_nhan || '',
    lich.lydo || '',
    lich.trangthai || ''];
  cacGiaTri.forEach((giatri) => {
    const cellGiaTri = document.createElement('td');
    cellGiaTri.textContent = String(giatri);
    dongLich.appendChild(cellGiaTri);
  });
  return dongLich;
}

function hienLoiLayLich(loi) {
  console.error('Lỗi tải lịch:', loi);
  if (bangLichKham) {
    bangLichKham.textContent = 'Không thể tải lịch khám';
  }
}

function hienThiLichKham(danhSachLich) {
  if (!bangLichKham) return;
  if (danhSachLich.length === 0) {
    bangLichKham.textContent = 'Hôm nay chưa có lịch đặt khám';
    return;
  }

  const bang = document.createElement('table');
  const dauBang = taoDauBang();
  const thanBang = document.createElement('tbody');

  danhSachLich.forEach((lich) => {
    const dongLich = taoDongLich(lich);
    thanBang.appendChild(dongLich);
  });

  bang.appendChild(dauBang);
  bang.appendChild(thanBang);

  bangLichKham.replaceChildren(bang);
}

async function layLichKham() {
  if (!bangLichKham) return;
  try {
    // tien_ich.js
    const ngay = layNgayThuc();
    const URL_XU_LY = `index.php?trang=tongquan&ajax=lich-kham&ngay=${encodeURIComponent(ngay)}`;
    // api.js
    const ketQua = await get(URL_XU_LY);
    const danhSachLich = ketQua.data || [];

    if (!ketQua.ok) {
      throw new Error(ketQua.msg || 'Không thể tải lịch khám.');
    }

    if (!Array.isArray(ketQua.data)) {
      throw new Error('Dữ liệu lịch không hợp lệ');
    }
    hienThiLichKham(ketQua.data);
  } catch (loi) {
      hienLoiLayLich(loi);
  }
}

window.addEventListener('DOMContentLoaded', layLichKham);
