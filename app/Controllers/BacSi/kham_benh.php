<?php
//Trang khám bệnh: Ca khám trong ngày, hoàn tất khám, xem hồ sơ khám.

require_once __DIR__ . '/../../Helpers/utils.php';
require_once __DIR__ . '/../../Models/lich_kham_bac_si.php';
require_once __DIR__ . '/../../Models/ho_so_kham.php';

//Hoàn tất khám: đổi trạng thái lịch sang "đã khám" và lưu chẩn đoán, lời dặn.
//Dùng transaction để hai bước cùng thành công hoặc cùng bị huỷ.
function bac_si_hoan_thanh_kham($conn, int $maLichKham, int $maBacSi, string $chuanDoan, string $loiDan): array
{
    $chuanDoan = trim($chuanDoan);
    $loiDan = trim($loiDan);

    if ($chuanDoan === '') {
        return ket_qua(false, 'Vui lòng nhập chẩn đoán.');
    }

    $lichKham = lich_kham_lay($conn, $maBacSi, $maLichKham);
    if ($lichKham === null) {
        return ket_qua(false, 'Không tìm thấy lịch khám.');
    }
    if ($lichKham['trangthai'] !== LK_DA_XAC_NHAN) {
        return ket_qua(false, 'Chỉ khám được lượt đã xác nhận.');
    }
    //Chỉ khám cho ca của ngày hiện tại
    if (substr((string) $lichKham['thoigianbatdau'], 0, 10) !== date('Y-m-d')) {
        return ket_qua(false, 'Chỉ được khám các ca trong ngày hôm nay.');
    }

    $conn->begin_transaction();

    try {
        $soDong = lich_kham_doi_trang_thai($conn, $maLichKham, LK_DA_XAC_NHAN, LK_DA_KHAM);
        if ($soDong !== 1) {
            $conn->rollback();

            return ket_qua(false, 'Lượt khám đã được xử lý trước đó.');
        }

        ho_so_kham_luu($conn, $maLichKham, $chuanDoan, $loiDan);

        $conn->commit();

        return ket_qua(true, 'Đã hoàn thành khám.');
    } catch (Throwable $loi) {
        $conn->rollback();
        error_log('hoan_thanh_kham: ' . $loi->getMessage());

        return ket_qua(false, 'Không lưu được hồ sơ, vui lòng thử lại.');
    }
}

//Hồ sơ khám đã thăm của một lượt khám.
function bac_si_lay_ho_so_kham($conn, int $maLichKham, int $maBacSi): array
{
    $lichKham = lich_kham_lay($conn, $maBacSi, $maLichKham);
    if ($lichKham === null) {
        return ket_qua(false, 'Không tìm thấy lịch khám.');
    }
    if ($lichKham['trangthai'] !== LK_DA_KHAM) {
        return ket_qua(false, 'Lượt khám này chưa có kết quả.');
    }

    return ket_qua(true, '', ho_so_kham_lay($conn, $maLichKham));
}

//Xử lý AJAX.
function bac_si_kham_benh_ajax($conn): void
{
    $maBacSi = ma_nguoi_dung_hien_tai();

    try {
        $dl = doc_du_lieu_gui();
        $ketQua = match ((string) ($_GET['ajax'] ?? '')) {
            'ds' => ket_qua(true, '', lich_kham_ca_hom_nay($conn, $maBacSi)),
            'xem' => bac_si_lay_ho_so_kham($conn, (int) ($_GET['ma'] ?? 0), $maBacSi),
            'hoan_thanh' => bac_si_hoan_thanh_kham(
                $conn,
                (int) ($dl['ma'] ?? 0),
                $maBacSi,
                (string) ($dl['chan_doan'] ?? ''),
                (string) ($dl['loi_dan'] ?? '')
            ),
            default => ket_qua(false, 'Hành động không hợp lệ.'),
        };
    } catch (Throwable $e) {
        error_log('kham-benh: ' . $e->getMessage());
        tra_json(ket_qua(false, 'Lỗi máy chủ, vui lòng thử lại.'), 500);
    }

    tra_json($ketQua);
}

function bac_si_kham_benh($conn): array
{
    return [];
}
