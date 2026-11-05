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

    $mysqli = ket_noi_db();

    $sql = "INSERT INTO lich_lam_viec (manguoidung, thoigianbatdau, thoigianketthuc, trangthai) VALUES (?, ?, ?, ?)";
    $themLich = thi_hanh($sql, [$maBacSi, $thoiGianBatDau, $thoiGianKetThuc, $trangThai]);
    if ($themLich->affected_rows > 0) {
        return ket_qua(true, 'Tạo lịch làm việc thành công.', ['ma_lich' => $themLich->insert_id]);
    } else {
        return ket_qua(false, 'Tạo lịch làm việc thất bại.');
    }
    
}

#lay lich lam viec ngay den ngay
function lay_danh_sach_lich_lam_viec(int $maBacSi, string $tuNgay, string $denNgay, $tt = ''): array
{
    #kiem tra ngay , khoang ngay co hop le khong 
    if (!hop_le_ngay($tuNgay) || !hop_le_ngay($denNgay)) return ket_qua(false, 'Ngày không hợp lệ.');
    if ($tuNgay > $denNgay) return ket_qua(false, 'Ngày bắt đầu phải trước ngày kết thúc.');


    $tuNgayGio = ghep_ngay_gio($tuNgay, '00:00');
    $denNgayGio = ghep_ngay_gio($denNgay, '23:59', ':59');
    #sql lay lich
    $sql = "SELECT malichlamviec, thoigianbatdau, thoigianketthuc, trangthai
            FROM lich_lam_viec
            WHERE manguoidung = ? AND thoigianbatdau >= ? AND  thoigianketthuc <= ?
            ORDER BY thoigianbatdau ASC";
    $danhSach = truy_van($sql, 'iss', [$maBacSi, $tuNgayGio, $denNgayGio]);
    return ket_qua(true, 'Lấy danh sách lịch làm việc thành công.', ['danh_sach' => $danhSach]);
}
