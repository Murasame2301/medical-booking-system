<?php
/* Tien ich truy van: giu kieu goi ngan gon (truy_van / truy_van_mot / thi_hanh)
   cho cac model, nhung van nhan $conn tu ben ngoai. */

if (!function_exists('truy_van')) {
    /**
     * Chay SELECT, tra ve mang cac dong.
     */
    function truy_van(mysqli $conn, string $sql, string $kieu = '', array $thamSo = []): array
    {
        $stmt = $conn->prepare($sql);
        if ($thamSo !== []) {
            $stmt->bind_param($kieu, ...$thamSo);
        }
        $stmt->execute();
        $ketQua = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $ketQua;
    }
}

if (!function_exists('truy_van_mot')) {
    /**
     * Chay SELECT, tra ve dong dau tien hoac null.
     */
    function truy_van_mot(mysqli $conn, string $sql, string $kieu = '', array $thamSo = []): ?array
    {
        $rows = truy_van($conn, $sql, $kieu, $thamSo);

        return $rows[0] ?? null;
    }
}

if (!function_exists('thi_hanh')) {
    /**
     * Chay INSERT/UPDATE/DELETE, tra ve mysqli_stmt da execute (ben goi tu dong
     * lay insert_id / affected_rows roi close).
     */
    function thi_hanh(mysqli $conn, string $sql, string $kieu = '', array $thamSo = []): mysqli_stmt
    {
        $stmt = $conn->prepare($sql);
        if ($thamSo !== []) {
            $stmt->bind_param($kieu, ...$thamSo);
        }
        $stmt->execute();

        return $stmt;
    }
}
