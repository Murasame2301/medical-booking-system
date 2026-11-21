<?php
//Tiện ích truy vấn dùng chung cho các model: truy_van, truy_van_mot, thi_hanh.

if (!function_exists('truy_van')) {
    //Chạy SELECT, trả về mảng các dòng.
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
    //Chạy SELECT, trả về dòng đầu tiên hoặc null.
    function truy_van_mot(mysqli $conn, string $sql, string $kieu = '', array $thamSo = []): ?array
    {
        $rows = truy_van($conn, $sql, $kieu, $thamSo);

        return $rows[0] ?? null;
    }
}

if (!function_exists('thi_hanh')) {
    //Chạy INSERT/UPDATE/DELETE, trả về stmt để bên gọi lấy insert_id, affected_rows rồi close.
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
