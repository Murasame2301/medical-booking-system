document.addEventListener("DOMContentLoaded", function () {
  // ===== HANG SO =====
  const SO_PHUT_TOI_THIEU = 5;
  const SO_PHUT_TOI_DA = 60;
  const CUOI_NGAY = 23 * 60 + 59;
  const TAT_CA = "tat_ca";
  const CON_TRONG = "con_trong";

  // `lich` chi chua du lieu lay tu CSDL
  let lich = [];
  let soLanTai = 0;

  // ===== PHAN TU =====
  const ngayKham = document.getElementById("ngay-kham");
  const gioBatDau = document.getElementById("tg-bat-dau");
  const gioKetThuc = document.getElementById("tg-ket-thuc");
  const soPhut = document.getElementById("so-phut");
  const locTu = document.getElementById("loc-tu");
  const locDen = document.getElementById("loc-den");
  const locTrangThai = document.getElementById("loc-trang-thai");
  const thanBang = document.getElementById("than-bang");

  // ===== VE BANG =====
  function taoDongKhungGio(k) {
  // co luot kham dang hieu luc => coi nhu da dat
  const daDat = k.daCoLuot || k.trangThai === "da_dat";
  const trangThaiHienThi = daDat ? "da_dat" : k.trangThai;

  // 3 o dau la chu thuong
  const dong = taoDong([k.ma, k.ngay, gioDauCuoi(k.bd, k.kt)]);
  if (daDat) dong.classList.add("dong-da-dat"); // lam mo ca dong

  // o trang thai: nhan co mau
  const oTrangThai = taoPhanTu("td");
  oTrangThai.appendChild(taoNhanLLV(trangThaiHienThi));
  dong.appendChild(oTrangThai);

  // o xoa
  const oXoa = taoPhanTu("td");
  const nutXoa = taoPhanTu("button", "btn do nho", "Xóa");
  nutXoa.type = "button";
  nutXoa.dataset.xoa = k.ma;
  if (daDat) {
    nutXoa.disabled = true;
    nutXoa.title = "Khung giờ đã được đặt nên không thể xóa";
  }
  oXoa.appendChild(nutXoa);
  dong.appendChild(oXoa);
  return dong;
}

  function taoDongRong(noiDung = "Không có khung giờ phù hợp.") {
    const dong = taoPhanTu("tr");
    const o = taoPhanTu("td", "", noiDung);
    o.colSpan = 5;
    o.style.textAlign = "center";
    o.style.color = "#667085";
    dong.appendChild(o);
    return dong;
  }

  // server da loc theo ngay va trang thai nen khong loc lai o client
  function veBang() {
    if (lich.length === 0) {
      thanBang.replaceChildren(taoDongRong());
      return;
    }
    thanBang.replaceChildren(...lich.map(taoDongKhungGio));
  }

  // ===== BO LOC =====
  function xemTuanHienTai() {
    const tuan = tuanChua(layNgayThuc());
    ghi(locTu, tuan.tu);
    ghi(locDen, tuan.den);
    locTrangThai.value = TAT_CA;
  }

  // ===== GIO BAT DAU / KET THUC / SO PHUT =====
  function laySoPhut() {
    return Number(soPhut.value) || Number(soPhut.dataset.cu) || 30;
  }

  function capNhatGioKetThuc() {
    const sp = laySoPhut();
    if (!gioBatDau.value || sp < SO_PHUT_TOI_THIEU || sp > SO_PHUT_TOI_DA)
      return;
    const pKetThuc = Math.min(gioSangPhut(gioBatDau.value) + sp, CUOI_NGAY);
    ghi(gioKetThuc, phutSangGio(pKetThuc));
  }

  // nguoi dung doi gio ket thuc -> tinh lai so phut (neu hop le)
  function capNhatSoPhut() {
    if (!gioBatDau.value || !gioKetThuc.value) return;
    const chenh = gioSangPhut(gioKetThuc.value) - gioSangPhut(gioBatDau.value);
    if (chenh >= SO_PHUT_TOI_THIEU && chenh <= SO_PHUT_TOI_DA) {
      ghi(soPhut, chenh);
    }
  }

  // roi o gio ket thuc: neu khong hop le thi tra ve gio theo so phut hien tai
  function chotGioKetThuc() {
    const chenh = gioSangPhut(gioKetThuc.value) - gioSangPhut(gioBatDau.value);
    if (chenh >= SO_PHUT_TOI_THIEU && chenh <= SO_PHUT_TOI_DA) {
      ghi(soPhut, chenh);
    } else {
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

  // sau khi tao xong: gio bat dau moi = gio ket thuc cu
  function congDonThoiGian() {
    if (gioSangPhut(gioKetThuc.value) + SO_PHUT_TOI_THIEU > CUOI_NGAY)
      return false;
    ghi(gioBatDau, gioKetThuc.value);
    capNhatGioKetThuc();
    return true;
  }

  // ===== GOI SERVER =====
  async function guiYeuCauTaoLich(ngay, bd, giaTriSoPhut) {
    try {
      await post("index.php?trang=lichlamviec&ajax=tao", {
        ngay,
        gio_bat_dau: bd,
        so_phut: giaTriSoPhut,
      });
      return true;
    } catch (loi) {
      console.error("Lỗi tạo khung giờ:", loi);
      alert(loi.message);
      return false;
    }
  }

  // lay lich tu server (theo gia tri bo loc HIEN TAI) roi ve bang
  async function taiKhungGio(baoLoi = true) {
    const lanNay = ++soLanTai;
    const params = new URLSearchParams({
      trang: "lichlamviec",
      ajax: "lich-lam-viec",
      tu_ngay: locTu.value,
      den_ngay: locDen.value,
      trang_thai: locTrangThai.value || TAT_CA,
    });

    let ketQua;
    try {
      ketQua = await get(`index.php?${params.toString()}`);
    } catch (loi) {
      if (lanNay !== soLanTai) return false;
      console.error("LỖI GỌI SERVER:", loi);
      lich = [];
      thanBang.replaceChildren(taoDongRong("Không tải được dữ liệu."));
      if (baoLoi) alert(loi.message);
      return false;
    }
    if (lanNay !== soLanTai) return true; // da co yeu cau moi hon

    try {
      lich = (ketQua.data?.danh_sach || []).map(function (k) {
        return {
          ma: Number(k.malichlamviec),
          ngay: String(k.thoigianbatdau).slice(0, 10),
          bd: String(k.thoigianbatdau).slice(11, 16),
          kt: String(k.thoigianketthuc).slice(11, 16),
          trangThai: String(k.trangthai),
          daCoLuot: Boolean(Number(k.da_co_luot)),
        };
      });
      veBang();
      return true;
    } catch (loi) {
      console.error("LỖI VẼ BẢNG:", loi);
      thanBang.replaceChildren(taoDongRong("Lỗi hiển thị dữ liệu."));
      if (baoLoi) alert("Lỗi hiển thị: " + loi.message);
      return false;
    }
  }

  // ===== SU KIEN TAO LICH =====
  function ganSuKienTaoLich() {
    const formTao = document.getElementById("form-tao");
    let dangGui = false;

    formTao.addEventListener("submit", async function (e) {
      e.preventDefault();
      if (dangGui) return;

      [ngayKham, gioBatDau, gioKetThuc].forEach(khoiPhuc);
      chotSoPhut(); // dua so phut ve 5-60 va tinh lai gio ket thuc

      const ngay = ngayKham.value;
      const bd = gioBatDau.value;
      const kt = gioKetThuc.value;
      const giaTriSoPhut = Number(soPhut.value);

      // 1. kiem tra o client
      if (!ngay || !bd || !kt)
        return alert("Vui lòng chọn đủ ngày, giờ bắt đầu và giờ kết thúc.");
      if (ngay < layNgayThuc())
        return alert("Không thể tạo khung giờ ở ngày đã qua.");
      if (gioSangPhut(bd) + giaTriSoPhut > CUOI_NGAY)
        return alert("Khung giờ vượt quá cuối ngày.");

      const trung = lich.find(function (k) {
        return (
          k.ngay === ngay &&
          gioSangPhut(bd) < gioSangPhut(k.kt) &&
          gioSangPhut(kt) > gioSangPhut(k.bd)
        );
      });
      if (trung)
        return alert(
          `Trùng với khung giờ ${gioDauCuoi(trung.bd, trung.kt)} đã có.`,
        );

      const nutTao = formTao.querySelector('[type="submit"]');
      dangGui = true;
      if (nutTao) nutTao.disabled = true;

      try {
        // 2. gui len server
        const daTao = await guiYeuCauTaoLich(ngay, bd, giaTriSoPhut);
        if (!daTao) return;

        // 3. thanh cong -> dua bo loc ve noi co khung gio moi
        if (ngay < locTu.value || ngay > locDen.value) {
          const tuan = tuanChua(ngay);
          ghi(locTu, tuan.tu);
          ghi(locDen, tuan.den);
        }
        if (locTrangThai.value !== TAT_CA && locTrangThai.value !== CON_TRONG) {
          locTrangThai.value = TAT_CA;
        }

        const taiDuoc = await taiKhungGio(false);
        if (!taiDuoc) {
          alert(
            "Đã tạo khung giờ nhưng không tải lại được danh sách. Vui lòng tải lại trang.",
          );
          return;
        }

        if (!congDonThoiGian()) alert("Đã tới cuối ngày. Hãy chọn ngày khác.");
      } finally {
        dangGui = false;
        if (nutTao) nutTao.disabled = false;
      }
    });
  }

  // ===== SU KIEN BO LOC =====
  function ganSuKienLoc() {
    const formLoc = document.getElementById("form-loc");
    const datLai = document.getElementById("dat-lai");

    function dieuChinhNgay() {
      if (locDen.value < locTu.value) ghi(locDen, locTu.value);
    }

    [locTu, locDen, locTrangThai].forEach(function (el) {
      el.addEventListener("change", async function () {
        dieuChinhNgay();
        await taiKhungGio();
      });
    });

    formLoc.addEventListener("submit", async function (e) {
      e.preventDefault();
      [locTu, locDen].forEach(khoiPhuc);
      dieuChinhNgay();
      await taiKhungGio();
    });

    datLai.addEventListener("click", async function (e) {
      e.preventDefault();
      xemTuanHienTai();
      await taiKhungGio();
    });
  }

  // ===== SU KIEN XOA =====
  function ganSuKienXoa() {
    const urlXoa = "index.php?trang=lichlamviec&ajax=xoa";

    thanBang.addEventListener("click", async function (e) {
      const nutXoa = e.target.closest("[data-xoa]");
      if (!nutXoa) return;

      const k = lich.find(function (item) {
        return item.ma === Number(nutXoa.dataset.xoa);
      });
      if (!k) return;

      if (k.trangThai !== "con_trong")
        return alert("Chỉ xóa được khung giờ còn trống.");
      if (k.daCoLuot)
        return alert("Khung giờ đã có lượt khám nên không thể xóa.");
      if (!confirm("Xóa khung giờ này?")) return;

      try {
        await post(urlXoa, { ma: k.ma });
        await taiKhungGio();
      } catch (loi) {
        console.error("Lỗi xóa khung giờ:", loi);
        alert(loi.message);
      }
    });
  }

  // ===== SU KIEN SO PHUT, GIO =====
  soPhut.addEventListener("input", dangNhapSoPhut);
  soPhut.addEventListener("blur", chotSoPhut);
  soPhut.addEventListener("keydown", function (e) {
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

  gioBatDau.addEventListener("input", capNhatGioKetThuc);
  gioBatDau.addEventListener("change", capNhatGioKetThuc);
  gioKetThuc.addEventListener("change", capNhatSoPhut);
  gioKetThuc.addEventListener("blur", chotGioKetThuc);

  // ===== KHOI TAO =====
  ghi(ngayKham, layNgayThuc());
  ghi(gioBatDau, layGioThuc());
  ghi(
    soPhut,
    Math.min(SO_PHUT_TOI_DA, Math.max(SO_PHUT_TOI_THIEU, laySoPhut() || 30)),
  );
  capNhatGioKetThuc();
  xemTuanHienTai();
  [ngayKham, gioBatDau, gioKetThuc, locTu, locDen].forEach(giuKhongTrong);

  ganSuKienTaoLich();
  ganSuKienLoc();
  ganSuKienXoa();

  thanBang.replaceChildren(taoDongRong("Đang tải..."));
  taiKhungGio();
});
