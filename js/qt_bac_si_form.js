// Kiểm tra một ô (sdt hoặc email) bằng AJAX rồi hiện kết quả ngay dưới ô đó
function kiem_tra_truong(o_nhap, cot) {
  const cho_hien = document.getElementById("thong_bao_" + cot);
  // Ô ẩn name="id": 0 khi thêm mới, khác 0 khi sửa. Gửi lên để server bỏ qua chính người này.
  const bo_qua_id = document.querySelector('input[name="id"]').value;

  const tham_so = new URLSearchParams({
    cot: cot,
    giatri: o_nhap.value,
    bo_qua_id: bo_qua_id,
  });

  fetch("xuly/ajax_kiem_tra_trung.php?" + tham_so.toString())
    .then(function (phan_hoi) {
      return phan_hoi.json();
    })
    .then(function (kq) {
      cho_hien.textContent = kq.thong_bao;
      cho_hien.style.color = kq.hop_le ? "green" : "red";
    })
    .catch(function (loi) {
      console.error(loi);
      cho_hien.textContent = "";
    });
}

document.getElementById("sdt").addEventListener("blur", function () {
  kiem_tra_truong(this, "sdt");
});

document.getElementById("email").addEventListener("blur", function () {
  kiem_tra_truong(this, "email");
});
