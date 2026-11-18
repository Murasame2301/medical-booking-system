<?php
/* Trang lich lam viec: tao, sua, xoa khung gio va loc theo khoang ngay. */

require_once __DIR__ . '/../../Helpers/utils.php';
require_once __DIR__ . '/../../Models/lich_lam_viec.php';

// gio ket thuc = gio bat dau + so phut (khong qua 23:59)
function bac_si_tinh_gio_ket_thuc(string $gioBatDau, int $soPhut): string
{
    return phut_sang_gio(min(gio_sang_phut($gioBatDau) + $soPhut, CUOI_NGAY_PHUT));
}

/**
 * Tra ve noi dung loi, "" neu hop le.
 */
function bac_si_kiem_tra_khung_gio(string $ngay, string $tuGio, int $soPhut): string
{
    if (!hop_le_ngay($ngay)) {
        return 'Ngày không hợp lệ.';
    }
    if ($ngay < date('Y-m-d')) {
        return 'Không thể đặt khung giờ ở ngày đã qua.';
    }
    if (!hop_le_gio($tuGio)) {
        return 'Giờ bắt đầu không hợp lệ.';
    }
    if ($soPhut < SO_PHUT_TOI_THIEU || $soPhut > SO_PHUT_TOI_DA) {
        return 'Số phút cho mỗi lượt phải từ ' . SO_PHUT_TOI_THIEU . ' đến ' . SO_PHUT_TOI_DA . ' phút.';
    }
    if (gio_sang_phut($tuGio) + $soPhut > CUOI_NGAY_PHUT) {
        return 'Khung giờ vượt quá cuối ngày.';
    }

    return '';
}

function bac_si_thong_bao_trung(string $ngay, string $tuGio, string $denGio): string
{
    return "Khung giờ từ $tuGio đến $denGio ngày $ngay đã được tạo trong lịch làm việc.";
}

// 1062: trung gio bat dau (UNIQUE); 1644: trigger bao chong lan
function bac_si_la_loi_trung(mysqli_sql_exception $e): bool
{
    return in_array($e->getCode(), [1062, 1644], true);
}

/**
 * Tao mot khung gio moi: kiem tra du lieu, chong trung roi ghi.
 */
function bac_si_tao_khung_gio($conn, int $maBacSi, string $ngay, string $tuGio, int $soPhut): array
{
    if ($loi = bac_si_kiem_tra_khung_gio($ngay, $tuGio, $soPhut)) {
        return ket_qua(false, $loi);
    }

    $denGio = bac_si_tinh_gio_ket_thuc($tuGio, $soPhut);
    $baoTrung = bac_si_thong_bao_trung($ngay, $tuGio, $denGio);
    if (lich_lam_viec_bi_trung($conn, $maBacSi, $ngay, $tuGio, $denGio)) {
        return ket_qua(false, $baoTrung);
    }

    try {
        $maLich = lich_lam_viec_tao($conn, $maBacSi, ghep_ngay_gio($ngay, $tuGio), ghep_ngay_gio($ngay, $denGio));
    } catch (mysqli_sql_exception $e) {
        if (bac_si_la_loi_trung($e)) {
            return ket_qua(false, $baoTrung);
        }
        throw $e;
    }

    return $maLich > 0
        ? ket_qua(true, 'Tạo lịch làm việc thành công.', ['ma_lich' => $maLich])
        : ket_qua(false, 'Tạo lịch làm việc thất bại.');
}

/**
 * Sua ngay / gio bat dau / so phut cua khung gio con trong va chua co luot kham.
 */
