<?php
//Trang tổng quan bác sĩ
require_once __DIR__ . '/../../Helpers/utils.php';
require_once __DIR__ . '/../../Models/lich_kham_bac_si.php';

//Dữ liệu của một ngày: thống kê, danh sách lịch và khung giờ còn trống.
function bac_si_dashboard_du_lieu_ngay($conn, int $maBacSi, string $ngay): array
{
    if (!hop_le_ngay($ngay)) {
        return ket_qua(false, 'Ngày không hợp lệ.');
    }

    return ket_qua(true, '', [
        'thong_ke' => lich_kham_thong_ke_ngay($conn, $maBacSi, $ngay),
        'danh_sach_lich' => lich_kham_theo_ngay($conn, $maBacSi, $ngay),
        'lich_con_trong' => lich_lam_viec_lay_con_trong($conn, $maBacSi, $ngay),
    ]);
}

//Xử lý AJAX
function bac_si_tong_quan_ajax($conn): void
{
    $maBacSi = ma_nguoi_dung_hien_tai();
    $ngay = (string) ($_GET['ngay'] ?? date('Y-m-d'));

    if ($maBacSi <= 0) {
        tra_json(ket_qua(false, 'Không tìm thấy thông tin bác sĩ.'));
    }

    try {
        tra_json(bac_si_dashboard_du_lieu_ngay($conn, $maBacSi, $ngay));
    } catch (Throwable $e) {
        error_log('tong-quan: ' . $e->getMessage());
        tra_json(ket_qua(false, 'Lỗi máy chủ, vui lòng thử lại.'), 500);
    }
}

//Render trang (JS trong assets/js/bac-si/dashboard.js tự gọi API ở trên).
function bac_si_tong_quan($conn): array
{
    return [];
}