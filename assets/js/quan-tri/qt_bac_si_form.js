//Form thêm/sửa bác sĩ: kiểm tra ô số điện thoại và email ngay khi rời ô.

function kiemTraTruong(oNhap, cot) {
  const choHien = document.getElementById("thong_bao_" + cot);
  if (!choHien) return;

  //Ô ẩn manguoidung: 0 khi thêm mới, khác 0 khi sửa.
  //Gửi lên để server bỏ qua chính người này khi kiểm tra trùng.
  const oMa = document.querySelector('input[name="manguoidung"]');
  const boQuaMa = oMa ? oMa.value : "0";

  const thamSo = new URLSearchParams({
    cot: cot,
    giatri: oNhap.value,
    bo_qua_id: boQuaMa,
  });

  fetch("index.php?page=quan-tri/ajax/kiem-tra-trung&" + thamSo.toString(), {
    headers: { "X-Requested-With": "XMLHttpRequest" },
  })
    .then(function (phanHoi) {
      return phanHoi.json();
    })
    .then(function (kq) {
      choHien.textContent = kq.msg;
      choHien.style.color = kq.ok ? "green" : "red";
    })
    .catch(function (loi) {
      console.error(loi);
      choHien.textContent = "";
    });
}

const oSdt = document.getElementById("sdt");
if (oSdt) {
  oSdt.addEventListener("blur", function () {
    kiemTraTruong(this, "sdt");
  });
}

const oEmail = document.getElementById("email");
if (oEmail) {
  oEmail.addEventListener("blur", function () {
    kiemTraTruong(this, "email");
  });
}
