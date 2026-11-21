<?php
//Model quản trị: thống kê, người dùng, bác sĩ.

//Các con số cho trang tổng quan (một dòng duy nhất).
function quan_tri_thong_ke($conn)
{
    return truy_van_mot(
        $conn,
        "SELECT
            (SELECT COUNT(*) FROM NGUOI_DUNG WHERE mavaitro = ?) AS benh_nhan,
            (SELECT COUNT(*) FROM BAC_SI) AS bac_si,
            (SELECT COUNT(*) FROM LICH_KHAM WHERE trangthai = ?) AS lich_cho,
            (SELECT COUNT(*) FROM NGUOI_DUNG WHERE trangthai = ?) AS tai_khoan_khoa",
        'iss',
        [VT_BENH_NHAN, LK_CHO_XAC_NHAN, TK_BI_KHOA]
    ) ?? ['benh_nhan' => 0, 'bac_si' => 0, 'lich_cho' => 0, 'tai_khoan_khoa' => 0];
}

//Danh sách vai trò dạng [mavaitro => motavaitro].
function quan_tri_lay_vai_tro($conn)
{
    $ds = [];
    foreach (truy_van($conn, "SELECT mavaitro, motavaitro FROM VAI_TRO ORDER BY mavaitro") as $dong) {
        $ds[(int) $dong['mavaitro']] = $dong['motavaitro'];
    }

    return $ds;
}

//Danh sách người dùng. $tukhoa tìm trong họ tên hoặc SĐT; $vaitro = 0 là tất cả.
function quan_tri_lay_nguoi_dung($conn, $tukhoa = '', $vaitro = 0)
{
    $tim = '%' . addcslashes($tukhoa, '%_\\') . '%';

    return truy_van(
        $conn,
        "SELECT n.manguoidung, n.mavaitro, n.hoten, n.sdt, n.trangthai, n.ngaytao,
                v.motavaitro AS tenvaitro_hien_thi
         FROM NGUOI_DUNG n
         JOIN VAI_TRO v ON v.mavaitro = n.mavaitro
         WHERE (n.hoten LIKE ? OR n.sdt LIKE ?)
           AND (? = 0 OR n.mavaitro = ?)
         ORDER BY n.ngaytao DESC, n.manguoidung DESC
         LIMIT 200",
        'ssii',
        [$tim, $tim, $vaitro, $vaitro]
    );
}

//Một người dùng (mã, vai trò, trạng thái).
function quan_tri_lay_nguoi_dung_theo_ma($conn, $manguoidung)
{
    return truy_van_mot(
        $conn,
        "SELECT manguoidung, mavaitro, trangthai FROM NGUOI_DUNG WHERE manguoidung = ?",
        'i',
        [$manguoidung]
    );
}

function quan_tri_dat_trang_thai($conn, $manguoidung, $trangthai)
{
    $stmt = thi_hanh(
        $conn,
        "UPDATE NGUOI_DUNG SET trangthai = ? WHERE manguoidung = ?",
        'si',
        [$trangthai, $manguoidung]
    );
    $stmt->close();
}

//Khoá, mở khoá tài khoản ($hanhdong: 'khoa' hoặc 'mo').
//Không khoá quản trị viên và chính mình.
function quan_tri_doi_trang_thai_tai_khoan($conn, $manguoidung, $hanhdong, $nguoiThucHien)
{
    $dich = $manguoidung > 0 ? quan_tri_lay_nguoi_dung_theo_ma($conn, $manguoidung) : null;

    if ($dich === null) {
        return ket_qua(false, 'Không tìm thấy người dùng.');
    }

    if ($hanhdong !== 'khoa' && $hanhdong !== 'mo') {
        return ket_qua(false, 'Thao tác không hợp lệ.');
    }

    if ($hanhdong === 'khoa' && ((int) $dich['mavaitro'] === VT_QUAN_TRI || $manguoidung === (int) $nguoiThucHien)) {
        return ket_qua(false, 'Không thể khóa tài khoản quản trị viên hoặc chính bạn.');
    }

    $trangthai = $hanhdong === 'khoa' ? TK_BI_KHOA : TK_HOAT_DONG;
    quan_tri_dat_trang_thai($conn, $manguoidung, $trangthai);

    return ket_qua(
        true,
        $hanhdong === 'khoa' ? 'Đã khóa tài khoản.' : 'Đã mở khóa tài khoản.',
        ['trangthai' => $trangthai]
    );
}

