<?php
/* Trang lich kham cua bac si: tim kiem, xac nhan, huy va doi trang thai luot kham. */

require_once __DIR__ . '/../../Helpers/utils.php';
require_once __DIR__ . '/../../Models/lich_kham_bac_si.php';

// trang thai hien tai => cac trang thai duoc phep chuyen sang
/* (da_kham va da_huy la trang thai cuoi). Trigger trong CSDL cung chan cac
   duong chuyen khong hop le, day la lop kiem tra som de bao loi ro rang. */
const LK_CHUYEN_TRANG_THAI = [
    LK_CHO_XAC_NHAN => [LK_DA_XAC_NHAN, LK_DA_HUY],
    LK_DA_XAC_NHAN  => [LK_DA_KHAM, LK_DA_HUY],
];

/**
 * Doi trang thai lich kham.
 * Khi huy: bat buoc ghi nguoihuy (rang buoc chk_lich_kham_huy) va lydohuy (neu co).
 * Khong con buoc "tra khung gio ve con_trong": trigger trong CSDL lo viec do.
 */
function bac_si_doi_trang_thai_hop_le(
    $conn,
    int $maLichKham,
    int $maBacSi,
    array $tuCacTT,
    string $sangTT,
    string $msgThanhCong,
    ?string $lyDoHuy = null
): array {
    $lk = lich_kham_lay($conn, $maBacSi, $maLichKham);
    if (!$lk) {
        return ket_qua(false, 'Không tìm thấy lịch khám.');
    }

    if (!in_array($lk['trangthai'], $tuCacTT, true)) {
        return ket_qua(false, 'Trạng thái hiện tại (' . nhan_trang_thai($lk['trangthai'])[0] . ') không cho phép thao tác này.');
    }

    $huy = ($sangTT === LK_DA_HUY);
    if ($huy) {
        $lyDoHuy = trim((string) $lyDoHuy);
        if (chung_do_dai($lyDoHuy) > 255) {
            return ket_qua(false, 'Lý do huỷ tối đa 255 ký tự.');
        }
        if ($lyDoHuy === '') {
            $lyDoHuy = null;
        }
    }

    $soDong = lich_kham_doi_trang_thai(
        $conn,
        $maLichKham,
        (string) $lk['trangthai'],
        $sangTT,
        $huy ? $maBacSi : null,
        $huy ? $lyDoHuy : null
    );

    if ($soDong < 1) {
        return ket_qua(false, 'Lượt khám vừa được thay đổi.');
    }

    return ket_qua(true, $msgThanhCong);
}

function bac_si_xac_nhan_lich($conn, int $maBacSi, int $maLichKham): array
{
    return bac_si_doi_trang_thai_hop_le(
        $conn,
        $maLichKham,
        $maBacSi,
        [LK_CHO_XAC_NHAN],
        LK_DA_XAC_NHAN,
        'Đã xác nhận lịch khám.'
    );
}

// khop nut Huy tren giao dien: cho xac nhan + da xac nhan
function bac_si_huy_lich($conn, int $maBacSi, int $maLichKham, ?string $lyDoHuy = null): array
{
    return bac_si_doi_trang_thai_hop_le(
        $conn,
        $maLichKham,
        $maBacSi,
        [LK_CHO_XAC_NHAN, LK_DA_XAC_NHAN],
        LK_DA_HUY,
        'Đã huỷ lịch khám.',
        $lyDoHuy
    );
}

/**
 * Doi trang thai tu o chon tren bang: kiem tra duong chuyen hop le roi ghi.
 */
function bac_si_doi_theo_yeu_cau($conn, int $maBacSi, int $maLichKham, string $sangTT, ?string $lyDoHuy = null): array
{
    if (!in_array($sangTT, LK_TRANG_THAI, true)) {
        return ket_qua(false, 'Trạng thái không hợp lệ.');
    }

    $lk = lich_kham_lay($conn, $maBacSi, $maLichKham);
    if (!$lk) {
        return ket_qua(false, 'Không tìm thấy lịch khám.');
    }

    $duocPhep = LK_CHUYEN_TRANG_THAI[$lk['trangthai']] ?? [];
    if (!in_array($sangTT, $duocPhep, true)) {
        return ket_qua(false, 'Không thể chuyển từ "' . nhan_trang_thai($lk['trangthai'])[0]
            . '" sang "' . nhan_trang_thai($sangTT)[0] . '".');
    }

    return bac_si_doi_trang_thai_hop_le(
        $conn,
        $maLichKham,
        $maBacSi,
        [$lk['trangthai']],
        $sangTT,
        'Đã chuyển sang "' . nhan_trang_thai($sangTT)[0] . '".',
        $lyDoHuy
    );
}

/**
 * Xu ly AJAX page=bac-si/lich-kham&ajax=... (lich-kham | doi-trang-thai | xac-nhan | huy).
 */
function bac_si_lich_kham_ajax($conn): void
{
    $maBacSi = ma_nguoi_dung_hien_tai();
    $hanhDong = (string) ($_GET['ajax'] ?? '');

    try {
        $dl = doc_du_lieu_gui();
        $ma = (int) ($dl['ma'] ?? 0);
        $ketQua = match ($hanhDong) {
            'lich-kham' => lich_kham_tim_kiem(
                $conn,
                $maBacSi,
                (string) ($_GET['tu_ngay'] ?? ''),
                (string) ($_GET['den_ngay'] ?? ''),
                (string) ($_GET['trang_thai'] ?? '')
            ),
            'doi-trang-thai' => bac_si_doi_theo_yeu_cau(
                $conn,
                $maBacSi,
                $ma,
                (string) ($dl['trang_thai'] ?? ''),
                (string) ($dl['ly_do_huy'] ?? '')
            ),
            'xac-nhan' => bac_si_xac_nhan_lich($conn, $maBacSi, $ma),
            'huy' => bac_si_huy_lich($conn, $maBacSi, $ma, (string) ($dl['ly_do_huy'] ?? '')),
            default => ket_qua(false, 'Hành động không hợp lệ.'),
        };
    } catch (Throwable $e) {
        error_log('lich-kham: ' . $e->getMessage());
        tra_json(ket_qua(false, 'Lỗi máy chủ, vui lòng thử lại.'), 500);
    }

    tra_json($ketQua);
}

function bac_si_lich_kham($conn): array
{
    return [];
}
