<?php
require_once __DIR__ . '/../chung/hang_so.php';
require_once __DIR__ . '/../chung/co_so_du_lieu.php';
require_once __DIR__ . '/../chung/tien_ich.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
#gio ket thuc = gio bat dau + so phut
function tinh_gio_ket_thuc(string $gioBatDau, int $soPhut): string
{
    return phut_sang_gio(min(gio_sang_phut($gioBatDau) + $soPhut, 23 * 60 + 59));
}

// khung gio da tung co lich kham chua? tinh ca lich da huy,
// vi lich_kham.malichlamviec co khoa ngoai RESTRICT nen khung gio do khong xoa duoc
function khung_gio_co_luot_kham(int $maLichLamViec): bool
{
    $sql = "SELECT COUNT(*) AS so_luong
            FROM lich_kham
            WHERE malichlamviec = ? AND trangthai <> 'da_huy'";
    $ketQua = truy_van_mot($sql, 'i', [$maLichLamViec]);
    return (int)($ketQua['so_luong'] ?? 0) > 0;
}

// $boQuaMa: ma khung gio dang sua (de khong tinh trung voi chinh no), 0 = khong bo qua
function khung_gio_bi_trung(int $maBacSi, string $ngay, string $gioBatDau, string $gioKetThuc, int $boQuaMa = 0): bool
{
    #ghep
    $tgBatDau = ghep_ngay_gio($ngay, $gioBatDau);
    $tgKetThuc = ghep_ngay_gio($ngay, $gioKetThuc);

    #sql
    $sql = " SELECT COUNT(*) AS so_luong
             FROM lich_lam_viec
             WHERE manguoidung = ? AND thoigianbatdau < ? AND thoigianketthuc > ? AND malichlamviec <> ?";
    $ketQua = truy_van_mot($sql, 'issi', [$maBacSi, $tgKetThuc, $tgBatDau, $boQuaMa]);
    # kiem tra ket ra so_luong, so_luong, va tra ve true or false
    return (int)($ketQua['so_luong'] ?? 0) > 0;
}

#tao mot lich
function tao_mot_lich_kham(int $maBacSi, string $ngay, string $tuGio, int $soPhut): array
{
    #kiem tra ngay, gio, sophut tao lich
    if (!hop_le_ngay($ngay)) return ket_qua(false, 'Ngày không hợp lệ.');
    if ($ngay < date('Y-m-d')) return ket_qua(false, 'Không thể tạo khung giờ ở ngày đã qua.');
    if (!hop_le_gio($tuGio)) return ket_qua(false, 'Giờ bắt đầu không hợp lệ');
    if ($soPhut < 5 || $soPhut > 60) return ket_qua(false, 'Số phút cho mỗi lượt phải từ 5 phút đến 60 phút.');
     if (gio_sang_phut($tuGio) + $soPhut > 23 * 60 + 59) {
        return ket_qua(false, 'Khung giờ vượt quá cuối ngày.');
    }
    #kiem tra trung gio
    $denGio = tinh_gio_ket_thuc($tuGio, $soPhut);
    // bat dau sat 23:59 thi gio ket thuc bi chan o 23:59 -> bang gio bat dau (vi pham CHECK cua bang)
    if ($denGio <= $tuGio) return ket_qua(false, 'Khung giờ vượt quá cuối ngày.');
    if (khung_gio_bi_trung($maBacSi, $ngay, $tuGio, $denGio)) return ket_qua(false, 'Khung giờ từ ' . $tuGio . ' đến ' . $denGio . ' ngày ' . $ngay . ' đã được tạo trong lịch làm việc.');


    $thoiGianBatDau = ghep_ngay_gio($ngay, $tuGio);
    $thoiGianKetThuc = ghep_ngay_gio($ngay, $denGio);
    $trangThai = LLV_CON_TRONG; # trang thai mac dinh

    $sql = "INSERT INTO lich_lam_viec (manguoidung, thoigianbatdau, thoigianketthuc, trangthai) VALUES (?, ?, ?, ?)";
    $kieu = 'isss';
    try {
        $themLich = thi_hanh($sql, $kieu, [$maBacSi, $thoiGianBatDau, $thoiGianKetThuc, $trangThai]);
    } catch (mysqli_sql_exception $e) {
        // 1062: trung gio bat dau (UNIQUE), 1644: trigger bao chong lan
        if ($e->getCode() === 1062 || $e->getCode() === 1644) {
            return ket_qua(false, 'Khung giờ từ ' . $tuGio . ' đến ' . $denGio . ' ngày ' . $ngay . ' đã được tạo trong lịch làm việc.');
        }
        throw $e;
    }
    $soDong = $themLich->affected_rows;
    $maLich = $themLich->insert_id;
    $themLich->close();

    if ($soDong > 0) {
        return ket_qua(true, 'Tạo lịch làm việc thành công.', ['ma_lich' => $maLich]);
    } else {
        return ket_qua(false, 'Tạo lịch làm việc thất bại.');
    }
}