function bac_si_sua_khung_gio($conn, int $maBacSi, int $maLichLamViec, string $ngay, string $tuGio, int $soPhut): array
{
    if ($loi = bac_si_kiem_tra_khung_gio($ngay, $tuGio, $soPhut)) {
        return ket_qua(false, $loi);
    }

    $hienTai = lich_lam_viec_lay($conn, $maBacSi, $maLichLamViec);
    if (!$hienTai['ok']) {
        return $hienTai;
    }
    if ($hienTai['data']['lich_lam_viec']['trangthai'] !== LLV_CON_TRONG
        || lich_lam_viec_co_luot_kham($conn, $maLichLamViec)) {
        return ket_qua(false, 'Chỉ sửa được khung giờ còn trống.');
    }

    $denGio = bac_si_tinh_gio_ket_thuc($tuGio, $soPhut);
    $baoTrung = bac_si_thong_bao_trung($ngay, $tuGio, $denGio);
    if (lich_lam_viec_bi_trung($conn, $maBacSi, $ngay, $tuGio, $denGio, $maLichLamViec)) {
        return ket_qua(false, $baoTrung);
    }

    try {
        $soDong = lich_lam_viec_cap_nhat(
            $conn,
            $maBacSi,
            $maLichLamViec,
            ghep_ngay_gio($ngay, $tuGio),
            ghep_ngay_gio($ngay, $denGio)
        );
    } catch (mysqli_sql_exception $e) {
        if (bac_si_la_loi_trung($e)) {
            return ket_qua(false, $baoTrung);
        }
        throw $e;
    }

    return $soDong > 0
        ? ket_qua(true, 'Cập nhật lịch làm việc thành công.')
        : ket_qua(false, 'Cập nhật lịch làm việc thất bại hoặc không có thay đổi.');
}

/**
 * Chi xoa khi "con trong" va khong co luot kham dang hieu luc.
 */
function bac_si_xoa_khung_gio($conn, int $maBacSi, int $maLichLamViec): array
{
    $lich = lich_lam_viec_lay($conn, $maBacSi, $maLichLamViec);
    if (!$lich['ok']) {
        return $lich;
    }

    if ($lich['data']['lich_lam_viec']['trangthai'] !== LLV_CON_TRONG) {
        return ket_qua(false, 'Chỉ xoá được khung giờ còn trống.');
    }
    if (lich_lam_viec_co_luot_kham($conn, $maLichLamViec)) {
        return ket_qua(false, 'Không thể xoá lịch làm việc vì đã có lượt khám.');
    }

    try {
        $soDong = lich_lam_viec_xoa($conn, $maBacSi, $maLichLamViec);
    } catch (mysqli_sql_exception $e) {
        // 1451: van con dong tham chieu (vd: vua co nguoi dat)
        if ((int) $e->getCode() === 1451) {
            return ket_qua(false, 'Không thể xoá lịch làm việc vì đã có lượt khám.');
        }
        throw $e;
    }

    return $soDong > 0
        ? ket_qua(true, 'Xoá lịch làm việc thành công.')
        : ket_qua(false, 'Xoá lịch làm việc thất bại.');
}

/**
 * Xu ly AJAX page=bac-si/lich-lam-viec&ajax=... (lich-lam-viec | tao | sua | xoa).
 */
function bac_si_lich_lam_viec_ajax($conn): void
{
    $maBacSi = ma_nguoi_dung_hien_tai();
    $hanhDong = (string) ($_GET['ajax'] ?? '');

    try {
        $r = doc_du_lieu_gui();
        $ketQua = match ($hanhDong) {
            'lich-lam-viec' => lich_lam_viec_lay_danh_sach(
                $conn,
                $maBacSi,
                (string) ($_GET['tu_ngay'] ?? ''),
                (string) ($_GET['den_ngay'] ?? ''),
                (string) ($_GET['trang_thai'] ?? '')
            ),
            'tao' => bac_si_tao_khung_gio(
                $conn,
                $maBacSi,
                (string) ($r['ngay'] ?? ''),
                (string) ($r['gio_bat_dau'] ?? ''),
                (int) ($r['so_phut'] ?? 0)
            ),
            'sua' => bac_si_sua_khung_gio(
                $conn,
                $maBacSi,
                (int) ($r['ma'] ?? 0),
                (string) ($r['ngay'] ?? ''),
                (string) ($r['gio_bat_dau'] ?? ''),
                (int) ($r['so_phut'] ?? 0)
            ),
            'xoa' => bac_si_xoa_khung_gio($conn, $maBacSi, (int) ($r['ma'] ?? 0)),
            default => ket_qua(false, 'Hành động không hợp lệ.'),
        };
    } catch (Throwable $e) {
        error_log('lich-lam-viec: ' . $e->getMessage());
        tra_json(ket_qua(false, 'Lỗi máy chủ, vui lòng thử lại.'), 500);
    }

    tra_json($ketQua);
}

function bac_si_lich_lam_viec($conn): array
{
    return [];
}
