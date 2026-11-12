<?php
// Đếm nhanh các con số cho trang tổng quan, chỉ cần MỘT câu truy vấn
// (mỗi dấu ngoặc con là một phép đếm riêng, kết quả là một dòng duy nhất)
function thong_ke_tong_quan($conn) {
    $ketqua = $conn->query(
        "SELECT
            (SELECT COUNT(*) FROM NGUOI_DUNG WHERE mavaitro = 1) AS benh_nhan,
            (SELECT COUNT(*) FROM BAC_SI) AS bac_si,
            (SELECT COUNT(*) FROM LICH_KHAM WHERE trangthai = 'cho_xac_nhan') AS lich_cho,
            (SELECT COUNT(*) FROM NGUOI_DUNG WHERE trangthai = 'bi_khoa') AS tai_khoan_khoa"
    );
    return $ketqua->fetch_assoc();
}


// Danh sách vai trò dạng [mã => tên hiển thị], ví dụ [1 => 'Bệnh nhân', 2 => 'Bác sĩ', ...]
function lay_ds_vai_tro($conn) {
    $ketqua = $conn->query("SELECT mavaitro, motavaitro FROM VAI_TRO ORDER BY mavaitro");
    $ds = [];
    foreach ($ketqua->fetch_all(MYSQLI_ASSOC) as $dong) {
        $ds[$dong['mavaitro']] = $dong['motavaitro'];
    }
    return $ds;
}

