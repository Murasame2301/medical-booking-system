document.addEventListener("DOMContentLoaded", () => {
  const SO_PHUT_TOI_THIEU = 5;
  const SO_PHUT_TOI_DA = 480;
  const CUOI_NGAY = 23 * 60 + 59;

  let lich = [];
  let maTiepTheo = 1;

  const formTao = document.getElementById("form-tao");
  const ngayKham = document.getElementById("ngay-kham");
  const gioBatDau = document.getElementById("tg-bat-dau");
  const gioKetThuc = document.getElementById("tg-ket-thuc");
  const soPhut = document.getElementById("so-phut");

  const formLoc = document.getElementById("form-loc");
  const locTu = document.getElementById("loc-tu");
  const locDen = document.getElementById("loc-den");
  const locTrangThai = document.getElementById("loc-trang-thai");
  const datLai = document.getElementById("dat-lai");
  const thanBang = document.getElementById("than-bang");

  // ===== VE BANG =====
  function taoDongKhungGio(k) {
    const dong = taoDong([
      k.ma,
      k.ngay,
      gioDauCuoi(k.bd, k.kt),
      k.trangThai === "conTrong" ? "Còn trống" : "Ngừng nhận",
    ]);

    const oXoa = taoPhanTu("td");
    const nutXoa = taoPhanTu("button", "btn do nho", "Xóa");
    nutXoa.type = "button";
    nutXoa.dataset.xoa = k.ma;
    oXoa.appendChild(nutXoa);
    dong.appendChild(oXoa);

    return dong;
  }

  function taoDongRong() {
    const dong = taoPhanTu("tr");
    const o = taoPhanTu("td", "", "Không có khung giờ phù hợp.");
    o.colSpan = 5;
    o.style.textAlign = "center";
    dong.appendChild(o);
    return dong;
  }

  function veBang() {
    const tuNgay = locTu.value;
    const denNgay = locDen.value;
    const trangThaiLoc = locTrangThai.value;

    const danhSachLoc = lich.filter((k) => {
      const trongKhoangNgay = (!tuNgay || k.ngay >= tuNgay) && (!denNgay || k.ngay <= denNgay);
      const dungTrangThai = !trangThaiLoc || k.trangThai === trangThaiLoc;
      return trongKhoangNgay && dungTrangThai;
    });

    if (danhSachLoc.length === 0) {
      thanBang.replaceChildren(taoDongRong());
      return;
    }
    thanBang.replaceChildren(...danhSachLoc.map(taoDongKhungGio));
  }

  // ===== BO LOC =====
  function xemTuanHienTai() {
    const tuan = tuanChua(layNgayThuc());
    ghi(locTu, tuan.tu);
    ghi(locDen, tuan.den);
    locTrangThai.value = "";
  }

  function chinhKhoangNgay() {
    if (locDen.value < locTu.value) ghi(locDen, locTu.value);
    veBang();
  }

  // ===== GIO BAT DAU, GIO KET THUC, SO PHUT =====
  function laySoPhut() {
    return Number(soPhut.value) || Number(soPhut.dataset.cu) || 30;
  }

  function capNhatGioKetThuc() {
    const sp = laySoPhut();
    if (!gioBatDau.value || sp < SO_PHUT_TOI_THIEU || sp > SO_PHUT_TOI_DA) return;

    const pBatDau = gioSangPhut(gioBatDau.value);
    const pKetThuc = Math.min(pBatDau + sp, CUOI_NGAY);
    ghi(gioKetThuc, phutSangGio(pKetThuc));
  }

  function capNhatSoPhut() {
    if (!gioBatDau.value || !gioKetThuc.value) return;
    const chenh = gioSangPhut(gioKetThuc.value) - gioSangPhut(gioBatDau.value);
    if (chenh >= 1) soPhut.value = chenh;
  }

  function chotGioKetThuc() {
    const chenh = gioSangPhut(gioKetThuc.value) - gioSangPhut(gioBatDau.value);
    if (chenh >= SO_PHUT_TOI_THIEU && chenh <= SO_PHUT_TOI_DA) {
      ghi(soPhut, chenh);
    } else {
      soPhut.value = soPhut.dataset.cu;
      capNhatGioKetThuc();
    }
  }

  function dangNhapSoPhut() {
    const gt = laySoPhut();
    if (gt >= SO_PHUT_TOI_THIEU && gt <= SO_PHUT_TOI_DA) {
      soPhut.dataset.cu = String(gt);
      capNhatGioKetThuc();
    }
  }

  function chotSoPhut() {
    let gt = laySoPhut();
    if (gt <= 0) gt = Number(soPhut.dataset.cu) || 30;
    ghi(soPhut, Math.min(SO_PHUT_TOI_DA, Math.max(SO_PHUT_TOI_THIEU, gt)));
    capNhatGioKetThuc();
  }

  function congDonThoiGian() {
    if (gioSangPhut(gioKetThuc.value) + SO_PHUT_TOI_THIEU > CUOI_NGAY) return false;
    ghi(gioBatDau, gioKetThuc.value);
    capNhatGioKetThuc();
    return true;
  }

  // ===== KHOI TAO =====
  ghi(ngayKham, layNgayThuc());
  ghi(gioBatDau, layGioThuc());
  ghi(soPhut, Math.min(SO_PHUT_TOI_DA, Math.max(SO_PHUT_TOI_THIEU, laySoPhut() || 30)));
  capNhatGioKetThuc();
  xemTuanHienTai();
  [ngayKham, gioBatDau, gioKetThuc, locTu, locDen].forEach(giuKhongTrong);
  veBang();

  // ===== SU KIEN: SO PHUT =====
  soPhut.addEventListener("input", dangNhapSoPhut);
  soPhut.addEventListener("blur", chotSoPhut);
  soPhut.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      chotSoPhut();
      soPhut.blur();
    } else if (e.key === "ArrowUp" || e.key === "ArrowDown") {
      e.preventDefault();
      const gt = laySoPhut() + (e.key === "ArrowUp" ? 1 : -1);
      ghi(soPhut, Math.min(SO_PHUT_TOI_DA, Math.max(SO_PHUT_TOI_THIEU, gt)));
      capNhatGioKetThuc();
    }
  });

  // doi gio bat dau thi gio ket thuc tang theo
  gioBatDau.addEventListener("input", capNhatGioKetThuc);
  gioBatDau.addEventListener("change", capNhatGioKetThuc);
  gioKetThuc.addEventListener("change", capNhatSoPhut);
  gioKetThuc.addEventListener("blur", chotGioKetThuc);

  // ===== SU KIEN: TAO KHUNG GIO =====
  formTao.addEventListener("submit", (e) => {
    e.preventDefault();
    [ngayKham, gioBatDau, gioKetThuc].forEach(khoiPhuc);
    chotGioKetThuc();
    chotSoPhut();

    const bd = gioBatDau.value;
    const kt = gioKetThuc.value;
    const ngay = ngayKham.value;

    if (ngay < layNgayThuc()) return alert("Không thể tạo khung giờ ở ngày đã qua.");
    if (gioSangPhut(kt) <= gioSangPhut(bd)) return alert("Giờ kết thúc phải sau giờ bắt đầu.");

    const trung = lich.find((k) =>
      k.ngay === ngay &&
      gioSangPhut(bd) < gioSangPhut(k.kt) &&
      gioSangPhut(kt) > gioSangPhut(k.bd)
    );
    if (trung) return alert(`Trùng với khung giờ ${gioDauCuoi(trung.bd, trung.kt)} đã có.`);

    lich.push({ ma: maTiepTheo++, ngay, bd, kt, trangThai: "conTrong", daCoLuot: false });

    if (ngay < locTu.value || ngay > locDen.value) {
      const tuan = tuanChua(ngay);
      ghi(locTu, tuan.tu);
      ghi(locDen, tuan.den);
    }
    veBang();

    if (!congDonThoiGian()) alert("Đã tới cuối ngày. Hãy chọn ngày khác.");
  });

  // ===== SU KIEN: BO LOC =====
  [locTu, locDen].forEach((el) => el.addEventListener("blur", chinhKhoangNgay));
  [locTu, locDen, locTrangThai].forEach((el) => el.addEventListener("change", veBang));

  formLoc.addEventListener("submit", (e) => {
    e.preventDefault();
    [locTu, locDen].forEach(khoiPhuc);
    chinhKhoangNgay();
  });

  datLai.addEventListener("click", (e) => {
    e.preventDefault();
    xemTuanHienTai();
    veBang();
  });

  // ===== SU KIEN: XOA KHUNG GIO =====
  thanBang.addEventListener("click", (e) => {
    const nutXoa = e.target.closest("[data-xoa]");
    if (!nutXoa) return;

    const k = lich.find((item) => item.ma === Number(nutXoa.dataset.xoa));
    if (!k) return;

    if (k.daCoLuot) return alert("Khung giờ đã có LƯỢT KHÁM nên không thể xóa.");
    if (confirm("Xóa khung giờ này?")) {
      lich = lich.filter((item) => item.ma !== k.ma);
      veBang();
    }
  });
});