<?php

// Truy van lich lam viec / lich kham. Moi ham nhan $conn lam tham so dau tien.
// Loi truy van nem RuntimeException de controller bat va bao loi cho nguoi dung.

/**
 * Chay mot cau SELECT khong tham so, tra ve mang cac dong.
 *
 * @throws RuntimeException
 */
function lich_kham_lay_nhieu_dong($conn, $sql)
{
    $stmt = $conn->prepare($sql);
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

/**
 * bind_param voi so tham so thay doi duoc (mysqli doi tham so phai truyen theo tham chieu).
 */
function lich_kham_gan_tham_so($stmt, $types, $params)
{
    $references = [$types];
    foreach ($params as $key => $value) {
        $references[] = &$params[$key];
    }

    call_user_func_array([$stmt, 'bind_param'], $references);
}

function lich_kham_lay_danh_sach_chuyen_khoa($conn)
{
    // Mot ten chuyen khoa co the ung voi nhieu machuyenkhoa (moi khoa mot ma),
    // nen gom theo ten de danh sach chi hien thi moi chuyen khoa mot lan.
    $sql = "SELECT MIN(machuyenkhoa) AS machuyenkhoa, tenchuyenkhoa
            FROM CHUYEN_KHOA
            GROUP BY tenchuyenkhoa
            ORDER BY tenchuyenkhoa ASC";

    return lich_kham_lay_nhieu_dong($conn, $sql);
}

function lich_kham_lay_danh_sach_bac_si($conn, $machuyenkhoa = null)
{
    $sql = "SELECT DISTINCT nd.manguoidung, nd.hoten, bs.trinhdo
            FROM BAC_SI bs
            INNER JOIN NGUOI_DUNG nd ON nd.manguoidung = bs.manguoidung
            INNER JOIN BAC_SI_CHUYEN_KHOA bsck
                ON bsck.manguoidung = bs.manguoidung
            INNER JOIN CHUYEN_KHOA ck
                ON ck.machuyenkhoa = bsck.machuyenkhoa";

    if ($machuyenkhoa !== null) {
        // Loc theo ten chuyen khoa de gom ca cac machuyenkhoa cung ten.
        $sql .= " WHERE ck.tenchuyenkhoa = (
                      SELECT tenchuyenkhoa FROM CHUYEN_KHOA WHERE machuyenkhoa = ?
                  )";
    }

    $sql .= " ORDER BY nd.hoten ASC";
    $stmt = $conn->prepare($sql);
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

function lich_kham_tim_khung_gio($conn, $ngay, $machuyenkhoa = null, $manguoidung = null)
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
              AND llv.thoigianbatdau >= ?";
    $types = 'ss';
    $params = [$ngay, thoi_diem_chan_dat_lich()];

    if ($machuyenkhoa !== null) {
        // Loc theo ten chuyen khoa de gom ca cac machuyenkhoa cung ten.
        $sql .= " AND ck.tenchuyenkhoa = (
                      SELECT tenchuyenkhoa FROM CHUYEN_KHOA WHERE machuyenkhoa = ?
                  )";
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

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Không thể chuẩn bị danh sách khung giờ.');
    }

    lich_kham_gan_tham_so($stmt, $types, $params);
    if (!$stmt->execute()) {
        $stmt->close();
        throw new RuntimeException('Không thể tải danh sách khung giờ.');
    }

    $result = $stmt->get_result();
    $slots = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $slots;
}

