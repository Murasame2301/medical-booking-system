<?php

//Truy vấn bảng NGUOI_DUNG.
//Lỗi truy vấn ném RuntimeException cho controller bắt.

//Tìm tài khoản theo số điện thoại, kèm vai trò và trạng thái cho trang đăng nhập.
function nguoi_dung_tim_theo_sdt($conn, $phonenumber)
{
    $sql = "SELECT manguoidung, mavaitro, hoten, sdt, matkhau, trangthai
            FROM NGUOI_DUNG
            WHERE sdt = ?
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Không thể chuẩn bị truy vấn người dùng.');
    }

    $stmt->bind_param('s', $phonenumber);
    if (!$stmt->execute()) {
        $stmt->close();
        throw new RuntimeException('Không thể thực hiện truy vấn người dùng.');
    }

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    return $user ?: null;
}

//Tạo tài khoản bệnh nhân mới.
//Trả về mã người dùng vừa tạo.
function nguoi_dung_tao_tai_khoan($conn, $fullname, $phonenumber, $password)
{
    $sql = "INSERT INTO NGUOI_DUNG(mavaitro, hoten, sdt, matkhau)
            VALUES(1, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException(
            'Không thể chuẩn bị truy vấn tạo tài khoản.',
            $conn->errno
        );
    }

    $stmt->bind_param('sss', $fullname, $phonenumber, $password);
    if (!$stmt->execute()) {
        $errorCode = $stmt->errno;
        $stmt->close();
        throw new RuntimeException('Không thể tạo tài khoản.', $errorCode);
    }

    $manguoidung = $conn->insert_id;
    $stmt->close();

    return $manguoidung;
}

//Lấy hồ sơ bệnh nhân theo mã người dùng.
function nguoi_dung_lay_ho_so($conn, $manguoidung)
{
    $sql = "SELECT hoten, ngaysinh, gioitinh, email, diachi, bhyt, cccd, dantoc
            FROM NGUOI_DUNG
            WHERE manguoidung = ? AND mavaitro = 1
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Không thể chuẩn bị dữ liệu hồ sơ.');
    }

    $stmt->bind_param('i', $manguoidung);
    if (!$stmt->execute()) {
        $stmt->close();
        throw new RuntimeException('Không thể tải thông tin hồ sơ.');
    }

    $result = $stmt->get_result();
    $hoSo = $result->fetch_assoc();
    $stmt->close();

    return $hoSo ?: null;
}

//Lấy đầy đủ thông tin tài khoản, gồm cả mật khẩu đã hash.
//Không đưa cột matkhau vào view.
function nguoi_dung_lay_tai_khoan($conn, $manguoidung)
{
    $sql = "SELECT manguoidung, mavaitro, hoten, sdt, email, matkhau, ngaysinh,
                   gioitinh, diachi, ngaytao, trangthai, cccd, dantoc, bhyt
            FROM NGUOI_DUNG
            WHERE manguoidung = ?
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Không thể chuẩn bị truy vấn tài khoản.');
    }

    $stmt->bind_param('i', $manguoidung);
    if (!$stmt->execute()) {
        $stmt->close();
        throw new RuntimeException('Không thể tải thông tin tài khoản.');
    }

    $result = $stmt->get_result();
    $taiKhoan = $result->fetch_assoc();
    $stmt->close();

    return $taiKhoan ?: null;
}

//Đường dẫn ảnh đại diện của một tài khoản.
function nguoi_dung_lay_anh($conn, $manguoidung)
{
    $r = truy_van_mot($conn, "SELECT anhdaidien FROM NGUOI_DUNG WHERE manguoidung = ?", 'i', [$manguoidung]);

    return $r['anhdaidien'] ?? null;
}

//Cập nhật mật khẩu đã hash.
function nguoi_dung_cap_nhat_mat_khau($conn, $manguoidung, $matKhauHash)
{
    $sql = "UPDATE NGUOI_DUNG
            SET matkhau = ?
            WHERE manguoidung = ? AND mavaitro = 1";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Không thể chuẩn bị cập nhật mật khẩu.');
    }

    $stmt->bind_param('si', $matKhauHash, $manguoidung);

    if (!$stmt->execute()) {
        $errorCode = $stmt->errno;
        $stmt->close();
        throw new RuntimeException('Không thể cập nhật mật khẩu.', $errorCode);
    }

    $stmt->close();
}

//Cập nhật hồ sơ bệnh nhân
function nguoi_dung_cap_nhat_ho_so(
    $conn,
    $manguoidung,
    $hoten,
    $ngaysinh,
    $gioitinh,
    $email,
    $diachi,
    $bhyt,
    $cccd = null,
    $dantoc = null
) {
    $sql = "UPDATE NGUOI_DUNG
            SET hoten = ?, ngaysinh = ?, gioitinh = ?, email = NULLIF(?, ''),
                diachi = ?, bhyt = NULLIF(?, '')";
    $types = 'ssssss';
    $params = [$hoten, $ngaysinh, $gioitinh, $email, $diachi, $bhyt];

    if ($cccd !== null || $dantoc !== null) {
        $sql .= ", cccd = NULLIF(?, ''), dantoc = NULLIF(?, '')";
        $types .= 'ss';
        $params[] = (string) $cccd;
        $params[] = (string) $dantoc;
    }

    $sql .= " WHERE manguoidung = ? AND mavaitro = 1";
    $types .= 'i';
    $params[] = $manguoidung;

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Không thể chuẩn bị cập nhật thông tin cá nhân.');
    }

    $stmt->bind_param($types, ...$params);

    if (!$stmt->execute()) {
        $errorCode = $stmt->errno;
        $stmt->close();
        throw new RuntimeException('Không thể cập nhật thông tin cá nhân.', $errorCode);
    }

    $stmt->close();
}