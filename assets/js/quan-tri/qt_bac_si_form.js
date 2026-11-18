// Form them / sua bac si: kiem tra o so dien thoai va email ngay khi roi o.

function kiemTraTruong(oNhap, cot) {
  const choHien = document.getElementById("thong_bao_" + cot);
  if (!choHien) return;

  // O an manguoidung: 0 khi them moi, khac 0 khi sua.
  // Gui len de server bo qua chinh nguoi nay khi kiem tra trung.
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
