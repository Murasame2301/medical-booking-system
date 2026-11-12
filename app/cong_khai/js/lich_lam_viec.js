document.addEventListener("DOMContentLoaded", function () {
  //  THOI GIAN TOI THIEU VA TOI DA
  const SO_PHUT_TOI_THIEU = 5;
  const SO_PHUT_TOI_DA = 60;
  const CUOI_NGAY = 23 * 60 + 59;

  // `lich` CHI chua du lieu lay tu CSDL (khong tu them / xoa tai cho)
  let lich = [];
  // du lieu tra ve tu sever lay cap nhat trong ham gapSuKienTaoLich
  // lich = (ketQua.data.danh_sach || []).map(function (k) {
  //       return {
  //         ma: Number(k.malichlamviec),
  //         ngay: k.thoigianbatdau.slice(0, 10),
  //         bd: k.thoigianbatdau.slice(11, 16),
  //         kt: k.thoigianketthuc.slice(11, 16),
  //         trangThai: Number(k.trangthai),
  //       };
  //     });
  // lay phan tu tao lich
  const ngayKham = document.getElementById("ngay-kham");
  const gioBatDau = document.getElementById("tg-bat-dau");
  const gioKetThuc = document.getElementById("tg-ket-thuc");
  const soPhut = document.getElementById("so-phut");
  // lay phan tu bo loc
  const locTu = document.getElementById("loc-tu");
  const locDen = document.getElementById("loc-den");
  const locTrangThai = document.getElementById("loc-trang-thai");
  const thanBang = document.getElementById("than-bang");

  // lau gia tri cua bo loc
  const tuNgay = locTu.value;
  const denNgay = locDen.value;
  const trangThaiLoc = locTrangThai.value;
  // VE BANG
  function taoDongKhungGio(k) {
    const dong = taoDong([
      k.ma,
      k.ngay,
      gioDauCuoi(k.bd, k.kt),
      (LLV_NHAN_TRANG_THAI[k.trangThai] || { chu: k.trangThai }).chu,
    ]);

    const oXoa = taoPhanTu("td");
    const nutXoa = taoPhanTu("button", "btn do nho", "Xóa");

    nutXoa.type = "button";
    nutXoa.dataset.xoa = k.ma;
    oXoa.appendChild(nutXoa);
    dong.appendChild(oXoa);

    return dong;
  }

  function taoDongRong(noiDung = "Không có khung giờ phù hợp.") {
    const dong = taoPhanTu("tr");
    const o = taoPhanTu("td", "", noiDung);
    o.colSpan = 5;
    o.style.textAlign = "center";
    dong.appendChild(o);
    return dong;
  }

  function veBang() {
    const danhSachLoc = lich.filter(function (k) {
      const trongKhoangNgay =
        (!tuNgay || k.ngay >= tuNgay) && (!denNgay || k.ngay <= denNgay);
      const dungTrangThai = !trangThaiLoc || k.trangThai === trangThaiLoc;
      return trongKhoangNgay && dungTrangThai;
    });

    if (danhSachLoc.length === 0) {
      thanBang.replaceChildren(taoDongRong());
      return;
    }
    thanBang.replaceChildren(...danhSachLoc.map(taoDongKhungGio));
  }

  // LOC TIM KIEM
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

  // GIO BAT DAU, GIO KET THUC, VA SO PHUT KHI CHON HOAC NHAP VAO (TOI DA 60)
  function laySoPhut() {
    return Number(soPhut.value) || Number(soPhut.dataset.cu) || 30;
  }

  function capNhatGioKetThuc() {
    const sp = laySoPhut();
    if (!gioBatDau.value || sp < SO_PHUT_TOI_THIEU || sp > SO_PHUT_TOI_DA)
      return;

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
    if (gioSangPhut(gioKetThuc.value) + SO_PHUT_TOI_THIEU > CUOI_NGAY)
      return false;
    ghi(gioBatDau, gioKetThuc.value);
    capNhatGioKetThuc();
    return true;
  }

  // GOI SEVER
  // BUOC 1 - HAM GUI YEU CAU TAO LICH len server.
  // Chi gui yeu cau :tra ve true neu server bao tao thanh cong, false neu that bai (da bao loi).
  async function guiYeuCauTaoLich(ngay, bd, soPhut) {
    const urlTao = "index.php?trang=khunggio&ajax=tao";
    try {
      const ketQua = await post(urlTao, {
        ngay,
        gio_bat_dau: bd,
        so_phut: soPhut,
      });
      if (!ketQua || !ketQua.ok) {
        throw new Error((ketQua && ketQua.msg) || "Không thể tạo khung giờ");
      }
      return true;
    } catch (loi) {
      console.error("Lỗi tạo khung giờ:", loi);
      alert(loi.message);
      return false;
    }
  }

  // BUOC 2 - HAM GUI YEU CAU LAY LICH tu server roi ve len giao dien.
  // Lay toan bo khung gio tu CSDL roi ve bang
  // Tra ve true neu tai thanh cong, false neu loi.
  // baoLoi = false: khong alert (de noi goi tu tu bao loi theo ngu canh cua no).
  async function taiKhungGio(baoLoi = true) {
    const urlDanhSach = `index.php?trang=khunggio&ajax=lich-lam-viec&tu_ngay=${tuNgay}&den_ngay=${denNgay}$trang_thai=${trangThaiLoc}`;
    try {
      const ketQua = await get(urlDanhSach);
      if (!ketQua.ok) throw new Error(ketQua.msg || "Không thể tải khung giờ");
      // ket qua tra ve tu sever
      lich = (ketQua.data.danh_sach || []).map(function (k) {
        return {
          ma: Number(k.malichlamviec),
          ngay: k.thoigianbatdau.slice(0, 10),
          bd: k.thoigianbatdau.slice(11, 16),
          kt: k.thoigianketthuc.slice(11, 16),
          trangThai: Number(k.trangthai),
        };
      });
      veBang();
      return true;
    } catch (loi) {
      console.error("Lỗi tải khung giờ:", loi);
      if (baoLoi) alert(loi.message);
      return false;
    }
  }

  // Tao lich va hien thi khung gio theo quy trinh
  // Quy trinh:
  //   1. Kiem tra du lieu o client
  //   2. GUI len server
  //   3. CHI KHI server bao thanh cong -> moi doi bo loc, lay lai tu CSDL, ve bang, tang gio
  // Neu buoc 2 that bai: chi bao loi, giu nguyen bang / bo loc / gio dang nhap.
  function ganSuKienTaoLich() {
    const formTao = document.getElementById("form-tao");
    let dangGui = false; // chan bam tao nhieu lan trong luc dang gui / dang tai lai

    formTao.addEventListener("submit", async function (e) {
      e.preventDefault();
      if (dangGui) return;
      // sever tao lich khong can gio ketthuc , gio ket thuc = bat dau + so phut
      [ngayKham, gioBatDau, gioBatDau].forEach(khoiPhuc);
      chotGioKetThuc();
      chotSoPhut();

      const bd = gioBatDau.value;
      const kt = gioKetThuc.value;
      const soPhut = soPhut.value;
      const ngay = ngayKham.value;

      // 1: kiem tra o client
      if (!ngay || !bd || !kt)
        return alert("Vui lòng chọn đủ ngày, giờ bắt đầu và giờ kết thúc.");
      if (ngay < layNgayThuc())
        return alert("Không thể tạo khung giờ ở ngày đã qua.");
      if (gioSangPhut(kt) <= gioSangPhut(bd))
        return alert("Giờ kết thúc phải sau giờ bắt đầu.");

      // kiem tra trung lap tren du lieu da lay tu CSDL (server cung co kiem tra)
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

      // khoa nut tao tu luc gui cho den khi tai lai xong
      const nutTao = formTao.querySelector('[type="submit"]');
      dangGui = true;
      if (nutTao) nutTao.disabled = true;

      try {
        // 2: Gui len server
        // sever tao lich khong can gio ketthuc , gio ket thuc = bat dau + so phut
        const daTao = await guiYeuCauTaoLich(ngay, bd, soPhut);
        if (!daTao) return; // that bai -> dung o day, khong lam gi them

        // 3: Server bao tao THANH CONG -> moi gui yeu cau LAY lich tu server de hien len giao dien
        // dua bo loc ve noi co khung gio moi de no hien ra
        if (ngay < locTu.value || ngay > locDen.value) {
          const tuan = tuanChua(ngay);
          ghi(locTu, tuan.tu);
          ghi(locDen, tuan.den);
        }
        if (locTrangThai.value && locTrangThai.value !== "con_trong") {
          locTrangThai.value = ""; // khung gio moi la "Còn trống", tranh bi bo loc an mat
        }

        const taiDuoc = await taiKhungGio(false);
        if (!taiDuoc) {
          alert(
            "Đã tạo khung giờ nhưng không tải lại được danh sách từ server. Vui lòng tải lại trang.",
          );
          return;
        }

        // tang gio cho lan tao ke tiep
        if (!congDonThoiGian()) alert("Đã tới cuối ngày. Hãy chọn ngày khác.");
      } finally {
        dangGui = false;
        if (nutTao) nutTao.disabled = false;
      }
    });
  }

  // SU KIEN KHI DUNG BO LOC LICH
  function ganSuKienLoc() {
    const formLoc = document.getElementById("form-loc");
    const datLai = document.getElementById("dat-lai");
    // 1: loc ngay
    [locTu, locDen].forEach(function (el) {
      el.addEventListener("blur", chinhKhoangNgay);
    });
    // 2: loc ngay va co trang thai
    [locTu, locDen, locTrangThai].forEach(function (el) {
      el.addEventListener("change", veBang);
    });
    //gui yeu cau loc
    formLoc.addEventListener("submit", function (e) {
      e.preventDefault();
      [locTu, locDen].forEach(khoiPhuc);
      chinhKhoangNgay();
    });
    // dat lai bo loc
    datLai.addEventListener("click", function (e) {
      e.preventDefault();
      xemTuanHienTai();
      veBang();
    });
  }

  // SU KIEN XOA KHUNG GIO LAM VIEC
  function ganSuKienXoa() {
    const urlXoa = "index.php?trang=khunggio&ajax=xoa"; // doi cho khop index.php cua ban

    thanBang.addEventListener("click", async function (e) {
      const nutXoa = e.target.closest("[data-xoa]");
      if (!nutXoa) return;

      const k = lich.find(function (item) {
        return item.ma === Number(nutXoa.dataset.xoa);
      });
      if (!k) return;

      if (k.daCoLuot)
        return alert("Khung giờ đã có LƯỢT KHÁM nên không thể xóa.");
      if (!confirm("Xóa khung giờ này?")) return;

      // gui yeu cau xoa
      try {
        const ketQua = await post(urlXoa, { ma: k.ma });
        if (!ketQua.ok)
          throw new Error(ketQua.msg || "Không thể xóa khung giờ");
        // Xoa thanh cong -> lay lai tu CSDL
        await taiKhungGio();
      } catch (loi) {
        console.error("Lỗi xóa khung giờ:", loi);
        alert(loi.message);
      }
    });
  }

  // ===== SU KIEN: SO PHUT, GIO =====
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

  // doi gio bat dau thi gio ket thuc tang theo
  gioBatDau.addEventListener("input", capNhatGioKetThuc);
  gioBatDau.addEventListener("change", capNhatGioKetThuc);
  gioKetThuc.addEventListener("change", capNhatSoPhut);
  gioKetThuc.addEventListener("blur", chotGioKetThuc);

  // KHOI TAO
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

  // Chua co du lieu: hien "dang tai", sau do lay tu CSDL
  thanBang.replaceChildren(taoDongRong("Đang tải..."));
  taiKhungGio();
});
