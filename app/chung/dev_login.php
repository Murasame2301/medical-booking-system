<!-- <?php -->
/**
 * dev_login.php
 * Giả lập phiên đăng nhập trong giai đoạn phát triển giao diện,
 * khi chưa làm chức năng đăng nhập thật (CHE_DO_PHAT_TRIEN = true trong hang_so.php).
 *
 * Khi làm đăng nhập thật: chỉ cần ngừng require file này trong khoi_tao.php,
 * thay bằng dang_nhap_xu_ly.php đọc từ bảng nguoi_dung trong CSDL.
 */

// if (CHE_DO_PHAT_TRIEN && !isset($_SESSION['ma_nguoi_dung'])) {
//     $_SESSION['ma_nguoi_dung'] = DEV_MA_NGUOI_DUNG;
//     $_SESSION['ho_ten']        = DEV_HO_TEN;
//     $_SESSION['vai_tro']       = DEV_VAI_TRO;
// }