#lay lich lam viec ngay den ngay
function lay_danh_sach_lich_lam_viec(int $maBacSi, string $tuNgay, string $denNgay, array|string $trangThai = []): array
{
    if (!hop_le_ngay($tuNgay) || !hop_le_ngay($denNgay)) {
        return ket_qua(false, 'Ngày không hợp lệ.');
    }
    if ($tuNgay > $denNgay) {
        return ket_qua(false, 'Ngày bắt đầu phải trước hoặc bằng ngày kết thúc.');
    }

    // dem trang thai thanh chuoi
    $tt = is_string($trangThai) ? explode(',', $trangThai) : $trangThai;
    $tt = array_values(array_unique(array_filter(
        array_map('trim', array_map('strval', $tt)),
        fn($v) => $v !== ''
    )));

    // neu trang thai la tat ca thi lay het 
    if (in_array((string)LLV_TAT_CA, $tt, true)) {
        $tt = [];
    }

    // kiem tra trang thai hop le
    $hopLe = array_map('strval', [LLV_CON_TRONG, LLV_DA_DAT]);

    if (array_diff($tt, $hopLe)) {
        return ket_qua(false, 'Trạng thái không hợp lệ.');
    }

    // tham so ngay
    $tuNgayGio = ghep_ngay_gio($tuNgay, '00:00', ':00');
    $denNgayGio = ghep_ngay_gio($denNgay, '23:59', ':59');

    // lay danh sach lam viec
    $sql = "SELECT llv.malichlamviec,llv.manguoidung,llv.thoigianbatdau,llv.thoigianketthuc,llv.trangthai,
                EXISTS (
                    SELECT 1
                    FROM lich_kham lk
                    WHERE lk.malichlamviec = llv.malichlamviec
                    AND lk.trangthai <> 'da_huy' ) AS da_co_luot
            FROM lich_lam_viec llv
            WHERE llv.manguoidung = ? AND llv.thoigianbatdau >= ? AND llv.thoigianbatdau <= ?";
    $kieu = 'iss';
    $thamSo = [$maBacSi, $tuNgayGio, $denNgayGio];

    // loc trang thai neu nhu co 
    if (!empty($tt)) {
        $sql .= " AND llv.trangthai IN (" . implode(',', array_fill(0, count($tt), '?')) . ")";
        $kieu .= str_repeat('s', count($tt));
        $thamSo = array_merge($thamSo, $tt);
    }
    // sap xep theo thoi gian 
    $sql .= " ORDER BY llv.thoigianbatdau ASC ";
    // truy van
    $danhSach = truy_van($sql, $kieu, $thamSo);
    // tra ve ket qua
    return ket_qua(true, 'Lấy danh sách lịch làm việc thành công.', ['danh_sach' => $danhSach]);
}

function lay_lich_lam_viec(int $maBacSi, int $maLichLamViec): array
{
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

// $gioBatDau, $gioKetThuc la gio dang HH:MM; ngay cua khung gio giu nguyen
function cap_nhat_lich_lam_viec(int $maBacSi, int $maLichLamViec, string $gioBatDau, string $gioKetThuc, string $trangThai): array
{
    # kiem tra hop le ngay gio
    if (!hop_le_gio($gioBatDau) || !hop_le_gio($gioKetThuc)) return ket_qua(false, 'Giờ không hợp lệ.');
    if ($gioBatDau >= $gioKetThuc) return ket_qua(false, 'Giờ bắt đầu phải trước giờ kết thúc.');

    # kiem tra trang thai hop le
    if (!in_array($trangThai, [LLV_CON_TRONG, LLV_DA_DAT], true)) {
        return ket_qua(false, 'Trạng thái không hợp lệ.');
    }

    # lay khung gio hien tai (dung cua bac si) de biet ngay
    $hienTai = lay_lich_lam_viec($maBacSi, $maLichLamViec);
    if (!$hienTai['ok']) return $hienTai;
    $ngay = substr($hienTai['data']['lich_lam_viec']['thoigianbatdau'], 0, 10);

    # kiem tra trung gio voi lich khac (bo qua chinh khung gio dang sua)
    if (khung_gio_bi_trung($maBacSi, $ngay, $gioBatDau, $gioKetThuc, $maLichLamViec)) {
        return ket_qua(false, 'Khung giờ từ ' . $gioBatDau . ' đến ' . $gioKetThuc . ' đã được tạo trong lịch làm việc.');
    }

    # cap nhat lich lam viec
    $sql = "UPDATE lich_lam_viec
            SET thoigianbatdau = ?, thoigianketthuc = ?, trangthai = ?
            WHERE malichlamviec = ? AND manguoidung = ?";
    try {
        $stmt = thi_hanh($sql, 'sssii', [ghep_ngay_gio($ngay, $gioBatDau), ghep_ngay_gio($ngay, $gioKetThuc), $trangThai, $maLichLamViec, $maBacSi]);
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() === 1062 || $e->getCode() === 1644) {
            return ket_qua(false, 'Khung giờ từ ' . $gioBatDau . ' đến ' . $gioKetThuc . ' đã được tạo trong lịch làm việc.');
        }
        throw $e;
    }
    $soDong = $stmt->affected_rows;
    $stmt->close();

    if ($soDong > 0) {
        return ket_qua(true, 'Cập nhật lịch làm việc thành công.');
    } else {
        return ket_qua(false, 'Cập nhật lịch làm việc thất bại hoặc không có thay đổi.');
    }
}