//Tất cả bác sĩ (kể cả đang bị khoá).
function quan_tri_lay_ds_bac_si($conn)
{
    return truy_van(
        $conn,
        "SELECT n.manguoidung, n.hoten, n.sdt, n.trangthai, b.trinhdo,
                GROUP_CONCAT(ck.tenchuyenkhoa ORDER BY ck.tenchuyenkhoa SEPARATOR ', ') AS chuyenkhoa
         FROM BAC_SI b
         JOIN NGUOI_DUNG n ON n.manguoidung = b.manguoidung
         LEFT JOIN BAC_SI_CHUYEN_KHOA bc ON bc.manguoidung = b.manguoidung
         LEFT JOIN CHUYEN_KHOA ck ON ck.machuyenkhoa = bc.machuyenkhoa
         GROUP BY n.manguoidung, n.hoten, n.sdt, n.trangthai, b.trinhdo
         ORDER BY n.hoten"
    );
}

//Thông tin một bác sĩ để điền form sửa;
//trả về null nếu không phải bác sĩ.
function quan_tri_lay_bac_si_chi_tiet($conn, $manguoidung)
{
    return truy_van_mot(
        $conn,
        "SELECT n.manguoidung, n.hoten, n.sdt, n.email, b.trinhdo, b.gioithieu
         FROM BAC_SI b
         JOIN NGUOI_DUNG n ON n.manguoidung = b.manguoidung
         WHERE b.manguoidung = ?",
        'i',
        [$manguoidung]
    );
}

//Danh mục chuyên khoa dạng [machuyenkhoa => tenchuyenkhoa].
function quan_tri_lay_chuyen_khoa($conn)
{
    $ds = [];
    foreach (truy_van($conn, "SELECT machuyenkhoa, tenchuyenkhoa FROM CHUYEN_KHOA ORDER BY tenchuyenkhoa") as $dong) {
        $ds[(int) $dong['machuyenkhoa']] = $dong['tenchuyenkhoa'];
    }

    return $ds;
}

//Mảng mã chuyên khoa của một bác sĩ.
function quan_tri_lay_chuyen_khoa_cua_bac_si($conn, $manguoidung)
{
    $dong = truy_van($conn, "SELECT machuyenkhoa FROM BAC_SI_CHUYEN_KHOA WHERE manguoidung = ?", 'i', [$manguoidung]);

    return array_map('intval', array_column($dong, 'machuyenkhoa'));
}

//Có người khác dùng giá trị này chưa? $cot chỉ nhận sdt hoặc email.
function quan_tri_da_co_nguoi_dung($conn, $cot, $giatri, $boQuaMa = 0)
{
    if ($cot !== 'sdt' && $cot !== 'email') {
        throw new InvalidArgumentException('Cột không hợp lệ: ' . $cot);
    }

    return truy_van_mot(
        $conn,
        "SELECT manguoidung FROM NGUOI_DUNG WHERE $cot = ? AND manguoidung <> ?",
        'si',
        [$giatri, $boQuaMa]
    ) !== null;
}

//Kiểm tra tức thì SĐT, email cho form bác sĩ. Trả ket_qua(), ok = hợp lệ.
function quan_tri_kiem_tra_truong($conn, $cot, $giatri, $boQuaMa = 0)
{
    $giatri = trim((string) $giatri);

    if ($cot === 'sdt') {
        if (!hop_le_sdt($giatri)) {
            return ket_qua(false, 'Số điện thoại phải gồm 10 chữ số, bắt đầu bằng số 0.');
        }
        $ten = 'Số điện thoại';
    } elseif ($cot === 'email') {
        if ($giatri === '') {
            return ket_qua(true, '');
        }
        if (chung_do_dai($giatri) > 100 || !filter_var($giatri, FILTER_VALIDATE_EMAIL)) {
            return ket_qua(false, 'Email không hợp lệ.');
        }
        $ten = 'Email';
    } else {
        return ket_qua(false, 'Trường không hợp lệ.');
    }

    if (quan_tri_da_co_nguoi_dung($conn, $cot, $giatri, $boQuaMa)) {
        return ket_qua(false, $ten . ' này đã được người khác sử dụng.');
    }

    return ket_qua(true, $ten . ' có thể dùng.');
}

