<?php

class NguoiDung
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    /**
     * Tìm tài khoản theo số điện thoại.
     *
     * @return array|null
     * @throws RuntimeException Khi không thể thực hiện truy vấn.
     */
    public function timTheoSoDienThoai($phonenumber)
    {
        $sql = "SELECT manguoidung, sdt, matkhau
                FROM NGUOI_DUNG
                WHERE sdt = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
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
     * Tạo tài khoản bệnh nhân mới.
     *
     * @return int Mã người dùng vừa tạo.
     * @throws RuntimeException Khi không thể tạo tài khoản.
     */
    public function taoTaiKhoan($fullname, $phonenumber, $password)
    {
        $sql = "INSERT INTO NGUOI_DUNG(mavaitro, hoten, sdt, matkhau)
                VALUES(1, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException(
                'Không thể chuẩn bị truy vấn tạo tài khoản.',
                $this->conn->errno
            );
        }

        $stmt->bind_param('sss', $fullname, $phonenumber, $password);
        if (!$stmt->execute()) {
            $errorCode = $stmt->errno;
            $stmt->close();
            throw new RuntimeException('Không thể tạo tài khoản.', $errorCode);
        }

        $manguoidung = $this->conn->insert_id;
        $stmt->close();

        return $manguoidung;
    }

    /**
     * Lấy hồ sơ bệnh nhân theo mã người dùng.
     *
     * @return array|null
     * @throws RuntimeException Khi không thể truy vấn hồ sơ.
     */
    public function layHoSo($manguoidung)
    {
        $sql = "SELECT hoten, ngaysinh, gioitinh, email, diachi, bhyt, cccd, dantoc
                FROM NGUOI_DUNG
                WHERE manguoidung = ? AND mavaitro = 1
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
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
     * Lấy đầy đủ thông tin tài khoản, gồm cả mật khẩu đã hash để controller xác thực.
     * Không đưa cột matkhau vào view.
     *
     * @return array|null
     * @throws RuntimeException Khi không thể truy vấn tài khoản.
     */
    public function layTaiKhoan($manguoidung)
    {
        $sql = "SELECT manguoidung, mavaitro, hoten, sdt, email, matkhau, ngaysinh,
                       gioitinh, diachi, ngaytao, trangthai, cccd, dantoc, bhyt
                FROM NGUOI_DUNG
                WHERE manguoidung = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
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
     * Cập nhật mật khẩu đã hash.
     *
     * @throws RuntimeException Khi không thể cập nhật mật khẩu.
     */
    public function capNhatMatKhau($manguoidung, $matKhauHash)
    {
        $sql = "UPDATE NGUOI_DUNG
                SET matkhau = ?
                WHERE manguoidung = ? AND mavaitro = 1";

        $stmt = $this->conn->prepare($sql);
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
     * Cập nhật thông tin hồ sơ bệnh nhân.
     * $cccd và $dantoc nhận null để giữ nguyên giá trị hiện có trong DB
     * (luồng tao-ho-so.php không gửi hai trường này).
     *
     * @throws RuntimeException Khi không thể cập nhật; mã lỗi là errno của MySQL.
     */
    public function capNhatHoSo(
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

        $stmt = $this->conn->prepare($sql);
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
}
