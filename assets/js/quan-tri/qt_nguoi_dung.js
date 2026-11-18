// Quan ly nguoi dung: tim kiem bang AJAX va khoa / mo khoa tai khoan.
// Token CSRF do layout/header.php in ra: <meta name="csrf-token" content="...">

function layTokenCsrf() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.content : "";
}

function hienThongBaoAjax(noiDung, thanhCong) {
  const choHien = document.getElementById("thong_bao_ajax");
  if (!choHien) return;
  choHien.textContent = noiDung;
  choHien.style.color = thanhCong ? "green" : "red";
}

function capNhatDong(dong, trangThai) {
  const oTrangThai = dong.querySelector(".o-trang-thai");
  const nut = dong.querySelector('button[name="hanhdong"]');
  if (!oTrangThai || !nut) return;

  if (trangThai === "bi_khoa") {
    oTrangThai.innerHTML = '<span class="trangthai tt-huy">Bị khoá</span>';
    nut.value = "mo";
    nut.textContent = "Mở khoá";
    nut.className = "nut-mo";
  } else {
    oTrangThai.innerHTML = '<span class="trangthai tt-xacnhan">Hoạt động</span>';
    nut.value = "khoa";
    nut.textContent = "Khoá";
    nut.className = "nut-huy";
  }
}

function guiKhoaMo(form) {
  const duLieu = new FormData(form);
  const nut = form.querySelector('button[name="hanhdong"]');
  if (nut) duLieu.set("hanhdong", nut.value);

  fetch("index.php?page=quan-tri/ajax/khoa-mo", {
    method: "POST",
    body: duLieu,
    headers: {
      "X-Requested-With": "XMLHttpRequest",
      "X-CSRF-Token": layTokenCsrf(),
    },
  })
    .then(function (phanHoi) {
      return phanHoi.json();
    })
    .then(function (kq) {
      hienThongBaoAjax(kq.msg, kq.ok);
      if (kq.ok && kq.data && kq.data.trangthai) {
        capNhatDong(form.closest("tr"), kq.data.trangthai);
      }
    })
    .catch(function (loi) {
      console.error(loi);
      hienThongBaoAjax("Có lỗi: " + loi.message, false);
    });
}

// Gan su kien cho tat ca form khoa/mo dang co trong trang
function ganSuKienKhoaMo() {
  document.querySelectorAll(".form-khoa-mo").forEach(function (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      if (!confirm("Bạn chắc chắn muốn thực hiện thao tác này?")) return;
      guiKhoaMo(form);
    });
  });
}

// Tim kiem bang AJAX: lay du lieu o loc, nhan ve bang moi roi thay vao trang
function timNguoiDung(formLoc) {
  const thamSo = new URLSearchParams(new FormData(formLoc)).toString();

  fetch("index.php?page=quan-tri/ajax/tim-nguoi-dung&" + thamSo, {
    headers: { "X-Requested-With": "XMLHttpRequest" },
  })
    .then(function (phanHoi) {
      if (!phanHoi.ok) throw new Error("Lỗi " + phanHoi.status);
      return phanHoi.text();
    })
    .then(function (html) {
      document.getElementById("khu_bang").innerHTML = html;
      ganSuKienKhoaMo(); // bang moi co form moi, gan lai su kien
    })
    .catch(function (loi) {
      console.error(loi);
      hienThongBaoAjax("Không tìm kiếm được: " + loi.message, false);
    });
}

ganSuKienKhoaMo();

const formLoc = document.getElementById("form-loc-nguoi-dung");
if (formLoc) {
  formLoc.addEventListener("submit", function (e) {
    e.preventDefault();
    timNguoiDung(e.target);
  });
}