function lich_kham_lay_chi_tiet_khung_gio($conn, $malichlamviec)
{
    $sql = "SELECT llv.malichlamviec,
                   llv.thoigianbatdau,
                   llv.thoigianketthuc,
                   nd.manguoidung AS mabacsi,
                   nd.hoten AS tenbacsi,
                   bs.trinhdo,
                   bs.gioithieu,
                   GROUP_CONCAT(DISTINCT ck.tenchuyenkhoa ORDER BY ck.tenchuyenkhoa SEPARATOR ', ') AS chuyenkhoa
            FROM LICH_LAM_VIEC llv
            INNER JOIN BAC_SI bs ON bs.manguoidung = llv.manguoidung
            INNER JOIN NGUOI_DUNG nd ON nd.manguoidung = bs.manguoidung
            INNER JOIN BAC_SI_CHUYEN_KHOA bsck
                ON bsck.manguoidung = bs.manguoidung
            INNER JOIN CHUYEN_KHOA ck ON ck.machuyenkhoa = bsck.machuyenkhoa
            WHERE llv.malichlamviec = ?
              AND llv.trangthai = 'con_trong'
              AND llv.thoigianbatdau >= ?
            GROUP BY llv.malichlamviec,
                     llv.thoigianbatdau,
                     llv.thoigianketthuc,
                     nd.manguoidung,
                     nd.hoten,
                     bs.trinhdo,
                     bs.gioithieu
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Không thể chuẩn bị thông tin khung giờ.');
    }

    $stmt->bind_param('is', $malichlamviec, thoi_diem_chan_dat_lich());
    if (!$stmt->execute()) {
        $stmt->close();
        throw new RuntimeException('Không thể tải thông tin khung giờ.');
    }

    $slot = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();

    return $slot;
}

function lich_kham_lay_ho_so_benh_nhan($conn, $manguoidung)
{
    $sql = "SELECT manguoidung, hoten, sdt, email, ngaysinh, gioitinh, diachi, bhyt
            FROM NGUOI_DUNG
            WHERE manguoidung = ? AND mavaitro = 1
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Không thể chuẩn bị hồ sơ bệnh nhân.');
    }

    $stmt->bind_param('i', $manguoidung);
    if (!$stmt->execute()) {
        $stmt->close();
        throw new RuntimeException('Không thể tải hồ sơ bệnh nhân.');
    }

    $patient = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();

    return $patient;
}

function lich_kham_lay_lich_su_dat_kham($conn, $manguoidung)
{
    // Chuyen khoa dung LEFT JOIN de lich van hien khi bac si chua duoc gan chuyen khoa.
    $sql = "SELECT lk.malichkham,
                   lk.trangthai,
                   lk.lydo,
                   lk.lydohuy,
                   lk.ngaytao,
                   lk.ngaycapnhat,
                   llv.malichlamviec,
                   llv.thoigianbatdau,
                   llv.thoigianketthuc,
                   nd.hoten AS tenbacsi,
                   bs.trinhdo,
                   GROUP_CONCAT(DISTINCT ck.tenchuyenkhoa
                                ORDER BY ck.tenchuyenkhoa
                                SEPARATOR ', ') AS chuyenkhoa
            FROM LICH_KHAM lk
            INNER JOIN LICH_LAM_VIEC llv
                ON llv.malichlamviec = lk.malichlamviec
            INNER JOIN BAC_SI bs
                ON bs.manguoidung = llv.manguoidung
            INNER JOIN NGUOI_DUNG nd
                ON nd.manguoidung = bs.manguoidung
            LEFT JOIN BAC_SI_CHUYEN_KHOA bsck
                ON bsck.manguoidung = bs.manguoidung
            LEFT JOIN CHUYEN_KHOA ck
                ON ck.machuyenkhoa = bsck.machuyenkhoa
            WHERE lk.manguoidung = ?
            GROUP BY lk.malichkham,
                     lk.trangthai,
                     lk.lydo,
                     lk.lydohuy,
                     lk.ngaytao,
                     lk.ngaycapnhat,
                     llv.malichlamviec,
                     llv.thoigianbatdau,
                     llv.thoigianketthuc,
                     nd.hoten,
                     bs.trinhdo
            ORDER BY llv.thoigianbatdau DESC, lk.malichkham DESC";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Không thể chuẩn bị danh sách lịch sử đặt khám.');
    }

    $stmt->bind_param('i', $manguoidung);
    if (!$stmt->execute()) {
        $stmt->close();
        throw new RuntimeException('Không thể tải lịch sử đặt khám.');
    }

    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $rows;
}

