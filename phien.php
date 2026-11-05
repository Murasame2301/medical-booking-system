<?php
const CHE_DO_PHAT_TRIEN = true;   // đổi thành false khi có đăng nhập thật

function khoi_dong_phien_bac_si()
{
  if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'path' => '/']);
    session_start();
  }
  // Session giả lập, chỉ chạy khi đang phát triển giao diện
  if (CHE_DO_PHAT_TRIEN && !isset($_SESSION['email'])) {
    $_SESSION['email']    = 'doctor@gmail.com';
    $_SESSION['mavaitro'] = 2;
    $_SESSION['hoten']    = 'Nguyễn Văn A';
    $_SESSION['mabacsi']  = 1;
  }
}

function yeu_cau_bac_si()
{
  khoi_dong_phien_bac_si();
  $laBacSi = isset($_SESSION['mabacsi'], $_SESSION['mavaitro']) && (int)$_SESSION['mavaitro'] === 2;
  if (!$laBacSi) {
    header("Location: ../lichlamviec.php");   // sửa đường dẫn cho đúng
    exit;
  }
}
