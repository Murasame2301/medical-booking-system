document.addEventListener("DOMContentLoaded", function () {
  // ************ HANG SO
  const SO_COT = 9;
  const URL_AJAX = "index.php?trang=lichkham&ajax=";

  // ************ CHON PHAN TU ($ lay tu tien_ich.js)
  const formLoc = $("form-loc-kham");
  const locTuKhoa = $("loc-tu-khoa");
  const locTu = $("loc-tu");
  const locDen = $("loc-den");
  const locTrangThai = $("loc-trang-thai");
  const thanBang = $("than-bang-kham");
  const datLai = $("dat-lai");

  if (!thanBang) return;

  let hen = null; // bo hen gio cho o tim kiem
  let danhSachLichKham = [];
  let soLanTai = 0;
  let dangGui = false;

  // ************ TIEN TICH NHO
  // bo dau + chu thuong de tim kiem
  function chuanHoa(s) {
    return String(s || "")
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .replace(/đ/g, "d")
      .replace(/Đ/g, "D")
      .toLowerCase()
      .trim();
  }

  // 1 dong tu server -> doi tuong phia client
  function chuyenLichKham(k) {
    return {
      ma: Number(k.malichkham),
      trangThai: String(k.trangthai),
      ten: String(k.hoten || ""),
      ngaySinh: k.ngaysinh ? hienNgay(String(k.ngaysinh).slice(0, 10)) : "—",
      gioiTinh: String(k.gioitinh || ""),
      sdt: String(k.sdt || ""),
      ngay: String(k.thoigianbatdau).slice(0, 10),
      gioBatDau: String(k.thoigianbatdau).slice(11, 16),
      gioKetThuc: String(k.thoigianketthuc).slice(11, 16),
      lyDo: String(k.lydo || ""),
    };
  }

  // so dien thoai dinh dang 4 3 3
  function dinhDangSdt(sdt) {
    return sdt.replace(/^(\d{4})(\d{3})(\d{3})$/, "$1 $2 $3");
  }

  // ************ HAM LOC (phia client: tu khoa + loc lai cho chac)
  function locLichKham(danhSach, dieuKien) {
    const tuKhoa = (dieuKien && dieuKien.tuKhoa) || "";
    const tuNgay = (dieuKien && dieuKien.tuNgay) || "";
    const denNgay = (dieuKien && dieuKien.denNgay) || "";
    const trangThai = (dieuKien && dieuKien.trangThai) || "";

    const tk = chuanHoa(tuKhoa);
    const tkSo = tuKhoa.replace(/\s/g, "");

    return danhSach.filter(function (k) {
      const dungNgay =
        (!tuNgay || k.ngay >= tuNgay) && (!denNgay || k.ngay <= denNgay);
      const dungTrangThai =
        !trangThai || trangThai === TAT_CA || k.trangThai === trangThai;
      // chi tim theo ten (khong dau, khong phan biet hoa thuong) hoac so dien thoai
      const dungTuKhoa =
        !tk ||
        chuanHoa(k.ten).includes(tk) ||
        (tkSo !== "" && k.sdt.includes(tkSo));
      return dungNgay && dungTrangThai && dungTuKhoa;
    });
  }

  // ************ VE BANG
  function taoThaoTac(k) {
    if (k.trangThai === LOC_LICH_KHAM.CHO_XAC_NHAN) {
      return taoOChua(
        taoNut("Xác nhận", "btn xanh nho", { xacnhan: k.ma }),
        " ",
        taoNut("Hủy", "btn do nho", { huy: k.ma }),
      );
    }
    if (k.trangThai === LOC_LICH_KHAM.DA_XAC_NHAN) {
      return taoOChua(taoNut("Hủy", "btn do nho", { huy: k.ma }));
    }
    return taoO("td", "—");
  }

  function taoDongKham(k) {
    const dong = taoPhanTu("tr");
    dong.append(
      taoOChua(taoNhanLK(k.trangThai)), // FIX: truoc day goi taoNhan (khong ton tai)
      taoO("td", k.ma),
      taoO("td", k.ten),
      taoO("td", k.ngaySinh),
      taoO("td", k.gioiTinh),
      taoO("td", dinhDangSdt(k.sdt)),
      taoO("td", hienNgay(k.ngay) + " " + gioDauCuoi(k.gioBatDau, k.gioKetThuc)),
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

  // ************ YEU CAU SERVER
  async function taiLichKham(baoLoi) {
    if (baoLoi === undefined) baoLoi = true;

    const lanNay = ++soLanTai;
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
    if (lanNay !== soLanTai) return true; // da co yeu cau moi hon

    danhSachLichKham = (ketQua.data?.danh_sach || []).map(chuyenLichKham);
    veBang();
    return true;
  }

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

  // hoi ly do huy: tra ve null neu bam Cancel, chuoi (co the rong) neu dong y
  function hoiLyDoHuy(ten) {
    const v = prompt(
      "Hủy lịch khám của " + ten + ".\nNhập lý do hủy (có thể để trống):",
      "",
    );
    return v === null ? null : v.trim();
  }

  // xac nhan hoac huy
  async function doiTrangThai(ma, hanhDong, cauHoi) {
    if (dangGui) return;

    const lich = danhSachLichKham.find(function (k) {
      return k.ma === ma;
    });
    if (!lich) return;

    const duLieu = { ma: ma };
    if (hanhDong === "huy") {
      const lyDo = hoiLyDoHuy(lich.ten);
      if (lyDo === null) return;
      duLieu.ly_do_huy = lyDo;
    } else if (!confirm(cauHoi(lich))) {
      return;
    }

    dangGui = true;
    try {
      const xong = await guiYeuCauLK(hanhDong, duLieu);
      if (xong) await taiLichKham(false); // lay trang thai that tu DB
    } finally {
      dangGui = false;
    }
  }

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

  // ************ HAM CHO BO LOC
  // doi ngay / trang thai -> phai tai lai tu server (server loc theo khoang ngay)
  function xuLyDoiBoLoc() {
    dieuChinhNgayLoc(locTu, locDen);
    taiLichKham();
  }

  // go tu khoa -> chi loc tren du lieu da tai
  function xuLyGoTuKhoa() {
    clearTimeout(hen);
    hen = setTimeout(veBang, 250);
  }

  function xuLyGuiBoLoc(e) {
    e.preventDefault();
    clearTimeout(hen);
    xuLyDoiBoLoc();
  }

  // mac dinh luon la 1 ngay: hom nay
  function datMacDinh() {
    const homNay = layNgayThuc();
    datKhoangLoc(locTu, locDen, { tu: homNay, den: homNay });
    locTrangThai.value = TAT_CA;
    locTuKhoa.value = "";
  }

  function xuLyDatLai() {
    clearTimeout(hen);
    datMacDinh(); // FIX: formLoc.reset() lam o ngay rong -> server bao "Ngay khong hop le"
    taiLichKham();
  }

  // ************ GAN SU KIEN
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
    datMacDinh();
    giuKhongTrong(locTu); // khong cho de trong ngay (server bat buoc co ngay)
    giuKhongTrong(locDen);
    ganSuKien();
    taiLichKham(); // FIX: truoc day chi goi veBang() nen khong bao gio lay du lieu tu server
  }

  khoiTao();
});