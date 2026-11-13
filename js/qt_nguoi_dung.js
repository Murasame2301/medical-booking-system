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

document.querySelectorAll(".form-khoa-mo").forEach(function (form) {
  form.addEventListener("submit", function (e) {
    e.preventDefault();
    if (!confirm("Ban chac chan muon thuc hien thao tac nay?")) return;
    gui_khoa_mo(form, e.submitter);
  });
});
