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
}
