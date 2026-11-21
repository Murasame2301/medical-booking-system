document.addEventListener("DOMContentLoaded", function () {
  const URL_AJAX = "index.php?page=bac-si/ca-nhan&ajax=";
  const ANH_TOI_DA = 2 * 1024 * 1024; //khớp ANH_TOI_DA_BYTE ở PHP
  const ANH_HOP_LE = ["image/jpeg", "image/png", "image/webp"];
  const MAT_KHAU_TOI_THIEU = 8; //khớp MAT_KHAU_TOI_THIEU ở PHP
  const GIOI_THIEU_TOI_DA = 1000; //khớp GIOI_THIEU_TOI_DA ở PHP

  //Tên trường trong form -> khoá trong dữ liệu server trả về.
  const TRUONG_FORM = {
    ten: "hoten",
    ngay_sinh: "ngaysinh",
    gioi_tinh: "gioitinh",
    trinh_do: "trinhdo",
    gioi_thieu: "gioithieu", //tuỳ chọn: chỉ dùng nếu form có ô này
    sdt: "sdt",
    email: "email",
    dia_chi: "diachi",
  };
  const TRUONG_TUY_CHON = ["gioi_thieu", "email"];

  // ************ CHỌN PHẦN TỬ
  const formThongTin = $("form-thong-tin");
  const formMatKhau = $("form-mat-khau");
  const btnDoiAnh = $("btn-doi-anh");
  const inputAnh = $("inp-anh");
  const btnXoaAnh = $("btn-xoa-anh");

  // ************ TRẠNG THÁI
  let anhDaLuu = null; //đường dẫn ảnh đang lưu trên máy chủ (null = chưa có)
  let dangGuiThongTin = false;
  let dangGuiMatKhau = false;

  // ************ THÔNG BÁO (thay alert) - kiểu dáng nằm trong ca_nhan.css
  function thongBao(noiDung, loi) {
    let t = $("toast-ca-nhan");
    if (!t) {
      t = document.createElement("div");
      t.id = "toast-ca-nhan";
      t.setAttribute("role", "status");
      t.setAttribute("aria-live", "polite");
      document.body.append(t);
    }
    t.textContent = noiDung;
    t.className = (loi ? "loi" : "ok") + " hien";
    clearTimeout(thongBao.h);
    thongBao.h = setTimeout(
      () => t.classList.remove("hien"),
      loi ? 4500 : 3000,
    );
  }

  // ************ TAB (có aria + điều hướng bằng phím mũi tên)
  function khoiTaoChuyenTab() {
    const tabButtons = [...document.querySelectorAll(".tab-nut")];
    const tabContents = [...document.querySelectorAll(".tab-noi-dung")];
    if (!tabButtons.length) return;

    function chon(index) {
      tabButtons.forEach(function (b, i) {
        b.classList.toggle("on", i === index);
        b.setAttribute("aria-selected", String(i === index));
        b.tabIndex = i === index ? 0 : -1;
      });
      tabContents.forEach(function (c, i) {
        c.classList.toggle("on", i === index);
        c.hidden = i !== index;
      });
    }

    tabButtons.forEach(function (btn, i) {
      btn.addEventListener("click", () => chon(i));
      btn.addEventListener("keydown", function (e) {
        if (e.key !== "ArrowRight" && e.key !== "ArrowLeft") return;
        e.preventDefault();
        const buoc = e.key === "ArrowRight" ? 1 : -1;
        const n = (i + buoc + tabButtons.length) % tabButtons.length;
        chon(n);
        tabButtons[n].focus();
      });
    });

    const dau = tabButtons.findIndex((b) => b.classList.contains("on"));
    chon(dau >= 0 ? dau : 0);
  }

  // ************ ẢNH ĐẠI DIỆN
  //PHP (html_avatar) in sẵn: data-chu = chữ viết tắt, data-anh = đường dẫn ảnh (nếu có).
  function cacKhungAvatar() {
    return document.querySelectorAll("[data-chu]");
  }

  function veAnhDaiDien(duongDan) {
    cacKhungAvatar().forEach(function (khung) {
      if (duongDan) {
        khung.style.backgroundImage = 'url("' + encodeURI(duongDan) + '")';
        khung.style.backgroundSize = "cover";
        khung.style.backgroundPosition = "center";
        khung.textContent = "";
        khung.classList.add("co-anh");
      } else {
        khung.style.backgroundImage = "";
        khung.textContent = khung.dataset.chu || "";
        khung.classList.remove("co-anh");
      }
    });
  }

  //Tải trước ảnh từ máy chủ rồi mới thay -> không bị nhảy/trắng khung.
  function taiTruocAnh(url) {
    return new Promise(function (ok, hong) {
      const img = new Image();
      img.onload = ok;
      img.onerror = () => hong(new Error("Không tải được ảnh vừa lưu."));
      img.src = url;
    });
  }

  function chuVietTat(hoTen) {
    const tu = String(hoTen).trim().split(/\s+/).filter(Boolean);
    if (!tu.length) return "?";
    return Array.from(tu[tu.length - 1])[0].toUpperCase();
  }

  function capNhatChuVietTat(hoTen) {
    const chu = chuVietTat(hoTen);
    cacKhungAvatar().forEach(function (khung) {
      khung.dataset.chu = chu;
      if (!khung.style.backgroundImage) khung.textContent = chu;
    });
  }

  //Cập nhật tên ở header/sidebar: các phần tử có thuộc tính data-ten.
  function capNhatTenHienThi(hoTen) {
    document.querySelectorAll("[data-ten]").forEach(function (el) {
      el.textContent = hoTen;
    });
  }

  function khoaNutAnh(khoa) {
    if (btnDoiAnh) btnDoiAnh.disabled = khoa;
    if (btnXoaAnh) btnXoaAnh.disabled = khoa;
    cacKhungAvatar().forEach((k) => k.classList.toggle("dang-tai", khoa));
  }

  async function xuLyChonAnh() {
    const file = inputAnh.files[0];
    if (!file) return;

    if (!ANH_HOP_LE.includes(file.type)) {
      thongBao("Chỉ chấp nhận ảnh JPG, PNG hoặc WEBP.", true);
      inputAnh.value = "";
      return;
    }
    if (file.size > ANH_TOI_DA) {
      thongBao("Ảnh tối đa " + ANH_TOI_DA / 1024 / 1024 + "MB.", true);
      inputAnh.value = "";
      return;
    }

    const urlTam = URL.createObjectURL(file);
    veAnhDaiDien(urlTam); //xem trước ngay

    const fd = new FormData();
    fd.append("anh", file);
    khoaNutAnh(true);
    try {
      const r = await post(URL_AJAX + "doi_anh", fd);
      const anhMoi = r.data.anh + "?v=" + Date.now(); //tránh cache ảnh cũ
      try {
        await taiTruocAnh(anhMoi);
      } catch (_) {
        //Đã lưu thành công, vẫn dùng đường dẫn mới.
      }
      anhDaLuu = anhMoi;
      veAnhDaiDien(anhDaLuu);
      cacKhungAvatar().forEach((k) => (k.dataset.anh = anhDaLuu));
      thongBao(r.msg);
    } catch (loi) {
      veAnhDaiDien(anhDaLuu); //thất bại -> trả về ảnh trước đó
      thongBao(loi.message, true);
    } finally {
      URL.revokeObjectURL(urlTam);
      khoaNutAnh(false);
      inputAnh.value = "";
    }
  }

  async function xuLyXoaAnh() {
    if (!anhDaLuu) {
      thongBao("Bạn chưa có ảnh đại diện.", true);
      return;
    }
    if (!confirm("Xóa ảnh đại diện hiện tại?")) return;

    khoaNutAnh(true);
    try {
      const r = await post(URL_AJAX + "xoa_anh", {});
      anhDaLuu = null;
      cacKhungAvatar().forEach((k) => delete k.dataset.anh);
      veAnhDaiDien(null);
      thongBao(r.msg);
    } catch (loi) {
      thongBao(loi.message, true);
    } finally {
      khoaNutAnh(false);
    }
  }

  function khoiTaoAnhDaiDien() {
    if (!btnDoiAnh || !inputAnh) return;

    const khungDau = document.querySelector("[data-chu]");
    anhDaLuu = (khungDau && khungDau.dataset.anh) || null;

    btnDoiAnh.addEventListener("click", () => inputAnh.click());
    inputAnh.addEventListener("change", xuLyChonAnh);
    if (btnXoaAnh) btnXoaAnh.addEventListener("click", xuLyXoaAnh);
  }

  // ************ FORM THÔNG TIN
  function napLaiForm(form, duLieu) {
    Object.keys(TRUONG_FORM).forEach(function (ten) {
      const o = form.elements[ten];
      const giaTri = duLieu[TRUONG_FORM[ten]];
      if (o && giaTri !== undefined) o.value = giaTri;
    });
  }

  //Kiểm tra nhanh phía client (PHP vẫn kiểm tra lại).
  function kiemTraThongTin(d) {
    const thieu = Object.keys(d).some(
      (k) => !TRUONG_TUY_CHON.includes(k) && String(d[k]).trim() === "",
    );
    if (thieu) return "Vui lòng nhập đầy đủ thông tin.";
    if (!/^0\d{9}$/.test(String(d.sdt).trim()))
      return "Số điện thoại không hợp lệ.";
    //Email không bắt buộc: chỉ kiểm tra định dạng khi có nhập.
    const email = String(d.email || "").trim();
    if (email !== "" && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email))
      return "Email không hợp lệ.";
    if (d.ngay_sinh > layNgayThuc(0)) return "Ngày sinh không hợp lệ.";
    if (d.gioi_thieu && d.gioi_thieu.length > GIOI_THIEU_TOI_DA)
      return "Phần giới thiệu tối đa " + GIOI_THIEU_TOI_DA + " ký tự.";
    return "";
  }

  async function xuLyGuiThongTin(e) {
    e.preventDefault(); //JS tự gửi bằng fetch, không dùng action của form
    if (dangGuiThongTin) return;

    const d = Object.fromEntries(new FormData(formThongTin));
    const loiForm = kiemTraThongTin(d);
    if (loiForm) {
      thongBao(loiForm, true);
      return;
    }

    const nutLuu = formThongTin.querySelector("button[type='submit']");
    dangGuiThongTin = true;
    if (nutLuu) nutLuu.disabled = true;
    try {
      const r = await post(URL_AJAX + "cap_nhat", d);
      if (r.data) {
        napLaiForm(formThongTin, r.data); //hiện đúng giá trị vừa lưu (đọc lại từ DB)
        if (r.data.hoten) {
          capNhatChuVietTat(r.data.hoten);
          capNhatTenHienThi(r.data.hoten);
        }
      }
      thongBao(r.msg);
    } catch (loi) {
      thongBao(loi.message, true);
    } finally {
      dangGuiThongTin = false;
      if (nutLuu) nutLuu.disabled = false;
    }
  }

  // ************ HIỆN/ẨN MẬT KHẨU
  function cacNutHienMatKhau() {
    return document.querySelectorAll("[data-password-target]");
  }

  function datLaiHienMatKhau() {
    if (!formMatKhau) return;
    formMatKhau.querySelectorAll("[data-password-target]").forEach(function (nut) {
      const o = document.getElementById(nut.getAttribute("data-password-target"));
      if (!o) return;
      o.type = "password";
      nut.setAttribute("aria-pressed", "false");
      nut.setAttribute("aria-label", "Hiện mật khẩu");
    });
  }

  function khoiTaoHienMatKhau() {
    cacNutHienMatKhau().forEach(function (nut) {
      const o = document.getElementById(nut.getAttribute("data-password-target"));
      if (!o) return;
      nut.hidden = false;
      nut.addEventListener("click", function () {
        const hien = o.type === "password";
        o.type = hien ? "text" : "password";
        nut.setAttribute("aria-pressed", String(hien));
        nut.setAttribute("aria-label", hien ? "Ẩn mật khẩu" : "Hiện mật khẩu");
        o.focus();
      });
    });
  }

  // ************ FORM ĐỔI MẬT KHẨU
  async function xuLyGuiMatKhau(e) {
    e.preventDefault();
    if (dangGuiMatKhau) return;

    const d = Object.fromEntries(new FormData(formMatKhau));
    if (d.mat_khau_moi !== d.xac_nhan) {
      thongBao("Mật khẩu mới và xác nhận mật khẩu không khớp.", true);
      return;
    }
    if (String(d.mat_khau_moi).length < MAT_KHAU_TOI_THIEU) {
      thongBao(
        "Mật khẩu mới phải có ít nhất " + MAT_KHAU_TOI_THIEU + " ký tự.",
        true,
      );
      return;
    }

    const nutLuu = formMatKhau.querySelector("button[type='submit']");
    dangGuiMatKhau = true;
    if (nutLuu) nutLuu.disabled = true;
    try {
      const r = await post(URL_AJAX + "doi_mat_khau", d);
      thongBao(r.msg);
      formMatKhau.reset();
      datLaiHienMatKhau();
    } catch (loi) {
      thongBao(loi.message, true);
    } finally {
      dangGuiMatKhau = false;
      if (nutLuu) nutLuu.disabled = false;
    }
  }

  // ************ GẮN SỰ KIỆN
  function ganSuKien() {
    if (formThongTin) formThongTin.addEventListener("submit", xuLyGuiThongTin);
    else console.error("ca_nhan.js: không tìm thấy #form-thong-tin.");

    if (formMatKhau) formMatKhau.addEventListener("submit", xuLyGuiMatKhau);
    else console.error("ca_nhan.js: không tìm thấy #form-mat-khau.");
  }

  // ************ KHỞI TẠO
  function khoiTao() {
    ganSuKien(); //gắn submit của form trước tiên
    [khoiTaoChuyenTab, khoiTaoAnhDaiDien, khoiTaoHienMatKhau].forEach(function (ham) {
      try {
        ham();
      } catch (loi) {
        console.error("ca_nhan.js " + ham.name + ":", loi);
      }
    });
  }

  khoiTao();
});