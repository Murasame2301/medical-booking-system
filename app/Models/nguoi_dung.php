<?php

// Truy van bang NGUOI_DUNG. Moi ham nhan $conn lam tham so dau tien.
// Loi truy van nem RuntimeException de controller bat va bao loi cho nguoi dung.

/**
 * Tim tai khoan theo so dien thoai.
 *
 * @return array|null
 * @throws RuntimeException Khi khong the thuc hien truy van.
 */
function nguoi_dung_tim_theo_sdt($conn, $phonenumber)
{
    $sql = "SELECT manguoidung, sdt, matkhau
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

/**
 * Tao tai khoan benh nhan moi.
 *
 * @return int Ma nguoi dung vua tao.
 * @throws RuntimeException Khi khong the tao tai khoan.
 */
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

/**
 * Lay ho so benh nhan theo ma nguoi dung.
 *
 * @return array|null
 * @throws RuntimeException Khi khong the truy van ho so.
 */
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

/**
 * Lay day du thong tin tai khoan, gom ca mat khau da hash de controller xac thuc.
 * Khong dua cot matkhau vao view.
 *
 * @return array|null
 * @throws RuntimeException Khi khong the truy van tai khoan.
 */
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

/**
 * Cap nhat mat khau da hash.
 *
 * @throws RuntimeException Khi khong the cap nhat mat khau.
 */
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

/**
 * Cap nhat thong tin ho so benh nhan.
 * $cccd va $dantoc nhan null de giu nguyen gia tri hien co trong DB
 * (luong tao-ho-so.php khong gui hai truong nay).
 *
 * @throws RuntimeException Khi khong the cap nhat; ma loi la errno cua MySQL.
 */
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
