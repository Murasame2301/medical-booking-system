<?php
function tim_nguoi_dung($conn, $sdt) {
    $stmt = $conn->prepare(
        "SELECT n.manguoidung AS ID, n.hoten AS HoTen, n.matkhau AS MatKhau,
                n.trangthai AS TrangThai, v.tenvaitro AS VaiTro
         FROM NGUOI_DUNG n
         JOIN VAI_TRO v ON v.mavaitro = n.mavaitro
         WHERE n.sdt = ?"
    );
    $stmt->bind_param("s", $sdt);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function da_co_nguoi_dung($conn, $cot, $giatri, $bo_qua_id = 0) {
    $cot_cho_phep = ['sdt', 'email', 'cccd', 'bhyt'];
    if (!in_array($cot, $cot_cho_phep, true)) {
        throw new InvalidArgumentException('Cột không hợp lệ: ' . $cot);
    }
    $stmt = $conn->prepare("SELECT manguoidung FROM NGUOI_DUNG WHERE $cot = ? AND manguoidung <> ?");
    $stmt->bind_param("si", $giatri, $bo_qua_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc() ? true : false;
}

function email_da_dung($conn, $email, $bo_qua_id = 0) {
    return da_co_nguoi_dung($conn, 'email', $email, $bo_qua_id);
}

function tao_benh_nhan($conn, $hoten, $sdt, $email, $matkhau_ma_hoa, $ngaysinh) {
    $email = null_neu_rong($email);
    try {
        $stmt = $conn->prepare("INSERT INTO NGUOI_DUNG (mavaitro, hoten, sdt, email, matkhau, ngaysinh) VALUES (1,?,?,?,?,?)");
        $stmt->bind_param("sssss", $hoten, $sdt, $email, $matkhau_ma_hoa, $ngaysinh);
        $stmt->execute();
        return true;
    } catch (mysqli_sql_exception $e) {
        return false;
    }
}

// Lấy danh sách chuyên khoa (kèm ID để làm bộ lọc)
function lay_chuyen_khoa($conn) {
    $ketqua = $conn->query("SELECT machuyenkhoa AS ID, tenchuyenkhoa AS ChuyenKhoa FROM CHUYEN_KHOA ORDER BY tenchuyenkhoa");
    return $ketqua->fetch_all(MYSQLI_ASSOC);
}

// Lấy danh sách bác sĩ. $id_chuyenkhoa = 0 nghĩa là lấy tất cả,
// khác 0 thì chỉ lấy bác sĩ thuộc chuyên khoa đó.
function lay_bac_si($conn, $id_chuyenkhoa = 0) {
    $stmt = $conn->prepare(
        "SELECT n.manguoidung AS ID, n.hoten AS HoTen,
                GROUP_CONCAT(ck.tenchuyenkhoa ORDER BY ck.tenchuyenkhoa SEPARATOR ', ') AS ChuyenKhoa
         FROM BAC_SI b
         JOIN NGUOI_DUNG n ON n.manguoidung = b.manguoidung
         LEFT JOIN BAC_SI_CHUYEN_KHOA bc ON bc.manguoidung = b.manguoidung
         LEFT JOIN CHUYEN_KHOA ck ON ck.machuyenkhoa = bc.machuyenkhoa
         WHERE n.trangthai = 'hoat_dong'
           AND (? = 0 OR EXISTS (
                SELECT 1 FROM BAC_SI_CHUYEN_KHOA x
                WHERE x.manguoidung = b.manguoidung AND x.machuyenkhoa = ?
           ))
         GROUP BY n.manguoidung, n.hoten
         ORDER BY n.hoten"
    );
    $stmt->bind_param("ii", $id_chuyenkhoa, $id_chuyenkhoa);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}


// Tài khoản có đang bị khóa (hoặc không còn tồn tại) không
function tai_khoan_bi_khoa($conn, $id) {
    $stmt = $conn->prepare("SELECT trangthai FROM NGUOI_DUNG WHERE manguoidung = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $dong = $stmt->get_result()->fetch_assoc();
    return !$dong || $dong['trangthai'] == 'bi_khoa';
}