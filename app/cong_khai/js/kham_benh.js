document.addEventListener("DOMContentLoaded", () => {
  const URL_AJAX = "index.php?trang=khambenh&ajax=";
  const thanBang = $("than-bang-kham-benh");
  const khungHoSo = $("khung-ho-so");
  if (!thanBang || !khungHoSo) return;

  const form = khungHoSo.querySelector("form.mau");
  const oVanBan = form ? form.querySelectorAll("textarea") : [];
  const btnHoanThanh = form ? form.querySelector("button[type='submit']") : null;
  if (oVanBan.length < 2 || !btnHoanThanh) {
    console.error("kham_benh.js: form hồ sơ cần 2 textarea và 1 nút submit.");
    return;
  }
  const [txtChanDoan, txtLoiDan] = oVanBan;

  let dsCa = [];
  let maDangChon = null;

  // ************ BANG
  function taoDongCa(k) {
    const dong = taoPhanTu("tr");
    dong.dataset.ma = k.ma;
    dong.style.cursor = "pointer";
    if (k.ma === maDangChon) dong.style.background = "#f0f7ff";

  
    const canKham = k.trang_thai === "da_xac_nhan";
    dong.append(
      taoO("td", gioDauCuoi(k.gio_bat_dau, k.gio_ket_thuc)),
      taoOChua(taoPhanTu("b", "", k.ten)),
      taoO("td", k.ly_do),
      taoOChua(taoNhanLK(k.trang_thai)),
      taoOChua(
        canKham
          ? taoNut("Khám", "btn xanh nho")
          : taoNut("Xem kết quả", "btn cam nho"),
      ),
    );
    return dong;
  }

 function veBang() {
  const khung = thanBang.closest(".bang-cuon");
  const viTri = khung ? khung.scrollTop : 0;

  veDanhSach(thanBang, dsCa, taoDongCa, 5, "Hôm nay chưa có ca khám nào.");

  if (khung) khung.scrollTop = viTri;
}

  async function taiDanhSach() {
    try {
      dsCa = (await get(URL_AJAX + "ds")).data;
    } catch (loi) {
      dsCa = [];
      alert(loi.message);
    }
    veBang();
  }

  // ************ HO SO
  // ca đã khám: chỉ xem; ca chưa khám: cho nhập
  function datCheDoForm(chiXem) {
    txtChanDoan.readOnly = txtLoiDan.readOnly = chiXem;
    btnHoanThanh.style.display = chiXem ? "none" : "";
  }

  async function moHoSo(k) {
    const daKham = k.trang_thai === "da_kham";
    maDangChon = k.ma;

    $("hs-tieu-de").textContent =
      (daKham ? "Kết quả khám: " : "Hồ sơ khám: ") + k.ten;
    $("hs-ngay-sinh").textContent = k.ngay_sinh;
    $("hs-gioi-tinh").textContent = NHAN_GIOI_TINH[k.gioi_tinh] || k.gioi_tinh;
    $("hs-sdt").textContent = k.sdt;
    $("hs-ly-do").textContent = k.ly_do;

    txtChanDoan.value = "";
    txtLoiDan.value = "";
    // đặt chế độ form NGAY, trước khi chờ mạng, để không còn trạng thái của ca trước
    datCheDoForm(daKham);
    khungHoSo.style.display = "block";
    veBang();

    if (!daKham) return;
    try {
      const hs = (await get(URL_AJAX + "xem&ma=" + k.ma)).data;
      if (maDangChon !== k.ma) return;
      txtChanDoan.value = hs.chuandoan;
      txtLoiDan.value = hs.loidan;
    } catch (loi) {
      if (maDangChon === k.ma) alert(loi.message);
    }
  }

  function dongHoSo() {
    khungHoSo.style.display = "none";
    maDangChon = null;
    veBang();
  }

  // ************ SU KIEN TUONG TAC
  thanBang.addEventListener("click", (e) => {
    const dong = e.target.closest("tr[data-ma]");
    if (!dong) return;
    const k = dsCa.find((x) => x.ma === Number(dong.dataset.ma));
    if (k) moHoSo(k);
  });

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (!maDangChon) return;
    if (!txtChanDoan.value.trim()) {
      alert("Vui lòng nhập chẩn đoán trước khi hoàn thành!");
      txtChanDoan.focus();
      return;
    }

    const ma = maDangChon;
    btnHoanThanh.disabled = true;
    try {
      const r = await post(URL_AJAX + "hoan_thanh", {
        ma: ma,
        chan_doan: txtChanDoan.value,
        loi_dan: txtLoiDan.value,
      });
      alert(r.msg);
      await taiDanhSach();
      // người dùng đã chuyển sang ca khác hoặc đóng hồ sơ trong lúc chờ thì không mở lại ca cũ
      if (maDangChon !== ma) return;
      const k = dsCa.find((x) => x.ma === ma);
      if (k) moHoSo(k);
      else dongHoSo();
    } catch (loi) {
      alert(loi.message);
    } finally {
      btnHoanThanh.disabled = false;
    }
  });

  const btnDong = $("btn-dong-ho-so");
  if (btnDong) btnDong.addEventListener("click", dongHoSo);

  taiDanhSach();
});