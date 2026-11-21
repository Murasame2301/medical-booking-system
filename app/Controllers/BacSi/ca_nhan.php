<?php
//Trang cá nhân bác sĩ

require_once __DIR__ . '/../../Helpers/utils.php';
require_once __DIR__ . '/../../Helpers/avatar.php';
require_once __DIR__ . '/../../Helpers/ho_so.php';
require_once __DIR__ . '/../../Models/ca_nhan.php';

const CA_NHAN_GIOI_THIEU_TOI_DA = 1000;

/**
 * Thong tin ca nhan cua bac si dang dang nhap.
 */
function bac_si_ca_nhan_lay($conn, int $maNguoiDung): array
{
    $r = ca_nhan_lay_thong_tin($conn, $maNguoiDung);
    if ($r === null) {
        return ket_qua(false, 'Không tìm thấy thông tin cá nhân.');
    }

    return ket_qua(true, '', array_map('strval', $r));
}

function bac_si_ca_nhan_thong_bao_trung(Throwable $loi): string
{
    $msg = $loi->getMessage();
    if (stripos($msg, 'email') !== false) {
        return 'Email đã được sử dụng.';
    }
    if (stripos($msg, 'sdt') !== false) {
        return 'Số điện thoại đã được sử dụng.';
    }

    return 'Email hoặc số điện thoại đã được sử dụng.';
}

//Kiểm tra và lưu thông tin cá nhân. Chỉ bắt buộc họ tên và SĐT
//các trường khác được để trống vì tài khoản do quản trị tạo có thể chưa có email.
function bac_si_ca_nhan_cap_nhat_thong_tin($conn, int $ma, array $dl): array
{
    $ten = trim((string) ($dl['ten'] ?? ''));
    $ngaySinh = trim((string) ($dl['ngay_sinh'] ?? ''));
    $gioiTinh = trim((string) ($dl['gioi_tinh'] ?? ''));
    $trinhDo = trim((string) ($dl['trinh_do'] ?? ''));
    $sdt = trim((string) ($dl['sdt'] ?? ''));
    $email = trim((string) ($dl['email'] ?? ''));
    $diaChi = trim((string) ($dl['dia_chi'] ?? ''));

    $coGioiThieu = array_key_exists('gioi_thieu', $dl);
    $gioiThieu = $coGioiThieu ? trim((string) $dl['gioi_thieu']) : null;

    if ($ten === '' || $sdt === '') {
        return ket_qua(false, 'Vui lòng nhập họ tên và số điện thoại.');
    }
    if (
        chung_do_dai($ten) > 100 || chung_do_dai($trinhDo) > 100
        || chung_do_dai($email) > 100 || chung_do_dai($diaChi) > 255
    ) {
        return ket_qua(false, 'Thông tin nhập quá dài.');
    }
    if ($coGioiThieu && chung_do_dai($gioiThieu) > CA_NHAN_GIOI_THIEU_TOI_DA) {
        return ket_qua(false, 'Phần giới thiệu tối đa ' . CA_NHAN_GIOI_THIEU_TOI_DA . ' ký tự.');
    }
    if ($ngaySinh !== '') {
        if (!hop_le_ngay($ngaySinh) || $ngaySinh > date('Y-m-d') || $ngaySinh < '1900-01-01') {
            return ket_qua(false, 'Ngày sinh không hợp lệ.');
        }
    }
    if ($gioiTinh !== '' && !in_array($gioiTinh, ['nam', 'nu'], true)) {
        return ket_qua(false, 'Giới tính không hợp lệ.');
    }
    if (!hop_le_sdt($sdt)) {
        return ket_qua(false, 'Số điện thoại không hợp lệ.');
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ket_qua(false, 'Email không hợp lệ.');
    }

    $conn->begin_transaction();
    try {
        ca_nhan_cap_nhat($conn, [
            'ma' => $ma,
            'ten' => $ten,
            'ngay_sinh' => $ngaySinh,
            'gioi_tinh' => $gioiTinh,
            'trinh_do' => $trinhDo,
            'sdt' => $sdt,
            'email' => $email,
            'dia_chi' => $diaChi,
            'co_gioi_thieu' => $coGioiThieu,
            'gioi_thieu' => $gioiThieu,
        ]);

        $conn->commit();
    } catch (Throwable $loi) {
        $conn->rollback();
        if ($loi instanceof mysqli_sql_exception && (int) $loi->getCode() === 1062) {
            return ket_qua(false, bac_si_ca_nhan_thong_bao_trung($loi));
        }
        error_log('cap_nhat_trang_ca_nhan: ' . $loi->getMessage());

        return ket_qua(false, 'Không cập nhật được thông tin, vui lòng thử lại.');
    }

    //Tên trong session dùng cho menu, cập nhật luôn
    $_SESSION[SS_HO_TEN] = $ten;

    $moi = bac_si_ca_nhan_lay($conn, $ma);

    return ket_qua(true, 'Đã cập nhật thông tin cá nhân.', $moi['data'] ?? ['hoten' => $ten]);
}


