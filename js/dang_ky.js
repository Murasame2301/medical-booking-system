function kiem_tra_sdt() {
  const o_sdt = document.getElementById("sdt");
  const cho_hien = document.getElementById("thong_bao_sdt");

  fetch("xuly/ajax_kiem_tra_sdt.php?sdt=" + encodeURIComponent(o_sdt.value))
    .then(function (phan_hoi) {
      return phan_hoi.json();
    })
    .then(function (kq) {
      cho_hien.textContent = kq.thong_bao;
      cho_hien.style.color = kq.hop_le ? "green" : "red";
    });
}

document.getElementById("sdt").addEventListener("blur", kiem_tra_sdt);
