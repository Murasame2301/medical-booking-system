<?php
/* Bang dinh tuyen cua toan he thong.
   Moi muc: tieu de, view, controller (file), ham (ten ham xu ly), js, css,
   va quyen (null = cong khai, so = vai tro duoc vao).

   Cac trang benh nhan tra ve ca trang HTML tu trong view (giao dien cua
   lengtrungtin); trang bac si va quan tri render trong layout rieng qua
   'khung'. */

return [
    /* ---------------------------------------------------------------- */
    /* Cong khai                                                         */
    /* ---------------------------------------------------------------- */
    'dang-nhap' => [
        'tieu_de' => 'Đăng nhập',
        'view' => 'auth/login.php',
        'controller' => 'app/Controllers/AuthController.php',
        'ham' => 'auth_dang_nhap',
        'quyen' => null,
    ],
    'dang-ky' => [
        'tieu_de' => 'Đăng ký tài khoản',
        'view' => 'auth/register.php',
        'controller' => 'app/Controllers/AuthController.php',
        'ham' => 'auth_dang_ky',
        'quyen' => null,
    ],
    'dang-xuat' => [
        'tieu_de' => 'Đăng xuất',
        'controller' => 'app/Controllers/AuthController.php',
        'ham' => 'auth_dang_xuat',
        'quyen' => null,
    ],

    /* ---------------------------------------------------------------- */
    /* Benh nhan                                                         */
    /* ---------------------------------------------------------------- */
    'trang-chu' => [
        'tieu_de' => 'Đặt lịch khám',
        'view' => 'benh-nhan/trang-chu.php',
        'controller' => 'app/Controllers/BenhNhan/trang_chu.php',
        'ham' => 'benh_nhan_trang_chu',
        'css' => ['home.css', 'nav-menu.css', 'footer.css'],
        'js' => [],
        'quyen' => null,
    ],
    'dat-kham' => [
        'tieu_de' => 'Tìm lịch khám',
        'view' => 'benh-nhan/dat-kham.php',
        'controller' => 'app/Controllers/BenhNhan/lich_kham.php',
        'ham' => 'benh_nhan_dat_kham',
        'quyen' => null,
    ],
    'chi-tiet-lich-kham' => [
        'tieu_de' => 'Chi tiết lịch khám',
        'view' => 'benh-nhan/chi-tiet-lich-kham.php',
        'controller' => 'app/Controllers/BenhNhan/lich_kham.php',
        'ham' => 'benh_nhan_chi_tiet_lich_kham',
        'quyen' => null,
    ],
    'lich-su-dat-kham' => [
        'tieu_de' => 'Lịch sử đặt khám',
        'view' => 'benh-nhan/lich-su-dat-kham.php',
        'controller' => 'app/Controllers/BenhNhan/lich_kham.php',
        'ham' => 'benh_nhan_lich_su_dat_kham',
        'quyen' => VT_BENH_NHAN,
    ],
    'thong-tin-tai-khoan' => [
        'tieu_de' => 'Thông tin tài khoản',
        'view' => 'benh-nhan/thong-tin-tai-khoan.php',
        'controller' => 'app/Controllers/BenhNhan/tai_khoan.php',
        'ham' => 'benh_nhan_thong_tin_tai_khoan',
        'quyen' => VT_BENH_NHAN,
    ],
    'doi-mat-khau' => [
        'tieu_de' => 'Đổi mật khẩu',
        'view' => 'benh-nhan/doi-mat-khau.php',
        'controller' => 'app/Controllers/BenhNhan/tai_khoan.php',
        'ham' => 'benh_nhan_doi_mat_khau',
        'quyen' => VT_BENH_NHAN,
    ],
    'tao-ho-so' => [
        'tieu_de' => 'Hồ sơ bệnh nhân',
        'view' => 'benh-nhan/tao-ho-so.php',
        'controller' => 'app/Controllers/BenhNhan/ho_so.php',
        'ham' => 'benh_nhan_tao_ho_so',
        'quyen' => VT_BENH_NHAN,
    ],

    /* ---------------------------------------------------------------- */
    /* Bac si                                                            */
    /* ---------------------------------------------------------------- */
    'bac-si/tong-quan' => [
        'tieu_de' => 'Tổng quan - Phòng khám',
        'view' => 'bac-si/tongquan.php',
        'controller' => 'app/Controllers/BacSi/tong_quan.php',
        'ham' => 'bac_si_tong_quan',
        'xu_ly' => 'bac_si_tong_quan_ajax',
        'js' => ['api.js', 'utils.js', 'dashboard.js'],
        'khung' => 'bac-si',
        'quyen' => VT_BAC_SI,
    ],
    'bac-si/lich-lam-viec' => [
        'tieu_de' => 'Lịch làm việc - Phòng khám',
        'view' => 'bac-si/lichlamviec.php',
        'controller' => 'app/Controllers/BacSi/lich_lam_viec.php',
        'ham' => 'bac_si_lich_lam_viec',
        'xu_ly' => 'bac_si_lich_lam_viec_ajax',
        'js' => ['api.js', 'utils.js', 'schedule.js'],
        'khung' => 'bac-si',
        'quyen' => VT_BAC_SI,
    ],
    'bac-si/lich-kham' => [
        'tieu_de' => 'Lịch khám - Phòng khám',
        'view' => 'bac-si/lichkham.php',
        'controller' => 'app/Controllers/BacSi/lich_kham.php',
        'ham' => 'bac_si_lich_kham',
        'xu_ly' => 'bac_si_lich_kham_ajax',
        'js' => ['api.js', 'utils.js', 'appointments.js'],
        'khung' => 'bac-si',
        'quyen' => VT_BAC_SI,
    ],
    'bac-si/kham-benh' => [
        'tieu_de' => 'Khám bệnh - Phòng khám',
        'view' => 'bac-si/khambenh.php',
        'controller' => 'app/Controllers/BacSi/kham_benh.php',
        'ham' => 'bac_si_kham_benh',
        'xu_ly' => 'bac_si_kham_benh_ajax',
        'js' => ['api.js', 'utils.js', 'exam.js'],
        'khung' => 'bac-si',
        'quyen' => VT_BAC_SI,
    ],
    'bac-si/ca-nhan' => [
        'tieu_de' => 'Thông tin cá nhân',
        'view' => 'bac-si/canhan.php',
        'controller' => 'app/Controllers/BacSi/ca_nhan.php',
        'ham' => 'bac_si_ca_nhan',
        'xu_ly' => 'bac_si_ca_nhan_ajax',
        'js' => ['api.js', 'utils.js', 'profile.js'],
        'khung' => 'bac-si',
        'quyen' => VT_BAC_SI,
    ],

    /* ---------------------------------------------------------------- */
    /* Quan tri                                                          */
    /* ---------------------------------------------------------------- */
    'quan-tri/tong-quan' => [
        'tieu_de' => 'Quản trị',
        'view' => 'quan-tri/tong-quan.php',
        'controller' => 'app/Controllers/QuanTri/tong_quan.php',
        'ham' => 'quan_tri_tong_quan',
        'khung' => 'quan-tri',
        'quyen' => VT_QUAN_TRI,
    ],
    'quan-tri/nguoi-dung' => [
        'tieu_de' => 'Quản lý người dùng',
        'view' => 'quan-tri/nguoi-dung.php',
        'controller' => 'app/Controllers/QuanTri/nguoi_dung.php',
        'ham' => 'quan_tri_nguoi_dung',
        'khung' => 'quan-tri',
        'js' => ['qt_nguoi_dung.js'],
        'quyen' => VT_QUAN_TRI,
    ],
    'quan-tri/bac-si' => [
        'tieu_de' => 'Quản lý bác sĩ',
        'view' => 'quan-tri/bac-si.php',
        'controller' => 'app/Controllers/QuanTri/bac_si.php',
        'ham' => 'quan_tri_bac_si',
        'khung' => 'quan-tri',
        'quyen' => VT_QUAN_TRI,
    ],
    'quan-tri/bac-si/them' => [
        'tieu_de' => 'Thêm bác sĩ',
        'view' => 'quan-tri/bac-si-form.php',
        'controller' => 'app/Controllers/QuanTri/bac_si.php',
        'ham' => 'quan_tri_bac_si_form',
        'khung' => 'quan-tri',
        'js' => ['qt_bac_si_form.js'],
        'quyen' => VT_QUAN_TRI,
    ],
    'quan-tri/bac-si/sua' => [
        'tieu_de' => 'Sửa bác sĩ',
        'view' => 'quan-tri/bac-si-form.php',
        'controller' => 'app/Controllers/QuanTri/bac_si.php',
        'ham' => 'quan_tri_bac_si_form',
        'khung' => 'quan-tri',
        'js' => ['qt_bac_si_form.js'],
        'quyen' => VT_QUAN_TRI,
    ],

    /* Trang chi xu ly POST, khong co view: luu bac si, khoa/mo khoa tai khoan. */
    'quan-tri/bac-si/luu' => [
        'tieu_de' => 'Lưu bác sĩ',
        'controller' => 'app/Controllers/QuanTri/bac_si.php',
        'ham' => 'quan_tri_bac_si_luu',
        'quyen' => VT_QUAN_TRI,
    ],
    'quan-tri/nguoi-dung/khoa' => [
        'tieu_de' => 'Khoá tài khoản',
        'controller' => 'app/Controllers/QuanTri/nguoi_dung.php',
        'ham' => 'quan_tri_khoa_mo_tai_khoan',
        'quyen' => VT_QUAN_TRI,
    ],

    /* AJAX cua khu quan tri. */
    'quan-tri/ajax/tim-nguoi-dung' => [
        'tieu_de' => 'Tìm người dùng',
        'controller' => 'app/Controllers/QuanTri/ajax.php',
        'ham' => 'quan_tri_tim_nguoi_dung_trang',
        'xu_ly' => 'quan_tri_ajax_tim_nguoi_dung',
        'quyen' => VT_QUAN_TRI,
    ],
    'quan-tri/ajax/khoa-mo' => [
        'tieu_de' => 'Khoá tài khoản',
        'controller' => 'app/Controllers/QuanTri/ajax.php',
        'ham' => 'quan_tri_khoa_mo_tai_khoan',
        'xu_ly' => 'quan_tri_ajax_khoa_mo',
        'quyen' => VT_QUAN_TRI,
    ],
    'quan-tri/ajax/kiem-tra-trung' => [
        'tieu_de' => 'Kiểm tra trùng',
        'controller' => 'app/Controllers/QuanTri/ajax.php',
        'ham' => 'quan_tri_kiem_tra_trung_trang',
        'xu_ly' => 'quan_tri_ajax_kiem_tra_trung',
        'quyen' => VT_QUAN_TRI,
    ],
];
