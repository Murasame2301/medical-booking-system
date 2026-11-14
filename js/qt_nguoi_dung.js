function cap_nhat_dong(dong, trangthai) {
  const o_trang_thai = dong.querySelector(".o-trang-thai");
  const nut = dong.querySelector('button[name="hanhdong"]');

  if (trangthai === "bi_khoa") {
    o_trang_thai.innerHTML = '<span class="trangthai tt-huy">Bi khoa</span>';
    nut.value = "mo";
    nut.textContent = "Mo khoa";
    nut.className = "nut-mo";
  } else {
    o_trang_thai.innerHTML =
      '<span class="trangthai tt-xacnhan">Hoat dong</span>';
    nut.value = "khoa";
    nut.textContent = "Khóa";
    nut.className = "nut-huy";
  }
}

function hien_thong_bao_ajax(noidung, thanh_cong) {
  const cho_hien = document.getElementById("thong_bao_ajax");
  cho_hien.textContent = noidung;
  cho_hien.style.color = thanh_cong ? "green" : "red";
}

function gui_khoa_mo(form, nut_bam) {
  const du_lieu = new FormData(form, nut_bam);

  fetch("xuly/ajax_khoa_mo_tai_khoan.php", {
    method: "POST",
    body: du_lieu,
  })
    .then(function (phan_hoi) {
      return phan_hoi.json();
    })
    .then(function (kq) {
      hien_thong_bao_ajax(kq.thong_bao, kq.ok);
      if (kq.ok) cap_nhat_dong(form.closest("tr"), kq.trangthai);
    })
    .catch(function (loi) {
      console.error(loi);
      hien_thong_bao_ajax("Co loi: " + loi.message, false);
    });
}

// Gắn sự kiện cho tất cả form khóa/mở khóa đang có trong trang
function gan_su_kien_khoa_mo() {
  document.querySelectorAll(".form-khoa-mo").forEach(function (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      if (!confirm("Bạn chắc chắn muốn thực hiện thao tác này?")) return;
      gui_khoa_mo(form, e.submitter);
    });
  });
}

// Tìm kiếm bằng AJAX: lấy dữ liệu ô lọc, nhận về bảng mới và thay vào trang
function tim_nguoi_dung(form_loc) {
  const tham_so = new URLSearchParams(new FormData(form_loc)).toString();

  fetch("xuly/ajax_tim_nguoi_dung.php?" + tham_so)
    .then(function (phan_hoi) {
      if (!phan_hoi.ok) {
        throw new Error("Lỗi " + phan_hoi.status);
      }
      return phan_hoi.text();
    })
    .then(function (html) {
      document.getElementById("khu_bang").innerHTML = html;
      gan_su_kien_khoa_mo(); // bảng mới có form mới, gắn lại sự kiện
    })
    .catch(function (loi) {
      console.error(loi);
      hien_thong_bao_ajax("Không tìm kiếm được: " + loi.message, false);
    });
}

gan_su_kien_khoa_mo();

document.querySelector(".bo-loc").addEventListener("submit", function (e) {
  e.preventDefault();
  tim_nguoi_dung(e.target);
});