//Ghi danh sách chuyên khoa cho bác sĩ (gọi trong transaction).
function quan_tri_gan_chuyen_khoa($conn, $maBacSi, $dsChuyenKhoa)
{
    foreach ($dsChuyenKhoa as $maChuyenKhoa) {
        $stmt = thi_hanh(
            $conn,
            "INSERT INTO BAC_SI_CHUYEN_KHOA (manguoidung, machuyenkhoa) VALUES (?, ?)",
            'ii',
            [$maBacSi, $maChuyenKhoa]
        );
        $stmt->close();
    }
}

//Thêm bác sĩ: NGUOI_DUNG + BAC_SI + BAC_SI_CHUYEN_KHOA trong một transaction.
function quan_tri_tao_bac_si($conn, $d, $matKhauHash, $dsChuyenKhoa)
{
    $email = $d['email'] === '' ? null : $d['email'];
    $trinhdo = $d['trinhdo'] === '' ? null : $d['trinhdo'];
    $gioithieu = $d['gioithieu'] === '' ? null : $d['gioithieu'];
    $vaiTro = VT_BAC_SI;

    $conn->begin_transaction();
    try {
        $stmt = thi_hanh(
            $conn,
            "INSERT INTO NGUOI_DUNG (mavaitro, hoten, sdt, email, matkhau) VALUES (?, ?, ?, ?, ?)",
            'issss',
            [$vaiTro, $d['hoten'], $d['sdt'], $email, $matKhauHash]
        );
        $ma = (int) $conn->insert_id;
        $stmt->close();

        $stmt = thi_hanh(
            $conn,
            "INSERT INTO BAC_SI (manguoidung, trinhdo, gioithieu) VALUES (?, ?, ?)",
            'iss',
            [$ma, $trinhdo, $gioithieu]
        );
        $stmt->close();

        quan_tri_gan_chuyen_khoa($conn, $ma, $dsChuyenKhoa);

        $conn->commit();

        return true;
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();

        return false;
    }
}

//Sửa bác sĩ. $matKhauHash = null nghĩa là giữ mật khẩu cũ.
function quan_tri_cap_nhat_bac_si($conn, $ma, $d, $matKhauHash, $dsChuyenKhoa)
{
    $email = $d['email'] === '' ? null : $d['email'];
    $trinhdo = $d['trinhdo'] === '' ? null : $d['trinhdo'];
    $gioithieu = $d['gioithieu'] === '' ? null : $d['gioithieu'];
    $vaiTro = VT_BAC_SI;

    $conn->begin_transaction();
    try {
        $stmt = thi_hanh(
            $conn,
            "UPDATE NGUOI_DUNG SET hoten = ?, sdt = ?, email = ? WHERE manguoidung = ? AND mavaitro = ?",
            'sssii',
            [$d['hoten'], $d['sdt'], $email, $ma, $vaiTro]
        );
        $stmt->close();

        if ($matKhauHash !== null) {
            $stmt = thi_hanh(
                $conn,
                "UPDATE NGUOI_DUNG SET matkhau = ? WHERE manguoidung = ? AND mavaitro = ?",
                'sii',
                [$matKhauHash, $ma, $vaiTro]
            );
            $stmt->close();
        }

        $stmt = thi_hanh(
            $conn,
            "UPDATE BAC_SI SET trinhdo = ?, gioithieu = ? WHERE manguoidung = ?",
            'ssi',
            [$trinhdo, $gioithieu, $ma]
        );
        $stmt->close();

        //Chuyên khoa: xoá hết cũ rồi ghi lại danh sách mới.
        $stmt = thi_hanh($conn, "DELETE FROM BAC_SI_CHUYEN_KHOA WHERE manguoidung = ?", 'i', [$ma]);
        $stmt->close();
        quan_tri_gan_chuyen_khoa($conn, $ma, $dsChuyenKhoa);

        $conn->commit();

        return true;
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();

        return false;
    }
}