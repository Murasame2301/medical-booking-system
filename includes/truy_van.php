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


// Lấy các khung giờ còn trống của một bác sĩ:
// đang mở (con_trong), ở tương lai, và chưa có lịch khám nào đang giữ chỗ.
// k.malichdangdung chỉ có giá trị khi lịch chưa bị hủy (xem file SQL).
function lay_gio_trong($conn, $id_bacsi) {
    $baygio = date('Y-m-d H:i:s');
    $stmt = $conn->prepare(
        "SELECT l.malichlamviec, l.thoigianbatdau, l.thoigianketthuc
         FROM LICH_LAM_VIEC l
         WHERE l.manguoidung = ?
           AND l.trangthai = 'mo'
           AND l.thoigianbatdau > ?
           AND NOT EXISTS (
               SELECT 1 FROM LICH_KHAM k WHERE k.malichdangdung = l.malichlamviec
           )
         ORDER BY l.thoigianbatdau"
    );
    $stmt->bind_param("is", $id_bacsi, $baygio);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Lưu lịch khám. Trả về false nếu khung giờ vừa bị người khác đặt mất
// (DB không cho hai lịch chưa hủy cùng một khung giờ nên sẽ báo lỗi).
function luu_lich_kham($conn, $id_benhnhan, $id_lichlamviec, $lydo) {
    if ($lydo == "") {
        $lydo = null;
    }
    try {
        $stmt = $conn->prepare("INSERT INTO LICH_KHAM (manguoidung, malichlamviec, lydo) VALUES (?,?,?)");
        $stmt->bind_param("iis", $id_benhnhan, $id_lichlamviec, $lydo);
        $stmt->execute();
        return true;
    } catch (mysqli_sql_exception $e) {
        return false;
    }
}

// Lấy tất cả lịch khám của một bệnh nhân (mới nhất trước).
// Bí danh giữ nguyên tên khóa cũ nên trang hiển thị gần như không phải sửa.
function lay_lich_cua_toi($conn, $id_benhnhan) {
    $stmt = $conn->prepare(
        "SELECT k.malichkham AS ID, k.lydo AS LyDo, k.trangthai AS TrangThai,
                l.thoigianbatdau AS ThoiGian, n.hoten AS TenBacSi,
                (SELECT GROUP_CONCAT(ck.tenchuyenkhoa ORDER BY ck.tenchuyenkhoa SEPARATOR ', ')
                 FROM BAC_SI_CHUYEN_KHOA bc
                 JOIN CHUYEN_KHOA ck ON ck.machuyenkhoa = bc.machuyenkhoa
                 WHERE bc.manguoidung = l.manguoidung) AS ChuyenKhoa
         FROM LICH_KHAM k
         JOIN LICH_LAM_VIEC l ON l.malichlamviec = k.malichlamviec
         JOIN NGUOI_DUNG n ON n.manguoidung = l.manguoidung
         WHERE k.manguoidung = ?
         ORDER BY l.thoigianbatdau DESC"
    );
    $stmt->bind_param("i", $id_benhnhan);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Hủy lịch: chỉ hủy được lịch CỦA CHÍNH MÌNH và còn 'cho_xac_nhan'.
// Phải ghi nguoihuy (hoặc lydohuy) vì DB bắt buộc khi trạng thái là 'da_huy'.
function huy_lich($conn, $id_lich, $id_benhnhan) {
    $stmt = $conn->prepare(
        "UPDATE LICH_KHAM
         SET trangthai = 'da_huy', nguoihuy = ?, lydohuy = 'Bệnh nhân tự hủy'
         WHERE malichkham = ? AND manguoidung = ? AND trangthai = 'cho_xac_nhan'"
    );
    $stmt->bind_param("iii", $id_benhnhan, $id_lich, $id_benhnhan);
    $stmt->execute();
    return $stmt->affected_rows > 0;
}

// Kiểm tra một khung giờ có đúng là của bác sĩ này và còn trống không
function khung_gio_con_trong($conn, $id_bacsi, $id_gio) {
    foreach (lay_gio_trong($conn, $id_bacsi) as $g) {
        if ($g['malichlamviec'] == $id_gio) {
            return true;
        }
    }
    return false;
}

// Lấy thông tin cá nhân của một người dùng
function lay_thong_tin_ca_nhan($conn, $id) {
    $stmt = $conn->prepare(
        "SELECT hoten, sdt, email, ngaysinh, gioitinh, diachi, cccd, dantoc, bhyt
         FROM NGUOI_DUNG WHERE manguoidung = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Cập nhật thông tin cá nhân. $d là mảng gồm: hoten, ngaysinh, gioitinh, email, diachi, cccd, dantoc, bhyt
function cap_nhat_thong_tin_ca_nhan($conn, $id, $d) {
    // Ô nào để trống thì lưu NULL (đặc biệt cần cho email vì cột email không được trùng)
    foreach ($d as $khoa => $giatri) {
        if ($giatri === '') {
            $d[$khoa] = null;
        }
    }
    try {
        $stmt = $conn->prepare(
            "UPDATE NGUOI_DUNG
             SET hoten=?, ngaysinh=?, gioitinh=?, email=?, diachi=?, cccd=?, dantoc=?, bhyt=?
             WHERE manguoidung=?"
        );
        $stmt->bind_param("ssssssssi", $d['hoten'], $d['ngaysinh'], $d['gioitinh'], $d['email'],
                          $d['diachi'], $d['cccd'], $d['dantoc'], $d['bhyt'], $id);
        $stmt->execute();
        return true;
    } catch (mysqli_sql_exception $e) {
        return false;
    }
}

// Lấy chuỗi mật khẩu đã mã hóa của một người dùng (để kiểm tra mật khẩu hiện tại)
function lay_mat_khau($conn, $id) {
    $stmt = $conn->prepare("SELECT matkhau FROM NGUOI_DUNG WHERE manguoidung = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $dong = $stmt->get_result()->fetch_assoc();
    return $dong ? $dong['matkhau'] : '';
}

// Đổi mật khẩu (nhận vào chuỗi ĐÃ mã hóa)
function doi_mat_khau($conn, $id, $matkhau_ma_hoa) {
    $stmt = $conn->prepare("UPDATE NGUOI_DUNG SET matkhau = ? WHERE manguoidung = ?");
    $stmt->bind_param("si", $matkhau_ma_hoa, $id);
    $stmt->execute();
}

// Tài khoản có đang bị khóa (hoặc không còn tồn tại) không
function tai_khoan_bi_khoa($conn, $id) {
    $stmt = $conn->prepare("SELECT trangthai FROM NGUOI_DUNG WHERE manguoidung = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $dong = $stmt->get_result()->fetch_assoc();
    return !$dong || $dong['trangthai'] == 'bi_khoa';
}