<?php
/* Quan ly bac si: danh sach, form them / sua, luu. */

require_once __DIR__ . '/__nap.php';

function quan_tri_bac_si($conn)
{
    return [
        'flash' => quan_tri_lay_flash(),
        'ds' => quan_tri_lay_ds_bac_si($conn),
    ];
}

/* Form them (khong co ma) hoac sua (?id=...). */
function quan_tri_bac_si_form($conn)
{
    $ma = chung_so_nguyen($_GET['id'] ?? 0);
    $dsChuyenKhoa = quan_tri_lay_chuyen_khoa($conn);

    if ($ma > 0) {
        $bs = quan_tri_lay_bac_si_chi_tiet($conn, $ma);
        if ($bs === null) {
            quan_tri_dat_flash('loi', 'Không tìm thấy bác sĩ.');
            chuyen_trang('index.php?page=quan-tri/bac-si');
        }
        $bs['chuyenkhoa'] = quan_tri_lay_chuyen_khoa_cua_bac_si($conn, $ma);
    } else {
        $bs = ['hoten' => '', 'sdt' => '', 'email' => '', 'trinhdo' => '', 'gioithieu' => '', 'chuyenkhoa' => []];
    }

    /* Lan truoc nhap loi thi du lieu da go de len du lieu tu CSDL. */
    $bs = array_merge($bs, quan_tri_lay_form_cu());
    $bs['email'] = (string) ($bs['email'] ?? '');
    $bs['trinhdo'] = (string) ($bs['trinhdo'] ?? '');
    $bs['gioithieu'] = (string) ($bs['gioithieu'] ?? '');

    return [
        'flash' => quan_tri_lay_flash(),
        'ma' => $ma,
        'bs' => $bs,
        'ds_chuyenkhoa' => $dsChuyenKhoa,
    ];
}

/* Kiem tra du lieu form bac si. Tra ve chuoi loi, rong la hop le. */
function quan_tri_kiem_tra_bac_si($conn, $ma, $d, $matKhau, $dsChuyenKhoa)
{
    if ($d['hoten'] === '' || $d['sdt'] === '') {
        return 'Vui lòng nhập họ tên và số điện thoại.';
    }
    if (chung_do_dai($d['hoten']) > 100) {
        return 'Họ tên tối đa 100 ký tự.';
    }
    if (!hop_le_sdt($d['sdt'])) {
        return 'Số điện thoại phải gồm 10 chữ số, bắt đầu bằng số 0.';
    }
    if (quan_tri_da_co_nguoi_dung($conn, 'sdt', $d['sdt'], $ma)) {
        return 'Số điện thoại này đã được người khác sử dụng.';
    }
    if ($d['email'] !== '') {
        if (chung_do_dai($d['email']) > 100 || !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
            return 'Email không hợp lệ.';
        }
        if (quan_tri_da_co_nguoi_dung($conn, 'email', $d['email'], $ma)) {
            return 'Email này đã được người khác sử dụng.';
        }
    }
    if ($ma === 0 || $matKhau !== '') {
        $loiMatKhau = ho_so_kiem_tra_mat_khau_moi($matKhau);
        if ($loiMatKhau !== '') {
            return $loiMatKhau;
        }
    }
    if (count($dsChuyenKhoa) === 0) {
        return 'Vui lòng chọn ít nhất một chuyên khoa.';
    }
    if (chung_do_dai($d['trinhdo']) > 100) {
        return 'Trình độ tối đa 100 ký tự.';
    }
    if (chung_do_dai($d['gioithieu']) > 5000) {
        return 'Giới thiệu tối đa 5000 ký tự.';
    }

    return '';
}

/* Luu bac si (POST). Thanh cong ve danh sach; loi quay lai form kem du lieu da go. */
function quan_tri_bac_si_luu($conn)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        chuyen_trang('index.php?page=quan-tri/bac-si');
    }

    $ma = chung_so_nguyen($_POST['manguoidung'] ?? 0);   // 0 = them moi
    $d = [
        'hoten' => chung_chuoi($_POST['hoten'] ?? ''),
        'sdt' => chung_chuoi($_POST['sdt'] ?? ''),
        'email' => chung_chuoi($_POST['email'] ?? ''),
        'trinhdo' => chung_chuoi($_POST['trinhdo'] ?? ''),
        'gioithieu' => chung_chuoi($_POST['gioithieu'] ?? ''),
    ];
    $matKhau = is_string($_POST['matkhau'] ?? null) ? $_POST['matkhau'] : '';

    $veForm = $ma > 0
        ? 'index.php?page=quan-tri/bac-si-form&id=' . $ma
        : 'index.php?page=quan-tri/bac-si-form';

    if ($ma > 0 && quan_tri_lay_bac_si_chi_tiet($conn, $ma) === null) {
        quan_tri_dat_flash('loi', 'Không tìm thấy bác sĩ.');
        chuyen_trang_sau_post('index.php?page=quan-tri/bac-si');
    }

    /* Chi giu ma chuyen khoa co that, bo trung. */
    $tatCa = quan_tri_lay_chuyen_khoa($conn);
    $gui = is_array($_POST['chuyenkhoa'] ?? null) ? $_POST['chuyenkhoa'] : [];
    $dsChuyenKhoa = [];
    foreach ($gui as $giaTri) {
        $so = chung_so_duong($giaTri);
        if ($so !== null && isset($tatCa[$so])) {
            $dsChuyenKhoa[$so] = $so;
        }
    }
    $dsChuyenKhoa = array_values($dsChuyenKhoa);

    $loi = quan_tri_kiem_tra_bac_si($conn, $ma, $d, $matKhau, $dsChuyenKhoa);

    $matKhauHash = null;
    if ($loi === '') {
        $matKhauHash = $matKhau !== '' ? password_hash($matKhau, PASSWORD_DEFAULT) : null;
        $thanhCong = $ma === 0
            ? quan_tri_tao_bac_si($conn, $d, $matKhauHash, $dsChuyenKhoa)
            : quan_tri_cap_nhat_bac_si($conn, $ma, $d, $matKhauHash, $dsChuyenKhoa);

        if ($thanhCong) {
            quan_tri_dat_flash('ok', $ma === 0 ? 'Đã thêm bác sĩ.' : 'Đã cập nhật thông tin bác sĩ.');
            chuyen_trang_sau_post('index.php?page=quan-tri/bac-si');
        }

        $loi = $ma === 0
            ? 'Có lỗi khi thêm bác sĩ, vui lòng thử lại.'
            : 'Có lỗi khi lưu thông tin, vui lòng thử lại.';
    }

    $d['chuyenkhoa'] = $dsChuyenKhoa;
    quan_tri_luu_form_cu($d);
    quan_tri_dat_flash('loi', $loi);
    chuyen_trang_sau_post($veForm);
}