// Danh sách người dùng. $tukhoa tìm trong họ tên hoặc SĐT; $vaitro = 0 là tất cả vai trò.
function lay_ds_nguoi_dung($conn, $tukhoa = '', $vaitro = 0) {
    $tim = '%' . $tukhoa . '%';   // % nghĩa là "chứa chuỗi này ở bất kỳ vị trí nào"
    $stmt = $conn->prepare(
        "SELECT n.manguoidung AS ID, n.hoten AS HoTen, n.sdt AS SDT, n.trangthai AS TrangThai,
                n.ngaytao AS NgayTao, v.tenvaitro AS VaiTro, v.motavaitro AS TenVaiTro
         FROM NGUOI_DUNG n
         JOIN VAI_TRO v ON v.mavaitro = n.mavaitro
         WHERE (n.hoten LIKE ? OR n.sdt LIKE ?)
           AND (? = 0 OR n.mavaitro = ?)
         ORDER BY n.ngaytao DESC, n.manguoidung DESC
         LIMIT 200"
    );
    $stmt->bind_param("ssii", $tim, $tim, $vaitro, $vaitro);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Vai trò của một người dùng ('benh_nhan', 'bac_si', 'quan_tri'), null nếu không có người đó
function lay_vai_tro_cua($conn, $id) {
    $stmt = $conn->prepare(
        "SELECT v.tenvaitro FROM NGUOI_DUNG n
         JOIN VAI_TRO v ON v.mavaitro = n.mavaitro
         WHERE n.manguoidung = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $dong = $stmt->get_result()->fetch_assoc();
    return $dong ? $dong['tenvaitro'] : null;
}

// Đặt trạng thái tài khoản: 'hoat_dong' hoặc 'bi_khoa'
function dat_trang_thai_tai_khoan($conn, $id, $trangthai) {
    $stmt = $conn->prepare("UPDATE NGUOI_DUNG SET trangthai = ? WHERE manguoidung = ?");
    $stmt->bind_param("si", $trangthai, $id);
    $stmt->execute();
}

// Danh sách TẤT CẢ bác sĩ (kể cả đang bị khóa) cho trang quản lý
function lay_ds_bac_si_quan_tri($conn) {
    $ketqua = $conn->query(
        "SELECT n.manguoidung AS ID, n.hoten AS HoTen, n.sdt AS SDT, n.trangthai AS TrangThai,
                b.trinhdo AS TrinhDo,
                GROUP_CONCAT(ck.tenchuyenkhoa ORDER BY ck.tenchuyenkhoa SEPARATOR ', ') AS ChuyenKhoa
         FROM BAC_SI b
         JOIN NGUOI_DUNG n ON n.manguoidung = b.manguoidung
         LEFT JOIN BAC_SI_CHUYEN_KHOA bc ON bc.manguoidung = b.manguoidung
         LEFT JOIN CHUYEN_KHOA ck ON ck.machuyenkhoa = bc.machuyenkhoa
         GROUP BY n.manguoidung, n.hoten, n.sdt, n.trangthai, b.trinhdo
         ORDER BY n.hoten"
    );
    return $ketqua->fetch_all(MYSQLI_ASSOC);
}

// Thông tin chi tiết một bác sĩ để điền vào form sửa (null nếu không phải bác sĩ).
// Tên khóa viết thường, trùng với tên ô trong form.
function lay_bac_si_chi_tiet($conn, $id) {
    $stmt = $conn->prepare(
        "SELECT n.manguoidung AS ID, n.hoten AS hoten, n.sdt AS sdt, n.email AS email,
                b.trinhdo AS trinhdo, b.gioithieu AS gioithieu
         FROM BAC_SI b
         JOIN NGUOI_DUNG n ON n.manguoidung = b.manguoidung
         WHERE b.manguoidung = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Mảng mã các chuyên khoa của một bác sĩ, ví dụ [1, 2]
function lay_chuyen_khoa_cua_bac_si($conn, $id) {
    $stmt = $conn->prepare("SELECT machuyenkhoa FROM BAC_SI_CHUYEN_KHOA WHERE manguoidung = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'machuyenkhoa');
}

// Hàm phụ: ghi danh sách chuyên khoa cho bác sĩ (dùng bên trong giao dịch của hai hàm dưới)
function gan_chuyen_khoa($conn, $id_bacsi, $ds_chuyenkhoa) {
    $stmt = $conn->prepare("INSERT INTO BAC_SI_CHUYEN_KHOA (manguoidung, machuyenkhoa) VALUES (?, ?)");
    foreach ($ds_chuyenkhoa as $id_ck) {
        $stmt->bind_param("ii", $id_bacsi, $id_ck);
        $stmt->execute();
    }
}

// Thêm bác sĩ mới: ghi NGUOI_DUNG, BAC_SI, BAC_SI_CHUYEN_KHOA trong một giao dịch
function tao_bac_si($conn, $d, $matkhau_ma_hoa, $ds_chuyenkhoa) {
    $email = null_neu_rong($d['email']);
    $trinhdo = null_neu_rong($d['trinhdo']);
    $gioithieu = null_neu_rong($d['gioithieu']);

    $conn->begin_transaction();
    try {
        // mavaitro = 2 là bác sĩ (theo bảng VAI_TRO)
        $stmt = $conn->prepare("INSERT INTO NGUOI_DUNG (mavaitro, hoten, sdt, email, matkhau) VALUES (2,?,?,?,?)");
        $stmt->bind_param("ssss", $d['hoten'], $d['sdt'], $email, $matkhau_ma_hoa);
        $stmt->execute();
        $id = $conn->insert_id;

        $stmt = $conn->prepare("INSERT INTO BAC_SI (manguoidung, trinhdo, gioithieu) VALUES (?,?,?)");
        $stmt->bind_param("iss", $id, $trinhdo, $gioithieu);
        $stmt->execute();

        gan_chuyen_khoa($conn, $id, $ds_chuyenkhoa);

        $conn->commit();
        return true;
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        return false;
    }
}

// Sửa bác sĩ. $matkhau_ma_hoa = null nghĩa là giữ nguyên mật khẩu cũ.
function cap_nhat_bac_si($conn, $id, $d, $matkhau_ma_hoa, $ds_chuyenkhoa) {
    $email = null_neu_rong($d['email']);
    $trinhdo = null_neu_rong($d['trinhdo']);
    $gioithieu = null_neu_rong($d['gioithieu']);

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("UPDATE NGUOI_DUNG SET hoten=?, sdt=?, email=? WHERE manguoidung=?");
        $stmt->bind_param("sssi", $d['hoten'], $d['sdt'], $email, $id);
        $stmt->execute();

        if ($matkhau_ma_hoa !== null) {
            $stmt = $conn->prepare("UPDATE NGUOI_DUNG SET matkhau=? WHERE manguoidung=?");
            $stmt->bind_param("si", $matkhau_ma_hoa, $id);
            $stmt->execute();
        }

        $stmt = $conn->prepare("UPDATE BAC_SI SET trinhdo=?, gioithieu=? WHERE manguoidung=?");
        $stmt->bind_param("ssi", $trinhdo, $gioithieu, $id);
        $stmt->execute();

        // Chuyên khoa: xóa hết cái cũ rồi ghi lại danh sách mới
        $stmt = $conn->prepare("DELETE FROM BAC_SI_CHUYEN_KHOA WHERE manguoidung=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        gan_chuyen_khoa($conn, $id, $ds_chuyenkhoa);

        $conn->commit();
        return true;
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        return false;
    }
}