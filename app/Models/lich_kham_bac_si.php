<?php
//Truy vấn lịch khám theo góc nhìn bác sĩ.

require_once __DIR__ . '/../Helpers/utils.php';
require_once __DIR__ . '/lich_lam_viec.php';

//Tìm lịch khám của bác sĩ. Bỏ trống ngày thì lấy toàn bộ; lọc theo trạng thái.
function lich_kham_tim_kiem($conn, int $maBacSi, string $tuNgay, string $denNgay, string $trangThai = ''): array
{
    //Ngày chỉ kiểm tra khi có nhập; để trống cả hai nghĩa là không lọc ngày.
    if (($tuNgay !== '' && !hop_le_ngay($tuNgay)) || ($denNgay !== '' && !hop_le_ngay($denNgay))) {
        return ket_qua(false, 'Ngày không hợp lệ.');
    }
    if ($tuNgay !== '' && $denNgay !== '' && $tuNgay > $denNgay) {
        return ket_qua(false, 'Ngày bắt đầu phải trước hoặc bằng ngày kết thúc.');
    }

    $tt = ($trangThai === '' || $trangThai === LLV_TAT_CA)
        ? []
        : array_values(array_unique(array_filter(array_map('trim', explode(',', $trangThai)))));
    if (array_diff($tt, LK_TRANG_THAI)) {
        return ket_qua(false, 'Trạng thái không hợp lệ.');
    }

    $sql = "SELECT lk.trangthai, lk.malichkham, nd.hoten, nd.ngaysinh,
                   nd.gioitinh, nd.sdt, lk.lydo, lk.lydohuy,
                   nd_huy.hoten AS tennguoi_huy, nd_huy.mavaitro AS vai_tro_nguoi_huy,
                   llv.thoigianbatdau, llv.thoigianketthuc
            FROM LICH_LAM_VIEC llv
            JOIN LICH_KHAM lk ON llv.malichlamviec = lk.malichlamviec
            JOIN NGUOI_DUNG nd ON lk.manguoidung = nd.manguoidung
            LEFT JOIN NGUOI_DUNG nd_huy ON lk.nguoihuy = nd_huy.manguoidung
            WHERE llv.manguoidung = ?";
    $kieu = 'i';
    $thamSo = [$maBacSi];

    if ($tuNgay !== '') {
        $sql .= ' AND llv.thoigianbatdau >= ?';
        $kieu .= 's';
        $thamSo[] = ghep_ngay_gio($tuNgay, GIO_DAU_NGAY);
    }
    if ($denNgay !== '') {
        $sql .= ' AND llv.thoigianketthuc <= ?';
        $kieu .= 's';
        $thamSo[] = ghep_ngay_gio($denNgay, GIO_CUOI_NGAY, ':59');
    }

    if ($tt) {
        $sql .= " AND lk.trangthai IN (" . implode(',', array_fill(0, count($tt), '?')) . ')';
        $kieu .= str_repeat('s', count($tt));
        $thamSo = array_merge($thamSo, $tt);
    }
    $sql .= ' ORDER BY llv.thoigianbatdau ASC, lk.malichkham ASC';

    return ket_qua(true, 'Lấy danh sách lịch khám thành công.', [
        'danh_sach' => truy_van($conn, $sql, $kieu, $thamSo),
    ]);
}

//Một lịch khám của bác sĩ.
function lich_kham_lay($conn, int $maBacSi, int $maLichKham): ?array
{
    return truy_van_mot(
        $conn,
        "SELECT lk.*, llv.thoigianbatdau, llv.thoigianketthuc
         FROM LICH_KHAM lk
         JOIN LICH_LAM_VIEC llv ON lk.malichlamviec = llv.malichlamviec
         WHERE llv.manguoidung = ? AND lk.malichkham = ?",
        'ii',
        [$maBacSi, $maLichKham]
    ) ?: null;
}

//Ghi trạng thái mới nếu trạng thái cũ còn khớp.
//Trigger tự cập nhật LICH_LAM_VIEC. Trả số dòng bị ảnh hưởng.
function lich_kham_doi_trang_thai($conn, int $maLichKham, string $trangThaiCu, string $trangThaiMoi, ?int $nguoiHuy = null, ?string $lyDoHuy = null): int
{
    if ($nguoiHuy !== null) {
        $stmt = thi_hanh(
            $conn,
            "UPDATE LICH_KHAM SET trangthai = ?, nguoihuy = ?, lydohuy = ?
             WHERE malichkham = ? AND trangthai = ?",
            'sisis',
            [$trangThaiMoi, $nguoiHuy, $lyDoHuy, $maLichKham, $trangThaiCu]
        );
    } else {
        $stmt = thi_hanh(
            $conn,
            "UPDATE LICH_KHAM SET trangthai = ?
             WHERE malichkham = ? AND trangthai = ?",
            'sis',
            [$trangThaiMoi, $maLichKham, $trangThaiCu]
        );
    }

    $soDong = $stmt->affected_rows;
    $stmt->close();

    return $soDong;
}

