<?php
require_once __DIR__ . '/app/Core/khoitao.php';

bat_buoc_quan_tri();

$id = (int)($_GET['id'] ?? 0);   // 0 = thêm mới, khác 0 = sửa bác sĩ có ID đó

// Danh sách chuyên khoa cho các ô tích: [ID => tên]
$ds_chuyenkhoa = [];
foreach (lay_chuyen_khoa($conn) as $ck) {
    $ds_chuyenkhoa[$ck['ID']] = $ck['ChuyenKhoa'];
}

if ($id > 0) {
    $bs = lay_bac_si_chi_tiet($conn, $id);
    if (!$bs) {
        dat_thong_bao('loi', 'Không tìm thấy bác sĩ.');
        chuyen_trang('qt_bac_si.php');
    }
    $bs['chuyenkhoa'] = lay_chuyen_khoa_cua_bac_si($conn, $id);
} else {
    $bs = ['hoten' => '', 'sdt' => '', 'email' => '', 'trinhdo' => '', 'gioithieu' => '', 'chuyenkhoa' => []];
}

// Nếu lần trước nhập lỗi thì dữ liệu đã gõ ghi đè lên dữ liệu từ DB
$bs = array_merge($bs, lay_du_lieu_cu());

hien_dau_trang($id > 0 ? 'Sửa bác sĩ' : 'Thêm bác sĩ');
?>
<div class="khung">
    <h2><?php echo $id > 0 ? 'Sửa thông tin bác sĩ' : 'Thêm bác sĩ'; ?></h2>

    <?php hien_thong_bao_phien(); ?>

    <form method="post" action="app/Controllers/xuly_luu_bac_si.php">
        <?php
        o_an('id', $id);
        nhap('Họ tên *', 'hoten', 'text', $bs['hoten']);
        nhap('Số điện thoại * (dùng để đăng nhập)', 'sdt', 'tel', $bs['sdt']);
        echo '<span id="thong_bao_sdt"></span>';
        nhap('Email', 'email', 'email', $bs['email'] ?? '', false);
        echo '<span id="thong_bao_email"></span>';
        // Thêm mới: mật khẩu bắt buộc. Sửa: để trống nghĩa là giữ nguyên mật khẩu cũ.
        nhap($id > 0 ? 'Mật khẩu mới (để trống nếu không đổi)' : 'Mật khẩu ban đầu *',
               'matkhau', 'password', '', $id == 0);
        nhap('Trình độ', 'trinhdo', 'text', $bs['trinhdo'] ?? '', false);
        o_nhieu_dong('Giới thiệu', 'gioithieu', $bs['gioithieu'] ?? '');
        o_tich_chon('Chuyên khoa * (chọn ít nhất một)', 'chuyenkhoa', $ds_chuyenkhoa, $bs['chuyenkhoa']);
        ?>
        <button type="submit" class="nut nut-luc">Lưu</button>
    </form>

    <a class="lien-ket" href="qt_bac_si.php">Quay lại danh sách</a>
</div>
<script src="assets/js/qt_bac_si_form.js"></script>
<?php
hien_cuoi_trang();