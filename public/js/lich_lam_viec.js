document.addEventListener("DOMContentLoaded", () => {
  const SO_PHUT_TOI_THIEU = 5; 
  const SO_PHUT_TOI_DA = 480; 

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

//  XU lY GIO BAT DAU, GIO KET THUC, SO PHUT
  const laySoPhut = () => Number(soPhut.value) || Number(soPhut.dataset.cu) || 30;

  const capNhatGioKetThuc = () => {
    const sp = laySoPhut();
    if (!gioBatDau.value || sp < SO_PHUT_TOI_THIEU || sp > SO_PHUT_TOI_DA) return;
    
    const pBatDau = gioSangPhut(gioBatDau.value);
    const pKetThuc = Math.min(pBatDau + sp, 23 * 60 + 59);
    ghi(gioKetThuc, phutSangGio(pKetThuc));
    
    if (pKetThuc - pBatDau !== sp) ghi(soPhut, pKetThuc - pBatDau);
  };

  const capNhatSoPhut = () => {
    if (!gioBatDau.value || !gioKetThuc.value) return;
    const chenh = gioSangPhut(gioKetThuc.value) - gioSangPhut(gioBatDau.value);
    if (chenh >= 1) soPhut.value = chenh;
  };

  const chotGioKetThuc = () => {
    const chenh = gioSangPhut(gioKetThuc.value) - gioSangPhut(gioBatDau.value);
    if (chenh >= SO_PHUT_TOI_THIEU && chenh <= SO_PHUT_TOI_DA) {
      ghi(soPhut, chenh);
    } else {
      soPhut.value = soPhut.dataset.cu;
      capNhatGioKetThuc();
    }
  };

  const dangNhapSoPhut = () => {
    const gt = laySoPhut();
    if (gt >= SO_PHUT_TOI_THIEU && gt <= SO_PHUT_TOI_DA) {
      soPhut.dataset.cu = String(gt);
      capNhatGioKetThuc();
    }
  };

  const chotSoPhut = () => {
    let gt = laySoPhut();
    if (gt <= 0) gt = Number(soPhut.dataset.cu) || 30;
    ghi(soPhut, Math.min(SO_PHUT_TOI_DA, Math.max(SO_PHUT_TOI_THIEU, gt)));
    capNhatGioKetThuc();
  };

  const congDonThoiGian = () => {
    if (gioSangPhut(gioKetThuc.value) + SO_PHUT_TOI_THIEU > 23 * 60 + 59) return false;
    ghi(gioBatDau, gioKetThuc.value);
    capNhatGioKetThuc();
    return true;
  };

// DU KIEU GIA VA GIAO DIEN
  const NHAN_TRANG_THAI = {
    conTrong: { chu: "Còn trống", lop: "n-luc" },
    ngungNhan: { chu: "Ngừng nhận", lop: "n-xam" },
  };

  let lich = [
    { ma: 21, ngay: tuanChua(layNgayThuc()).tu, bd: "08:00", kt: "08:30", trangThai: "conTrong", daCoLuot: false },
    { ma: 22, ngay: tuanChua(layNgayThuc()).tu, bd: "08:30", kt: "09:00", trangThai: "conTrong", daCoLuot: true },
    { ma: 23, ngay: tuanChua(layNgayThuc()).tu, bd: "09:00", kt: "09:30", trangThai: "ngungNhan", daCoLuot: false },
  ];
  let maTiepTheo = 24;

  const taoDongHTML = (k) => {
    const nhan = NHAN_TRANG_THAI[k.trangThai];
    const nutSua = (k.trangThai === "conTrong" && !k.daCoLuot) 
      ? `<button type="button" class="btn cam nho" data-sua="${k.ma}">Chỉnh sửa</button> ` : '';
    const nutXoa = `<button type="button" class="btn do nho" data-xoa="${k.ma}" ${k.daCoLuot ? 'title="Đã có lượt khám"' : ''}>Xóa</button>`;
    
    return `
      <tr>
        <td>${k.ma}</td>
        <td>${hienNgay(k.ngay)}</td>
        <td>${k.bd}–${k.kt}</td>
        <td><span class="nhan ${nhan.lop}">${nhan.chu}</span></td>
        <td>${nutSua}${nutXoa}</td>
      </tr>
    `;
  };

  const veBang = () => {
    const { value: tuNgay } = locTu, { value: denNgay } = locDen, { value: tt } = locTrangThai;
    const danhSach = lich
      .filter((k) => k.ngay >= tuNgay && k.ngay <= denNgay && (!tt || k.trangThai === tt))
      .sort((a, b) => (a.ngay + a.bd).localeCompare(b.ngay + b.bd));

    thanBang.innerHTML = danhSach.length 
      ? danhSach.map(taoDongHTML).join("") 
      : `<tr><td colspan="5">Không có lịch làm việc trong khoảng này.</td></tr>`;
  };

  const xemTuanHienTai = () => {
    const tuan = tuanChua(layNgayThuc());
    ghi(locTu, tuan.tu);
    ghi(locDen, tuan.den);
    locTrangThai.value = "";
  };

 // SU KIEN TUONG TAC
  ghi(ngayKham, layNgayThuc());
  ghi(gioBatDau, layGioThuc());
  ghi(soPhut, Math.min(SO_PHUT_TOI_DA, Math.max(SO_PHUT_TOI_THIEU, laySoPhut() || 30)));
  capNhatGioKetThuc();
  xemTuanHienTai();
  [ngayKham, gioBatDau, gioKetThuc, locTu, locDen].forEach(giuKhongTrong);
  veBang();

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

  gioBatDau.addEventListener("change", capNhatGioKetThuc);
  gioKetThuc.addEventListener("change", capNhatSoPhut);
  gioKetThuc.addEventListener("blur", chotGioKetThuc);

  formTao.addEventListener("submit", (e) => {
    e.preventDefault();
    [ngayKham, gioBatDau, gioKetThuc].forEach(khoiPhuc);
    chotGioKetThuc();
    chotSoPhut();

    const bd = gioBatDau.value, kt = gioKetThuc.value, ngay = ngayKham.value;
    if (gioSangPhut(kt) <= gioSangPhut(bd)) return alert("Giờ kết thúc phải sau giờ bắt đầu.");
    
    const trung = lich.find((k) => k.ngay === ngay && bd < k.kt && kt > k.bd);
    if (trung) return alert(`Trùng với khung giờ ${trung.bd}–${trung.kt} đã có.`);

    lich.push({ ma: maTiepTheo++, ngay, bd, kt, trangThai: "conTrong", daCoLuot: false });

    if (ngay < locTu.value || ngay > locDen.value) {
      const tuan = tuanChua(ngay);
      ghi(locTu, tuan.tu);
      ghi(locDen, tuan.den);
    }
    veBang();

    if (!congDonThoiGian()) alert("Đã tới cuối ngày . Hãy chọn ngày khác.");
  });

  const chinhKhoangNgay = () => {
    if (locDen.value < locTu.value) ghi(locDen, locTu.value);
    veBang();
  };

  [locTu, locDen].forEach(el => el.addEventListener("blur", chinhKhoangNgay));
  [locTu, locDen, locTrangThai].forEach(el => el.addEventListener("change", veBang));

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

  thanBang.addEventListener("click", (e) => {
    const nutXoa = e.target.closest("[data-xoa]");
    if (!nutXoa) return;
    const k = lich.find((item) => item.ma === Number(nutXoa.dataset.xoa));
    
    if (k?.daCoLuot) return alert("Khung giờ đã có LƯỢT KHÁM nên không thể xóa.");
    if (confirm("Xóa khung giờ này?")) {
      lich = lich.filter((item) => item.ma !== k.ma);
      veBang();
    }
  });
});