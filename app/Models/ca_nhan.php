<?php
//Truy vấn thông tin cá nhân của bác sĩ (NGUOI_DUNG + BAC_SI).

//Thông tin cá nhân đầy đủ của một bác sĩ
function ca_nhan_lay_thong_tin($conn, int $maNguoiDung): ?array
{
    return truy_van_mot(
        $conn,
        "SELECT nd.hoten, nd.ngaysinh, nd.gioitinh, bs.trinhdo, bs.gioithieu,
                nd.sdt, nd.email, nd.diachi, nd.anhdaidien
         FROM NGUOI_DUNG nd
         JOIN BAC_SI bs ON nd.manguoidung = bs.manguoidung
         WHERE nd.manguoidung = ?",
        'i',
        [$maNguoiDung]
    );
}

//Cập nhật thông tin cá nhân.
//Phải gọi bên trong transaction của nơi gọi.
function ca_nhan_cap_nhat($conn, array $giaTri): void
{
    thi_hanh(
        $conn,
        "UPDATE NGUOI_DUNG
         SET hoten = ?, ngaysinh = ?, gioitinh = ?, sdt = ?, email = ?, diachi = ?
         WHERE manguoidung = ?",
        'ssssssi',
        [
            $giaTri['ten'],
            $giaTri['ngay_sinh'] === '' ? null : $giaTri['ngay_sinh'],
            $giaTri['gioi_tinh'] === '' ? null : $giaTri['gioi_tinh'],
            $giaTri['sdt'],
            $giaTri['email'] === '' ? null : $giaTri['email'],
            $giaTri['dia_chi'] === '' ? null : $giaTri['dia_chi'],
            $giaTri['ma'],
        ]
    )->close();

    if ($giaTri['co_gioi_thieu']) {
        thi_hanh(
            $conn,
            "UPDATE BAC_SI SET trinhdo = ?, gioithieu = ? WHERE manguoidung = ?",
            'ssi',
            [$giaTri['trinh_do'] === '' ? null : $giaTri['trinh_do'], $giaTri['gioi_thieu'] === '' ? null : $giaTri['gioi_thieu'], $giaTri['ma']]
        )->close();
    } else {
        thi_hanh(
            $conn,
            "UPDATE BAC_SI SET trinhdo = ? WHERE manguoidung = ?",
            'si',
            [$giaTri['trinh_do'] === '' ? null : $giaTri['trinh_do'], $giaTri['ma']]
        )->close();
    }
}

//Hash mật khẩu hiện tại của tài khoản.
function ca_nhan_lay_hash_mat_khau($conn, int $maNguoiDung): ?array
{
    return truy_van_mot($conn, "SELECT matkhau FROM NGUOI_DUNG WHERE manguoidung = ?", 'i', [$maNguoiDung]);
}

//Ghi hash mật khẩu mới.
function ca_nhan_cap_nhat_mat_khau($conn, int $maNguoiDung, string $matKhauMaHoa): void
{
    thi_hanh(
        $conn,
        "UPDATE NGUOI_DUNG SET matkhau = ? WHERE manguoidung = ?",
        'si',
        [$matKhauMaHoa, $maNguoiDung]
    )->close();
}

//Đường dẫn ảnh đại diện hiện tại.
function ca_nhan_lay_anh($conn, int $maNguoiDung): array
{
    return truy_van_mot($conn, "SELECT anhdaidien FROM NGUOI_DUNG WHERE manguoidung = ?", 'i', [$maNguoiDung]) ?? [];
}

//Ghi đường dẫn ảnh đại diện mới.
function ca_nhan_cap_nhat_anh($conn, int $maNguoiDung, ?string $duongDan): void
{
    thi_hanh($conn, "UPDATE NGUOI_DUNG SET anhdaidien = ? WHERE manguoidung = ?", 'si', [$duongDan, $maNguoiDung])->close();
}