//Validate va đổi mật khẩu.
function bac_si_ca_nhan_doi_mat_khau($conn, int $ma, array $dl): array
{
    $hienTai = (string) ($dl['mat_khau_hien_tai'] ?? '');
    $moi = (string) ($dl['mat_khau_moi'] ?? '');
    $xacNhan = (string) ($dl['xac_nhan'] ?? '');

    if ($hienTai === '' || $moi === '' || $xacNhan === '') {
        return ket_qua(false, 'Vui lòng nhập đầy đủ mật khẩu.');
    }
    if ($moi !== $xacNhan) {
        return ket_qua(false, 'Mật khẩu mới và xác nhận không khớp.');
    }
    $loiMatKhau = ho_so_kiem_tra_mat_khau_moi($moi);
    if ($loiMatKhau !== '') {
        return ket_qua(false, $loiMatKhau);
    }
    if ($moi === $hienTai) {
        return ket_qua(false, 'Mật khẩu mới phải khác mật khẩu hiện tại.');
    }

    $nd = ca_nhan_lay_hash_mat_khau($conn, $ma);
    if ($nd === null || !password_verify($hienTai, (string) $nd['matkhau'])) {
        return ket_qua(false, 'Mật khẩu hiện tại không đúng.');
    }

    ca_nhan_cap_nhat_mat_khau($conn, $ma, password_hash($moi, PASSWORD_DEFAULT));

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
        csrf_rotate();
    }

    return ket_qua(true, 'Đã đổi mật khẩu.');
}

function bac_si_ca_nhan_thu_muc_anh(): string
{
    return rtrim(anh_thu_muc_that(), '/\\');
}

function bac_si_ca_nhan_xoa_tep_anh(?string $duongDan): void
{
    if (!$duongDan) {
        return;
    }

    $tep = bac_si_ca_nhan_thu_muc_anh() . '/' . basename($duongDan);
    if (is_file($tep)) {
        @unlink($tep);
    }
}

function bac_si_ca_nhan_chuan_bi_thu_muc_anh(): ?string
{
    $thuMuc = bac_si_ca_nhan_thu_muc_anh();
    if (!is_dir($thuMuc) && !mkdir($thuMuc, 0755, true) && !is_dir($thuMuc)) {
        return null;
    }

    $ht = $thuMuc . '/.htaccess';
    if (!is_file($ht)) {
        @file_put_contents(
            $ht,
            "<FilesMatch \"\\.(php|phtml|phar)$\">\n  Require all denied\n</FilesMatch>\n"
        );
    }

    return $thuMuc;
}

