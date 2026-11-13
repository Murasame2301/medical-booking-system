<?php

class LichKham
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function layDanhSachChuyenKhoa()
    {
        $sql = "SELECT machuyenkhoa, tenchuyenkhoa
                FROM CHUYEN_KHOA
                ORDER BY tenchuyenkhoa ASC";

        return $this->layNhieuDong($sql);
    }

    public function layDanhSachBacSi($machuyenkhoa = null)
    {
        $sql = "SELECT DISTINCT nd.manguoidung, nd.hoten, bs.trinhdo
                FROM BAC_SI bs
                INNER JOIN NGUOI_DUNG nd ON nd.manguoidung = bs.manguoidung
                INNER JOIN BAC_SI_CHUYEN_KHOA bsck
                    ON bsck.manguoidung = bs.manguoidung";

        if ($machuyenkhoa !== null) {
            $sql .= " WHERE bsck.machuyenkhoa = ?";
        }

        $sql .= " ORDER BY nd.hoten ASC";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('Không thể chuẩn bị danh sách bác sĩ.');
        }

        if ($machuyenkhoa !== null) {
            $stmt->bind_param('i', $machuyenkhoa);
        }

        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('Không thể tải danh sách bác sĩ.');
        }

        $result = $stmt->get_result();
        $doctors = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $doctors;
    }

    public function timKhungGio($ngay, $machuyenkhoa = null, $manguoidung = null)
    {
        $sql = "SELECT llv.malichlamviec,
                       llv.thoigianbatdau,
                       llv.thoigianketthuc,
                       nd.manguoidung AS mabacsi,
                       nd.hoten AS tenbacsi,
                       bs.trinhdo,
                       GROUP_CONCAT(DISTINCT ck.tenchuyenkhoa ORDER BY ck.tenchuyenkhoa SEPARATOR ', ') AS chuyenkhoa
                FROM LICH_LAM_VIEC llv
                INNER JOIN BAC_SI bs ON bs.manguoidung = llv.manguoidung
                INNER JOIN NGUOI_DUNG nd ON nd.manguoidung = bs.manguoidung
                INNER JOIN BAC_SI_CHUYEN_KHOA bsck
                    ON bsck.manguoidung = bs.manguoidung
                INNER JOIN CHUYEN_KHOA ck ON ck.machuyenkhoa = bsck.machuyenkhoa
                WHERE llv.trangthai = 'con_trong'
                  AND DATE(llv.thoigianbatdau) = ?
                  AND llv.thoigianbatdau >= NOW()
                  AND NOT EXISTS (
                      SELECT 1
                      FROM LICH_KHAM lk
                      WHERE lk.malichlamviec = llv.malichlamviec
                        AND lk.trangthai IN ('cho_xac_nhan', 'da_xac_nhan', 'da_kham')
                  )";
        $types = 's';
        $params = [$ngay];

        if ($machuyenkhoa !== null) {
            $sql .= " AND bsck.machuyenkhoa = ?";
            $types .= 'i';
            $params[] = $machuyenkhoa;
        }

        if ($manguoidung !== null) {
            $sql .= " AND bs.manguoidung = ?";
            $types .= 'i';
            $params[] = $manguoidung;
        }

        $sql .= " GROUP BY llv.malichlamviec,
                           llv.thoigianbatdau,
                           llv.thoigianketthuc,
                           nd.manguoidung,
                           nd.hoten,
                           bs.trinhdo
                  ORDER BY llv.thoigianbatdau ASC, nd.hoten ASC";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('Không thể chuẩn bị danh sách khung giờ.');
        }

        $this->bindParams($stmt, $types, $params);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('Không thể tải danh sách khung giờ.');
        }

        $result = $stmt->get_result();
        $slots = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $slots;
    }

    public function coBenhNhan($manguoidung)
    {
        $sql = "SELECT 1 FROM BENH_NHAN WHERE manguoidung = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('Không thể kiểm tra hồ sơ bệnh nhân.');
        }

        $stmt->bind_param('i', $manguoidung);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('Không thể kiểm tra hồ sơ bệnh nhân.');
        }

        $hasPatientProfile = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        return $hasPatientProfile;
    }

    public function datLich($manguoidung, $malichlamviec, $lydo)
    {
        $this->conn->begin_transaction();

        try {
            $sql = "SELECT llv.malichlamviec
                    FROM LICH_LAM_VIEC llv
                    WHERE llv.malichlamviec = ?
                      AND llv.trangthai = 'con_trong'
                      AND llv.thoigianbatdau >= NOW()
                      AND NOT EXISTS (
                          SELECT 1
                          FROM LICH_KHAM lk
                          WHERE lk.malichlamviec = llv.malichlamviec
                            AND lk.trangthai IN ('cho_xac_nhan', 'da_xac_nhan', 'da_kham')
                      )
                    FOR UPDATE";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new RuntimeException('Không thể chuẩn bị kiểm tra khung giờ.');
            }

            $stmt->bind_param('i', $malichlamviec);
            if (!$stmt->execute()) {
                $stmt->close();
                throw new RuntimeException('Không thể kiểm tra khung giờ.');
            }

            $slotExists = $stmt->get_result()->num_rows > 0;
            $stmt->close();

            if (!$slotExists) {
                throw new RuntimeException('Khung giờ này không còn trống.', 409);
            }

            $insertSql = "INSERT INTO LICH_KHAM(manguoidung, malichlamviec, lydo)
                          VALUES(?, ?, NULLIF(?, ''))";
            $insertStmt = $this->conn->prepare($insertSql);
            if (!$insertStmt) {
                throw new RuntimeException('Không thể chuẩn bị tạo lịch khám.');
            }

            $insertStmt->bind_param('iis', $manguoidung, $malichlamviec, $lydo);
            if (!$insertStmt->execute()) {
                $errorCode = $insertStmt->errno;
                $insertStmt->close();
                throw new RuntimeException('Không thể tạo lịch khám.', $errorCode);
            }

            $malichkham = $this->conn->insert_id;
            $insertStmt->close();
            $this->conn->commit();

            return $malichkham;
        } catch (Throwable $exception) {
            $this->conn->rollback();
            throw $exception;
        }
    }

    private function layNhieuDong($sql)
    {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('Không thể chuẩn bị dữ liệu bộ lọc.');
        }

        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('Không thể tải dữ liệu bộ lọc.');
        }

        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }

    private function bindParams($stmt, $types, $params)
    {
        $references = [$types];
        foreach ($params as $key => $value) {
            $references[] = &$params[$key];
        }

        call_user_func_array([$stmt, 'bind_param'], $references);
    }
}