/**
 * Huy lich cua chinh benh nhan dang dang nhap.
 *
 * Khong dung UPDATE ... INNER JOIN LICH_LAM_VIEC: trigger tren LICH_KHAM co
 * UPDATE vao LICH_LAM_VIEC, va MySQL khong cho mot cau vua doc vua ghi cung
 * bang qua trigger. Vi vay tach thanh hai buoc trong cung transaction:
 *   1. SELECT ... FOR UPDATE khoa dong lich kham va doc gio kham.
 *   2. UPDATE LICH_KHAM theo khoa chinh (khong dung toi LICH_LAM_VIEC).
 *
 * @return bool true neu huy duoc.
 */
function lich_kham_huy_lich($conn, $malichkham, $manguoidung, $lydo)
{
    $conn->begin_transaction();

    try {
        $sql = "SELECT lk.malichkham
                FROM LICH_KHAM lk
                INNER JOIN LICH_LAM_VIEC llv
                    ON llv.malichlamviec = lk.malichlamviec
                WHERE lk.malichkham = ?
                  AND lk.manguoidung = ?
                  AND lk.trangthai IN ('cho_xac_nhan', 'da_xac_nhan')
                  AND llv.thoigianbatdau >= NOW()
                FOR UPDATE";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('Không thể chuẩn bị huỷ lịch khám.');
        }

        $stmt->bind_param('ii', $malichkham, $manguoidung);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('Không thể kiểm tra lịch khám.');
        }

        $conHuyDuoc = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if (!$conHuyDuoc) {
            $conn->rollback();

            return false;
        }

        $update = $conn->prepare(
            "UPDATE LICH_KHAM
             SET trangthai = 'da_huy', lydohuy = ?, nguoihuy = ?
             WHERE malichkham = ? AND manguoidung = ?"
        );
        if (!$update) {
            throw new RuntimeException('Không thể chuẩn bị cập nhật lịch khám.');
        }

        $update->bind_param('siii', $lydo, $manguoidung, $malichkham, $manguoidung);
        if (!$update->execute()) {
            $errorCode = $update->errno;
            $update->close();
            throw new RuntimeException('Không thể huỷ lịch khám.', $errorCode);
        }

        $affected = $update->affected_rows;
        $update->close();
        $conn->commit();

        return $affected === 1;
    } catch (Throwable $exception) {
        $conn->rollback();
        throw $exception;
    }
}

/**
 * Dat lich trong mot giao dich: khoa khung gio, kiem tra con trong, roi ghi.
 *
 * @return int Ma lich kham vua tao.
 * @throws RuntimeException Ma 409 khi khung gio khong con trong.
 */
function lich_kham_dat_lich($conn, $manguoidung, $malichlamviec, $lydo)
{
    $conn->begin_transaction();

    try {
        $sql = "SELECT llv.malichlamviec
                FROM LICH_LAM_VIEC llv
                WHERE llv.malichlamviec = ?
                  AND llv.trangthai = 'con_trong'
                  AND llv.thoigianbatdau >= ?
                  AND NOT EXISTS (
                      SELECT 1
                      FROM LICH_KHAM lk
                      WHERE lk.malichlamviec = llv.malichlamviec
                        AND lk.trangthai IN ('cho_xac_nhan', 'da_xac_nhan', 'da_kham')
                  )
                FOR UPDATE";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('Không thể chuẩn bị kiểm tra khung giờ.');
        }

        $stmt->bind_param('is', $malichlamviec, thoi_diem_chan_dat_lich());
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
        $insertStmt = $conn->prepare($insertSql);
        if (!$insertStmt) {
            throw new RuntimeException('Không thể chuẩn bị tạo lịch khám.');
        }

        $insertStmt->bind_param('iis', $manguoidung, $malichlamviec, $lydo);
        if (!$insertStmt->execute()) {
            $errorCode = $insertStmt->errno;
            $insertStmt->close();
            throw new RuntimeException('Không thể tạo lịch khám.', $errorCode);
        }

        $malichkham = $conn->insert_id;
        $insertStmt->close();
        $conn->commit();

        return $malichkham;
    } catch (Throwable $exception) {
        $conn->rollback();
        throw $exception;
    }
}