//Validate file ảnh đại diện mới tải lên và lưu lại.
function bac_si_ca_nhan_luu_anh($conn, int $ma, ?array $file): array
{
    $loiTai = [
        UPLOAD_ERR_INI_SIZE => 'Ảnh vượt giới hạn của máy chủ.',
        UPLOAD_ERR_FORM_SIZE => 'Ảnh vượt giới hạn cho phép.',
        UPLOAD_ERR_PARTIAL => 'Ảnh tải lên chưa đầy đủ, vui lòng thử lại.',
        UPLOAD_ERR_NO_FILE => 'Bạn chưa chọn ảnh (hoặc ảnh quá lớn so với máy chủ).',
        UPLOAD_ERR_NO_TMP_DIR => 'Máy chủ thiếu thư mục tạm.',
        UPLOAD_ERR_CANT_WRITE => 'Máy chủ không ghi được ảnh.',
    ];
    $maLoi = $file['error'] ?? UPLOAD_ERR_NO_FILE;
    if (!$file || $maLoi !== UPLOAD_ERR_OK) {
        return ket_qua(false, $loiTai[$maLoi] ?? 'Không nhận được ảnh.');
    }
    if ($file['size'] > ANH_TOI_DA_BYTE) {
        return ket_qua(false, 'Ảnh tối đa 2MB.');
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        return ket_qua(false, 'Tệp tải lên không hợp lệ.');
    }

    $info = @getimagesize($file['tmp_name']);
    $duoi = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'][$info[2] ?? 0] ?? null;
    if ($duoi === null) {
        return ket_qua(false, 'Chỉ chấp nhận ảnh JPG, PNG hoặc WEBP.');
    }
    if ($info[0] * $info[1] > ANH_TOI_DA_DIEM) {
        return ket_qua(false, 'Kích thước ảnh quá lớn.');
    }

    $thuMuc = bac_si_ca_nhan_chuan_bi_thu_muc_anh();
    if ($thuMuc === null) {
        return ket_qua(false, 'Không tạo được thư mục lưu ảnh.');
    }

    $ten = 'av_' . $ma . '_' . bin2hex(random_bytes(6)) . '.' . $duoi;
    $tepMoi = $thuMuc . '/' . $ten;
    if (!move_uploaded_file($file['tmp_name'], $tepMoi)) {
        return ket_qua(false, 'Không lưu được ảnh.');
    }

    $duongDan = ANH_THU_MUC . $ten;
    try {
        $cu = ca_nhan_lay_anh($conn, $ma);
        ca_nhan_cap_nhat_anh($conn, $ma, $duongDan);
    } catch (Throwable $e) {
        @unlink($tepMoi);
        throw $e;
    }

    bac_si_ca_nhan_xoa_tep_anh($cu['anhdaidien'] ?? null);

    return ket_qua(true, 'Đã cập nhật ảnh đại diện.', ['anh' => $duongDan]);
}

function bac_si_ca_nhan_xoa_anh($conn, int $ma): array
{
    $cu = ca_nhan_lay_anh($conn, $ma);
    ca_nhan_cap_nhat_anh($conn, $ma, null);
    bac_si_ca_nhan_xoa_tep_anh($cu['anhdaidien'] ?? null);

    return ket_qua(true, 'Đã xoá ảnh đại diện.');
}

//Xử lý AJAX
function bac_si_ca_nhan_ajax($conn): void
{
    $ma = ma_nguoi_dung_hien_tai();

    try {
        $dl = doc_du_lieu_gui();
        $ketQua = match ((string) ($_GET['ajax'] ?? '')) {
            'thong_tin' => bac_si_ca_nhan_lay($conn, $ma),
            'cap_nhat' => bac_si_ca_nhan_cap_nhat_thong_tin($conn, $ma, $dl),
            'doi_mat_khau' => bac_si_ca_nhan_doi_mat_khau($conn, $ma, $dl),
            'doi_anh' => bac_si_ca_nhan_luu_anh($conn, $ma, $_FILES['anh'] ?? null),
            'xoa_anh' => bac_si_ca_nhan_xoa_anh($conn, $ma),
            default => ket_qua(false, 'Hành động không hợp lệ.'),
        };
    } catch (Throwable $e) {
        error_log('ca-nhan: ' . $e->getMessage());
        tra_json(ket_qua(false, 'Lỗi máy chủ, vui lòng thử lại.'), 500);
    }

    tra_json($ketQua);
}

//Dữ liệu cho view trang cá nhân
function bac_si_ca_nhan($conn): array
{
    $maNd = ma_nguoi_dung_hien_tai();

    return [
        'thong_tin' => ca_nhan_lay_thong_tin($conn, $maNd) ?? [],
        'ma' => $maNd,
    ];
}