//Các ca đã xác nhận của ngày hôm nay
function lich_kham_ca_hom_nay($conn, int $maBacSi): array
{
    $homNay = date('Y-m-d');
    $ngayMai = date('Y-m-d', strtotime('+1 day'));

    $ds = truy_van(
        $conn,
        "SELECT lk.malichkham      AS ma,
                llv.thoigianbatdau  AS gio_bat_dau,
                llv.thoigianketthuc AS gio_ket_thuc,
                lk.lydo             AS ly_do,
                lk.trangthai        AS trang_thai,
                nd.hoten            AS ten,
                nd.ngaysinh         AS ngay_sinh,
                nd.gioitinh         AS gioi_tinh,
                nd.sdt              AS sdt
         FROM LICH_LAM_VIEC llv
         JOIN LICH_KHAM lk  ON llv.malichlamviec = lk.malichlamviec
         JOIN NGUOI_DUNG nd ON lk.manguoidung = nd.manguoidung
         WHERE llv.manguoidung = ?
           AND llv.thoigianbatdau >= ?
           AND llv.thoigianbatdau <  ?
           AND lk.trangthai IN (?, ?)
         ORDER BY llv.thoigianbatdau ASC, lk.malichkham ASC",
        'issss',
        [
            $maBacSi,
            ghep_ngay_gio($homNay, '00:00'),
            ghep_ngay_gio($ngayMai, '00:00'),
            LK_DA_XAC_NHAN,
            LK_DA_KHAM,
        ]
    );

    foreach ($ds as &$k) {
        $k['ma'] = (int) $k['ma'];
        $k['gio_bat_dau'] = substr((string) $k['gio_bat_dau'], 11, 5);
        $k['gio_ket_thuc'] = substr((string) $k['gio_ket_thuc'], 11, 5);
        $k['ngay_sinh'] = $k['ngay_sinh'] ? dinh_dang_ngay((string) $k['ngay_sinh']) : '';
        $k['ly_do'] = (string) $k['ly_do'];
        $k['sdt'] = (string) $k['sdt'];
    }
    unset($k);

    return $ds;
}

//Danh sách lịch khám của một ngày (bỏ qua đã huỷ nếu $boDaHuy).
function lich_kham_theo_ngay($conn, int $maBacSi, string $ngay, bool $boDaHuy = true): array
{
    $sql = "SELECT llv.manguoidung AS ma_bac_si,
                   nd.hoten AS ho_ten_benh_nhan,
                   lk.malichkham,
                   lk.lydo,
                   lk.trangthai,
                   TIME(llv.thoigianbatdau) AS gio_bat_dau,
                   TIME(llv.thoigianketthuc) AS gio_ket_thuc
            FROM LICH_KHAM lk
            JOIN LICH_LAM_VIEC llv ON lk.malichlamviec = llv.malichlamviec
            JOIN NGUOI_DUNG nd ON lk.manguoidung = nd.manguoidung
            WHERE llv.manguoidung = ? AND llv.thoigianbatdau BETWEEN ? AND ?";

    $kieu = 'iss';
    $tham = [$maBacSi, ghep_ngay_gio($ngay, '00:00'), ghep_ngay_gio($ngay, '23:59', ':59')];

    if ($boDaHuy) {
        $sql .= " AND lk.trangthai <> ?";
        $kieu .= 's';
        $tham[] = LK_DA_HUY;
    }

    $sql .= " ORDER BY llv.thoigianbatdau";

    return truy_van($conn, $sql, $kieu, $tham);
}

//Thống kê nhanh của 1 ngày: tổng lượt, chờ xác nhận, đã khám, khung giờ trống.
function lich_kham_thong_ke_ngay($conn, int $maBacSi, string $ngay): array
{
    $r = truy_van_mot(
        $conn,
        "SELECT SUM(lk.trangthai <> ?) AS tong_luot,
                SUM(lk.trangthai = ?) AS cho_xac_nhan,
                SUM(lk.trangthai = ?) AS da_kham
         FROM LICH_KHAM lk
         JOIN LICH_LAM_VIEC llv ON lk.malichlamviec = llv.malichlamviec
         WHERE llv.manguoidung = ? AND DATE(llv.thoigianbatdau) = ?",
        'sssis',
        [LK_DA_HUY, LK_CHO_XAC_NHAN, LK_DA_KHAM, $maBacSi, $ngay]
    );

    return [
        'tong_luot' => (int) ($r['tong_luot'] ?? 0),
        'cho_xac_nhan' => (int) ($r['cho_xac_nhan'] ?? 0),
        'da_kham' => (int) ($r['da_kham'] ?? 0),
        'khung_gio_trong' => lich_lam_viec_dem_con_trong($conn, $maBacSi, $ngay),
    ];
}
