<?php
require_once __DIR__ . '/../chung/hang_so.php';
require_once __DIR__ . '/../chung/co_so_du_lieu.php';
require_once __DIR__ . '/../chung/tien_ich.php';

#gio ket thuc = gio bat dau + so phut
function tinh_gio_ket_thuc(string $gioBatDau, int $soPhut): string
{
    return phut_sang_gio(min(gio_sang_phut($gioBatDau) + $soPhut, 23 * 60 + 59));
}

// khong tinh lich da huy
function khung_gio_co_luot_kham(int $maLichKham): bool
{
    $sql = "SELECT COUNT(*) AS so_luong
            FROM lich_kham
            WHERE malichkham = ? AND trangthai <> ?";
    $ketQua = truy_van_mot($sql, 'is', [$maLichKham, LK_DA_HUY]);
    return (int)($ketQua['so_luong'] ?? 0) > 0;
}

function khung_gio_bi_trung(int $maBacSi, string $ngay, string $gioBatDau, string $gioKetThuc): bool
{
    #ghep
    $tgBatDau = ghep_ngay_gio($ngay, $gioBatDau);
    $tgKetThuc = ghep_ngay_gio($ngay, $gioKetThuc);

    #sql
    $sql = " SELECT COUNT(*) AS so_luong
             FROM lich_lam_viec
             WHERE manguoidung = ? AND thoigianbatdau < ? AND thoigianketthuc > ?";
    $ketQua = truy_van_mot($sql, 'iss', [$maBacSi, $tgKetThuc, $tgBatDau]);
    # kiem tra ket ra so_luong, so_luong, va tra ve true or false
    return (int)($ketQua['so_luong'] ?? 0) > 0;
}

#tao mot lich
function tao_mot_lich_kham(int $maBacSi, string $ngay, string $tuGio, int $soPhut): array
{
    #kiem tra ngay, gio, sophut tao lich
    if (!hop_le_ngay($ngay)) return ket_qua(false, 'Ngày không hợp lệ.');
    if (!hop_le_gio($tuGio)) return ket_qua(false, 'Giờ bắt đầu không hợp lệ');
    if ($soPhut < 5 || $soPhut > 60) return ket_qua(false, 'Số phút cho mỗi lượt phải từ 5 phút đến 60 phút.');

    #kiem tra trung gio
    $denGio = tinh_gio_ket_thuc($tuGio, $soPhut);
    if (khung_gio_bi_trung($maBacSi, $ngay, $tuGio, $denGio)) return ket_qua(false, 'Khung giờ từ ' . $tuGio . ' đến ' . $denGio . ' ngày ' . $ngay . ' đã được tạo trong lịch làm việc.');


    $thoiGianBatDau = ghep_ngay_gio($ngay, $tuGio);
    $thoiGianKetThuc = ghep_ngay_gio($ngay, $denGio);
    $trangThai = LLV_CON_TRONG; # trang thai mac dinh

    $sql = "INSERT INTO lich_lam_viec (manguoidung, thoigianbatdau, thoigianketthuc, trangthai) VALUES (?, ?, ?, ?)";
    $kieu = 'isss';
    $themLich = thi_hanh($sql, $kieu, [$maBacSi, $thoiGianBatDau, $thoiGianKetThuc, $trangThai]);
    if ($themLich->affected_rows > 0) {
        return ket_qua(true, 'Tạo lịch làm việc thành công.', ['ma_lich' => $themLich->insert_id]);
    } else {
        return ket_qua(false, 'Tạo lịch làm việc thất bại.');
    }
}

#lay lich lam viec ngay den ngay
function lay_danh_sach_lich_lam_viec(int $maBacSi, string $tuNgay, string $denNgay, array|string $trangThai = []): array
{
    # kiem tra hop le ngay
    if (!hop_le_ngay($tuNgay) || !hop_le_ngay($denNgay)) return ket_qua(false, 'Ngày không hợp lệ.');
    if ($tuNgay > $denNgay) return ket_qua(false, 'Ngày bắt đầu phải trước ngày kết thúc.');

    #chuan hoa: a,b hoac [a,b], rong thi lay tat ca
    $tt = is_string($trangThai) ? explode(',', $trangThai) : $trangThai;
    $tt = array_unique(array_filter(array_map('trim', $tt)));

    # kiem tra trang thai hop le
    if (array_diff($tt, [LLV_CON_TRONG, LLV_NGUNG_NHAN])) {
        return ket_qua(false, 'Trạng thái không hợp lệ.');
    }

    $tuNgayGio  = ghep_ngay_gio($tuNgay, '00:00');
    $denNgayGio = ghep_ngay_gio($denNgay, '23:59', ':59');

    #sql lay lich lam viec cua bac si tu ngay den ngay
    $sql = "SELECT malichlamviec, thoigianbatdau, thoigianketthuc, trangthai
            FROM lich_lam_viec
            WHERE manguoidung = ? AND thoigianbatdau >= ? AND thoigianketthuc <= ?";
    $kieu   = 'iss';
    $thamSo = [$maBacSi, $denNgayGio, $tuNgayGio];

    # loc trang thai neu co
    if ($tt) {
        $sql   .= " AND trangthai IN (" . implode(',', array_fill(0, count($tt), '?')) . ")";
        $kieu  .= str_repeat('s', count($tt));
        $thamSo = array_merge($thamSo, $tt);
    }

    $sql .= " ORDER BY thoigianbatdau ASC";

    $danhSach = truy_van($sql, $kieu, $thamSo);
    return ket_qua(true, 'Lấy danh sách lịch làm việc thành công.', ['danh_sach' => $danhSach]);
}