// xoa lich lam viec
// xoa lich lam viec: chi xoa khi "con trong" va khong co luot kham dang hieu luc
function xoa_lich_lam_viec(int $maBacSi, int $maLichLamViec): array
{
    $lichLamViec = lay_lich_lam_viec($maBacSi, $maLichLamViec);
    if (!$lichLamViec['ok']) {
        return ket_qua(false, 'Không tìm thấy lịch làm việc.');
    }
    # chi khung "con trong" moi duoc xoa
    if ($lichLamViec['data']['lich_lam_viec']['trangthai'] !== LLV_CON_TRONG) {
        return ket_qua(false, 'Chỉ xóa được khung giờ còn trống.');
    }
    # co luot kham dang hieu luc (cho xac nhan, da xac nhan, da kham) thi khong xoa
    if (khung_gio_co_luot_kham($maLichLamViec)) {
        return ket_qua(false, 'Không thể xóa lịch làm việc vì đã có lượt khám.');
    }
    try {
        # luot da huy khong con y nghia, xoa truoc de khong vuong khoa ngoai RESTRICT
        $stmtHuy = thi_hanh(
            "DELETE FROM lich_kham WHERE malichlamviec = ? AND trangthai = 'da_huy'",
            'i',
            [$maLichLamViec]
        );
        $stmtHuy->close();

        # xoa khung gio
        $stmt = thi_hanh(
            "DELETE FROM lich_lam_viec WHERE malichlamviec = ? AND manguoidung = ?",
            'ii',
            [$maLichLamViec, $maBacSi]
        );
    } catch (mysqli_sql_exception $e) {
        // 1451: van con dong tham chieu (vd: ho so benh nhan, hoac vua co nguoi dat)
        if ($e->getCode() === 1451) {
            return ket_qua(false, 'Không thể xóa lịch làm việc vì đã có lượt khám.');
        }
        throw $e;
    }
    $soDong = $stmt->affected_rows;
    $stmt->close();

    return $soDong > 0
        ? ket_qua(true, 'Xóa lịch làm việc thành công.')
        : ket_qua(false, 'Xóa lịch làm việc thất bại.');
}



//  XU LY AJAX: yeu cau tao, xoa, thay doi lic và loc lich 
if (in_array($_GET['trang'] ?? '', ['lichlamviec'], true) && isset($_GET['ajax'])) {

    $maBacSi = (int)($_SESSION['ma_nguoi_dung'] ?? 0);
    $la_post = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';

    // chua dang nhap / khong phai bac si
    if ($maBacSi <= 0 || (int)($_SESSION['vai_tro'] ?? 0) !== VT_BAC_SI) {
        http_response_code(401);
        tra_json(ket_qua(false, 'Bạn cần đăng nhập bằng tài khoản bác sĩ.'));
        exit;
    }

    // loi bat ngo van tra JSON de JS hien thong bao
    try {
        switch ($_GET['ajax']) {
            case 'tao':
                if (!$la_post) {
                    http_response_code(405);
                    tra_json(ket_qua(false, 'Hệ thống đang xử lý.'));
                    exit;
                }
                $r = doc_du_lieu_gui();
                $ngay = (string)($r['ngay'] ?? '');
                $bd = (string)($r['gio_bat_dau'] ?? '');
                $soPhut = (int)($r['so_phut'] ?? 0);
                // trong ham co kiem tra du lieu
                tra_json(tao_mot_lich_kham($maBacSi, $ngay, $bd, $soPhut));
                exit;

            case 'lich-lam-viec':
                $tuNgay = (string)($_GET['tu_ngay'] ?? '');
                $denNgay = (string)($_GET['den_ngay'] ?? '');
                $trangThai = (string)($_GET['trang_thai'] ?? '');


                if ($trangThai === 'tat_ca') {
                    $trangThai = (string)LLV_TAT_CA;
                }
                // trong ham co kiem tra du lieu
                tra_json(lay_danh_sach_lich_lam_viec($maBacSi, $tuNgay, $denNgay, $trangThai));
                exit;

            case 'xoa':
                if (!$la_post) {
                    http_response_code(405);
                    tra_json(ket_qua(false, 'Hệ thống đang xử lý.'));
                    exit;
                }
                $r = doc_du_lieu_gui();
                $maLichLamViec = (int)($r['ma'] ?? 0);
                // trong ham co kiem tra du lieu
                tra_json(xoa_lich_lam_viec($maBacSi, $maLichLamViec));
                exit;

            default:
                http_response_code(400);
                tra_json(ket_qua(false, 'Yêu cầu không hợp lệ.'));
                exit;
        }
    } catch (Throwable $e) {
        error_log('lich-lam-viec: ' . $e->getMessage());
        tra_json(ket_qua(false, 'Lỗi máy chủ, vui lòng thử lại.'));
        exit;
    }
}
