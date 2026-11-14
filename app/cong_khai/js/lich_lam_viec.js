document.addEventListener("DOMContentLoaded", function () {
  // ************ HANG SO
  const SO_PHUT_TOI_THIEU = 5;
  const SO_PHUT_TOI_DA = 60;
  const CUOI_NGAY = 23 * 60 + 59;
  const SO_COT = 5;
  const URL_AJAX = "index.php?trang=lichlamviec&ajax=";

  // ************  TRANG THAI
  let lich = []; // chi chua du lieu lay tu CSDL
  let soLanTai = 0; // bo qua ket qua cu khi co yeu cau moi hon
  let maDangSua = null; // ma khung gio dang sua (null = khong sua)
  let dangGui = false; // dang gui tao/luu -> chan bam lien tiep

  // ************ VE BANG CHON PHAN TU
  const formTao = $("form-tao");
  const ngayKham = $("ngay-kham");
  const gioBatDau = $("tg-bat-dau");
  const gioKetThuc = $("tg-ket-thuc");
  const soPhut = $("so-phut");

  const formLoc = $("form-loc");
  const datLai = $("dat-lai");
  const locTu = $("loc-tu");
  const locDen = $("loc-den");
  const locTrangThai = $("loc-trang-thai");

  const thanBang = $("than-bang");

  // ************ TIEN ICH NHO
  function chenhPhut(bd, kt) {
    return gioSangPhut(kt) - gioSangPhut(bd);
  }

  function coTheSua(k) {
    return k.trangThai === LOC_LLV.CON_TRONG && !k.daCoLuot;
  }

  // chuyen 1 dong tu server sang dang dung o client
  function chuyenKhungGio(k) {
    return {
      ma: Number(k.malichlamviec),
      ngay: String(k.thoigianbatdau).slice(0, 10),
      bd: String(k.thoigianbatdau).slice(11, 16),
      kt: String(k.thoigianketthuc).slice(11, 16),
      trangThai: String(k.trangthai),
      daCoLuot: Boolean(Number(k.da_co_luot)),
    };
  }

  // chan gui trung + khoa nut trong luc chay tac vu
  async function chayMotLan(nut, tacVu) {
    if (dangGui) return;
    dangGui = true;
    if (nut) nut.disabled = true;
    try {
      return await tacVu();
    } finally {
      dangGui = false;
      if (nut) nut.disabled = false;
    }
  }

  // ************ VE BANG
  // dong o che do sua: o hien gia tri doi thanh o nhap
  function taoDongSua(k) {
    const dong = taoDong([k.ma]); // o ma: giu nguyen
    dong.classList.add("dong-dang-sua");

    const inNgay = taoOInput("date", "ngay", k.ngay);
    inNgay.min = layNgayThuc();

    dong.append(
      taoOChua(inNgay),
      taoOChua(
        taoOInput("time", "bd", k.bd),
        " - ",
        taoOInput("time", "kt", k.kt),
      ),
      taoOChua(taoNhanLLV(k.trangThai)),
      taoOChua(
        taoNut("Lưu", "btn nho xanh", { luu: k.ma }),
        " ",
        taoNut("Hủy", "btn nho xam", { huy: k.ma }),
      ),
    );
    return dong;
  }

  // dong o che do xem
  function taoDongKhungGio(k) {
    // co luot kham dang hieu luc => coi nhu da dat
    const daDat = k.daCoLuot || k.trangThai === LOC_LLV.DA_DAT;

    if (k.ma === maDangSua && !daDat) return taoDongSua(k);

    const dong = taoDong([k.ma, k.ngay, gioDauCuoi(k.bd, k.kt)]);
    dong.classList.toggle("dong-da-dat", daDat);

    const nutXoa = taoNut("Xóa", "btn do nho", { xoa: k.ma });
    if (daDat) {
      nutXoa.disabled = true;
      nutXoa.title = "Khung giờ đã được đặt nên không thể xóa";
    }

    const thaoTac = [];
    if (coTheSua(k) && !daDat)
      thaoTac.push(taoNut("Sửa", "btn nho cam", { sua: k.ma }), " ");
    thaoTac.push(nutXoa);

    dong.append(
      taoOChua(taoNhanLLV(daDat ? LOC_LLV.DA_DAT : k.trangThai)),
      taoOChua(...thaoTac),
    );
    return dong;
  }

  // server da loc theo ngay va trang thai nen khong loc lai o client
  function veBang() {
    veDanhSach(
      thanBang,
      lich,
      taoDongKhungGio,
      SO_COT,
      "Không có khung giờ phù hợp.",
    );
  }

  //************ TINH GIO KHI TAO LICH
  function laySoPhut() {
    return Number(soPhut.value) || Number(soPhut.dataset.cu) || 30;
  }

  function hopLeSoPhut(gt) {
    return gt >= SO_PHUT_TOI_THIEU && gt <= SO_PHUT_TOI_DA;
  }

  function kepSoPhut(gt) {
    return Math.min(SO_PHUT_TOI_DA, Math.max(SO_PHUT_TOI_THIEU, gt));
  }

  // gio ket thuc = gio bat dau + so phut
  function capNhatGioKetThuc() {
    const sp = laySoPhut();
    if (!gioBatDau.value || !hopLeSoPhut(sp)) return;
    ghi(
      gioKetThuc,
      phutSangGio(Math.min(gioSangPhut(gioBatDau.value) + sp, CUOI_NGAY)),
    );
  }

  // doi gio ket thuc -> tinh lai so phut; tra ve true neu hop le
  function dongBoSoPhutTuGio() {
    if (!gioBatDau.value || !gioKetThuc.value) return false;
    const chenh = chenhPhut(gioBatDau.value, gioKetThuc.value);
    if (!hopLeSoPhut(chenh)) return false;
    ghi(soPhut, chenh);
    return true;
  }

  // dua so phut ve 5-60 va tinh lai gio ket thuc
  function chotSoPhut() {
    ghi(soPhut, kepSoPhut(laySoPhut()));
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

  // BO LOC
  function xemTuanHienTai() {
    datTuanHienTai(locTu, locDen, locTrangThai);
  }

  // dua bo loc ve noi co khung gio vua tao/sua
  function dieuChinhLocTheoNgay(ngay) {
    if (ngay < locTu.value || ngay > locDen.value)
      datKhoangLoc(locTu, locDen, tuanChua(ngay));
    // khung gio vua tao luon con trong -> khong hien khi dang loc "da dat"
    if (
      locTrangThai.value !== LOC_LLV.TAT_CA &&
      locTrangThai.value !== LOC_LLV.CON_TRONG
    )
      locTrangThai.value = LOC_LLV.TAT_CA;
  }

  //************ KIEM TRA DU LIEU TRUOC KHI GUI
  function timKhungTrung(ngay, bd, kt, maBoQua) {
    const b = gioSangPhut(bd);
    const e = gioSangPhut(kt);
    return lich.find(function (k) {
      return (
        k.ma !== maBoQua &&
        k.ngay === ngay &&
        b < gioSangPhut(k.kt) &&
        e > gioSangPhut(k.bd)
      );
    });
  }

  // tra ve noi dung loi, hoac "" neu hop le
  function kiemTraKhungGio(ngay, bd, kt, maBoQua) {
    if (maBoQua === undefined) maBoQua = null;

    if (!ngay || !bd || !kt)
      return "Vui lòng chọn đủ ngày, giờ bắt đầu và giờ kết thúc.";
    if (ngay < layNgayThuc()) return "Không thể đặt khung giờ ở ngày đã qua.";
    if (!hopLeSoPhut(chenhPhut(bd, kt)))
      return (
        "Khung giờ phải dài từ " +
        SO_PHUT_TOI_THIEU +
        " đến " +
        SO_PHUT_TOI_DA +
        " phút và giờ kết thúc phải sau giờ bắt đầu."
      );

    const trung = timKhungTrung(ngay, bd, kt, maBoQua);
    return trung
      ? "Trùng với khung giờ " + gioDauCuoi(trung.bd, trung.kt) + " đã có."
      : "";
  }

  // ************ GOI SERVER
  async function taiKhungGio(baoLoi) {
    if (baoLoi === undefined) baoLoi = true;

    const lanNay = ++soLanTai;
    const params = new URLSearchParams({
      tu_ngay: locTu.value,
      den_ngay: locDen.value,
      trang_thai: layLocLLV(locTrangThai.value),
    });

    let ketQua;
    try {
      ketQua = await get(URL_AJAX + "lich-lam-viec&" + params);
    } catch (loi) {
      if (lanNay !== soLanTai) return false;
      lich = [];
      maDangSua = null;
      thanBang.replaceChildren(taoDongRong(SO_COT, "Không tải được dữ liệu."));
      if (baoLoi) alert(loi.message);
      return false;
    }
    if (lanNay !== soLanTai) return true; // da co yeu cau moi hon

    lich = (ketQua.data?.danh_sach || []).map(chuyenKhungGio);

    // dong dang sua khong con trong danh sach -> thoat che do sua
    if (
      !lich.some(function (k) {
        return k.ma === maDangSua;
      })
    )
      maDangSua = null;

    veBang();
    if (baoLoi && lich.length === 0) alert("Không tìm thấy lịch.");
    return true;
  }

  // gui yeu cau tao / sua / xoa; thanh cong tra ve true
  async function guiYeuCauLLV(hanhDong, duLieu) {
    try {
      await post(URL_AJAX + hanhDong, duLieu);
      return true;
    } catch (loi) {
      console.error("Lỗi " + hanhDong + " khung giờ:", loi);
      alert(loi.message);
      return false;
    }
  }

  // sau khi tao/sua thanh cong: dua bo loc ve ngay do roi tai lai bang
  async function taiLaiSauKhiGhi(ngay, thongBaoLoi) {
    dieuChinhLocTheoNgay(ngay);
    if (await taiKhungGio(false)) return true;
    alert(thongBaoLoi);
    return false;
  }

  //************ SU KIEN LOC
  async function xuLyDoiBoLoc() {
    dieuChinhNgayLoc(locTu, locDen);
    await taiKhungGio();
  }

  async function xuLyGuiBoLoc(e) {
    e.preventDefault();
    [locTu, locDen].forEach(khoiPhuc);
    await xuLyDoiBoLoc();
  }

  async function xuLyDatLai(e) {
    e.preventDefault();
    xemTuanHienTai();
    await taiKhungGio();
  }

  //************  SU KIEN TAO LICH
  async function xuLyTaoLich(e) {
    e.preventDefault();
    if (dangGui) return;

    [ngayKham, gioBatDau, gioKetThuc].forEach(khoiPhuc);
    chotSoPhut();

    const ngay = ngayKham.value;
    const bd = gioBatDau.value;
    const kt = gioKetThuc.value;
    const giaTriSoPhut = Number(soPhut.value);

    // 1: kiem tra o client
    if (bd && gioSangPhut(bd) + giaTriSoPhut > CUOI_NGAY)
      return alert("Khung giờ vượt quá cuối ngày.");
    const loi = kiemTraKhungGio(ngay, bd, kt);
    if (loi) return alert(loi);

    await chayMotLan(
      formTao.querySelector('[type="submit"]'),
      async function () {
        // 2: gui len server
        const daTao = await guiYeuCauLLV("tao", {
          ngay: ngay,
          gio_bat_dau: bd,
          so_phut: giaTriSoPhut,
        });
        if (!daTao) return;

        // 3: tai lai bang, roi cong don gio cho khung ke tiep
        const taiDuoc = await taiLaiSauKhiGhi(
          ngay,
          "Đã tạo khung giờ nhưng không tải lại được danh sách. Vui lòng tải lại trang.",
        );
        if (taiDuoc && !congDonThoiGian())
          alert("Đã tới cuối ngày. Hãy chọn ngày khác.");
      },
    );
  }

  // ************ SU KIEN BANG: click Sua / Luu / Huy / Xoa, phim Enter / Esc
  function timTheoMa(ma) {
    return lich.find(function (item) {
      return item.ma === Number(ma);
    });
  }

  function xuLyBamSua(nutSua) {
    const k = timTheoMa(nutSua.dataset.sua);
    if (!k) return;
    if (!coTheSua(k)) return alert("Chỉ sửa được khung giờ còn trống.");

    maDangSua = k.ma;
    veBang();
  }

  function thoatCheDoSua() {
    maDangSua = null;
    veBang();
  }

  // luu: kiem tra, gui server, tai lai bang
  async function luuDong(dong) {
    if (dangGui || maDangSua === null) return;

    const ma = maDangSua;
    const ngay = dong.querySelector('[data-truong="ngay"]').value;
    const bd = dong.querySelector('[data-truong="bd"]').value;
    const kt = dong.querySelector('[data-truong="kt"]').value;

    const loi = kiemTraKhungGio(ngay, bd, kt, ma);
    if (loi) return alert(loi);

    await chayMotLan(dong.querySelector("[data-luu]"), async function () {
      const daLuu = await guiYeuCauLLV("sua", {
        ma: ma,
        ngay: ngay,
        gio_bat_dau: bd,
        so_phut: chenhPhut(bd, kt),
      });
      if (!daLuu) return; // giu nguyen dong o nhap de nguoi dung sua tiep

      maDangSua = null;
      await taiLaiSauKhiGhi(
        ngay,
        "Đã lưu khung giờ nhưng không tải lại được danh sách. Vui lòng tải lại trang.",
      );
    });
  }

  async function xuLyBamXoa(nutXoa) {
    const k = timTheoMa(nutXoa.dataset.xoa);
    if (!k) return;

    if (k.trangThai !== LOC_LLV.CON_TRONG)
      return alert("Chỉ xóa được khung giờ còn trống.");
    if (k.daCoLuot)
      return alert("Khung giờ đã có lượt khám nên không thể xóa.");
    if (!confirm("Xóa khung giờ này?")) return;

    if (await guiYeuCauLLV("xoa", { ma: k.ma })) await taiKhungGio();
  }

  // dieu phoi click tren bang
  function xuLyClickBang(e) {
    const nut = e.target.closest(
      "[data-sua], [data-luu], [data-huy], [data-xoa]",
    );
    if (!nut) return;

    if ("sua" in nut.dataset) return xuLyBamSua(nut);
    if ("luu" in nut.dataset) return luuDong(nut.closest("tr"));
    if ("huy" in nut.dataset) return thoatCheDoSua();
    if ("xoa" in nut.dataset) return xuLyBamXoa(nut);
  }

  // Enter = luu (chi khi dang go o o nhap), Esc = huy
  function xuLyPhimBang(e) {
    const dong = e.target.closest("tr.dong-dang-sua");
    if (!dong) return;

    if (e.key === "Enter" && e.target.matches("input")) {
      e.preventDefault();
      luuDong(dong);
    } else if (e.key === "Escape") {
      thoatCheDoSua();
    }
  }

  //************  SU KIEN THOI GIAN TRONG FORM
  function xuLyNhapSoPhut() {
    const gt = laySoPhut();
    if (!hopLeSoPhut(gt)) return;
    soPhut.dataset.cu = String(gt);
    capNhatGioKetThuc();
  }

  function xuLyPhimSoPhut(e) {
    if (e.key === "Enter") {
      e.preventDefault();
      chotSoPhut();
      soPhut.blur();
    } else if (e.key === "ArrowUp" || e.key === "ArrowDown") {
      e.preventDefault();
      ghi(soPhut, kepSoPhut(laySoPhut() + (e.key === "ArrowUp" ? 1 : -1)));
      capNhatGioKetThuc();
    }
  }

  // roi o gio ket thuc: neu khong hop le thi tra ve gio theo so phut hien tai
  function xuLyRoiGioKetThuc() {
    if (!dongBoSoPhutTuGio()) capNhatGioKetThuc();
  }

  // ************ GAN SU KIEN
  function ganSuKien() {
    [locTu, locDen, locTrangThai].forEach(function (el) {
      el.addEventListener("change", xuLyDoiBoLoc);
    });
    formLoc.addEventListener("submit", xuLyGuiBoLoc);
    datLai.addEventListener("click", xuLyDatLai);

    formTao.addEventListener("submit", xuLyTaoLich);

    thanBang.addEventListener("click", xuLyClickBang);
    thanBang.addEventListener("keydown", xuLyPhimBang);

    soPhut.addEventListener("input", xuLyNhapSoPhut);
    soPhut.addEventListener("blur", chotSoPhut);
    soPhut.addEventListener("keydown", xuLyPhimSoPhut);
    ["input", "change"].forEach(function (ev) {
      gioBatDau.addEventListener(ev, capNhatGioKetThuc);
    });
    gioKetThuc.addEventListener("change", dongBoSoPhutTuGio);
    gioKetThuc.addEventListener("blur", xuLyRoiGioKetThuc);
  }

  // ************ KHOI TAO
  function khoiTao() {
    ghi(ngayKham, layNgayThuc());
    ghi(gioBatDau, layGioThuc());
    ghi(soPhut, kepSoPhut(laySoPhut()));
    capNhatGioKetThuc();
    xemTuanHienTai();
    [ngayKham, gioBatDau, gioKetThuc, locTu, locDen].forEach(giuKhongTrong);

    ganSuKien();

    thanBang.replaceChildren(taoDongRong(SO_COT, "Đang tải..."));
    taiKhungGio();
  }

  khoiTao();
});
