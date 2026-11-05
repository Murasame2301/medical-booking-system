<?php
require_once __DIR__ . '/../chung/hang_so.php';
require_once __DIR__ . '/../chung/co_so_du_lieu.php';
require_once __DIR__ . '/../chung/tien_ich.php';

#gio ket thuc = gio bat dau + so phut
function tinh_gio_ket_thuc(string $gioBatDau, int$soPhut): string {
  return phut_sang_gio(min(gio_sang_phut($gioBatDau) +$soPhut, 23 * 60 + 59));
}

// khong tinh lich da huy
function khung_gio_co_luot_kham(int $maLichKham): bool {
    $sql = "SELECT COUNT(*) AS so_luong
            FROM lich_kham
            WHERE malichkham = ? AND trangthai <> ?";
    $ketQua = truy_van_mot($sql, 'is', [$maLichKham, LK_DA_HUY]);
    return (int)($ketQua['so_luong'] ?? 0) > 0;
}


function khung_gio_bi_trung (int $maBacSi, string $ngay, string $gioBatDau, string $gioKetThuc) : bool {
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

function tao_mot_lich_kham(int $maBacSi, string $ngay, string $tuGio, int $soPhut): array {
    #kiem tra ngay, gio, sophut tao lich
    if(!hop_le_ngay($ngay)) return ket_qua(false, 'Ngày không hợp lệ.');
    if(!hop_le_gio($tuGio)) return ket_qua(false, 'Giờ bắt đầu không hợp lệ');
    if($soPhut < 5 || $soPhut > 60) return ket_qua(false, 'Số phút cho mỗi lượt phải từ 5 phút đến 60 phút.');

    #kiem tra trung gio
    $denGio = tinh_gio_ket_thuc($tuGio, $soPhut);
    if(khung_gio_bi_trung($maBacSi, $ngay, $tuGio, $denGio)) return ket_qua(false, 'Khung giờ từ $tuGio đến $denGio ngày $ngay đã được tạo trong lịch làm việc.');


    $thoiGianBatDau = ghep_ngay_gio($ngay, $tuGio);
    $thoiGianKetThuc = ghep_ngay_gio($ngay, $denGio);
    $trangThai = LLV_CON_TRONG; # trang thai mac dinh

    $mysqli = ket_noi_db();

    $sql = "INSERT INTO (manguoidung, thoigianbatdau, thoigianketthuc, trangthai) VALUES (?, ?, ?, ?)";
    $themLich = $mysqli->prepare($sql);

    if($themLich === false) return ket_qua(false, 'Không thể thêm lịch do lỗi hệ thống.');

    $themLich->bind_param('isss', $maBacSi, $thoiGianBatDau, $thoiGianKetThuc, $trangThai);

    try {
        $ketQua = $themLich->execute();
        $themLich->close();
        if($ketQua) return ket_qua(true, "Tạo lịch làm việc từ $tuGio đến $denGio ngày $ngay thành công.", ['thoi_gian_bat_dau' => $thoiGianBatDau,
                                                                                                            'thoi_gian_ket_thuc' => $thoiGianKetThuc]);
    } catch (Throwable $loi){
        if(isset($themLich)){
            $themLich->close();
            return ket_qua(false, 'Lỗi hệ thống khi tạo lịch: '.$loi->getMessage());
        }
    }

}

