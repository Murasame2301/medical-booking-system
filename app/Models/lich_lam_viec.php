<?php
//Truy vấn khung giờ làm việc của bác sĩ.
//Cột trangthai do trigger trong CSDL ghi.

require_once __DIR__ . '/../Helpers/utils.php';

//Khung giờ đã có lượt khám chưa huỷ chưa.
//Dùng để chặn sửa, xoá khung giờ.
function lich_lam_viec_co_luot_kham($conn, int $maLichLamViec): bool
{
    $r = truy_van_mot(
        $conn,
        "SELECT COUNT(*) AS so_luong FROM LICH_KHAM WHERE malichlamviec = ? AND trangthai <> ?",
        'is',
        [$maLichLamViec, LK_DA_HUY]
    );

    return (int) ($r['so_luong'] ?? 0) > 0;
}

//Khung giờ bị trùng với khung giờ khác của cùng bác sĩ
function lich_lam_viec_bi_trung($conn, int $maBacSi, string $ngay, string $gioBatDau, string $gioKetThuc, int $boQuaMa = 0): bool
{
    $r = truy_van_mot(
        $conn,
        "SELECT COUNT(*) AS so_luong
         FROM LICH_LAM_VIEC
         WHERE manguoidung = ? AND thoigianbatdau < ? AND thoigianketthuc > ? AND malichlamviec <> ?",
        'issi',
        [$maBacSi, ghep_ngay_gio($ngay, $gioKetThuc), ghep_ngay_gio($ngay, $gioBatDau), $boQuaMa]
    );

    return (int) ($r['so_luong'] ?? 0) > 0;
}

function lich_lam_viec_lay($conn, int $maBacSi, int $maLichLamViec): array
{
    $lich = truy_van_mot(
        $conn,
        "SELECT malichlamviec, manguoidung, thoigianbatdau, thoigianketthuc, trangthai
         FROM LICH_LAM_VIEC
         WHERE malichlamviec = ? AND manguoidung = ?",
        'ii',
        [$maLichLamViec, $maBacSi]
    );

    return $lich
        ? ket_qua(true, 'Lấy thông tin lịch làm việc thành công.', ['lich_lam_viec' => $lich])
        : ket_qua(false, 'Không tìm thấy lịch làm việc.');
}

//Lấy lịch làm việc từ ngày đến ngày, lọc theo trạng thái.
function lich_lam_viec_lay_danh_sach($conn, int $maBacSi, string $tuNgay, string $denNgay, string $trangThai = ''): array
{
    if (!hop_le_ngay($tuNgay) || !hop_le_ngay($denNgay)) {
        return ket_qua(false, 'Ngày không hợp lệ.');
    }
    if ($tuNgay > $denNgay) {
        return ket_qua(false, 'Ngày bắt đầu phải trước hoặc bằng ngày kết thúc.');
    }

    $tt = ($trangThai === '' || $trangThai === LLV_TAT_CA)
        ? []
        : array_values(array_unique(array_filter(array_map('trim', explode(',', $trangThai)))));
    if (array_diff($tt, LLV_TRANG_THAI)) {
        return ket_qua(false, 'Trạng thái không hợp lệ.');
    }

    //da_co_luot: để giao diện biết khung giờ nào đã có người đặt (chặn nút sửa, xoá).
    $sql = "SELECT llv.malichlamviec, llv.manguoidung, llv.thoigianbatdau, llv.thoigianketthuc, llv.trangthai,
                   EXISTS (SELECT 1 FROM LICH_KHAM lk
                           WHERE lk.malichlamviec = llv.malichlamviec AND lk.trangthai <> ?) AS da_co_luot
            FROM LICH_LAM_VIEC llv
            WHERE llv.manguoidung = ? AND llv.thoigianbatdau >= ? AND llv.thoigianbatdau <= ?";
    $kieu = 'siss';
    $thamSo = [
        LK_DA_HUY,
        $maBacSi,
        ghep_ngay_gio($tuNgay, GIO_DAU_NGAY),
        ghep_ngay_gio($denNgay, GIO_CUOI_NGAY, ':59'),
    ];

    if ($tt) {
        $sql .= " AND llv.trangthai IN (" . implode(',', array_fill(0, count($tt), '?')) . ")";
        $kieu .= str_repeat('s', count($tt));
        $thamSo = array_merge($thamSo, $tt);
    }
    $sql .= " ORDER BY llv.thoigianbatdau DESC";

    return ket_qua(true, 'Lấy danh sách lịch làm việc thành công.', [
        'danh_sach' => truy_van($conn, $sql, $kieu, $thamSo),
    ]);
}

