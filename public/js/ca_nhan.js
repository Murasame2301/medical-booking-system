document.addEventListener("DOMContentLoaded", function () {
  khoiTaoChuyenTab();
  khoiTaoDoiAnhDaiDien();
});

// chuye tab thong tin va bao mat
function khoiTaoChuyenTab() {
  const tabButtons = document.querySelectorAll(".tab-nut");
  const tabContents = document.querySelectorAll(".tab-noi-dung");

  tabButtons.forEach(function (btn, index) {
    btn.addEventListener("click", function () {
      chonTab(index, tabButtons, tabContents);
    });
  });
}

function chonTab(index, tabButtons, tabContents) {
  tabButtons.forEach(function (b) {
    b.classList.remove("on");
  });
  tabContents.forEach(function (c) {
    c.classList.remove("on");
  });

  tabButtons[index].classList.add("on");
  if (tabContents[index]) {
    tabContents[index].classList.add("on");
  }
}

function khoiTaoDoiAnhDaiDien() {
  const btnDoiAnh = document.querySelector(".anh-dai-dien .btn");
  const inputAnh = document.getElementById("inp-anh");

  if (!btnDoiAnh || !inputAnh) return;

  btnDoiAnh.addEventListener("click", function () {
    moHopThoaiChonAnh(inputAnh);
  });

  inputAnh.addEventListener("change", function () {
    xemTruocAnh(inputAnh);
  });
}

function moHopThoaiChonAnh(inputAnh) {
  inputAnh.click();
}

function xemTruocAnh(inputAnh) {
  const file = inputAnh.files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = function (e) {
    capNhatAnhDaiDien(e.target.result);
  };
  reader.readAsDataURL(file);
}

function capNhatAnhDaiDien(duongDanAnh) {
  const cackhungAvatar = document.querySelectorAll('.av-lon');
  cackhungAvatar.forEach(function(khung){
    khung.style.backgroundImage = "url(" + duongDanAnh + ")";
    khung.style.backgroundSize = "cover";
    khung.style.backgroundPosition = "center";
    khung.textContent = "";
  })
}
