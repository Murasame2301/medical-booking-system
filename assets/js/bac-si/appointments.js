document.addEventListener("DOMContentLoaded", function () {
  const SO_COT = 9;
  const URL_AJAX = "index.php?page=bac-si/lich-kham&ajax=";

  const formLoc = $("form-loc-kham");
  const locTuKhoa = $("loc-tu-khoa");
  const locTu = $("loc-tu");
  const locDen = $("loc-den");
  const locTrangThai = $("loc-trang-thai");
  const thanBang = $("than-bang-kham");
  const datLai = $("dat-lai");

  //hộp thoại huỷ lịch khám
  const hopHuy = $("hop-huy-kham");
  const formHuy = $("form-huy-kham");
  const huyThongTin = $("huy-thong-tin");
  const huyLyDo = $("huy-ly-do");
  const huyNutDong = $("huy-nut-dong");

  if (!thanBang) return;

  let hen = null; //bỏ hẹn giờ cho ô tìm kiếm
  let danhSachLichKham = [];
  let soLanTai = 0;
  let dangGui = false;

  // ************ TIỆN ÍCH NHỎ
  //Bỏ dấu + chữ thường để tìm kiếm.
  function chuanHoa(s) {
    return String(s || "")
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .replace(/đ/g, "d")
      .replace(/Đ/g, "D")
      .toLowerCase()
      .trim();
  }

  //Một dòng từ server -> đối tượng phía client.
  function chuyenLichKham(k) {
    return {
      ma: Number(k.malichkham),
      trangThai: String(k.trangthai),
      ten: String(k.hoten || ""),
      ngaySinh: k.ngaysinh ? hienNgay(String(k.ngaysinh).slice(0, 10)) : "—",
      gioiTinh: NHAN_GIOI_TINH[k.gioitinh] || "—",
      sdt: String(k.sdt || ""),
      ngay: String(k.thoigianbatdau).slice(0, 10),
      gioBatDau: String(k.thoigianbatdau).slice(11, 16),
      gioKetThuc: String(k.thoigianketthuc).slice(11, 16),
      lyDo: String(k.lydo || ""),
    };
  }

  //Số điện thoại định dạng 4 3 3.
  function dinhDangSdt(sdt) {
    return sdt.replace(/^(\d{4})(\d{3})(\d{3})$/, "$1 $2 $3");
  }

  // ************ HÀM LỌC (phía client: từ khoá + lọc lại cho chắc)
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
      const dungTrangThai = !trangThai || trangThai === TAT_CA || k.trangThai === trangThai;
      //Chỉ tìm theo tên (không dấu, không phân biệt hoa thường) hoặc số điện thoại.
      const dungTuKhoa = !tk ||
        chuanHoa(k.ten).includes(tk) ||
        (tkSo !== "" && k.sdt.includes(tkSo));
      return dungNgay && dungTrangThai && dungTuKhoa;
    });
  }

  // ************ VẼ BẢNG
  function taoThaoTac(k) {
    if (k.trangThai === LOC_LICH_KHAM.CHO_XAC_NHAN) {
      return taoOChua(
        taoNut("Xác nhận", "btn xanh nho", {
          xacnhan: k.ma
        }),
        " ",
        taoNut("Hủy", "btn do nho", {
          huy: k.ma
        }),
      );
    }
    if (k.trangThai === LOC_LICH_KHAM.DA_XAC_NHAN) {
      return taoOChua(taoNut("Hủy", "btn do nho", {
        huy: k.ma
      }));
    }
    return taoO("td", "—");
  }

  function taoDongKham(k) {
    const dong = taoPhanTu("tr");
    dong.append(
      taoOChua(taoNhanLK(k.trangThai)), //FIX: trước đây gọi taoNhan (không tồn tại)
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

  // ************ YÊU CẦU SERVER
  async function taiLichKham(baoLoi) {
    if (baoLoi === undefined) baoLoi = true;

    const lanNay = ++soLanTai;
    //Ô ngày để trống nghĩa là không lọc ngày -> hiển thị toàn bộ lịch khám.
    const params = new URLSearchParams({
      tu_ngay: locTu.value,
      den_ngay: locDen.value,
      trang_thai: layLocLichKham(locTrangThai.value),
    });
    if (locTu.value === "") params.delete("tu_ngay");
    if (locDen.value === "") params.delete("den_ngay");

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
    if (lanNay !== soLanTai) return true; //đã có yêu cầu mới hơn

    danhSachLichKham = ((ketQua.data && ketQua.data.danh_sach) || []).map(chuyenLichKham);
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

  //Hộp thoại nhập lý do huỷ: trả về Promise.
  //- chuỗi (có thể rỗng) khi bấm "Huỷ lịch"; null khi bấm Đóng / Esc.
  function hoiLyDoHuy(lich) {
    return new Promise(function (resolve) {
      huyThongTin.textContent =
        lich.ten +
        " — " +
        hienNgay(lich.ngay) +
        " " +
        gioDauCuoi(lich.gioBatDau, lich.gioKetThuc);
      huyLyDo.value = "";

      function xong(giaTri) {
        formHuy.removeEventListener("submit", khiGui);
        huyNutDong.removeEventListener("click", khiDong);
        hopHuy.removeEventListener("close", khiDong);
        if (hopHuy.open) hopHuy.close();
        resolve(giaTri);
      }

      function khiGui(e) {
        e.preventDefault();
        xong(huyLyDo.value.trim());
      }

      function khiDong() {
        xong(null);
      }

      formHuy.addEventListener("submit", khiGui);
      huyNutDong.addEventListener("click", khiDong);
      hopHuy.addEventListener("close", khiDong); //bấm Esc
      hopHuy.showModal();
      huyLyDo.focus();
    });
  }

  //Xác nhận hoặc huỷ.
  async function doiTrangThai(ma, hanhDong, cauHoi) {
    if (dangGui) return;

    const lich = danhSachLichKham.find(function (k) {
      return k.ma === ma;
    });
    if (!lich) return;

    const duLieu = {
      ma: ma
    };
    if (hanhDong === "huy") {
      const lyDo = await hoiLyDoHuy(lich);
      if (lyDo === null) return;
      duLieu.ly_do_huy = lyDo;
    } else if (!confirm(cauHoi(lich))) {
      return;
    }

    dangGui = true;
    try {
      const xong = await guiYeuCauLK(hanhDong, duLieu);
      if (xong) await taiLichKham(false); //lấy trạng thái thật từ DB
    } finally {
      dangGui = false;
    }
  }

  function xuLyClickBang(e) {
    const nutXacNhan = e.target.closest("[data-xacnhan]");
    const nutHuy = e.target.closest("[data-huy]");

    if (nutXacNhan) {
      doiTrangThai(
        Number(nutXacNhan.dataset.xacnhan),
        "xac-nhan",
        function (l) {
          return "Xác nhận lịch khám của " + l.ten + "?";
        },
      );
    } else if (nutHuy) {
      doiTrangThai(Number(nutHuy.dataset.huy), "huy", function (l) {
        return "Bạn muốn HỦY lịch khám của " + l.ten + "?";
      });
    }
  }

  // ************ HÀM CHO BỘ LỌC
  //Đổi ngày/trạng thái -> phải tải lại từ server (server lọc theo khoảng ngày).
  function xuLyDoiBoLoc() {
    dieuChinhNgayLoc(locTu, locDen);
    taiLichKham();
  }

  //Gõ từ khoá -> chỉ lọc trên dữ liệu đã tải.
  function xuLyGoTuKhoa() {
    clearTimeout(hen);
    hen = setTimeout(veBang, 250);
  }

  function xuLyGuiBoLoc(e) {
    e.preventDefault();
    clearTimeout(hen);
    xuLyDoiBoLoc();
  }

  //Mặc định ngày hôm nay; bỏ trống ngày thì hiển thị toàn bộ.
  function datMacDinh() {
    const homNay = layNgayThuc();
    datKhoangLoc(locTu, locDen, {
      tu: homNay,
      den: homNay
    });
    locTrangThai.value = TAT_CA;
    locTuKhoa.value = "";
  }

  function xuLyDatLai() {
    clearTimeout(hen);
    datMacDinh();
    taiLichKham();
  }

  // ************ GẮN SỰ KIỆN
  function ganSuKien() {
    locTuKhoa.addEventListener("input", xuLyGoTuKhoa);
    [locTu, locDen, locTrangThai].forEach(function (el) {
      el.addEventListener("change", xuLyDoiBoLoc);
    });
    formLoc.addEventListener("submit", xuLyGuiBoLoc);
    datLai.addEventListener("click", xuLyDatLai);
    thanBang.addEventListener("click", xuLyClickBang);
  }

  // ************ KHỞI TẠO
  function khoiTao() {
    datMacDinh();
    //Ô ngày được phép để trống: trống = không lọc ngày (hiện toàn bộ lịch).
    ganSuKien();
    taiLichKham();
  }

  khoiTao();
});