//Thêm một khung giờ làm việc.
function lich_lam_viec_tao($conn, int $maBacSi, string $batDau, string $ketThuc): int
{
    $stmt = thi_hanh(
        $conn,
        "INSERT INTO LICH_LAM_VIEC (manguoidung, thoigianbatdau, thoigianketthuc, trangthai)
         VALUES (?, ?, ?, ?)",
        'isss',
        [$maBacSi, $batDau, $ketThuc, LLV_CON_TRONG]
    );
    $ma = (int) $conn->insert_id;
    $stmt->close();

    return $ma;
}

//Đổi giờ của khung giờ còn trống.
//Trả về số dòng bị ảnh hưởng.
function lich_lam_viec_cap_nhat($conn, int $maBacSi, int $maLichLamViec, string $batDau, string $ketThuc): int
{
    $stmt = thi_hanh(
        $conn,
        "UPDATE LICH_LAM_VIEC
         SET thoigianbatdau = ?, thoigianketthuc = ?
         WHERE malichlamviec = ? AND manguoidung = ? AND trangthai = ?",
        'ssiis',
        [$batDau, $ketThuc, $maLichLamViec, $maBacSi, LLV_CON_TRONG]
    );
    $soDong = $stmt->affected_rows;
    $stmt->close();

    return $soDong;
}

//Xoá khung giờ (xoá trước các lượt đã huỷ để không vướng khoá ngoại).
//Trả về số dòng bị ảnh hưởng.
function lich_lam_viec_xoa($conn, int $maBacSi, int $maLichLamViec): int
{
    thi_hanh(
        $conn,
        "DELETE FROM LICH_KHAM WHERE malichlamviec = ? AND trangthai = ?",
        'is',
        [$maLichLamViec, LK_DA_HUY]
    )->close();

    $stmt = thi_hanh(
        $conn,
        "DELETE FROM LICH_LAM_VIEC WHERE malichlamviec = ? AND manguoidung = ?",
        'ii',
        [$maLichLamViec, $maBacSi]
    );
    $soDong = $stmt->affected_rows;
    $stmt->close();

    return $soDong;
}

//Đếm khung giờ còn trống của một ngày.
function lich_lam_viec_dem_con_trong($conn, int $maBacSi, string $ngay): int
{
    $r = truy_van_mot(
        $conn,
        "SELECT COUNT(*) AS con_trong
         FROM LICH_LAM_VIEC
         WHERE manguoidung = ? AND DATE(thoigianbatdau) = ? AND trangthai = ?",
        'iss',
        [$maBacSi, $ngay, LLV_CON_TRONG]
    );

    return (int) ($r['con_trong'] ?? 0);
}

//Danh sách khung giờ còn trống của một ngày.
function lich_lam_viec_lay_con_trong($conn, int $maBacSi, string $ngay): array
{
    return truy_van(
        $conn,
        "SELECT malichlamviec,
                TIME(thoigianbatdau) AS gio_bat_dau,
                TIME(thoigianketthuc) AS gio_ket_thuc
         FROM LICH_LAM_VIEC
         WHERE manguoidung = ? AND DATE(thoigianbatdau) = ? AND trangthai = ?
         ORDER BY thoigianbatdau",
        'iss',
        [$maBacSi, $ngay, LLV_CON_TRONG]
    );
}