function lay_lich_lam_viec(int $maBacSi, int $maLichLamViec): array {
    $sql = "SELECT malichlamviec, manguoidung, thoigianbatdau, thoigianketthuc, trangthai
            FROM lich_lam_viec
            WHERE malichlamviec = ? AND manguoidung = ?";
    $lichLamViec = truy_van_mot($sql, 'ii', [$maLichLamViec, $maBacSi]);
    if ($lichLamViec) {
        return ket_qua(true, 'Lấy thông tin lịch làm việc thành công.', ['lich_lam_viec' => $lichLamViec]);
    } else {
        return ket_qua(false, 'Không tìm thấy lịch làm việc.');
    }
}

function cap_nhat_lich_lam_viec(int $maBacSi, int $maLichLamViec, string $gioBatDau, string $gioKetThuc, string $trangThai): array
{
    # kiem tra hop le ngay gio
    if (!hop_le_gio($gioBatDau) || !hop_le_gio($gioKetThuc)) return ket_qua(false, 'Giờ không hợp lệ.');
    if ($gioBatDau >= $gioKetThuc) return ket_qua(false, 'Giờ bắt đầu phải trước giờ kết thúc.');

    # kiem tra trang thai hop le
    if (!in_array($trangThai, [LLV_CON_TRONG, LLV_NGUNG_NHAN])) {
        return ket_qua(false, 'Trạng thái không hợp lệ.');
    }

    # kiem tra trung gio voi lich khac
    if (khung_gio_bi_trung($maBacSi, substr($gioBatDau, 0, 10), $gioBatDau, $gioKetThuc)) {
        return ket_qua(false, 'Khung giờ từ ' . $gioBatDau . ' đến ' . $gioKetThuc . ' đã được tạo trong lịch làm việc.');
    }

    # cap nhat lich lam viec
    $sql = "UPDATE lich_lam_viec
            SET thoigianbatdau = ?, thoigianketthuc = ?, trangthai = ?
            WHERE malichlamviec = ? AND manguoidung = ?";
    $stmt = thi_hanh($sql,'sssii', [$gioBatDau, $gioKetThuc, $trangThai, $maLichLamViec, $maBacSi]);
    
    if ($stmt->affected_rows > 0) {
        return ket_qua(true, 'Cập nhật lịch làm việc thành công.');
    } else {
        return ket_qua(false, 'Cập nhật lịch làm việc thất bại hoặc không có thay đổi.');
    }
}

// xoa lich lam viec
function xoa_lich_lam_viec(int $maBacSi, int $maLichLamViec): array {
    # kiem tra lich lam viec co ton tai va thuoc bac si
    $lichLamViec = lay_lich_lam_viec($maBacSi, $maLichLamViec);
    if (!$lichLamViec['lich_lam_viec']) {
        return ket_qua(false, 'Không tìm thấy lịch làm việc.');
    }

    # kiem tra lich lam viec co luot kham chua
    $sql = "SELECT COUNT(*) AS so_luot_kham
            FROM lich_kham
            WHERE malichlamviec = ? AND trangthai <> ?";
    $luotKham = truy_van_mot($sql, 'is', [$maLichLamViec, LK_DA_HUY]);
    if ((int)($luotKham['so_luot_kham'] ?? 0) > 0) {
        return ket_qua(false, 'Không thể xóa lịch làm việc vì đã có lượt khám.');
    }

    # xoa lich lam viec
    $sql = "DELETE FROM lich_lam_viec WHERE malichlamviec = ? AND manguoidung = ?";
    $stmt = thi_hanh($sql, 'ii', [$maLichLamViec, $maBacSi]);

    if ($stmt->affected_rows > 0) {
        return ket_qua(true, 'Xóa lịch làm việc thành công.');
    } else {
        return ket_qua(false, 'Xóa lịch làm việc thất bại.');
    }
}

//  XU LY AJAX: yeu cau tao, xoa, thay doi lic và loc lich 


